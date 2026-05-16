<?php

namespace Modules\Flight\Service;

use Modules\Flight\Models\Airline;
use Modules\Flight\Models\Airport;

class FlightDataHelper
{
    private array $airlinesCache;
    private array $airportsCache;

    public function __construct()
    {
        $this->airlinesCache = Airline::getCachedAirlines();
        $this->airportsCache = Airport::getCachedAirports();
    }

    // ========================================
    // AIRLINE METHODS
    // ========================================

    public function getAirlineInfo(?string $code): ?array
    {
        if (!$code) return null;
        return $this->airlinesCache[$code] ?? null;
    }

    public function getAirlineName(?string $code): ?string
    {
        if (!$code) return null;
        $airline = $this->getAirlineInfo($code);
        return $airline['name'] ?? $code;
    }

    public function getAirlineImage(?string $code, string $size = 'medium'): ?string
    {
        if (!$code) return null;
        $airline = $this->getAirlineInfo($code);
        if (!$airline) return null;

        return match ($size) {
            'thumb' => $airline['image_thumb'] ?? null,
            'medium' => $airline['image_medium'] ?? null,
            'large' => $airline['image_large'] ?? null,
            'full' => $airline['image_url'] ?? null,
            default => $airline['image_medium'] ?? null,
        };
    }

    public function getAirlineImages(?string $code): array
    {
        return [
            'thumb' => $this->getAirlineImage($code, 'thumb'),
            'medium' => $this->getAirlineImage($code, 'medium'),
            'large' => $this->getAirlineImage($code, 'large'),
            'full' => $this->getAirlineImage($code, 'full'),
        ];
    }

    // ========================================
    // AIRPORT METHODS
    // ========================================

    public function getAirportInfo(?string $code): ?array
    {
        if (!$code) return null;
        return $this->airportsCache[$code] ?? null;
    }

    public function getAirportName(?string $code): ?string
    {
        if (!$code) return null;
        $airport = $this->getAirportInfo($code);
        return $airport['name'] ?? $code;
    }

    public function getAirportCity(?string $code): ?string
    {
        if (!$code) return null;
        $airport = $this->getAirportInfo($code);
        return $airport['city'] ?? $airport['address'] ?? null;
    }

    public function getAirportAddress(?string $code): ?string
    {
        if (!$code) return null;
        $airport = $this->getAirportInfo($code);
        return $airport['address'] ?? null;
    }

    public function getAirportCountry(?string $code): ?string
    {
        if (!$code) return null;
        $airport = $this->getAirportInfo($code);
        return $airport['country'] ?? null;
    }

    public function getAirportCountryCode(?string $code): ?string
    {
        if (!$code) return null;
        $airport = $this->getAirportInfo($code);
        return $airport['country_code'] ?? $airport['country'] ?? null;
    }

    // ========================================
    // AIRCRAFT METHODS
    // ========================================

    public function getAircraftName(?string $code): ?string
    {
        if (!$code) return null;

        $aircrafts = [
            '738' => 'Boeing 737-800',
            '788' => 'Boeing 787-8',
            '789' => 'Boeing 787-9',
            '77W' => 'Boeing 777-300ER',
            '773' => 'Boeing 777-300',
            '32Q' => 'Airbus A321neo',
            '32N' => 'Airbus A321neo',
            '32A' => 'Airbus A321',
            '321' => 'Airbus A321',
            '320' => 'Airbus A320',
            '332' => 'Airbus A330-200',
            '333' => 'Airbus A330-300',
            'DH8' => 'Dash 8',
            '7M8' => 'Boeing 737 MAX 8',
            '73H' => 'Boeing 737-800',
            'E90' => 'Embraer E190',
            'E75' => 'Embraer E175',
            'CRJ' => 'Bombardier CRJ',
            'AT7' => 'ATR 72',
            'A359' => 'Airbus A350-900',
            'A388' => 'Airbus A380-800',
            '744' => 'Boeing 747-400',
            '763' => 'Boeing 767-300',
            '764' => 'Boeing 767-400',
        ];

        return $aircrafts[$code] ?? $code;
    }

    // ========================================
    // PASSENGER TYPE METHODS
    // ========================================

    public function getPassengerTypeLabel(string $type): string
    {
        $labels = [
            'ADT' => 'Adult',
            'CNN' => 'Child',
            'CHD' => 'Child',
            'C03' => 'Child (2-4 years)',
            'C07' => 'Child (5-11 years)',
            'INF' => 'Infant',
            'INS' => 'Infant with Seat',
        ];

        return $labels[$type] ?? $type;
    }

    // ========================================
    // CABIN CLASS METHODS
    // ========================================

    public function getCabinCode(string $cabinClass): string
    {
        $codes = [
            'Economy' => 'Y',
            'PremiumEconomy' => 'W',
            'Premium Economy' => 'W',
            'Business' => 'C',
            'First' => 'F',
        ];

        return $codes[$cabinClass] ?? 'Y';
    }

    public function getCabinName(?string $code): ?string
    {
        if (!$code) return null;

        $cabins = [
            'Y' => 'Economy',
            'W' => 'Premium Economy',
            'C' => 'Business',
            'F' => 'First Class',
            'Economy' => 'Economy',
            'PremiumEconomy' => 'Premium Economy',
            'Business' => 'Business',
            'First' => 'First Class',
        ];

        return $cabins[$code] ?? $code;
    }

    // ========================================
    // MEAL METHODS
    // ========================================

    public function getMealDescription(?string $mealCode): ?string
    {
        if (!$mealCode) return null;

        $mealDescriptions = [
            'B' => 'Breakfast',
            'L' => 'Lunch',
            'D' => 'Dinner',
            'S' => 'Snack',
            'M' => 'Meal',
            'H' => 'Hot Meal',
            'C' => 'Cold Meal',
            'R' => 'Refreshment',
            'N' => 'No Meal',
            'BM' => 'Breakfast/Meal',
            'MS' => 'Meal/Snack',
        ];

        return $mealDescriptions[$mealCode] ?? $mealCode;
    }

    // ========================================
    // TAX METHODS
    // ========================================

    public function getTaxDescription(string $category): string
    {
        $descriptions = [
            'BD' => 'Bangladesh Travel Tax',
            'OW' => 'One Way Tax',
            'P7' => 'Passenger Service Charge',
            'P8' => 'Passenger Security Charge',
            'UT' => 'User Development Fee',
            'AE' => 'UAE Fee',
            'F6' => 'UAE Tourism Fee',
            'TP' => 'Tourism Promotion Fee',
            'ZR' => 'International Surcharge',
            'E5' => 'Embarkation Tax',
            'GZ' => 'Kuwait Fee',
            'L2' => 'Kuwait Departure Tax',
            'N4' => 'Kuwait Passenger Service',
            'YX' => 'Fuel Surcharge',
            '6H' => 'Government Tax',
            'YQ' => 'Carrier Surcharge',
            'YR' => 'Carrier Surcharge',
        ];

        return $descriptions[$category] ?? 'Tax';
    }

    // ========================================
    // DATE/TIME METHODS
    // ========================================

    public function formatTime12h(?string $datetime): ?string
    {
        if (!$datetime) return null;

        try {
            return (new \DateTime($datetime))->format('h:i A');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function extractDate(?string $datetime): ?string
    {
        if (!$datetime) return null;

        try {
            return (new \DateTime($datetime))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function extractTime(?string $datetime): ?string
    {
        if (!$datetime) return null;

        try {
            return (new \DateTime($datetime))->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function parseDateTime(string $datetime): array
    {
        try {
            $dt = new \DateTime($datetime);
            return [
                'date' => $dt->format('Y-m-d'),
                'time' => $dt->format('H:i:s'),
                'time_12h' => $dt->format('h:i A'),
            ];
        } catch (\Exception $e) {
            return [
                'date' => null,
                'time' => null,
                'time_12h' => null,
            ];
        }
    }

    public function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes}m";
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h";
    }

    public function formatDateTime(string $date): string
    {
        return date('Y-m-d\TH:i:s', strtotime($date));
    }

    // ========================================
    // PRICE METHODS
    // ========================================

    public function parsePrice(string $priceString): float
    {
        return (float)preg_replace('/[^0-9.]/', '', $priceString);
    }

    public function extractCurrency(string $priceString): string
    {
        preg_match('/^([A-Z]{3})/', $priceString, $matches);
        return $matches[1] ?? 'BDT';
    }

    // ========================================
    // CALCULATION METHODS
    // ========================================

    public function calculateDateAdjustment(?string $departureDate, ?string $arrivalDate): int
    {
        if (!$departureDate || !$arrivalDate) {
            return 0;
        }

        try {
            $dep = new \DateTime($departureDate);
            $arr = new \DateTime($arrivalDate);
            return (int)$arr->diff($dep)->days;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function calculateLayoverMinutes(string $arrivalDate, string $arrivalTime, string $departureDate, string $departureTime): int
    {
        try {
            preg_match('/(\d{2}:\d{2}:\d{2})/', $arrivalTime, $arrivalMatch);
            preg_match('/(\d{2}:\d{2}:\d{2})/', $departureTime, $departureMatch);

            $arrivalTimeClean = $arrivalMatch[1] ?? '00:00:00';
            $departureTimeClean = $departureMatch[1] ?? '00:00:00';

            $arrivalTimestamp = strtotime($arrivalDate . ' ' . $arrivalTimeClean);
            $departureTimestamp = strtotime($departureDate . ' ' . $departureTimeClean);

            if ($arrivalTimestamp && $departureTimestamp && $departureTimestamp > $arrivalTimestamp) {
                return (int)(($departureTimestamp - $arrivalTimestamp) / 60);
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return 0;
    }

    public function checkTerminalChange(?string $arrivalTerminal, ?string $departureTerminal): bool
    {
        if (!$arrivalTerminal || !$departureTerminal) {
            return false;
        }
        return $arrivalTerminal !== $departureTerminal;
    }

    public function identifyLegType(int $index, int $totalLegs): string
    {
        if ($totalLegs === 1) {
            return 'oneway';
        }

        if ($totalLegs === 2) {
            return $index === 0 ? 'outbound' : 'return';
        }

        if ($index === 0) {
            return 'first_leg';
        } elseif ($index === $totalLegs - 1) {
            return 'last_leg';
        } else {
            return 'middle_leg';
        }
    }

    public function parseISODuration(string $duration): int
    {
        if (empty($duration)) return 0;

        $minutes = 0;

        if (preg_match('/(\d+)D/', $duration, $matches)) {
            $minutes += (int)$matches[1] * 24 * 60;
        }

        if (preg_match('/(\d+)H/', $duration, $matches)) {
            $minutes += (int)$matches[1] * 60;
        }

        if (preg_match('/(\d+)M/', $duration, $matches)) {
            $minutes += (int)$matches[1];
        }

        return $minutes;
    }

    // ========================================
    // LAYOVER INFORMATION METHODS
    // ========================================

    public function addLayoverInformation(array $segments): array
    {
        $totalSegments = count($segments);

        for ($i = 0; $i < $totalSegments; $i++) {
            // Set segment type
            if ($totalSegments === 1) {
                $segments[$i]['segment_type'] = 'direct';
            } elseif ($i === 0) {
                $segments[$i]['segment_type'] = 'first';
            } elseif ($i === $totalSegments - 1) {
                $segments[$i]['segment_type'] = 'last';
            } else {
                $segments[$i]['segment_type'] = 'connecting';
            }

            // Calculate layover
            if ($i < $totalSegments - 1) {
                $nextSegment = $segments[$i + 1];

                $layoverMinutes = $this->calculateLayoverMinutes(
                    $segments[$i]['arrival']['date'],
                    $segments[$i]['arrival']['time'],
                    $nextSegment['departure']['date'],
                    $nextSegment['departure']['time']
                );

                $segments[$i]['layover_after'] = [
                    'airport_code' => $segments[$i]['arrival']['airport_code'],
                    'airport_name' => $segments[$i]['arrival']['airport_name'],
                    'city' => $segments[$i]['arrival']['city'],
                    'country' => $segments[$i]['arrival']['country'],
                    'minutes' => $layoverMinutes,
                    'formatted' => $this->formatDuration($layoverMinutes),
                    'is_overnight' => $segments[$i]['arrival']['date'] !== $nextSegment['departure']['date'],
                    'is_long_layover' => $layoverMinutes > 240,
                    'terminal_change' => $this->checkTerminalChange(
                        $segments[$i]['arrival']['terminal'],
                        $nextSegment['departure']['terminal']
                    ),
                ];
            } else {
                $segments[$i]['layover_after'] = null;
            }
        }

        return $segments;
    }

    private function getUniqueSegments(array $flightData): array
    {
        $uniqueSegments = [];
        $usedKeys = [];

        foreach ($flightData['legs'] as $leg) {
            $group = $leg['leg_number'] - 1;

            foreach ($leg['segments'] as $segment) {
                $key = $segment['key'];

                if (in_array($key, $usedKeys)) {
                    continue;
                }
                $usedKeys[] = $key;

                $segment['group'] = $group;
                $uniqueSegments[] = $segment;
            }
        }

        return $uniqueSegments;
    }

    public function extractStopDetails(array $segments): array
    {
        $stops = [];
        $totalSegments = count($segments);

        for ($i = 0; $i < $totalSegments - 1; $i++) {
            $currentSegment = $segments[$i];
            $nextSegment = $segments[$i + 1];

            $layoverMinutes = $this->calculateLayoverMinutes(
                $currentSegment['arrival']['date'],
                $currentSegment['arrival']['time'],
                $nextSegment['departure']['date'],
                $nextSegment['departure']['time']
            );

            $stops[] = [
                'stop_number' => $i + 1,
                'airport_code' => $currentSegment['arrival']['airport_code'],
                'airport_name' => $currentSegment['arrival']['airport_name'],
                'city' => $currentSegment['arrival']['city'],
                'address' => $currentSegment['arrival']['address'],
                'country' => $currentSegment['arrival']['country'],
                'arrival_time' => $currentSegment['arrival']['time'],
                'arrival_time_12h' => $currentSegment['arrival']['time_12h'],
                'arrival_date' => $currentSegment['arrival']['date'],
                'arrival_terminal' => $currentSegment['arrival']['terminal'],
                'departure_time' => $nextSegment['departure']['time'],
                'departure_time_12h' => $nextSegment['departure']['time_12h'],
                'departure_date' => $nextSegment['departure']['date'],
                'departure_terminal' => $nextSegment['departure']['terminal'],
                'layover_minutes' => $layoverMinutes,
                'layover_formatted' => $this->formatDuration($layoverMinutes),
                'is_overnight' => $currentSegment['arrival']['date'] !== $nextSegment['departure']['date'],
                'terminal_change' => $this->checkTerminalChange(
                    $currentSegment['arrival']['terminal'],
                    $nextSegment['departure']['terminal']
                ),
            ];
        }

        return $stops;
    }

    // FlightDataHelper.php এ add করুন:

    /**
     * Calculate passenger type from date of birth
     * ADT: 12+ years
     * CNN: 2-11 years
     * INF: 0-1 years (under 2)
     */
    public function getPassengerTypeFromDOB(string $dob, ?string $departureDate = null): array
    {
        $birthDate = new \DateTime($dob);
        $travelDate = $departureDate ? new \DateTime($departureDate) : new \DateTime();

        $age = $birthDate->diff($travelDate)->y;

        if ($age >= 12) {
            $type = 'ADT';
            $label = 'Adult';
        } elseif ($age >= 2) {
            $type = 'CNN';
            $label = 'Child';
        } else {
            $type = 'INF';
            $label = 'Infant';
        }

        return [
            'type' => $type,
            'label' => $label,
            'age' => $age,
        ];
    }
}
