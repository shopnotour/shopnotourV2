<?php

namespace Modules\Flight\Helpers;


use Illuminate\Support\Carbon;
use Modules\Flight\Models\Airport;

class SabreFlight
{

    /**
     * Build Sabre API payload from request
     */
    public static function buildPayload($request)
    {
        $tripType = $request->input('trip_type', 'oneway');
        $segments = $request->input('segments', []);

        $originDestinationInfo = self::buildOriginDestinationInfo($tripType, $segments, $request);
        $passengerTypeQuantity = self::buildPassengerTypeQuantity($request);

        return [
            "OTA_AirLowFareSearchRQ" => [
                "Version" => "5",
                "POS" => [
                    "Source" => [[
                        "PseudoCityCode" => config('flight.sabre.pcc', '27YK'),
                        "RequestorID" => [
                            "Type" => "1",
                            "ID" => config('flight.sabre.requestor_id', '1'),
                            "CompanyName" => [
                                "Code" => config('flight.sabre.company_code', 'TN'),
                                "CompanyShortName" => config('flight.sabre.company_short_name', 'TN')
                            ]
                        ]
                    ]]
                ],
                "OriginDestinationInformation" => $originDestinationInfo,
                "TravelerInfoSummary" => [
                    "AirTravelerAvail" => [[
                        "PassengerTypeQuantity" => $passengerTypeQuantity,
                    ]],
                ],
                "TravelPreferences" => [
                    "TPA_Extensions" => [
                        "NumTrips" => ["Number" => 50]
                    ],
                ],
                "TPA_Extensions" => [
                    "IntelliSellTransaction" => [
                        "RequestType" => ["Name" => "50ITINS"]
                    ],
                ],
            ],
        ];
    }

    /**
     * Build origin/destination information
     */
    private static function buildOriginDestinationInfo($tripType, $segments, $request)
    {
        $originDestinationInfo = [];

        if ($tripType === 'multi' && count($segments) > 0) {
            foreach ($segments as $index => $segment) {
                $fromAirport = Airport::find($segment['from'] ?? null);
                $toAirport = Airport::find($segment['to'] ?? null);

                if (!$fromAirport || !$toAirport) continue;

                $originDestinationInfo[] = [
                    "RPH" => (string)($index + 1),
                    "DepartureDateTime" => self::formatIsoDate($segment['departure'] ?? null),
                    "OriginLocation" => ["LocationCode" => $fromAirport->code],
                    "DestinationLocation" => ["LocationCode" => $toAirport->code],
                ];
            }
        } else {
            $origin = self::extractIataCode($request->input('from_where') ?: $request->input('from_airport') ?: $request->input('from'));
            $dest = self::extractIataCode($request->input('to_where') ?: $request->input('to_airport') ?: $request->input('to'));
            $depTime = self::formatIsoDate($segments[0]['departure'] ?? null);
            $returnDate = self::formatIsoDate($request->input('return_date'));

            $originDestinationInfo[] = [
                "RPH" => "1",
                "DepartureDateTime" => $depTime,
                "OriginLocation" => ["LocationCode" => $origin],
                "DestinationLocation" => ["LocationCode" => $dest],
            ];

            if ($tripType === 'round' && $returnDate && $returnDate !== '2025-10-15T00:00:00') {
                $originDestinationInfo[] = [
                    "RPH" => "2",
                    "DepartureDateTime" => $returnDate,
                    "OriginLocation" => ["LocationCode" => $dest],
                    "DestinationLocation" => ["LocationCode" => $origin],
                ];
            }
        }

        return $originDestinationInfo;
    }

    /**
     * Build passenger type quantity
     */
    private static function buildPassengerTypeQuantity($request)
    {
        $ptcs = [];

        $adults = (int) $request->input('adults', 1);
        if ($adults > 0) {
            $ptcs[] = ["Code" => "ADT", "Quantity" => $adults];
        }

        $childrenAges = $request->input('children_ages', []);
        $childrenCount = (int) $request->input('children', 0);

        if ($childrenCount > 0 && empty($childrenAges)) {
            $ptcs[] = ["Code" => "C07", "Quantity" => $childrenCount];
        } elseif (!empty($childrenAges)) {
            $c03Count = 0; // Ages 2-4
            $c07Count = 0; // Ages 5-11

            foreach ($childrenAges as $age) {
                $age = (int) $age;
                if ($age >= 2 && $age <= 4) {
                    $c03Count++;
                } elseif ($age >= 5 && $age <= 11) {
                    $c07Count++;
                }
            }

            if ($c03Count > 0) $ptcs[] = ["Code" => "C03", "Quantity" => $c03Count];
            if ($c07Count > 0) $ptcs[] = ["Code" => "C07", "Quantity" => $c07Count];
        }

        $infants = (int) $request->input('infants', 0);
        if ($infants > $adults) $infants = $adults;
        if ($infants > 0) {
            $ptcs[] = ["Code" => "INF", "Quantity" => $infants];
        }

        return $ptcs;
    }

    /**
     * Process Sabre API response
     */
    public static function processSabreResponse($saberData, $request)
    {
        if (!isset($saberData['groupedItineraryResponse']['itineraryGroups'])) {
            return [];
        }

        $gir = $saberData['groupedItineraryResponse'];
        $itineraryGroups = $gir['itineraryGroups'] ?? [];
        $scheduleDescs = $gir['scheduleDescs'] ?? [];
        $legDescs = $gir['legDescs'] ?? [];

        $datalist = [];

        foreach ($itineraryGroups as $groupIndex => $group) {
            $itineraries = $group['itineraries'] ?? [];
            if (!is_array($itineraries)) continue;

            foreach ($itineraries as $index => $itinerary) {
                $flightItem = self::processItinerary(
                    $itinerary,
                    $group,
                    $index,
                    $groupIndex,
                    $gir,
                    $scheduleDescs,
                    $legDescs,
                    $request
                );

                if ($flightItem) {
                    $datalist[] = $flightItem;
                }
            }
        }

        return $datalist;
    }

    /**
     * Process single itinerary
     */
    private static function processItinerary($itinerary, $group, $index, $groupIndex, $gir, $scheduleDescs, $legDescs, $request)
    {
        $helpers = self::initializeHelpers();
        extract($helpers);

        $pricingInfo = $itinerary['pricingInformation'][0] ?? [];
        $fare = $pricingInfo['fare'] ?? [];
        $passengerInfoList = $fare['passengerInfoList'] ?? [];

        $bookingClassMap = self::buildBookingClassMap($passengerInfoList);

        $allLegs = $itinerary['legs'] ?? [];
        $mockItineraries = [];

        foreach ($allLegs as $legIndex => $leg) {
            $mockItineraries[] = self::processLeg(
                $leg,
                $legIndex,
                $group,
                $legDescs,
                $scheduleDescs,
                $bookingClassMap,
                $getById
            );
        }

        $pricingData = self::extractPricingData($passengerInfoList, $request);

        return self::buildFlightItem(
            $itinerary,
            $mockItineraries,
            $pricingData,
            $group,
            $index,
            $groupIndex,
            $scheduleDescs,
            $legDescs,
            $gir,
            $request,
            $getById
        );
    }

    /**
     * Initialize helper functions
     */
    private static function initializeHelpers()
    {
        $getById = function (array $rows, $id) {
            foreach ($rows as $r) {
                if (($r['id'] ?? null) == $id) return $r;
            }
            return null;
        };

        $firstScalar = function ($v, $fallback = null) {
            if (is_scalar($v) || $v === null) return $v ?? $fallback;
            if (is_array($v)) {
                foreach ($v as $item) {
                    if (is_scalar($item)) return $item;
                }
            }
            return $fallback;
        };

        $strv = function ($v, $fallback = 'N/A') use ($firstScalar) {
            $x = $firstScalar($v, $fallback);
            return is_scalar($x) ? (string)$x : $fallback;
        };

        $numv = function ($v, $fallback = 0.0) use ($firstScalar) {
            $x = $firstScalar($v, $fallback);
            return is_numeric($x) ? (float)$x : (float)$fallback;
        };

        $clean = null;
        $clean = function ($value) use (&$clean) {
            if (is_array($value)) {
                $tmp = [];
                foreach ($value as $k => $v) {
                    $vv = $clean($v);
                    if ($vv === [] || $vv === null || $vv === '') continue;
                    $tmp[$k] = $vv;
                }
                return $tmp;
            }
            return $value;
        };

        return compact('getById', 'firstScalar', 'strv', 'numv', 'clean');
    }

    /**
     * Build booking class map
     */
    private static function buildBookingClassMap($passengerInfoList)
    {
        $segmentBookingClassMap = [];
        $segmentBookingClassMapByIndex = [];
        $segmentBookingClassMapByFareComponent = [];

        if (empty($passengerInfoList[0]['passengerInfo']['fareComponents'])) {
            return compact('segmentBookingClassMap', 'segmentBookingClassMapByIndex', 'segmentBookingClassMapByFareComponent');
        }

        $fareComponents = $passengerInfoList[0]['passengerInfo']['fareComponents'];

        foreach ($fareComponents as $fcIndex => $fc) {
            if (!isset($fc['segments']) || !is_array($fc['segments'])) continue;

            foreach ($fc['segments'] as $segIndex => $fcSeg) {
                $bookingCode = self::extractBookingCode($fcSeg, $fc);

                if ($bookingCode) {
                    self::mapBookingCode(
                        $bookingCode,
                        $fcSeg['segment'] ?? [],
                        $fcIndex,
                        $segIndex,
                        $segmentBookingClassMap,
                        $segmentBookingClassMapByIndex,
                        $segmentBookingClassMapByFareComponent
                    );
                }
            }
        }

        return compact('segmentBookingClassMap', 'segmentBookingClassMapByIndex', 'segmentBookingClassMapByFareComponent');
    }

    /**
     * Extract booking code
     */
    private static function extractBookingCode($fcSeg, $fc)
    {
        $bookingCode = $fcSeg['segment']['bookingCode'] ?? ($fc['bookingCode'] ?? null);

        if (empty($bookingCode)) return null;

        $bookingCode = strtoupper(trim($bookingCode));

        if (strlen($bookingCode) != 1 || !preg_match('/^[A-Z]$/', $bookingCode)) {
            return null;
        }

        return $bookingCode;
    }

    /**
     * Map booking code
     */
    private static function mapBookingCode($bookingCode, $segData, $fcIndex, $segIndex, &$map, &$indexMap, &$fcMap)
    {
        $carrier = strtoupper(trim($segData['carrier'] ?? $segData['marketingCarrier'] ?? $segData['carrierCode'] ?? ''));
        $flightNum = trim($segData['flightNumber'] ?? $segData['number'] ?? '');
        $departure = $segData['departure'] ?? [];
        $depAirport = is_array($departure) ? strtoupper(trim($departure['airport'] ?? $departure['iataCode'] ?? '')) : '';
        $depAt = is_array($departure) ? ($departure['at'] ?? $departure['dateTime'] ?? '') : '';

        if ($carrier && $flightNum && $depAirport) {
            $flightNumNorm = ltrim($flightNum, '0') ?: $flightNum;

            $baseKey = strtoupper($carrier . '_' . $flightNum . '_' . $depAirport);
            $baseKeyNorm = strtoupper($carrier . '_' . $flightNumNorm . '_' . $depAirport);

            if ($depAt) {
                $timeKey = str_replace(['T', ':', '-', ' ', '+'], '', substr($depAt, 0, 16));
                $map[$baseKey . '_' . $timeKey] = $bookingCode;
                $map[$baseKeyNorm . '_' . $timeKey] = $bookingCode;
            }

            $map[$baseKey] = $bookingCode;
            $map[$baseKeyNorm] = $bookingCode;
        }

        $fcMap[$fcIndex][$segIndex] = $bookingCode;
        $indexMap[count($indexMap)] = $bookingCode;
    }

    /**
     * Process single leg
     */
    private static function processLeg($leg, $legIndex, $group, $legDescs, $scheduleDescs, $bookingClassMap, $getById)
    {
        $legRef = $leg['ref'] ?? null;
        $legDesc = $legRef !== null ? $getById($legDescs, $legRef) : null;
        $legSchedules = is_array($legDesc['schedules'] ?? null) ? ($legDesc['schedules'] ?? []) : [];

        $legDescription = $group['groupDescription']['legDescriptions'][$legIndex] ?? null;
        $departureDate = $legDescription ? $legDescription['departureDate'] : '';

        $legSegments = [];
        $previousArrivalDateTime = null;
        $segmentIndexInLeg = 0;

        foreach ($legSchedules as $sch) {
            $sd = isset($sch['ref']) ? $getById($scheduleDescs, $sch['ref']) : null;
            if (!$sd) continue;

            $segment = self::processSegment(
                $sd,
                $departureDate,
                $previousArrivalDateTime,
                $legIndex,
                $segmentIndexInLeg,
                $bookingClassMap
            );

            $legSegments[] = $segment['data'];
            $previousArrivalDateTime = $segment['previousArrival'];
            $segmentIndexInLeg++;
        }

        $durationMin = $legDesc['elapsedTime'] ?? 0;
        $durationValue = self::minsToIso($durationMin);

        return (object)[
            'duration' => $durationValue,
            'segments' => $legSegments
        ];
    }

    /**
     * Process segment
     */
    private static function processSegment($sd, $departureDate, $previousArrivalDateTime, $legIndex, $segmentIndexInLeg, $bookingClassMap)
    {
        $depDate = $sd['departure']['date'] ?? $departureDate;
        $depTime = $sd['departure']['time'] ?? '00:00:00';
        $arrDate = $sd['arrival']['date'] ?? $departureDate;
        $arrTime = $sd['arrival']['time'] ?? '00:00:00';

        $dates = self::calculateSegmentDates($depDate, $depTime, $arrDate, $arrTime, $previousArrivalDateTime);
        $carrierInfo = self::extractCarrierInfo($sd['carrier'] ?? null, $sd);
        $segBookingClass = self::getSegmentBookingClass(
            $sd,
            $carrierInfo,
            $dates,
            $legIndex,
            $segmentIndexInLeg,
            $bookingClassMap
        );

        $segmentData = [
            'departure' => [
                'iataCode' => $sd['departure']['airport'] ?? null,
                'at' => $dates['departure'],
            ],
            'arrival' => [
                'iataCode' => $sd['arrival']['airport'] ?? null,
                'at' => $dates['arrival'],
            ],
            'carrierCode' => $carrierInfo['code'],
            'number' => $carrierInfo['flightNumber'],
            'aircraft' => ['code' => $carrierInfo['equipment']],
            'operating' => ['carrierCode' => $carrierInfo['code']],
            'duration' => isset($sd['elapsedTime']) ? self::minsToIso($sd['elapsedTime']) : null,
            'id' => $sd['id'] ?? null,
            'bookingCode' => $segBookingClass,
            'class' => $segBookingClass,
        ];

        if (isset($sd['departure']['terminal'])) {
            $segmentData['departure']['terminal'] = $sd['departure']['terminal'];
        }

        return [
            'data' => $segmentData,
            'previousArrival' => $dates['arrival']
        ];
    }

    /**
     * Calculate segment dates
     */
    private static function calculateSegmentDates($depDate, $depTime, $arrDate, $arrTime, $previousArrivalDateTime)
    {
        if ($previousArrivalDateTime) {
            $prevArrival = new \DateTime($previousArrivalDateTime);
            $currentDep = new \DateTime($depDate . 'T' . $depTime);

            if ($currentDep < $prevArrival) {
                $prevArrival->modify('+1 day');
                $depDate = $prevArrival->format('Y-m-d');
            }
        }

        $depDateTime = new \DateTime($depDate . 'T' . $depTime);
        $arrDateTime = new \DateTime($arrDate . 'T' . $arrTime);

        if ($arrDateTime < $depDateTime) {
            $arrDateTime->modify('+1 day');
            $arrDate = $arrDateTime->format('Y-m-d');
        }

        if ($previousArrivalDateTime) {
            $prevArr = new \DateTime($previousArrivalDateTime);
            $currArr = new \DateTime($arrDate . 'T' . $arrTime);

            while ($currArr < $prevArr) {
                $currArr->modify('+1 day');
                $arrDate = $currArr->format('Y-m-d');
            }
        }

        return [
            'departure' => $depDate . 'T' . $depTime,
            'arrival' => $arrDate . 'T' . $arrTime,
        ];
    }

    /**
     * Extract carrier info
     */
    private static function extractCarrierInfo($carrier, $sd)
    {
        if (is_array($carrier)) {
            return [
                'code' => $carrier['marketing'] ?? $carrier['operating'] ?? null,
                'flightNumber' => $carrier['marketingFlightNumber'] ?? $carrier['operatingFlightNumber'] ?? null,
                'equipment' => $carrier['equipment']['code'] ?? null,
            ];
        }

        return [
            'code' => $carrier,
            'flightNumber' => $sd['flightNumber'] ?? null,
            'equipment' => $sd['equipment'] ?? null,
        ];
    }

    /**
     * Get segment booking class
     */
    private static function getSegmentBookingClass($sd, $carrierInfo, $dates, $legIndex, $segmentIndexInLeg, $bookingClassMap)
    {
        extract($bookingClassMap);

        $depAirport = strtoupper(trim($sd['departure']['airport'] ?? ''));
        $carrierCode = strtoupper(trim($carrierInfo['code'] ?? ''));
        $flightNum = trim($carrierInfo['flightNumber'] ?? '');

        $segBookingClass = self::matchBookingClassByKey(
            $carrierCode,
            $flightNum,
            $depAirport,
            $dates['departure'],
            $segmentBookingClassMap
        );

        if (empty($segBookingClass)) {
            $segBookingClass = $segmentBookingClassMapByFareComponent[$legIndex][$segmentIndexInLeg] ?? null;
        }

        if (empty($segBookingClass)) {
            $globalIndex = $segmentIndexInLeg;
            $segBookingClass = $segmentBookingClassMapByIndex[$globalIndex] ?? null;
        }

        if (empty($segBookingClass)) {
            $segBookingClass = self::extractDirectBookingClass($sd);
        }

        return $segBookingClass ?: 'Y';
    }

    /**
     * Match booking class by key
     */
    private static function matchBookingClassByKey($carrierCode, $flightNum, $depAirport, $depAt, $map)
    {
        if (!$carrierCode || !$flightNum || !$depAirport) return null;

        $flightNumNorm = ltrim($flightNum, '0') ?: $flightNum;
        $keyVariations = [];

        if ($depAt) {
            $timeKey = str_replace(['T', ':', '-', ' ', '+'], '', substr($depAt, 0, 16));
            $keyVariations[] = strtoupper("{$carrierCode}_{$flightNum}_{$depAirport}_{$timeKey}");
            $keyVariations[] = strtoupper("{$carrierCode}_{$flightNumNorm}_{$depAirport}_{$timeKey}");
        }

        $keyVariations[] = strtoupper("{$carrierCode}_{$flightNum}_{$depAirport}");
        $keyVariations[] = strtoupper("{$carrierCode}_{$flightNumNorm}_{$depAirport}");

        if (strlen($flightNum) < 4 && is_numeric($flightNum)) {
            $paddedFlightNum = str_pad($flightNum, 4, '0', STR_PAD_LEFT);
            $keyVariations[] = strtoupper("{$carrierCode}_{$paddedFlightNum}_{$depAirport}");
        }

        foreach ($keyVariations as $key) {
            if (isset($map[$key])) return $map[$key];
        }

        return null;
    }

    /**
     * Extract direct booking class
     */
    private static function extractDirectBookingClass($sd)
    {
        $bookingCode = $sd['bookingCode'] ?? $sd['class'] ?? null;

        if ($bookingCode) {
            $bookingCode = strtoupper(trim($bookingCode));
            if (strlen($bookingCode) == 1 && preg_match('/^[A-Z]$/', $bookingCode)) {
                return $bookingCode;
            }
        }

        return null;
    }

    /**
     * Extract pricing data
     */
    private static function extractPricingData($passengerInfoList, $request)
    {
        $passengerTypePricing = [];
        $grandTotal = 0.0;
        $baseFareTotal = 0.0;
        $taxAmountTotal = 0.0;
        $currency = 'BDT';

        foreach ($passengerInfoList as $paxListItem) {
            $passengerInfo = $paxListItem['passengerInfo'] ?? [];
            $passengerType = $passengerInfo['passengerType'] ?? 'ADT';
            $passengerNumber = (int)($passengerInfo['passengerNumber'] ?? 1);
            $passengerTotalFare = $passengerInfo['passengerTotalFare'] ?? [];

            $rawTotal = (float)($passengerTotalFare['totalFare'] ?? 0);
            $rawBase = (float)($passengerTotalFare['baseFareAmount'] ?? 0);
            $rawTax = (float)($passengerTotalFare['totalTaxAmount'] ?? 0);
            $paxCurrency = $passengerTotalFare['currency'] ?? 'BDT';

            // Swap Sabre's reversed fields
            $paxBasePerPerson = $rawTax > 0 ? $rawTax : ($rawTotal - $rawBase);
            $paxTaxPerPerson = $rawBase > 0 ? $rawBase : ($rawTotal - $paxBasePerPerson);

            $paxTotalForAll = round($rawTotal * $passengerNumber, 2);
            $paxBaseForAll = round($paxBasePerPerson * $passengerNumber, 2);
            $paxTaxForAll = round($paxTaxPerPerson * $passengerNumber, 2);

            $passengerTypePricing[$passengerType] = [
                'type' => $passengerType,
                'type_label' => self::getPassengerTypeLabel($passengerType),
                'count' => $passengerNumber,
                'base_fare' => $paxBasePerPerson,
                'tax' => $paxTaxPerPerson,
                'total' => $rawTotal,
                'base_fare_total' => $paxBaseForAll,
                'tax_total' => $paxTaxForAll,
                'total_total' => $paxTotalForAll,
                'currency' => $paxCurrency,
                'base_fare_formatted' => format_money($paxBasePerPerson),
                'tax_formatted' => format_money($paxTaxPerPerson),
                'total_formatted' => format_money($rawTotal),
                'subtotal_formatted' => format_money($paxTotalForAll),
            ];

            $grandTotal += $paxTotalForAll;
            $baseFareTotal += $paxBaseForAll;
            $taxAmountTotal += $paxTaxForAll;
            $currency = $paxCurrency;
        }

        return [
            'passengerTypePricing' => $passengerTypePricing,
            'grandTotal' => round($grandTotal, 2),
            'baseFareTotal' => round($baseFareTotal, 2),
            'taxAmountTotal' => round($taxAmountTotal, 2),
            'currency' => $currency,
        ];
    }

    /**
     * Build flight item
     */
    private static function buildFlightItem($itinerary, $mockItineraries, $pricingData, $group, $index, $groupIndex, $scheduleDescs, $legDescs, $gir, $request, $getById)
    {
        $firstMockItinerary = reset($mockItineraries);
        $lastMockItinerary = end($mockItineraries);

        $firstSegment = $firstMockItinerary->segments[0] ?? null;
        $lastSegmentArray = $lastMockItinerary->segments ?? [];
        $lastSegment = end($lastSegmentArray);

        $departureTime = $firstSegment['departure']['at'] ?? '';
        $departureCode = $firstSegment['departure']['iataCode'] ?? 'N/A';
        $arrivalTime = $lastSegment['arrival']['at'] ?? '';
        $arrivalCode = $lastSegment['arrival']['iataCode'] ?? 'N/A';
        $carrier = $firstSegment['carrierCode'] ?? 'N/A';

        $totalDurationMin = 0;
        foreach ($itinerary['legs'] ?? [] as $leg) {
            $legDesc = $getById($legDescs, $leg['ref'] ?? null);
            $totalDurationMin += (int)($legDesc['elapsedTime'] ?? 0);
        }
        $durationValue = self::minsToIso($totalDurationMin);

        $alldata = self::buildAllData(
            $itinerary,
            $mockItineraries,
            $pricingData,
            $carrier,
            $gir,
            $request
        );

        $overallBookingClass = $alldata['itineraries'][0]['segments'][0]['class'] ?? 'Y';

        return [
            'id' => rand(),
            'original_data' => (object)[
                'itineraries' => $mockItineraries,
                'travelerPricings' => [(object)[
                    'price' => (object)[
                        'total' => $pricingData['grandTotal'],
                        'base' => $pricingData['baseFareTotal'],
                        'currency' => $pricingData['currency'],
                    ]
                ]],
                'validatingAirlineCodes' => [$carrier],
                'price' => (object)[
                    'grandTotal' => $pricingData['grandTotal'],
                    'base' => $pricingData['baseFareTotal'],
                    'currency' => $pricingData['currency'],
                ],
                'passengerTypePricing' => $pricingData['passengerTypePricing'],
                'passengerInfoList' => $itinerary['pricingInformation'][0]['fare']['passengerInfoList'] ?? [],
            ],
            'passenger_type_pricing' => $pricingData['passengerTypePricing'],
            'formatted_price' => format_money($pricingData['grandTotal']),
            'formatted_tax' => format_money($pricingData['taxAmountTotal']),
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'departure_code' => $departureCode,
            'arrival_code' => $arrivalCode,
            'duration' => $durationValue,
            'airline_image_url' => self::getAirlineImage($carrier),
            'is_bookable' => true,
            'booking_class' => $overallBookingClass,
            'fare_details' => [
            'base_fare' => $pricingData['baseFareTotal'],
            'base_fare_formatted' => format_money($pricingData['baseFareTotal']),
            'tax' => $pricingData['taxAmountTotal'],
            'tax_formatted' => format_money($pricingData['taxAmountTotal']),
            'total_fare' => $pricingData['grandTotal'],
            'total_fare_formatted' => format_money($pricingData['grandTotal']),
            'passenger_breakdown' => $pricingData['passengerTypePricing'],
        ],
            'request_data' => [
        'adults' => (int)($request->input('adults', 1)),
        'children' => (int)($request->input('children', 0)),
        'infants' => (int)($request->input('infants', 0)),
        'trip_type' => $request->input('trip_type', 'oneway'),
        'travelClass' => $request->input('travelClass', 'ECONOMY'),
        'from_where' => $request->input('from_where'),
        'to_where' => $request->input('to_where'),
        'departure_date' => $request->input('departure_date'),
        'return_date' => $request->input('return_date'),
        'segments' => $request->input('segments', []),
    ],
            'alldata' => $alldata,
            'index' => $index,
            'airline_code' => $carrier,
            'price' => $pricingData['grandTotal'],
            'currency' => $pricingData['currency'],
            'tax' => $pricingData['taxAmountTotal'],
            'itineraries' => $mockItineraries,
            'traveler_pricings' => [(object)[
        'price' => (object)[
            'total' => $pricingData['grandTotal'],
            'base' => $pricingData['baseFareTotal'],
            'currency' => $pricingData['currency'],
        ]
    ]],
        ];
    }

    /**
     * Build alldata structure
     */
    private static function buildAllData($itinerary, $mockItineraries, $pricingData, $carrier, $gir, $request)
    {
        $pricingInfo = $itinerary['pricingInformation'][0] ?? [];
        $fare = $pricingInfo['fare'] ?? [];

        $alldataItineraries = [];
        foreach ($mockItineraries as $mockItin) {
            $alldataItineraries[] = [
                'duration' => $mockItin->duration,
                'segments' => array_map(function ($seg) {
                    $segArray = (array)$seg;

                    $segArray['departure_iata_code'] = $segArray['departure']['iataCode'] ?? null;
                    $segArray['arrival_iata_code'] = $segArray['arrival']['iataCode'] ?? null;
                    $segArray['departure_at'] = isset($segArray['departure']['at'])
                        ? Carbon::parse($segArray['departure']['at'])->format('Y-m-d\TH:i:s')
                        : null;
                    $segArray['arrival_at'] = $segArray['arrival']['at'] ?? null;
                    $segArray['carrier_code'] = $segArray['carrierCode'] ?? null;
                    $segArray['flight_number'] = $segArray['number'] ?? null;
                    $segArray['aircraft_code'] = $segArray['aircraft']['code'] ?? 'N/A';

                    return $segArray;
                }, $mockItin->segments),
            ];
        }

        $baseFareAmount = $pricingData['baseFareTotal'];
        $supplierFeePercent = config('flight.supplier_fee_percent', 0);
        $ticketingFeeFlat = config('flight.ticketing_fee_amount', 0);
        $supplierFee = $supplierFeePercent > 0 ? ($baseFareAmount * $supplierFeePercent / 100) : 0.0;
        $ticketingFee = $ticketingFeeFlat > 0 ? $ticketingFeeFlat : 0.0;

        return [
            'type' => 'flight-offer',
            'id' => $itinerary['id'] ?? null,
            'source' => 'Saver',
            'instantTicketingRequired' => isset($fare['vita']) ? ($fare['vita'] ? 'true' : 'false') : 'false',
            'nonHomogeneous' => 'false',
            'oneWay' => count($itinerary['legs'] ?? []) <= 1 ? 'true' : 'false',
            'isUpsellOffer' => 'false',
            'lastTicketingDate' => $fare['lastTicketDate'] ?? null,
            'lastTicketingDateTime' => self::getLastTicketingDateTime($fare),
            'validBookingTime' => self::getLastTicketingDateTime($fare) ?: ($fare['lastTicketDate'] ?? null),
            'numberOfBookableSeats' => '9',
            'itineraries' => $alldataItineraries,
            'price' => [
                'currency' => $pricingData['currency'],
                'total' => number_format($pricingData['grandTotal'], 2, '.', ''),
                'base' => number_format($pricingData['baseFareTotal'], 2, '.', ''),
                'fees' => [
                    ['amount' => number_format($supplierFee, 2, '.', ''), 'type' => 'SUPPLIER'],
                    ['amount' => number_format($ticketingFee, 2, '.', ''), 'type' => 'TICKETING'],
                ],
                'grandTotal' => number_format($pricingData['grandTotal'], 2, '.', ''),
                'tax' => number_format($pricingData['taxAmountTotal'], 2, '.', ''),
            ],
            'pricingOptions' => [
                'fareType' => isset($fare['fareType']) ? (array)$fare['fareType'] : ['PUBLISHED'],
                'includedCheckedBagsOnly' => 'true',
            ],
            'validatingAirlineCodes' => [$carrier],
            'travelerPricings' => self::buildTravelerPricings($pricingData, $fare, $gir),
            'grandTotal' => number_format($pricingData['grandTotal'], 2, '.', ''),
            'price' => number_format($pricingData['grandTotal'], 2, '.', ''),
            'base_price' => number_format($pricingData['baseFareTotal'], 2, '.', ''),
            'currency' => $pricingData['currency'],
            'tax_amount' => number_format($pricingData['taxAmountTotal'], 2, '.', ''),
            'supplier_fee' => number_format($supplierFee, 2, '.', ''),
            'ticketing_fee' => number_format($ticketingFee, 2, '.', ''),
            'total_fee' => number_format($supplierFee + $ticketingFee, 2, '.', ''),
            'base_fee' => number_format($pricingData['baseFareTotal'], 2, '.', ''),
            'airline_code' => $carrier,
            'available_seats' => 9,
            'bookable_seats' => 9,
            'passenger_type_pricing' => array_values($pricingData['passengerTypePricing']),
            'passengerInfoList' => $itinerary['pricingInformation'][0]['fare']['passengerInfoList'] ?? [],
            'traveler_pricings' => array_map(function ($pricing) use ($pricingData) {
                return [
                    'traveler_type' => $pricing['type'] ?? 'ADT',
                    'fare_option' => 'STANDARD',
                    'currency' => $pricingData['currency'],
                    'total' => number_format($pricing['total'] ?? 0, 2, '.', ''),
                    'base' => number_format($pricing['base_fare'] ?? 0, 2, '.', ''),
                    'cabin' => 'ECONOMY',
                    'fare_basis' => null,
                    'class' => null,
                ];
            }, $pricingData['passengerTypePricing']),
            'flight_routes' => self::buildFlightRoutes($mockItineraries),
            'routeSummary' => self::buildFlightRoutes($mockItineraries),
        ];
    }

    /**
     * Build traveler pricings
     */
    private static function buildTravelerPricings($pricingData, $fare, $gir)
    {
        $result = [];

        foreach ($pricingData['passengerTypePricing'] as $pricing) {
            $baggage = self::extractBaggage($fare, $gir);

            $result[] = [
                'travelerId' => '1',
                'fareOption' => 'STANDARD',
                'travelerType' => $pricing['type'] ?? 'ADULT',
                'price' => [
                    'currency' => $pricingData['currency'],
                    'total' => number_format($pricing['total'] ?? 0, 2, '.', ''),
                    'base' => number_format($pricing['base_fare'] ?? 0, 2, '.', ''),
                ],
                'fareDetailsBySegment' => [[
                    'segmentId' => null,
                    'cabin' => 'ECONOMY',
                    'fareBasis' => null,
                    'class' => null,
                    'includedCheckedBags' => $baggage['checked'],
                    'includedCabinBags' => $baggage['cabin'],
                ]],
            ];
        }

        return $result;
    }

    /**
     * Extract baggage info
     */
    private static function extractBaggage($fare, $gir)
    {
        $baggage = [
            'checked' => ['weight' => '20', 'weightUnit' => 'KG'],
            'cabin' => ['weight' => '7', 'weightUnit' => 'KG']
        ];

        $passengerInfoList = $fare['passengerInfoList'] ?? [];
        if (isset($passengerInfoList[0]['passengerInfo']['baggageInformation'][0]['allowance']['ref'])) {
            $baggageRef = $passengerInfoList[0]['passengerInfo']['baggageInformation'][0]['allowance']['ref'];
            $baggageAllowanceDescs = $gir['baggageAllowanceDescs'] ?? [];

            foreach ($baggageAllowanceDescs as $bagDesc) {
                if (($bagDesc['id'] ?? null) == $baggageRef) {
                    $weight = $bagDesc['weight'] ?? 20;
                    $unit = strtoupper($bagDesc['unit'] ?? 'KG');
                    $baggage['checked'] = ['weight' => (string)$weight, 'weightUnit' => $unit];
                    break;
                }
            }
        }

        return $baggage;
    }

    /**
     * Build flight routes
     */
    private static function buildFlightRoutes($mockItineraries)
    {
        return array_map(function ($mockItin) {
            $segments = [];

            foreach ($mockItin->segments as $seg) {
                $depAt = $seg['departure']['at'] ?? '';
                $arrAt = $seg['arrival']['at'] ?? '';

                $depTime = self::extractTime($depAt);
                $arrTime = self::extractTime($arrAt);

                $segBookingClass = $seg['bookingCode'] ?? $seg['class'] ?? 'Y';

                $segments[] = [
                    'departure_iata_code' => $seg['departure']['iataCode'] ?? 'N/A',
                    'departure_at' => $depAt,
                    'departure_time' => $depTime,
                    'arrival_iata_code' => $seg['arrival']['iataCode'] ?? 'N/A',
                    'arrival_at' => $arrAt,
                    'arrival_time' => $arrTime,
                    'carrier_code' => $seg['carrierCode'] ?? 'N/A',
                    'aircraft_code' => $seg['aircraft']['code'] ?? 'N/A',
                    'flight_number' => $seg['number'] ?? 'N/A',
                    'duration' => $seg['duration'] ?? 'N/A',
                    'class' => $segBookingClass,
                    'departure' => $seg['departure'] ?? [],
                    'arrival' => $seg['arrival'] ?? [],
                    'carrierCode' => $seg['carrierCode'] ?? 'N/A',
                    'number' => $seg['number'] ?? 'N/A',
                ];
            }

            return [
                'segments' => $segments,
                'duration' => $mockItin->duration ?? 'N/A'
            ];
        }, $mockItineraries);
    }

    /**
     * Utility Methods
     */
    private static function extractIataCode($value)
    {
        if (!$value) return null;
        if (preg_match('/\b([A-Z]{3})\b/', strtoupper($value), $m)) {
            return $m[1];
        }
        return strtoupper(trim($value));
    }

    private static function formatIsoDate($value, $fallback = '2025-10-15T00:00:00')
    {
        $value = trim((string)($value ?? ''));
        if ($value === '') return $fallback;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value . 'T00:00:00';
        }
        return $value;
    }

    private static function minsToIso($minutes)
    {
        if ($minutes === null || $minutes === '' || !is_numeric($minutes)) return null;

        $minutes = (int)$minutes;
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        $out = 'PT';
        if ($hours) $out .= $hours . 'H';
        if ($mins) $out .= $mins . 'M';

        return $out === 'PT' ? 'PT0M' : $out;
    }

    private static function extractTime($datetime)
    {
        if (empty($datetime) || strpos($datetime, 'T') === false) return '';

        $timeStr = substr($datetime, strpos($datetime, 'T') + 1);
        return preg_replace('/[+-]\d{2}:\d{2}$/', '', $timeStr);
    }

    private static function getLastTicketingDateTime($fare)
    {
        $lastTicketDate = $fare['lastTicketDate'] ?? null;
        $lastTicketTime = $fare['lastTicketTime'] ?? null;

        if ($lastTicketDate) {
            if ($lastTicketTime) {
                return $lastTicketDate . ' ' . $lastTicketTime . ':00';
            }
            return $lastTicketDate . ' 23:59:59';
        }

        return $fare['lastTicketDateTime'] ?? null;
    }

    private static function getAirlineImage($airlineCode)
    {
        try {
            $airline = \Modules\Flight\Models\Airline::where('designator', $airlineCode)->first();
            if ($airline && $airline->image_id) {
                return get_file_url($airline->image_id, 'thumb', true);
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        return '';
    }

    private static function getPassengerTypeLabel($code)
    {
        $labels = [
            'ADT' => 'Adult (12+ years)',
            'CNN' => 'Child (2-11 years)',
            'CHD' => 'Child (2-11 years)',
            'C03' => 'Child (2-4 years, No UT3 tax)',
            'C07' => 'Child (5-11 years, With UT3 tax)',
            'INF' => 'Infant (0-2 years)',
            'INS' => 'Infant (with seat)',
        ];

        return $labels[$code] ?? $code;
    }

}
