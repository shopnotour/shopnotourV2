<?php

namespace Modules\Booking\Service\Sabre;


/**
 * Builds the OTA_AirLowFareSearchRQ payload for Sabre Itinerary Revalidation.
 *
 * Usage:
 *   $builder  = new SabreRevalidateBuilder($flight, $pcc);
 *   $payload  = $builder->build();
 *   // pass $payload to SabreApiService::revalidateItinerary($payload)
 */
class SabreRevalidateBuilder
{
    protected array  $flight;
    protected string $pcc;

    public function __construct(array $flight, string $pcc)
    {
        $this->flight = $flight;
        $this->pcc    = $pcc;
    }

    // ─────────────────────────────────────────────
    // Entry point
    // ─────────────────────────────────────────────

    public function build(): array
    {
        return [
            'OTA_AirLowFareSearchRQ' => [
                'Version'                      => '5',
                'POS'                          => $this->buildPOS(),
                'OriginDestinationInformation' => $this->buildOriginDestinations(),
                'TravelPreferences'            => $this->buildTravelPreferences(),
                'TravelerInfoSummary'          => $this->buildTravelerInfo(),
                'TPA_Extensions'               => $this->buildTpaExtensions(),
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // POS
    // ─────────────────────────────────────────────

    protected function buildPOS(): array
    {
        return [
            'Source' => [
                [
                    'PseudoCityCode' => $this->pcc,
                    'RequestorID'    => [
                        'Type' => '1',
                        'ID'   => '1',
                        'CompanyName' => [
                            'Code'    => 'TN',
                            'content' => 'TN',
                        ],
                    ],
                ],
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // OriginDestinationInformation — one per leg
    // ─────────────────────────────────────────────

    protected function buildOriginDestinations(): array
    {
        /*
         * $flight['legs']:
         * {
         *   "leg_number": 1,
         *   "departure": { "airport_code": "DAC", "date": "2026-03-25", "time": "11:45:00+06:00" },
         *   "arrival":   { "airport_code": "DXB" },
         *   "segments":  [ { ... }, { ... } ]
         * }
         *
         * Note: SegmentType block is NOT included — not in the Sabre revalidation sample.
         */
        $legs   = $this->flight['legs'] ?? [];
        $odList = [];

        foreach ($legs as $leg) {
            $rph      = (int) ($leg['leg_number'] ?? (count($odList) + 1));
            $depInfo  = $leg['departure'] ?? [];
            $arrInfo  = $leg['arrival']   ?? [];
            $segments = $leg['segments']  ?? [];

            // OD-level departure = first segment departure
            $firstSegDep = $segments[0]['departure'] ?? $depInfo;
            $depDateTime = $this->buildDateTime(
                $firstSegDep['date'] ?? $depInfo['date'] ?? '',
                $firstSegDep['time'] ?? $depInfo['time'] ?? '00:00:00'
            );

            $odList[] = [
                'RPH'                 => (string) $rph,
                'DepartureDateTime'   => $depDateTime,
                'OriginLocation'      => ['LocationCode' => strtoupper($depInfo['airport_code'] ?? '')],
                'DestinationLocation' => ['LocationCode' => strtoupper($arrInfo['airport_code'] ?? '')],
                'TPA_Extensions'      => [
                    // ✅ No SegmentType here — sample doesn't have it
                    'Flight' => $this->buildFlightList($segments),
                ],
            ];
        }

        return $odList;
    }

    // ─────────────────────────────────────────────
    // Flight list per leg
    // ─────────────────────────────────────────────

    protected function buildFlightList(array $segments): array
    {
        /*
         * Each segment from $flight['legs'][n]['segments']:
         * {
         *   "carrier":           "AI",
         *   "operating_carrier": "AI",
         *   "flight_number":     2184,
         *   "departure": { "airport_code": "DAC", "date": "2026-03-25", "time": "11:45:00+06:00" },
         *   "arrival":   { "airport_code": "BOM", "date": "2026-03-25", "time": "14:45:00+05:30" },
         *   "fare_info": { "booking_code": "G" }
         * }
         */
        $flights = [];

        foreach ($segments as $seg) {
            $dep = $seg['departure'] ?? [];
            $arr = $seg['arrival']   ?? [];

            $depDt = $this->buildDateTime($dep['date'] ?? '', $dep['time'] ?? '00:00:00');
            $arrDt = $this->buildDateTime($arr['date'] ?? '', $arr['time'] ?? '00:00:00');

            $marketing   = strtoupper($seg['carrier']           ?? '');
            $operating   = strtoupper($seg['operating_carrier'] ?? $marketing);
            $bookingCode = strtoupper($seg['fare_info']['booking_code'] ?? 'Y');

            // ✅ Airline comes FIRST in each flight block — matches sample order
            $flights[] = [
                'Airline'             => [
                    'Marketing' => $marketing,
                    'Operating' => $operating,
                ],
                'Number'              => (int) ($seg['flight_number'] ?? 0),
                'ClassOfService'      => $bookingCode,
                'OriginLocation'      => ['LocationCode' => strtoupper($dep['airport_code'] ?? '')],
                'DestinationLocation' => ['LocationCode' => strtoupper($arr['airport_code'] ?? '')],
                'DepartureDateTime'   => $depDt,
                'ArrivalDateTime'     => $arrDt,
                'Type'                => 'A',
            ];
        }

        return $flights;
    }

    // ─────────────────────────────────────────────
    // TravelPreferences
    // ─────────────────────────────────────────────

    protected function buildTravelPreferences(): array
    {
        return [
            'TPA_Extensions' => [
                'VerificationItinCallLogic' => [
                    'Value'                   => 'M',     // ✅ M = revalidation (not B)
                    'AlwaysCheckAvailability' => true,    // ✅ Required for revalidation
                ],
            ],
            'Baggage' => [
                'RequestType'  => 'A',
                'Description'  => true,
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // TravelerInfoSummary
    // ─────────────────────────────────────────────

    protected function buildTravelerInfo(): array
    {
        /*
         * $flight['passengers']:
         * [
         *   { "type": "ADT", "count": 1 },
         *   { "type": "CNN", "count": 2 },
         * ]
         *
         * ✅ Each passenger type gets VoluntaryChanges block (per sample)
         * ✅ No SeatsRequested wrapper — sample doesn't have it
         */
        $passengers = $this->flight['passengers'] ?? [];

        if (empty($passengers)) {
            $passengers = [['type' => 'ADT', 'count' => 1]];
        }

        $passengerTypeQuantity = [];

        foreach ($passengers as $pax) {
            $code  = strtoupper($pax['type'] ?? 'ADT');
            $count = (int) ($pax['count'] ?? 1);

            $passengerTypeQuantity[] = [
                'Quantity' => $count,
                'Code'     => $code,
                'TPA_Extensions' => [
                    'VoluntaryChanges' => [
                        'Match' => 'Info',
                    ],
                ],
            ];
        }

        return [
            'AirTravelerAvail' => [
                ['PassengerTypeQuantity' => $passengerTypeQuantity],
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // Top-level TPA_Extensions
    // ─────────────────────────────────────────────

    protected function buildTpaExtensions(): array
    {
        // ✅ RequestType = "REVALIDATE" + ServiceTag = "REVALIDATE" (not "50ITINS")
        return [
            'IntelliSellTransaction' => [
                'RequestType' => ['Name' => 'REVALIDATE'],
                'ServiceTag'  => ['Name' => 'REVALIDATE'],
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // Helper — build "2026-03-25T11:45:00" from date + time
    // ─────────────────────────────────────────────

    /**
     * Strips timezone offset from time string and combines with date.
     *
     * Input : date = "2026-03-25"  |  time = "11:45:00+06:00"
     * Output: "2026-03-25T11:45:00"
     *
     * Sabre expects LOCAL time with no UTC offset.
     */
    protected function buildDateTime(string $date, string $time): string
    {
        // Remove +06:00 / -05:30 / Z
        $timeClean = preg_replace('/[+-]\d{2}:\d{2}$|Z$/u', '', trim($time));
        $timeClean = substr($timeClean, 0, 8); // "HH:MM:SS"

        if (empty($date)) {
            return '';
        }

        return $date . 'T' . $timeClean;
    }
}
