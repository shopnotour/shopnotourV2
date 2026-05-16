<?php

namespace Modules\Flight\Service\Sabre;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Booking\Models\Booking;

class SabrePnrBuilder
{
    private const TARGET_CITY = '27YK';
    private const RECEIVED_FROM = 'SabreAPIsTools';
    private const TICKET_TYPE = '7TAW';

    private Booking $booking;
    private Collection $passengers;
    private Collection $routes;

    /**
     * Build complete PNR payload
     */
    public function build(Booking $booking, Collection $passengers, Collection $routes): array
    {
        $this->booking = $booking;
        $this->passengers = $passengers;
        $this->routes = $this->sortRoutes($routes);

        return [
            'CreatePassengerNameRecordRQ' => [
                'version' => '2.5.0',
                'targetCity' => self::TARGET_CITY,
                'haltOnAirPriceError' => true,
                'TravelItineraryAddInfo' => $this->buildTravelItinerary(),
                'AirBook' => $this->buildAirBook(),
                'AirPrice' => $this->buildAirPrice(),
                'SpecialReqDetails' => $this->buildSpecialRequirements(),
                'PostProcessing' => $this->buildPostProcessing(),
            ]
        ];
    }

    /**
     * Sort routes chronologically
     */
    private function sortRoutes(Collection $routes): Collection
    {
        return $routes->sortBy('departure_at')->values();
    }

    /**
     * Build Travel Itinerary Add Info section
     */
    private function buildTravelItinerary(): array
    {
        return [
            'AgencyInfo' => [
                'Ticketing' => [
                    'TicketType' => self::TICKET_TYPE
                ]
            ],
            'CustomerInfo' => [
                'ContactNumbers' => [
                    'ContactNumber' => $this->buildContactNumbers()
                ],
                'Email' => $this->buildEmails(),
                'PersonName' => $this->buildPersonNames()
            ]
        ];
    }

    /**
     * Build person names for all passengers with infant linking
     */
    private function buildPersonNames(): array
    {
        $names = [];
        $adultIndex = null;

        foreach ($this->passengers as $index => $passenger) {
            $nameNumber = ($index + 1) . '.1';
            $passengerType = $this->getPassengerType($passenger);

            if ($passengerType === 'ADT') {
                $adultIndex = $index; // Track last adult for infant linking

                $names[] = [
                    'NameNumber' => $nameNumber,
                    'PassengerType' => 'ADT',
                    'GivenName' => $passenger->first_name . ' ' . strtoupper($passenger->title ?? $this->getDefaultTitle($passenger)),
                    'Surname' => $passenger->last_name
                ];
            }
//            elseif ($passengerType === 'INF') {
//                // Infant entry
//                $names[] = [
//                    'NameNumber' => $nameNumber,
//                    'PassengerType' => 'INF',
//                    'Infant' => true,
//                    'GivenName' => $passenger->first_name,
//                    'Surname' => $passenger->last_name
//                ];
//
//                // Mark the accompanying adult
//                if ($adultIndex !== null) {
//                    $names[$adultIndex]['WithInfant'] = true;
//                }
//            }
            elseif ($passengerType === 'INF') {
                // Calculate infant age in months
                $infantAge = $this->calculateInfantAgeInMonths($passenger->dob);
                $nameReference = $infantAge !== null ? 'I' . str_pad($infantAge, 2, '0', STR_PAD_LEFT) : 'I01';

                // Infant entry
                $names[] = [
                    'NameNumber' => $nameNumber,
                    'NameReference' => 'I12',  // ✅ Added
                    'PassengerType' => 'INF',
                    'Infant' => true,
                    'GivenName' => $passenger->first_name . ' ' . strtoupper($passenger->title ?? $this->getDefaultTitle($passenger)),
                    'Surname' => $passenger->last_name
                ];

                // Mark the accompanying adult
//                if ($adultIndex !== null) {
//                    $names[$adultIndex]['WithInfant'] = true;
//                }
            }
            else {
                // Child (CHD or age-specific like C07, C03)
                $childAge = $this->calculateAge($passenger->dob);
                $nameReference = $childAge !== null ? 'C' . str_pad($childAge, 2, '0', STR_PAD_LEFT) : 'C07';

                $childName = [
                    'NameNumber' => $nameNumber,
                    'NameReference' => $nameReference,
                    'PassengerType' => $nameReference,
                    'GivenName' => $passenger->first_name . ' ' . strtoupper($passenger->title ?? $this->getDefaultTitle($passenger)),
                    'Surname' => $passenger->last_name
                ];

//                if ($nameReference) {
//                    $childName['NameReference'] = $nameReference;
//                }

                $names[] = $childName;
            }
        }

        return $names;
    }

    private function getDefaultTitle($passenger): string
    {
        $isFemale = stripos($passenger->gender ?? '', 'F') === 0;
        return $isFemale ? 'MS' : 'MR';
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
     * Calculate age from date of birth
     */
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

    /**
     * Get passenger type code (ADT, CHD, INF)
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
     * Build contact numbers (only for first passenger)
     */
    private function buildContactNumbers(): array
    {
        $firstPassenger = $this->passengers->first();
        if (!$firstPassenger) {
            return [];
        }

        $phone = $this->cleanPhone($firstPassenger->phone ?? $this->booking->phone ?? '');
        if (empty($phone)) {
            return [];
        }

        $originAirport = $this->routes->first()->departure_iata_code ?? 'DAC';

        return [[
            'LocationCode' => $originAirport,
            'NameNumber' => '1.1',
            'Phone' => $phone,
            'PhoneUseType' => 'M'
        ]];
    }

    /**
     * Build email addresses (only for first passenger)
     */
    private function buildEmails(): array
    {
        $firstPassenger = $this->passengers->first();
        if (!$firstPassenger) {
            return [];
        }

        $email = $this->booking->email ?? 'shopnotour@gmail.com';
        if (empty($email)) {
            return [];
        }

        return [[
            'NameNumber' => '1.1',
            'Address' => $email
        ]];
    }

    /**
     * Build AirBook section with flight segments
     */
    private function buildAirBook(): array
    {
        return [
            'HaltOnStatus' => $this->getHaltOnStatusCodes(),
            'OriginDestinationInformation' => [
                'FlightSegment' => $this->buildFlightSegments()
            ],
            'RedisplayReservation' => [
                'NumAttempts' => 3,
                'WaitInterval' => 3000
            ]
        ];
    }

    /**
     * Get halt on status codes
     */
    private function getHaltOnStatusCodes(): array
    {
        $codes = ['NO', 'NN', 'UC', 'US', 'UN', 'LL', 'HL', 'HX', 'WL'];
//        $codes = ['NO', 'UC', 'US', 'UN', 'LL', 'HL', 'HX', 'WL'];
        return collect($codes)->map(fn($code) => ['Code' => $code])->toArray();
    }

    /**
     * Build flight segments with marriage group logic
     * NumberInParty excludes infants
     */
    private function buildFlightSegments(): array
    {
        // Count only adults and children (exclude infants)
        $totalPassengers = $this->passengers->filter(function ($passenger) {
            return $this->getPassengerType($passenger) !== 'INF';
        })->count();

        return $this->routes->map(function ($route, $index) use ($totalPassengers) {
            $flightNumber = $this->extractFlightNumber($route->flight_number);
            $marriageGroup = $this->determineMarriageGroup($route, $index);

            return [
                'DepartureDateTime' => $this->formatDateTime($route->departure_at),
                'FlightNumber' => $flightNumber,
                'Status' => 'NN',
                'ResBookDesigCode' => $route->class ?? 'Y',
                'NumberInParty' => (string)$totalPassengers,
                'DestinationLocation' => [
                    'LocationCode' => $route->arrival_iata_code
                ],
                'MarketingAirline' => [
                    'FlightNumber' => $flightNumber,
                    'Code' => $route->carrier_code,
                ],
                'MarriageGrp' => $marriageGroup,
                'OriginLocation' => [
                    'LocationCode' => $route->departure_iata_code
                ]
            ];
        })->toArray();
    }


    private function extractFlightNumber($flightNumber)
    {
        return explode('-', $flightNumber)[1] ?? $flightNumber;
    }

    /**
     * Determine marriage group (connection logic)
     */
    private function determineMarriageGroup($currentRoute, int $index): string
    {
        if ($index === 0) {
            return 'O';
        }

        $previousRoute = $this->routes->get($index - 1);
        if (!$previousRoute) {
            return 'O';
        }

        $isSameAirport = $currentRoute->departure_iata_code === $previousRoute->arrival_iata_code;

        if ($isSameAirport) {
            $prevArrival = Carbon::parse($previousRoute->arrival_at);
            $currDeparture = Carbon::parse($currentRoute->departure_at);
            $hoursDiff = $prevArrival->diffInHours($currDeparture);

            if ($hoursDiff < 24) {
                return 'I';
            }
        }

        return 'O';
    }

    /**
     * Build AirPrice section
     */
    private function buildAirPrice(): array
    {
        return [[
            'PriceRequestInformation' => [
                'Retain' => true,
                'OptionalQualifiers' => [
                    'PricingQualifiers' => [
                        'PassengerType' => $this->buildPassengerTypes()
                    ]
                ]
            ]
        ]];
    }

    /**
     * Build passenger type quantity for pricing
     */

    private function buildPassengerTypes(): array
    {
        $types = $this->passengers->groupBy(function ($passenger) {
            return strtoupper($passenger->passenger_type_code ?? 'ADT');
        });

        return $types->map(function ($group, $typeCode) {
            $sabreCode = match($typeCode) {
                'C07'   => 'C07',
                'C03'   => 'C03',
                'INF'   => 'INF',
                default => 'ADT',
            };

            return [
                'Code'     => $sabreCode,
                'Quantity' => (string)$group->count()
            ];
        })->values()->toArray();
    }
//    private function buildPassengerTypes(): array
//    {
//        $types = $this->passengers->groupBy(function ($passenger) {
//            return $this->getPassengerType($passenger);
//        });
//
//        return $types->map(function ($group, $type) {
//            return [
//                'Code' => $type,
//                'Quantity' => (string)$group->count()
//            ];
//        })->values()->toArray();
//    }

    /**
     * Build special requirements (DOCS, CTCM, CTCE, CHLD, INFT)
     */
    private function buildSpecialRequirements(): array
    {
        return [
            'SpecialService' => [
                'SpecialServiceInfo' => [
                    'AdvancePassenger' => $this->buildAdvancePassenger(),
                    'SecureFlight' => $this->buildSecureFlight(),
                    'Service' => $this->buildServiceRequests()
                ]
            ]
        ];
    }

    /**
     * Build Advance Passenger (DOCS) information
     */
    private function buildAdvancePassenger(): array
    {
        $advancePassengers = [];
        $adultNameNumber = null;

        foreach ($this->passengers as $index => $passenger) {
            $passengerType = $this->getPassengerType($passenger);
            $nameNumber = ($index + 1) . '.1';

            // Track first adult for infant association
            if ($passengerType === 'ADT' && $adultNameNumber === null) {
                $adultNameNumber = $nameNumber;
            }

            // For infant, use adult's NameNumber
            $docNameNumber = ($passengerType === 'INF' && $adultNameNumber) ? $adultNameNumber : $nameNumber;

            $passportNumber = $passenger->passport_number ?? 'A00000000';
            if (is_numeric($passportNumber)) {
                $passportNumber = (string)$passportNumber;
            }

            $gender = $this->getGender($passenger->gender ?? 'M', $passengerType === 'INF');

            $advancePassengers[] = [
                'SegmentNumber' => 'A',
                'Document' => [
                    'Number' => $passportNumber,
                    'ExpirationDate' => $this->formatDate($passenger->passport_expiry_date, true),
                    'Type' => 'P',
                    'IssueCountry' => $passenger->country ?? 'XX',
                    'NationalityCountry' => $passenger->country ?? 'XX'
                ],
                'PersonName' => [
                    'NameNumber' => $docNameNumber,
                    'GivenName' => $passenger->first_name . ' ' . strtoupper($passenger->title ?? $this->getDefaultTitle($passenger)),
                    'Surname' => $passenger->last_name,
                    'Gender' => $gender,
                    'DateOfBirth' => $this->formatDate($passenger->dob)
                ]
            ];
        }

        return $advancePassengers;
    }

    /**
     * Build Secure Flight (SFPD) information
     */
    private function buildSecureFlight(): array
    {
        $secureFlights = [];
        $adultNameNumber = null;

        foreach ($this->passengers as $index => $passenger) {
            $passengerType = $this->getPassengerType($passenger);
            $nameNumber = ($index + 1) . '.1';

            // Track first adult for infant association
            if ($passengerType === 'ADT' && $adultNameNumber === null) {
                $adultNameNumber = $nameNumber;
            }

            // For infant, use adult's NameNumber
            $sfNameNumber = ($passengerType === 'INF' && $adultNameNumber) ? $adultNameNumber : $nameNumber;

            $gender = $this->getGender($passenger->gender ?? 'M', $passengerType === 'INF');

            $secureFlights[] = [
                'SegmentNumber' => 'A',
                'PersonName' => [
                    'NameNumber' => $sfNameNumber,
                    'DateOfBirth' => $this->formatDate($passenger->dob),
                    'Gender' => $gender,
                    'GivenName' => $passenger->first_name . ' ' . strtoupper($passenger->title ?? $this->getDefaultTitle($passenger)),
                    'Surname' => $passenger->last_name
                ],
                'VendorPrefs' => [
                    'Airline' => [
                        'Hosted' => false
                    ]
                ]
            ];
        }

        return $secureFlights;
    }

    /**
     * Build service requests (CTCM, CTCE, CHLD, INFT)
     */
    private function buildServiceRequests(): array
    {
        $services = [];
        $firstPassenger = $this->passengers->first();

        if (!$firstPassenger) {
            return $services;
        }

        // Contact Mobile (CTCM)
        $phone = $this->cleanPhone($firstPassenger->phone ?? $this->booking->phone ?? '');
        if ($phone) {
            $services[] = [
                'SegmentNumber' => 'A',
                'SSR_Code' => 'CTCM',
                'PersonName' => ['NameNumber' => '1.1'],
                'Text' => $phone
            ];
        }

        // Contact Email (CTCE)
        $email = $this->booking->email ?? 'shopnotour@gmail.com';
        if ($email) {
            $services[] = [
                'SegmentNumber' => 'A',
                'SSR_Code' => 'CTCE',
                'PersonName' => ['NameNumber' => '1.1'],
                'Text' => str_replace('@', '//', $email)
            ];
        }

        // CHLD and INFT SSR codes
        $adultNameNumber = null;
        foreach ($this->passengers as $index => $passenger) {
            $nameNumber = ($index + 1) . '.1';
            $passengerType = $this->getPassengerType($passenger);

            // Track first adult
            if ($passengerType === 'ADT' && $adultNameNumber === null) {
                $adultNameNumber = $nameNumber;
            }

            // CHLD SSR for children
            if ($passengerType === 'CNN') {
                $dob = $this->formatDate($passenger->dob);
                if ($dob && $dob !== '1990-01-01') {
                    $dobFormatted = Carbon::parse($dob)->format('dMy'); // e.g., 27Jun18
                    $services[] = [
                        'SegmentNumber' => 'A',
                        'SSR_Code' => 'CHLD',
                        'PersonName' => ['NameNumber' => $nameNumber],
                        'Text' => $dobFormatted
                    ];
                }
            }

            // INFT SSR for infants
            if ($passengerType === 'INF' && $adultNameNumber) {
                $dob = $this->formatDate($passenger->dob);
                $dobFormatted = ($dob && $dob !== '1990-01-01') ?
                    Carbon::parse($dob)->format('dMy') : '01Jan25';

                $infantText = sprintf(
                    '%s/%s/%s',
                    $passenger->last_name,
                    $passenger->first_name,
                    $dobFormatted
                );

                $services[] = [
                    'SegmentNumber' => 'A',
                    'SSR_Code' => 'INFT',
                    'PersonName' => ['NameNumber' => $adultNameNumber],
                    'Text' => $infantText
                ];
            }
        }

        return $services;
    }

    /**
     * Build post processing section
     */
    private function buildPostProcessing(): array
    {
        return [
            'RedisplayReservation' => [
                'waitInterval' => 100,
                'returnExtendedPriceQuote' => true
            ],
            'EndTransaction' => [
                'Source' => [
                    'ReceivedFrom' => self::RECEIVED_FROM
                ]
            ]
        ];
    }

    /**
     * Format date time for Sabre API
     */
    private function formatDateTime(string $date): string
    {
        return Carbon::parse($date)->format('Y-m-d\TH:i:s');
    }

    /**
     * Format date (with fallback for invalid dates)
     */
    private function formatDate(?string $date, bool $isExpiry = false): string
    {
        if (empty($date) || (is_numeric($date) && strlen((string)$date) < 6)) {
            return $isExpiry
                ? Carbon::now()->addYears(5)->format('Y-m-d')
                : '1990-01-01';
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return '1990-01-01';
        }
    }

    /**
     * Get gender code (M/F or FI/MI for infants)
     */
    private function getGender(?string $gender, bool $isInfant = false): string
    {
        if (empty($gender)) {
            return $isInfant ? 'MI' : 'M';
        }

        $isFemale = stripos($gender, 'F') === 0;

        if ($isInfant) {
            return $isFemale ? 'FI' : 'MI';
        }

        return $isFemale ? 'F' : 'M';
    }

    /**
     * Clean phone number (remove non-numeric characters)
     */
    private function cleanPhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
