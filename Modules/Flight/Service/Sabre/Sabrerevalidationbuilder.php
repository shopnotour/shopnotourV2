<?php

namespace Modules\Flight\Service\Sabre;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Booking\Models\Booking;
class Sabrerevalidationbuilder
{

    private const DEFAULT_PCC = '27YK';
    private const DEFAULT_REQUEST_TYPE = '50ITINS';
    private const VERIFICATION_LOGIC = 'B';

    private Booking $booking;
    private Collection $passengers;
    private Collection $routes;
    private ?string $pcc;

    /**
     * Build revalidation payload for Sabre BFM (Bargain Finder Max)
     */
    public function build(
        Booking $booking,
        Collection $passengers,
        Collection $routes,
        ?string $pcc = null
    ): array {
        $this->booking = $booking;
        $this->passengers = $passengers;
        $this->routes = $this->sortAndGroupRoutes($routes);
        $this->pcc = $pcc ?? self::DEFAULT_PCC;

        return [
            'OTA_AirLowFareSearchRQ' => [
                'Version' => '6.1.0',
                'POS' => $this->buildPOS(),
                'OriginDestinationInformation' => $this->buildOriginDestinations(),
                'TravelPreferences' => $this->buildTravelPreferences(),
                'TravelerInfoSummary' => $this->buildTravelerInfo(),


                'TPA_Extensions' => $this->buildTPAExtensions(),
            ]
        ];
    }

    /**
     * Sort routes chronologically and group by journey (outbound/return)
     */
    private function sortAndGroupRoutes(Collection $routes): Collection
    {
        return $routes->sortBy('departure_at')->values();
    }

    /**
     * Build Travel Preferences
     */
    private function buildTravelPreferences(): array
    {
        return [
            'TPA_Extensions' => [
                'VerificationItinCallLogic' => [
                    'Value' => self::VERIFICATION_LOGIC,
                    'AlwaysCheckAvailability' => 'true'

                ]
            ]
        ];
    }

    /**
     * Build Traveler Information Summary
     */
    private function buildTravelerInfo(): array
    {
        // Count only adults and children (exclude infants)
        $seatsRequired = $this->passengers->filter(function ($passenger) {
            return $this->getPassengerType($passenger) !== 'INF';
        })->count();

        return [
            'SeatsRequested' => [$seatsRequired],
            'AirTravelerAvail' => [
                [
                    'PassengerTypeQuantity' => $this->buildPassengerQuantities()
                ]
            ]
        ];
    }

    /**
     * Build passenger type quantities
     */
//    private function buildPassengerQuantities(): array
//    {
//        $types = $this->passengers->groupBy(function ($passenger) {
//            return $this->getPassengerType($passenger);
//        });
//
//        return $types->map(function ($group, $type) {
//            return [
//                'Quantity' => $group->count(),
//                'Code' => $type,
//            ];
//        })->values()->toArray();
//    }

    private function buildPassengerQuantities(): array
    {
        $types = $this->passengers->groupBy(function ($passenger) {
            $passengerType = $this->getPassengerType($passenger);

            // For children, use age-specific codes (C07, C03, etc.)
            if ($passengerType === 'CNN') {
                $age = $this->calculateAge($passenger->dob);
                return $age !== null ? 'C' . str_pad($age, 2, '0', STR_PAD_LEFT) : 'CNN';
            }

            // For infants, use age in months (I12, I06, etc.)
            if ($passengerType === 'INF') {
                $months = $this->calculateInfantAgeInMonths($passenger->dob);
                return $months !== null ? 'I' . str_pad($months, 2, '0', STR_PAD_LEFT) : 'INF';
            }

            return $passengerType;
        });

        return $types->map(function ($group, $type) {
            return [
                'Code' => $type,
                'Quantity' => $group->count(),
                'TPA_Extensions' => [
                    'VoluntaryChanges' => [
                        'Match' => 'Info'
                    ]
                ]
            ];
        })->values()->toArray();
    }


    private function calculateAge(?string $dob): ?int
    {
        if (empty($dob)) {
            return null;
        }

        try {
            return Carbon::parse($dob)->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function calculateInfantAgeInMonths(?string $dob): ?int
    {
        if (empty($dob)) {
            return null;
        }

        try {
            $birthDate = Carbon::parse($dob);
            $now = Carbon::now();

            // Calculate total months
            $months = $birthDate->diffInMonths($now);

            // Cap at 24 months (2 years) as per airline standards
            return min($months, 24);
        } catch (\Exception $e) {
            return null;
        }
    }
    /**
     * Get passenger type code (ADT, CNN, INF)
     */
    private function getPassengerType($passenger): string
    {
        $type = $passenger->traveler_type ?? 'ADULT';

        return match(strtoupper($type)) {
            'CHILD', 'CHD' => 'CNN',
            'INFANT', 'INF' => 'INF',
            default => 'ADT'
        };
    }

    /**
     * Build Point of Sale
     */
    private function buildPOS(): array
    {
        return [
            'Source' => [
                [
                    'PseudoCityCode' => $this->pcc,
                    'RequestorID' => [
                        'Type' => '1',
                        'ID' => '1',
                        'CompanyName' => [
                            'Code' => 'TN',
                            'content' => 'TN'

                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Build Origin Destination Information (journeys)
     * Groups routes into outbound/return/multi-city legs
     */
    private function buildOriginDestinations(): array
    {
        $journeys = $this->groupRoutesIntoJourneys();

        return $journeys->map(function ($journeyRoutes, $index) {
            $rph = $index + 1;
            $firstRoute = $journeyRoutes->first();
            $lastRoute = $journeyRoutes->last();

            return [
                'RPH' => (string)$rph,
                'DepartureDateTime' => $this->formatDateTime($firstRoute->departure_at),
                'OriginLocation' => [
                    'LocationCode' => $firstRoute->departure_iata_code
                ],
                'DestinationLocation' => [
                    'LocationCode' => $lastRoute->arrival_iata_code
                ],
                'TPA_Extensions' => [
//                    'SegmentType' => [
//                        'Code' => 'O'
//                    ],
                    'Flight' => $this->buildFlightSegments($journeyRoutes)
                ]
            ];
        })->values()->toArray();
    }

    /**
     * Group routes into journeys (legs)
     * Logic: New journey starts when arrival city doesn't match next departure city
     * or when there's a significant time gap (24+ hours)
     */
    private function groupRoutesIntoJourneys(): Collection
    {
        $journeys = collect([]);
        $currentJourney = collect([]);

        foreach ($this->routes as $index => $route) {
            $currentJourney->push($route);

            $nextRoute = $this->routes->get($index + 1);

            // Start new journey if:
            // 1. No next route (last route)
            // 2. Arrival city doesn't match next departure city
            // 3. Time gap > 24 hours
            if (!$nextRoute ||
                $route->arrival_iata_code !== $nextRoute->departure_iata_code ||
                $this->hasLargeTimeGap($route, $nextRoute)) {

                $journeys->push($currentJourney);
                $currentJourney = collect([]);
            }
        }

        return $journeys;
    }

    /**
     * Check if there's a large time gap between routes (24+ hours)
     */
    private function hasLargeTimeGap($route1, $route2): bool
    {
        $arrival = Carbon::parse($route1->arrival_at);
        $departure = Carbon::parse($route2->departure_at);
        return $arrival->diffInHours($departure) >= 24;
    }

    /**
     * Build flight segments for a journey
     */
    private function buildFlightSegments(Collection $journeyRoutes): array
    {
        return $journeyRoutes->map(function ($route) {
            $flightNumber = $this->extractFlightNumber($route->flight_number);

            return [
                'Airline' => [
                    'Operating' => $route->carrier_code,
                    'Marketing' => $route->carrier_code
                ],
                'Number' => (int)$flightNumber,
                'ClassOfService' => $route->class ?? 'Y',
                'OriginLocation' => [
                    'LocationCode' => $route->departure_iata_code
                ],
                'DestinationLocation' => [
                    'LocationCode' => $route->arrival_iata_code
                ],
                'DepartureDateTime' => $this->formatDateTime($route->departure_at),
                'ArrivalDateTime' => $this->formatDateTime($route->arrival_at),
                'Type' => 'A',


            ];
        })->toArray();
    }

    /**
     * Extract numeric flight number from various formats
     * Examples: "BS-325" -> "325", "BS325" -> "325", "325" -> "325"
     */
    private function extractFlightNumber(string $flightNumber): string
    {
        // Remove airline code prefix (e.g., "BS-325" or "BS325")
        $cleaned = preg_replace('/^[A-Z]{2}[-\s]?/', '', trim($flightNumber));

        // Extract only digits
        if (preg_match('/(\d+)/', $cleaned, $matches)) {
            return $matches[1];
        }

        return $cleaned;
    }

    /**
     * Build TPA Extensions
     */
    private function buildTPAExtensions(): array
    {
        return [
            'IntelliSellTransaction' => [
                'RequestType' => [
                    'Name' => "REVALIDATE"
                ],
                'ServiceTag' => [
                    'Name' => "REVALIDATE"
                ]
            ]
        ];
    }

    /**
     * Format datetime for Sabre API (ISO 8601)
     */
    private function formatDateTime($datetime): string
    {
        return Carbon::parse($datetime)->format('Y-m-d\TH:i:s');
    }

    /**
     * Validate booking data before building payload
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->passengers->isEmpty()) {
            $errors[] = 'No passengers found';
        }

        if ($this->routes->isEmpty()) {
            $errors[] = 'No routes found';
        }

        // Validate each route has required fields
        foreach ($this->routes as $index => $route) {
            if (empty($route->departure_iata_code)) {
                $errors[] = "Route {$index}: Missing departure_iata_code";
            }
            if (empty($route->arrival_iata_code)) {
                $errors[] = "Route {$index}: Missing arrival_iata_code";
            }
            if (empty($route->departure_at)) {
                $errors[] = "Route {$index}: Missing departure_at";
            }
            if (empty($route->arrival_at)) {
                $errors[] = "Route {$index}: Missing arrival_at";
            }
            if (empty($route->carrier_code)) {
                $errors[] = "Route {$index}: Missing carrier_code";
            }
            if (empty($route->flight_number)) {
                $errors[] = "Route {$index}: Missing flight_number";
            }
        }

        return $errors;
    }

    /**
     * Get human-readable journey summary
     */
    public function getJourneySummary(): array
    {
        $journeys = $this->groupRoutesIntoJourneys();

        return $journeys->map(function ($journeyRoutes, $index) {
            $firstRoute = $journeyRoutes->first();
            $lastRoute = $journeyRoutes->last();

            return [
                'journey' => $index + 1,
                'type' => $this->determineJourneyType($index, $journeys->count()),
                'origin' => $firstRoute->departure_iata_code,
                'destination' => $lastRoute->arrival_iata_code,
                'departure' => Carbon::parse($firstRoute->departure_at)->format('Y-m-d H:i'),
                'arrival' => Carbon::parse($lastRoute->arrival_at)->format('Y-m-d H:i'),
                'segments' => $journeyRoutes->count(),
                'stops' => $journeyRoutes->count() - 1,
            ];
        })->toArray();
    }

    /**
     * Determine journey type (Outbound, Return, etc.)
     */
    private function determineJourneyType(int $index, int $totalJourneys): string
    {
        if ($totalJourneys === 1) {
            return 'One-way';
        }

        if ($totalJourneys === 2) {
            return $index === 0 ? 'Outbound' : 'Return';
        }

        return 'Leg ' . ($index + 1);
    }
}
