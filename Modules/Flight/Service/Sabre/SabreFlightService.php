<?php

namespace Modules\Flight\Service\Sabre;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Flight\Service\FlightChargesService;
use Modules\Flight\Service\FlightDataHelper;
use Modules\Flight\Service\FlightDiscountService;
use Modules\Flight\Service\PassengerProcessor;
use Modules\Flight\Service\SegmentProcessor;

class SabreFlightService
{
    private FlightDataHelper $helper;
    public function __construct(
        FlightDiscountService $discountService,
        FlightChargesService  $chargesService
    )
    {
        $this->discountService = $discountService;
        $this->chargesService = $chargesService;
        $this->helper = new FlightDataHelper();
    }


    /**
     * Search flights
     */
    public function search($searchData)
    {
        $sabrePayload = $this->buildSearchPayload($searchData);

        $response = $this->callSabreApi($sabrePayload);
        \Log::channel('daily')->info('Sabre search response: ' . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $this->transformSabreResponse($response);
    }


    /**
     * Transform Sabre response to standard format
     */
    private function transformSabreResponse(array $response): array
    {
        if (empty($response['groupedItineraryResponse']['itineraryGroups'])) {
            return [
                'success' => true,
                'flights' => [],
                'total_results' => 0,
                'message' => 'No flights found',
            ];
        }

        $itineraryGroups = $response['groupedItineraryResponse']['itineraryGroups'];
        $scheduleDescs = $response['groupedItineraryResponse']['scheduleDescs'] ?? [];
        $legDescs = $response['groupedItineraryResponse']['legDescs'] ?? [];
        $baggageDescs = $response['groupedItineraryResponse']['baggageAllowanceDescs'] ?? [];
        $taxDescs = $response['groupedItineraryResponse']['taxDescs'] ?? [];
        $fareComponentDescs = $response['groupedItineraryResponse']['fareComponentDescs'] ?? [];

        $flights = [];

        foreach ($itineraryGroups as $group) {
            $groupDescription = $group['groupDescription'] ?? [];
            foreach ($group['itineraries'] ?? [] as $itinerary) {
                $flights[] = $this->formatItinerary(
                    $itinerary, $scheduleDescs, $legDescs, $baggageDescs,
                    $groupDescription, $taxDescs, $fareComponentDescs
                );
            }
        }

        return [
            'success' => true,
            'flights' => $flights,
            'total_results' => count($flights),
            'currency' => 'BDT',
        ];
    }

    private function callSabreApi(array $payload): array
    {
//        return $payload;
        $version=config('sabre.api_version');
        $token = $this->getAuthToken();
//dd($token);
        $response = Http::withToken($token)
            ->acceptJson()
            ->withOptions([
                'verify' => config('app.env') === 'production'
            ])
            ->post(config('sabre.rest.base_url') . '/v5/offers/shop', $payload);

        if (!$response->successful()) {
            throw new \Exception('Sabre API call failed');
        }

        return $response->json();
    }
    public function buildSearchPayload(array $searchData): array
    {
        $travelPreferences = [
            "CabinPref" => [[
                "Cabin" => $this->mapTravelClass($searchData['travel_class'])
            ]],
            "TPA_Extensions" => [
                "NumTrips" => [
                    "Number" =>(int) config('sabre.search.num_trips')
                ],
                "DataSources" => [
                    "NDC"   => env('SABRE_NDC_SOURCE','Enable'),
                    "ATPCO" => env('Sabre_ATPCO_SOURCE','Enable'),
                    "LCC"   => env('SABRE_LCC_SOURCE','Disable'),
                ],
                "PreferNDCSourceOnTie" => [
                    "Value" => true
                ]
            ],
        ];
        // Add airline preference if provided (supports both single and multiple)
        if (!empty($searchData['airline_codes'])) {
            // Convert to array if string
            $airlineCodes = is_string($searchData['airline_codes'])
                ? explode(',', $searchData['airline_codes'])
                : $searchData['airline_codes'];

            // Clean and filter
            $airlineCodes = array_filter(array_map(function ($code) {
                return strtoupper(trim($code));
            }, $airlineCodes));

            // Add to preferences
            if (!empty($airlineCodes)) {
                $travelPreferences["VendorPref"] = array_map(function ($code) {
                    return [
                        "Code" => $code,
                        "PreferLevel" => "Preferred"
                    ];
                }, $airlineCodes);
            }
        }

        return [
            "OTA_AirLowFareSearchRQ" => [
                "Version" => config('sabre.api_version'),
                'POS' => $this->buildPOS(),
                "OriginDestinationInformation" => $this->buildOriginDestination($searchData['segments']),
                "TravelerInfoSummary" => [
                    "PriceRequestInformation" => [
                        "CurrencyCode" => "BDT"  // ← এটা যোগ করুন
                    ],
                    "AirTravelerAvail" => [[
                        "PassengerTypeQuantity" => $this->buildPassengerTypeQuantity($searchData['passengers']),
                    ]],
                ],
                "TravelPreferences" => $travelPreferences,
                "TPA_Extensions" => [
                    "IntelliSellTransaction" => [
                        "RequestType" => [
                            "Name" => config('sabre.search.request_type')
                        ]
                    ],
                ],
            ],
        ];
    }

    /**
     * Map travel class to Sabre format
     */
    private function mapTravelClass(string $class): string
    {
        $mapping = [
            'ECONOMY' => 'Economy',
            'BUSINESS' => 'Business',
            'FIRST' => 'First',
            'PREMIUM_ECONOMY' => 'PremiumEconomy',
        ];

        return $mapping[$class] ?? 'Economy';
    }

    /**
     * Build origin destination information
     */
    private function buildOriginDestination(array $segments): array
    {
        return collect($segments)->map(function ($segment, $index) {
            return [
                "RPH" => (string)($index + 1),
                "DepartureDateTime" => $this->helper->formatDateTime($segment['departure_date']),
                "OriginLocation" => [
                    "LocationCode" => $segment['origin']['code']
                ],
                "DestinationLocation" => [
                    "LocationCode" => $segment['destination']['code']
                ]
            ];
        })->values()->toArray();
    }

    /**
     * Build passenger type quantity for Sabre API
     */
    private function buildPassengerTypeQuantity(array $passengers): array
    {
        $passengerTypes = [];

        if ($passengers['adults'] > 0) {
            $passengerTypes[] = [
                "Code" => "ADT",
                "Quantity" => $passengers['adults']
            ];
        }

        if (!empty($passengers['children_ages'])) {
            $childCategories = $this->categorizeChildrenBySabreRules($passengers['children_ages']);

            if ($childCategories['c07'] > 0) {
                $passengerTypes[] = [
                    "Code" => "C07",
                    "Quantity" => $childCategories['c07']
                ];
            }
            if ($childCategories['c03'] > 0) {
                $passengerTypes[] = [
                    "Code" => "C03",
                    "Quantity" => $childCategories['c03']
                ];
            }


        }

        if (($passengers['infants'] ?? 0) > 0) {
            $passengerTypes[] = [
                "Code" => "INF",
                "Quantity" => $passengers['infants']
            ];
        }

        return $passengerTypes;
    }

    /**
     * Categorize children by Sabre's age rules
     */
    private function categorizeChildrenBySabreRules(array $childrenAges): array
    {
        $c03Count = 0;
        $c07Count = 0;

        foreach ($childrenAges as $age) {
            $age = (int)$age;

            if ($age >= 2 && $age <= 4) {
                $c03Count++;
            } elseif ($age >= 5 && $age <= 11) {
                $c07Count++;
            }
        }

        return [
            'c03' => $c03Count,
            'c07' => $c07Count,
        ];
    }

    /**
     * Build POS (Point of Sale)
     */
    private function buildPOS(): array
    {
        return [
            'Source' => [[
                'PseudoCityCode' => config('sabre.pcc'),
                'RequestorID' => [
                    'Type' => '1',
                    'ID' => '1',
                    'CompanyName' => [
                        'Code' => 'TN',
                        'CompanyShortName' => 'TN'
                    ]
                ]
            ]]
        ];
    }


    private function formatItinerary(
        array $itinerary,
        array $scheduleDescs,
        array $legDescs,
        array $baggageDescs,
        array $groupDescription   = [],
        array $taxDescs           = [],
        array $fareComponentDescs = []
    ): array {
        $pricing = $itinerary['pricingInformation'][0] ?? [];
        $fare = $pricing['fare'] ?? [];
        $totalFare = $fare['totalFare'] ?? [];
        $passengerInfoList = $fare['passengerInfoList'] ?? [];
        $firstPassengerInfo = $passengerInfoList[0]['passengerInfo'] ?? [];
        $fareComponents = $firstPassengerInfo['fareComponents'] ?? [];

        $distributionModel = $pricing['distributionModel'] ?? 'ATPCO';
        $isNDC = $distributionModel === 'NDC';

        $offerItemIds = [];
        if ($isNDC) {
            foreach ($passengerInfoList as $pax) {
                $id = $pax['passengerInfo']['offerItemId'] ?? null;
                if ($id && !in_array($id, $offerItemIds)) {
                    $offerItemIds[] = $id;
                }
            }
            // Fallback
            if (empty($offerItemIds) && !empty($fare['offerItemId'])) {
                $offerItemIds[] = $fare['offerItemId'];
            }
        }

        $legDescriptions = $groupDescription['legDescriptions'] ?? [];

        if ($isNDC) {
            $fareInfo = $this->extractNDCFareInfo($fareComponents, $fareComponentDescs);
        } else {
            $fareInfo = $this->extractFareComponentsInfo($fareComponents);
        }

        $legs = $this->formatLegs(
            $itinerary['legs'],
            $legDescs,
            $scheduleDescs,
            $legDescriptions,
            $fareInfo
        );

        // ✅ Extract data for calculations
        $validatingCarrier = $fare['validatingCarrierCode'] ?? null;
        $departureCode     = $legs[0]['departure']['airport_code'] ?? null;
        $arrivalCode       = $legs[0]['arrival']['airport_code'] ?? null;


        // API prices (grand total from Sabre)
        $apiSubtotal = $totalFare['totalPrice'] ?? 0;
        $baseFare = $totalFare['equivalentAmount'] ?? $totalFare['baseFareAmount'] ?? 0;
        $taxAmount   = $totalFare['totalTaxAmount'] ?? 0;

        if ($isNDC) {
            $passengerInfoList = $this->normalizeNDCPassengerInfoList($passengerInfoList);
        }

        // Calculate total segments
        $totalSegments = 0;
        foreach ($legs as $leg) {
            $totalSegments += $leg['total_segments'];
        }

        $validatingCarrierRoute = $this->getValidatingCarrierRoute(
            $validatingCarrier,
            $itinerary['legs'],
            $legDescs,
            $scheduleDescs
        );


        $flightDiscountInfo = $this->discountService->calculate(
            $validatingCarrier,
            $validatingCarrierRoute['departure'],  // আগে ছিল $departureCode
            $validatingCarrierRoute['arrival'],    // আগে ছিল $arrivalCode
            $passengerInfoList,
            $totalSegments,
            'sabre'
        );


        $grandTotal = $flightDiscountInfo['grand_total'];

        // ✅ Legacy price calculations (পুরানো view এর জন্য)
        $priceBeforeDiscounts = $grandTotal['api_subtotal']
            + $grandTotal['total_ait']
            + $grandTotal['total_service_charge'];

        $finalPrice = $priceBeforeDiscounts
            - $grandTotal['total_user_discount']
            - $grandTotal['total_user_seg_discount'];

        // সব leg এর segments flat করে pass করুন
        $allSegments = [];
        foreach ($legs as $leg) {
            foreach ($leg['segments'] as $seg) {
                $allSegments[] = $seg;
            }
        }

        return [
            'id'     => $itinerary['id'],
            'source' => 'sabre',
            'legs'   => $legs,

            'distribution_model' => $distributionModel,
            'is_ndc'             => $isNDC,
            'offer_id'           => $isNDC ? ($pricing['offer']['offerId'] ?? null) : null,
            'offer_item_id' => $isNDC ? $offerItemIds : null,
            'time_to_live'       => $isNDC ? ($pricing['offer']['timeToLive'] ?? null) : null,


            // ✅ Complete Price Breakdown
            'price' => [
                // API Original
                'api_base_fare' => $baseFare,
                'api_tax'       => $taxAmount,
                'api_subtotal'  => $apiSubtotal,

                // Charges
                'ait_amount'    => $grandTotal['total_ait'],
                'service_charge'=> $grandTotal['total_service_charge'],

                // Subtotal before discounts
                'subtotal_before_discount' => round($priceBeforeDiscounts, 2),

                // User Discounts
                'flight_discount'  => $grandTotal['total_user_discount'],
                'segment_discount' => $grandTotal['total_user_seg_discount'],
                'total_discounts'  => round($grandTotal['total_user_discount'] + $grandTotal['total_user_seg_discount'], 2),

                // ✅ Final user payable
                'total'         => round($finalPrice, 2),
                'currency'      => $totalFare['currency'] ?? 'BDT',
                'base_currency' => $totalFare['baseFareCurrency'] ?? 'USD',

                // ✅ Our side (নতুন)
                'own_discount'     => $grandTotal['total_own_discount'],
                'own_seg_discount' => $grandTotal['total_own_seg_discount'],
                'total_commission' => $grandTotal['total_commission'],
                'own_cost'         => $grandTotal['total_own_cost'],
                'gross_profit'     => $grandTotal['gross_profit'],
            ],

            // ✅ Flight Discount Details
            'flight_discount_details' => $flightDiscountInfo,

            // ✅ Per-passenger price breakdown (নতুন)
            'passenger_price_breakdown' => $flightDiscountInfo['passenger_breakdowns'],

            // ✅ Charges Details (backward compatibility)
            'charges_details' => [
                'ait_charge_percentage'        => $flightDiscountInfo['ait_charge_percentage'],
                'ait_amount'                   => $grandTotal['total_ait'],
                'service_charge'               => $grandTotal['total_service_charge'],
                'segment_discount_per_segment' => $flightDiscountInfo['segment_discount_per_segment'],
                'segment_discount_total'       => $grandTotal['total_user_seg_discount'],
                'flight_discount_label'        => $flightDiscountInfo['flight_discount_label'] ?? null,
                'segment_discount_label'       => $flightDiscountInfo['segment_discount_label'] ?? null,
            ],

            'passengers' => $this->formatPassengerBreakdown($passengerInfoList, $baggageDescs, $allSegments, $isNDC),
            'refundable'    => $isNDC ? null : !($firstPassengerInfo['nonRefundable'] ?? false),
            'refund_policy' => $isNDC
                ? 'fare_conditions_apply'
                : (($firstPassengerInfo['nonRefundable'] ?? false) ? 'non_refundable' : 'refundable'),

            'eTicketable'     => $fare['eTicketable'] ?? false,
            'validating_carrier' => $fare['validatingCarrierCode'] ?? null,
            'vita'            => $fare['vita'] ?? false,
            'last_ticket_date'=> $fare['lastTicketDate'] ?? null,
            'last_ticket_time'=> $fare['lastTicketTime'] ?? null,
            'pricing_source'  => $itinerary['pricingSource'] ?? null,
            'taxes_breakdown' => $this->formatTaxBreakdown($passengerInfoList, $taxDescs),
        ];
    }

    private function extractNDCFareInfo(array $fareComponents, array $fareComponentDescs = []): array
    {
        $descMap = [];
        foreach ($fareComponentDescs as $desc) {
            if (isset($desc['id'])) {
                $descMap[$desc['id']] = $desc;
            }
        }

        $fareInfo = [];
        foreach ($fareComponents as $component) {
            $ref      = $component['ref'] ?? null;
            $desc     = $ref ? ($descMap[$ref] ?? null) : null;
            $segments = $component['segments'] ?? [];

            foreach ($segments as $segment) {
                $segmentData = $segment['segment'] ?? [];
                $fareInfo[]  = [
                    'fare_basis_code'    => $desc['fareBasisCode']            ?? null,
                    'booking_code'       => $segmentData['bookingCode']       ?? null,
                    'cabin_code'         => $segmentData['cabinCode']         ?? null,
                    'meal_code'          => $segmentData['mealCode']          ?? null,
                    'seats_available'    => $segmentData['seatsAvailable']    ?? null,
                    'availability_break' => $segmentData['availabilityBreak'] ?? false,
                ];
            }
        }
        return $fareInfo;
    }

    private function normalizeNDCPassengerInfoListold(array $passengerInfoList): array
    {
        return array_map(function ($passengerItem) {
            $info = $passengerItem['passengerInfo'] ?? [];
            $fare = $info['passengerTotalFare']     ?? [];

            if (!isset($info['nonRefundable'])) {
                $passengerItem['passengerInfo']['nonRefundable'] = false;
            }
            if (empty($fare['equivalentAmount'])) {
                $passengerItem['passengerInfo']['passengerTotalFare']['equivalentAmount'] =
                    $fare['baseFareAmount'] ?? $fare['totalFare'] ?? 0;
            }
            if (empty($fare['equivalentCurrency'])) {
                $passengerItem['passengerInfo']['passengerTotalFare']['equivalentCurrency'] = 'BDT';
            }
            if (!isset($info['taxes'])) {
                $passengerItem['passengerInfo']['taxes'] = [];
            }
            if (!isset($info['currencyConversion'])) {
                $passengerItem['passengerInfo']['currencyConversion'] = [
                    'from'             => $fare['baseFareCurrency'] ?? 'BDT',
                    'to'               => 'BDT',
                    'exchangeRateUsed' => 1,
                ];
            }
            return $passengerItem;
        }, $passengerInfoList);
    }

   private function getValidatingCarrierRoute(
            ?string $validatingCarrier,
            array   $legs,
            array   $legDescs,
            array   $scheduleDescs
        ): array {
            $allRoutes = [];
        
            foreach ($legs as $leg) {
                $legDesc   = $legDescs[$leg['ref'] - 1] ?? [];
                $schedules = $legDesc['schedules']       ?? [];
        
                foreach ($schedules as $scheduleRef) {
                    $schedule = $scheduleDescs[$scheduleRef['ref'] - 1] ?? [];
                    $carrier  = $schedule['carrier']['marketing'] ?? null;
        
                    if ($carrier === $validatingCarrier) {
                        $allRoutes[] = [
                            'departure' => $schedule['departure']['airport'] ?? null,
                            'arrival'   => $schedule['arrival']['airport']   ?? null,
                        ];
                    }
                }
            }
        
            // fallback
            if (empty($allRoutes)) {
                $firstLegDesc   = $legDescs[$legs[0]['ref'] - 1]   ?? [];
                $firstSchedules = $firstLegDesc['schedules']        ?? [];
                $lastLegDesc    = $legDescs[end($legs)['ref'] - 1] ?? [];
                $lastSchedules  = $lastLegDesc['schedules']         ?? [];
        
                $firstDep = null;
                $lastArr  = null;
        
                if (!empty($firstSchedules)) {
                    $s = $scheduleDescs[$firstSchedules[0]['ref'] - 1] ?? [];
                    $firstDep = $s['departure']['airport'] ?? null;
                }
                if (!empty($lastSchedules)) {
                    $s = $scheduleDescs[end($lastSchedules)['ref'] - 1] ?? [];
                    $lastArr = $s['arrival']['airport'] ?? null;
                }
        
                return ['departure' => $firstDep, 'arrival' => $lastArr];
            }
        
            // ✅ সব routes discount service এ check করে best match return করো
            $bestRoute      = $allRoutes[0];
            $bestScore      = -1;
        
            foreach ($allRoutes as $route) {
                $score = $this->discountService->getRouteMatchScore(
                    $validatingCarrier,
                    $route['departure'],
                    $route['arrival'],
                    'sabre'
                );
        
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestRoute = $route;
                }
            }
        
            return $bestRoute;
        }
    private function normalizeNDCPassengerInfoList(array $passengerInfoList): array
    {
        return array_map(function ($passengerItem) {
            $info = $passengerItem['passengerInfo'] ?? [];
            $fare = $info['passengerTotalFare'] ?? [];

            // NDC এ baseFareCurrency = BDT, তাই equivalentAmount = baseFareAmount
            // ATPCO format এ আনো
            $passengerItem['passengerInfo']['nonRefundable'] =
                $info['nonRefundable'] ?? false;

            // equivalentAmount নিশ্চিত করো
            if (empty($fare['equivalentAmount'])) {
                $passengerItem['passengerInfo']['passengerTotalFare']['equivalentAmount'] =
                    $fare['baseFareAmount'] ?? $fare['totalFare'] ?? 0;
            }
            if (empty($fare['equivalentCurrency'])) {
                $passengerItem['passengerInfo']['passengerTotalFare']['equivalentCurrency'] = 'BDT';
            }

            // taxes empty হলে empty array দাও
            if (empty($info['taxes'])) {
                $passengerItem['passengerInfo']['taxes'] = [];
            }

            // currencyConversion না থাকলে default দাও
            if (empty($info['currencyConversion'])) {
                $passengerItem['passengerInfo']['currencyConversion'] = [
                    'from'              => $fare['baseFareCurrency'] ?? 'BDT',
                    'to'                => 'BDT',
                    'exchangeRateUsed'  => 1,
                ];
            }

            return $passengerItem;
        }, $passengerInfoList);
    }

    private function getValidatingCarrierRouteold(
        string $validatingCarrier,
        array $legs,
        array $legDescs,
        array $scheduleDescs
    ): array {
        $firstDeparture = null;
        $lastArrival    = null;

        foreach ($legs as $leg) {
            $legDesc  = $legDescs[$leg['ref'] - 1] ?? [];
            $schedules = $legDesc['schedules'] ?? [];

            foreach ($schedules as $scheduleRef) {
                $schedule = $scheduleDescs[$scheduleRef['ref'] - 1] ?? [];
                $carrier  = $schedule['carrier']['marketing'] ?? null;

                if ($carrier === $validatingCarrier) {
                    // প্রথমবার পেলে departure set করো
                    if ($firstDeparture === null) {
                        $firstDeparture = $schedule['departure']['airport'] ?? null;
                    }
                    // প্রতিবার update করো, শেষেরটা থাকবে
                    $lastArrival = $schedule['arrival']['airport'] ?? null;
                }
            }
        }

        return [
            'departure' => $firstDeparture,
            'arrival'   => $lastArrival,
        ];
    }
    /**
     * Format passenger breakdown
     */
    private function formatPassengerBreakdown(array $passengerInfoList, array $baggageDescs, array $segments = [], $isNDC): array
    {
        $passengers = [];

        foreach ($passengerInfoList as $passenger) {
            $info = $passenger['passengerInfo'] ?? [];
            $fare = $info['passengerTotalFare'] ?? [];
            $currencyConversion = $info['currencyConversion'] ?? [];

            $passengers[] = [
                'type' => $info['passengerType'] ?? 'ADT',
                'type_label' => $this->helper->getPassengerTypeLabel($info['passengerType'] ?? 'ADT'),
                'count' => $info['passengerNumber'] ?? 1,
                'total_fare' => $fare['totalFare'] ?? 0,
                'base_fare' => $fare['baseFareAmount'] ?? 0,
                'base_fare_currency' => $fare['baseFareCurrency'] ?? 'USD',
                'tax_amount' => $fare['totalTaxAmount'] ?? 0,
                'equivalent_amount' => $fare['equivalentAmount'] ?? 0,
                'equivalent_currency' => $fare['equivalentCurrency'] ?? 'BDT',
                'currency' => $fare['currency'] ?? 'BDT',
                'exchange_rate' => $currencyConversion['exchangeRateUsed'] ?? null,
                'exchange_from' => $currencyConversion['from'] ?? 'USD',
                'exchange_to' => $currencyConversion['to'] ?? 'BDT',
                'refundable'    => $isNDC ? null : !($info['nonRefundable'] ?? false),
                'refund_policy' => $isNDC
                    ? 'fare_conditions_apply'
                    : (($info['nonRefundable'] ?? false) ? 'non_refundable' : 'refundable'),

                'baggage'            => $this->formatBaggage($info['baggageInformation'] ?? [], $baggageDescs),           // backward compat
                'baggage_by_segment' => $this->formatBaggageBySegment(
                    $info['baggageInformation'] ?? [],
                    $baggageDescs,
                    $segments
                ),
            ];
        }

        return $passengers;
    }

    /**
     * Format baggage information
     */
    private function formatBaggageBySegment(array $baggageInfo, array $baggageDescs, array $segments = []): array
    {
        $segmentBaggage = [];

        foreach ($baggageInfo as $bag) {
            $ref     = $bag['allowance']['ref'] ?? null;
            $airline = $bag['airlineCode'] ?? null;
            $bagSegments = $bag['segments'] ?? [];

            if (!$ref) continue;
            $desc = $baggageDescs[$ref - 1] ?? null;
            if (!$desc) continue;

            // segment id গুলো collect করো
            $segIds = array_column($bagSegments, 'id');

            if (!empty($segIds) && !empty($segments)) {
                // প্রথম segment এর departure + শেষ segment এর arrival
                $firstSeg = $segments[$segIds[0]] ?? null;
                $lastSeg  = $segments[end($segIds)] ?? null;

                $departure = $firstSeg['departure']['airport_code'] ?? null;
                $arrival   = $lastSeg['arrival']['airport_code'] ?? null;
            } else {
                $departure = null;
                $arrival   = null;
            }

            $segmentBaggage[] = [
                'route'       => $departure && $arrival ? $departure . ' → ' . $arrival : ($airline . ' (all segments)'),
                'departure'   => $departure,
                'arrival'     => $arrival,
                'airline'     => $airline,
                'weight'      => $desc['weight'] ?? null,
                'unit'        => isset($desc['weight']) ? 'kg' : null,
                'piece_count' => $desc['pieceCount'] ?? null,
            ];
        }

        return $segmentBaggage;
    }

    private function formatBaggage(array $baggageInfo, array $baggageDescs): ?array
    {
        if (empty($baggageInfo)) return null;

        $minWeight = null;
        $minPiece  = null;
        $airline   = null;
        $hasVariation = false;

        foreach ($baggageInfo as $bag) {
            $ref = $bag['allowance']['ref'] ?? null;
            if (!$ref) continue;

            $desc = $baggageDescs[$ref - 1] ?? null;
            if (!$desc) continue;

            if (!$airline) $airline = $bag['airlineCode'] ?? null;

            if (isset($desc['weight'])) {
                if ($minWeight === null) {
                    $minWeight = $desc['weight'];
                } else {
                    if ($desc['weight'] !== $minWeight) $hasVariation = true;
                    $minWeight = min($minWeight, $desc['weight']);
                }
            }

            if (isset($desc['pieceCount'])) {
                if ($minPiece === null) {
                    $minPiece = $desc['pieceCount'];
                } else {
                    if ($desc['pieceCount'] !== $minPiece) $hasVariation = true;
                    $minPiece = min($minPiece, $desc['pieceCount']);
                }
            }
        }

        $result = [
            'weight'      => $minWeight,
            'unit'        => $minWeight ? 'kg' : null,
            'piece_count' => $minPiece,
            'airline'     => $airline,
        ];

        if ($hasVariation) {
            $result['note'] = 'Minimum allowance for entire journey';
        }

        return $result;
    }

    /**
     * Format legs
     */
//    private function formatLegs(array $legs, array $legDescs, array $scheduleDescs, array $legDescriptions = [], array $fareComponents = []): array
//    {
//        $formattedLegs = [];
//
//        foreach ($legs as $legIndex => $leg) {
//            $legDesc = $legDescs[$leg['ref'] - 1] ?? [];
//            $schedules = $legDesc['schedules'] ?? [];
//
//            if (empty($schedules)) {
//                continue;
//            }
//
//            $firstSchedule = $scheduleDescs[$schedules[0]['ref'] - 1] ?? [];
//            $lastSchedule = $scheduleDescs[end($schedules)['ref'] - 1] ?? [];
//
//            // ✅ Get matching legDescription by index
//            $legDescription = $legDescriptions[$legIndex] ?? [];
//
//            // ✅ Get departure date from legDescription
//            $departureDate = $legDescription['departureDate'] ?? null;
//
//            $segments = $this->formatSegments($schedules, $scheduleDescs, $fareComponents, $departureDate);
//            $segmentsWithLayover = $this->helper->addLayoverInformation($segments);
//
//            $departureAirportCode = $firstSchedule['departure']['airport'] ?? null;
//            $arrivalAirportCode = $lastSchedule['arrival']['airport'] ?? null;
//
//            // ✅ Calculate final arrival date from last segment
//            $finalArrivalDate = !empty($segments) ? end($segments)['arrival']['date'] : $departureDate;
//
//            $formattedLegs[] = [
//                'leg_number' => $legIndex + 1,
//                'leg_type' => $this->helper->identifyLegType($legIndex, count($legs)),
//                'duration' => $legDesc['elapsedTime'] ?? 0,
//                'duration_formatted' => $this->helper->formatDuration($legDesc['elapsedTime'] ?? 0),
//                'stops' => count($schedules) - 1,
//                'is_direct' => count($schedules) === 1,
//                'total_segments' => count($schedules),
//                'departure' => [
//                    'airport_code' => $departureAirportCode,
//                    'airport_name' => $this->helper->getAirportName($departureAirportCode),
//                    'city' => $firstSchedule['departure']['city'] ?? $this->helper->getAirportAddress($departureAirportCode),
//                    'address' => $this->helper->getAirportAddress($departureAirportCode),
//                    'country' => $firstSchedule['departure']['country'] ?? $this->helper->getAirportCountry($departureAirportCode),
//                    'time' => $firstSchedule['departure']['time'] ?? null,
//                    'time_12h' => $this->helper->formatTime12h($firstSchedule['departure']['time'] ?? null),
//                    'date' => $departureDate,
//                    'terminal' => $firstSchedule['departure']['terminal'] ?? null,
//                ],
//                'arrival' => [
//                    'airport_code' => $arrivalAirportCode,
//                    'airport_name' => $this->helper->getAirportName($arrivalAirportCode),
//                    'city' => $lastSchedule['arrival']['city'] ?? $this->helper->getAirportAddress($arrivalAirportCode),
//                    'address' => $this->helper->getAirportAddress($arrivalAirportCode),
//                    'country' => $lastSchedule['arrival']['country'] ?? $this->helper->getAirportCountry($arrivalAirportCode),
//                    'time' => $lastSchedule['arrival']['time'] ?? null,
//                    'time_12h' => $this->helper->formatTime12h($lastSchedule['arrival']['time'] ?? null),
//                    'date' => $finalArrivalDate, // ✅ Use last segment's arrival date
//                    'terminal' => $lastSchedule['arrival']['terminal'] ?? null,
//                    'date_adjustment' => $lastSchedule['arrival']['dateAdjustment'] ?? 0,
//                ],
//                'stops_detail' => $this->extractStopDetailsFromSchedules($schedules, $scheduleDescs, $segments),
//                'segments' => $segmentsWithLayover,
//            ];
//        }
//
//        return $formattedLegs;
//    }

    private function formatLegs(
        array $legs,
        array $legDescs,
        array $scheduleDescs,
        array $legDescriptions = [],
        array $flatFareInfo = []   // ← নাম change করো
    ): array {
        $formattedLegs = [];

        // ✅ একবারই flat করো, এবং offset track করো
//        $flatFareInfo = $this->extractFareComponentsInfo($fareComponents);
        $fareOffset = 0;

        foreach ($legs as $legIndex => $leg) {
            $legDesc = $legDescs[$leg['ref'] - 1] ?? [];
            $schedules = $legDesc['schedules'] ?? [];

            if (empty($schedules)) {
                continue;
            }

            $firstSchedule = $scheduleDescs[$schedules[0]['ref'] - 1] ?? [];
            $lastSchedule = $scheduleDescs[end($schedules)['ref'] - 1] ?? [];

            $legDescription = $legDescriptions[$legIndex] ?? [];
            $departureDate = $legDescription['departureDate'] ?? null;

            // ✅ flatFareInfo এবং fareOffset pass করো
            $segments = $this->formatSegments(
                $schedules,
                $scheduleDescs,
                $flatFareInfo,
                $departureDate,
                $fareOffset
            );

            // ✅ পরের leg এর জন্য offset বাড়াও
            $fareOffset += count($schedules);

            $segmentsWithLayover = $this->helper->addLayoverInformation($segments);

            $departureAirportCode = $firstSchedule['departure']['airport'] ?? null;
            $arrivalAirportCode = $lastSchedule['arrival']['airport'] ?? null;

            $finalArrivalDate = !empty($segments) ? end($segments)['arrival']['date'] : $departureDate;

            $formattedLegs[] = [
                'leg_number'         => $legIndex + 1,
                'leg_type'           => $this->helper->identifyLegType($legIndex, count($legs)),
                'duration'           => $legDesc['elapsedTime'] ?? 0,
                'duration_formatted' => $this->helper->formatDuration($legDesc['elapsedTime'] ?? 0),
                'stops'              => count($schedules) - 1,
                'is_direct'          => count($schedules) === 1,
                'total_segments'     => count($schedules),
                'departure' => [
                    'airport_code'      => $departureAirportCode,
                    'airport_name' => $this->helper->getAirportName($departureAirportCode),
                    'city'         => $firstSchedule['departure']['city'] ?? $this->helper->getAirportAddress($departureAirportCode),
                    'address'      => $this->helper->getAirportAddress($departureAirportCode),
                    'country'      => $firstSchedule['departure']['country'] ?? $this->helper->getAirportCountry($departureAirportCode),
                    'time'         => $firstSchedule['departure']['time'] ?? null,
                    'time_12h'     => $this->helper->formatTime12h($firstSchedule['departure']['time'] ?? null),
                    'date'         => $departureDate,
                    'terminal'     => $firstSchedule['departure']['terminal'] ?? null,
                ],
                'arrival' => [
                    'airport_code'         => $arrivalAirportCode,
                    'airport_name'    => $this->helper->getAirportName($arrivalAirportCode),
                    'city'            => $lastSchedule['arrival']['city'] ?? $this->helper->getAirportAddress($arrivalAirportCode),
                    'address'         => $this->helper->getAirportAddress($arrivalAirportCode),
                    'country'         => $lastSchedule['arrival']['country'] ?? $this->helper->getAirportCountry($arrivalAirportCode),
                    'time'            => $lastSchedule['arrival']['time'] ?? null,
                    'time_12h'        => $this->helper->formatTime12h($lastSchedule['arrival']['time'] ?? null),
                    'date'            => $finalArrivalDate,
                    'terminal'        => $lastSchedule['arrival']['terminal'] ?? null,
                    'date_adjustment' => $lastSchedule['arrival']['dateAdjustment'] ?? 0,
                ],
                'stops_detail' => $this->extractStopDetailsFromSchedules($schedules, $scheduleDescs, $segments),
                'segments'     => $segmentsWithLayover,
            ];
        }

        return $formattedLegs;
    }

    /**
     * Extract stop details from schedules
     */
    private function extractStopDetailsFromSchedules(array $schedules, array $scheduleDescs, array $segments): array
    {
        $stops = [];

        for ($i = 0; $i < count($schedules) - 1; $i++) {
            // ✅ Use actual segment dates instead of recalculating
            $currentSegment = $segments[$i] ?? null;
            $nextSegment = $segments[$i + 1] ?? null;

            if (!$currentSegment || !$nextSegment) {
                continue;
            }

            $currentScheduleRef = $schedules[$i]['ref'];
            $nextScheduleRef = $schedules[$i + 1]['ref'];

            $currentSchedule = $scheduleDescs[$currentScheduleRef - 1] ?? [];
            $nextSchedule = $scheduleDescs[$nextScheduleRef - 1] ?? [];

            $arrivalTime = $currentSchedule['arrival']['time'] ?? null;
            $departureTime = $nextSchedule['departure']['time'] ?? null;

            if (!$arrivalTime || !$departureTime) {
                continue;
            }

            // ✅ Use dates from actual segments
            $arrivalDate = $currentSegment['arrival']['date'];
            $departureDate = $nextSegment['departure']['date'];

            try {
                // Extract time component from ISO datetime
                preg_match('/(\d{2}:\d{2}:\d{2})/', $arrivalTime, $arrivalMatch);
                preg_match('/(\d{2}:\d{2}:\d{2})/', $departureTime, $departureMatch);

                $arrivalTimeClean = $arrivalMatch[1] ?? '00:00:00';
                $departureTimeClean = $departureMatch[1] ?? '00:00:00';

                // Combine date + time
                $arrivalDateTime = new \DateTime($arrivalDate . ' ' . $arrivalTimeClean);
                $departureDateTime = new \DateTime($departureDate . ' ' . $departureTimeClean);

                // Calculate difference in minutes
                $layoverMinutes = max(0, ($departureDateTime->getTimestamp() - $arrivalDateTime->getTimestamp()) / 60);
            } catch (\Exception $e) {
                $layoverMinutes = 0;
            }



            $stopAirportCode = $currentSchedule['arrival']['airport'] ?? null;

            $stops[] = [
                'stop_number' => $i + 1,
                'airport_code' => $stopAirportCode,
                'airport_name' => $this->helper->getAirportName($stopAirportCode),
                'city' => $currentSchedule['arrival']['city'] ?? $this->helper->getAirportAddress($stopAirportCode),
                'address' => $this->helper->getAirportAddress($stopAirportCode),
                'country' => $currentSchedule['arrival']['country'] ?? $this->helper->getAirportCountry($stopAirportCode),
                'arrival_time' => $arrivalTime,
                'arrival_time_12h' => $this->helper->formatTime12h($arrivalTime),
                'arrival_date' => $arrivalDate,
                'arrival_terminal' => $currentSchedule['arrival']['terminal'] ?? null,
                'departure_time' => $departureTime,
                'departure_time_12h' => $this->helper->formatTime12h($departureTime),
                'departure_date' => $departureDate,
                'departure_terminal' => $nextSchedule['departure']['terminal'] ?? null,
                'layover_minutes' => (int)$layoverMinutes,
                'layover_formatted' => $this->helper->formatDuration((int)$layoverMinutes),
//                'layover_formatted' => (int)$layoverMinutes,
                'is_overnight' => $arrivalDate !== $departureDate,
                'terminal_change' => $this->helper->checkTerminalChange(
                    $currentSchedule['arrival']['terminal'] ?? null,
                    $nextSchedule['departure']['terminal'] ?? null
                ),
            ];
        }

        return $stops;
    }

    /**
     * Check if layover is overnight
     */
    private function isOvernightLayover(string $arrivalTime, string $departureTime): bool
    {
        $arrival = new \DateTime($arrivalTime);
        $departure = new \DateTime($departureTime);

        return $arrival->format('Y-m-d') !== $departure->format('Y-m-d');
    }

    /**
     * Format segments
     */
//    private function formatSegments(
//        array  $scheduleRefs,
//        array  $scheduleDescs,
//        array  $fareComponents = [],
//        string $baseDepartureDate = null
//    ): array
//    {
//        $fareInfo = $this->extractFareComponentsInfo($fareComponents);
//        $currentDate = $baseDepartureDate; // Running date tracker
//
//        return collect($scheduleRefs)->map(function ($ref, $index) use ($scheduleDescs, $fareInfo, &$currentDate) {
//            $schedule = $scheduleDescs[$ref['ref'] - 1] ?? [];
//            $carrier = $schedule['carrier'] ?? [];
//            $segmentFareInfo = $fareInfo[$index] ?? null;
//
//            // ✅ STEP 1: Calculate segment departure date
//            $segmentDepartureDate = $currentDate;
//
//            // Check for departureDateAdjustment in the schedule reference
//            $departureDateAdjustment = $ref['departureDateAdjustment'] ?? 0;
//
//            if ($departureDateAdjustment > 0 && $currentDate) {
//                // Add days to current date
//                $segmentDepartureDate = date('Y-m-d', strtotime($currentDate . ' +' . $departureDateAdjustment . ' days'));
//            }
//
//            // ✅ STEP 2: Calculate segment arrival date
//            $arrivalDateAdjustment = $schedule['arrival']['dateAdjustment'] ?? 0;
//            $segmentArrivalDate = $segmentDepartureDate;
//
//            if ($arrivalDateAdjustment > 0 && $segmentDepartureDate) {
//                // Add days to departure date
//                $segmentArrivalDate = date('Y-m-d', strtotime($segmentDepartureDate . ' +' . $arrivalDateAdjustment . ' days'));
//            }
//
//            // ✅ STEP 3: Update current date for next segment
//            // Next segment will start calculation from this segment's arrival date
//            $currentDate = $segmentArrivalDate;
//
//            $carrierCode = $carrier['marketing'] ?? null;
//            $operatingCode = $carrier['operating'] ?? null;
//            $departureAirportCode = $schedule['departure']['airport'] ?? null;
//            $arrivalAirportCode = $schedule['arrival']['airport'] ?? null;
//
//            return [
//                'segment_number' => $index + 1,
//                'carrier' => $carrierCode,
//                'carrier_name' => $this->helper->getAirlineName($carrierCode),
//                'carrier_images' => [
//                    'thumb' => $this->helper->getAirlineImage($carrierCode, 'thumb'),
//                    'medium' => $this->helper->getAirlineImage($carrierCode, 'medium'),
//                    'large' => $this->helper->getAirlineImage($carrierCode, 'large'),
//                    'full' => $this->helper->getAirlineImage($carrierCode, 'full'),
//                ],
//                'operating_carrier' => $operatingCode,
//                'operating_carrier_name' => $this->helper->getAirlineName($operatingCode),
//                'is_codeshare' => $carrierCode !== $operatingCode,
//                'flight_number' => $carrier['marketingFlightNumber'] ?? null,
//                'operating_flight_number' => $carrier['operatingFlightNumber'] ?? null,
//                'full_flight_number' => ($carrierCode ?? '') . '-' . ($carrier['marketingFlightNumber'] ?? ''),
//                'departure' => [
//                    'airport_code' => $departureAirportCode,
//                    'airport_name' => $this->helper->getAirportName($departureAirportCode),
//                    'city' => $schedule['departure']['city'] ?? $this->helper->getAirportAddress($departureAirportCode),
//                    'address' => $this->helper->getAirportAddress($departureAirportCode),
//                    'country' => $schedule['departure']['country'] ?? $this->helper->getAirportCountry($departureAirportCode),
//                    'country_code' => $schedule['departure']['country'] ?? null,
//                    'time' => $schedule['departure']['time'] ?? null,
//                    'time_12h' => $this->helper->formatTime12h($schedule['departure']['time'] ?? null),
//                    'date' => $segmentDepartureDate, // ✅ Calculated with departureDateAdjustment
//                    'terminal' => $schedule['departure']['terminal'] ?? null,
//                ],
//                'arrival' => [
//                    'airport_code' => $arrivalAirportCode,
//                    'airport_name' => $this->helper->getAirportName($arrivalAirportCode),
//                    'city' => $schedule['arrival']['city'] ?? $this->helper->getAirportAddress($arrivalAirportCode),
//                    'address' => $this->helper->getAirportAddress($arrivalAirportCode),
//                    'country' => $schedule['arrival']['country'] ?? $this->helper->getAirportCountry($arrivalAirportCode),
//                    'country_code' => $schedule['arrival']['country'] ?? null,
//                    'time' => $schedule['arrival']['time'] ?? null,
//                    'time_12h' => $this->helper->formatTime12h($schedule['arrival']['time'] ?? null),
//                    'date' => $segmentArrivalDate, // ✅ Calculated with arrivalDateAdjustment
//                    'date_adjustment' => $arrivalDateAdjustment,
//                    'terminal' => $schedule['arrival']['terminal'] ?? null,
//                ],
//                'duration' => $schedule['elapsedTime'] ?? 0,
//                'duration_formatted' => $this->helper->formatDuration($schedule['elapsedTime'] ?? 0),
//                'miles' => $schedule['totalMilesFlown'] ?? 0,
////                'aircraft' => $carrier['equipment']['code'] ?? null,
////                'aircraft_type' => $carrier['equipment']['typeForFirstLeg'] ?? null,
////                'aircraft_name' => $this->helper->getAircraftName($carrier['equipment']['code'] ?? null),
//                'aircraft' => $carrier['equipment']['code'] ?? null,
//                'aircraft_type_first_leg' => $carrier['equipment']['typeForFirstLeg'] ?? null,
//                'aircraft_type_last_leg' => $carrier['equipment']['typeForLastLeg'] ?? null,
//                'aircraft_name' => $this->helper->getAircraftName($carrier['equipment']['code'] ?? null),
//                'meal_code' => $segmentFareInfo['meal_code'] ?? null,
//                'meal_description' => $this->helper->getMealDescription($segmentFareInfo['meal_code'] ?? null),
//                'eTicketable' => $schedule['eTicketable'] ?? false,
//                'stop_count' => $schedule['stopCount'] ?? 0,
//                'frequency' => $schedule['frequency'] ?? null,
//                'fare_info' => $segmentFareInfo ? [
//                    'fare_basis_code' => $segmentFareInfo['fare_basis_code'],
//                    'booking_code' => $segmentFareInfo['booking_code'],
//                    'cabin_code' => $segmentFareInfo['cabin_code'],
//                    'cabin_name' => $this->helper->getCabinName($segmentFareInfo['cabin_code']),
//                    'seats_available' => $segmentFareInfo['seats_available'],
//                    'availability_break' => $segmentFareInfo['availability_break'],
//                ] : null,
//                'alliances' => $carrier['alliances'] ?? null,
//                'disclosure' => $carrier['disclosure'] ?? null,
//                'message' => $schedule['message'] ?? null,
//                'message_type' => $schedule['messageType'] ?? null,
//                'traffic_restriction' => $schedule['trafficRestriction'] ?? null,
//            ];
//        })->toArray();
//    }

    private function formatSegments(
        array  $scheduleRefs,
        array  $scheduleDescs,
        array  $fareInfo = [],
        string $baseDepartureDate = null,
        int    $fareOffset = 0  // ✅ নতুন parameter
    ): array
    {
        $currentDate = $baseDepartureDate;

        return collect($scheduleRefs)->map(function ($ref, $index) use ($scheduleDescs, $fareInfo, &$currentDate, $fareOffset) {
            $schedule = $scheduleDescs[$ref['ref'] - 1] ?? [];
            $carrier = $schedule['carrier'] ?? [];

            // ✅ offset দিয়ে সঠিক fareInfo index
            $segmentFareInfo = $fareInfo[$fareOffset + $index] ?? null;

            // Departure date calculation
            $segmentDepartureDate = $currentDate;
            $departureDateAdjustment = $ref['departureDateAdjustment'] ?? 0;

            if ($departureDateAdjustment > 0 && $currentDate) {
                $segmentDepartureDate = date('Y-m-d', strtotime($currentDate . ' +' . $departureDateAdjustment . ' days'));
            }

            // Arrival date calculation
            $arrivalDateAdjustment = $schedule['arrival']['dateAdjustment'] ?? 0;
            $segmentArrivalDate = $segmentDepartureDate;

            if ($arrivalDateAdjustment > 0 && $segmentDepartureDate) {
                $segmentArrivalDate = date('Y-m-d', strtotime($segmentDepartureDate . ' +' . $arrivalDateAdjustment . ' days'));
            }

            // Update current date for next segment
            $currentDate = $segmentArrivalDate;

            $carrierCode = $carrier['marketing'] ?? null;
            $operatingCode = $carrier['operating'] ?? null;
            $departureAirportCode = $schedule['departure']['airport'] ?? null;
            $arrivalAirportCode = $schedule['arrival']['airport'] ?? null;

            return [
                'segment_number'          => $index + 1,
                'carrier'                 => $carrierCode,
                'carrier_name'            => $this->helper->getAirlineName($carrierCode),
                'carrier_images'          => [
                    'thumb'  => $this->helper->getAirlineImage($carrierCode, 'thumb'),
                    'medium' => $this->helper->getAirlineImage($carrierCode, 'medium'),
                    'large'  => $this->helper->getAirlineImage($carrierCode, 'large'),
                    'full'   => $this->helper->getAirlineImage($carrierCode, 'full'),
                ],
                'operating_carrier'       => $operatingCode,
                'operating_carrier_name'  => $this->helper->getAirlineName($operatingCode),
                'is_codeshare'            => $carrierCode !== $operatingCode,
                'flight_number'           => $carrier['marketingFlightNumber'] ?? null,
                'operating_flight_number' => $carrier['operatingFlightNumber'] ?? null,
                'full_flight_number'      => ($carrierCode ?? '') . '-' . ($carrier['marketingFlightNumber'] ?? ''),
                'departure' => [
                    'airport_code' => $departureAirportCode,
                    'airport_name' => $this->helper->getAirportName($departureAirportCode),
                    'city'         => $schedule['departure']['city'] ?? $this->helper->getAirportAddress($departureAirportCode),
                    'address'      => $this->helper->getAirportAddress($departureAirportCode),
                    'country'      => $schedule['departure']['country'] ?? $this->helper->getAirportCountry($departureAirportCode),
                    'country_code' => $schedule['departure']['country'] ?? null,
                    'time'         => $schedule['departure']['time'] ?? null,
                    'time_12h'     => $this->helper->formatTime12h($schedule['departure']['time'] ?? null),
                    'date'         => $segmentDepartureDate,
                    'terminal'     => $schedule['departure']['terminal'] ?? null,
                ],
                'arrival' => [
                    'airport_code'   => $arrivalAirportCode,
                    'airport_name'   => $this->helper->getAirportName($arrivalAirportCode),
                    'city'           => $schedule['arrival']['city'] ?? $this->helper->getAirportAddress($arrivalAirportCode),
                    'address'        => $this->helper->getAirportAddress($arrivalAirportCode),
                    'country'        => $schedule['arrival']['country'] ?? $this->helper->getAirportCountry($arrivalAirportCode),
                    'country_code'   => $schedule['arrival']['country'] ?? null,
                    'time'           => $schedule['arrival']['time'] ?? null,
                    'time_12h'       => $this->helper->formatTime12h($schedule['arrival']['time'] ?? null),
                    'date'           => $segmentArrivalDate,
                    'date_adjustment'=> $arrivalDateAdjustment,
                    'terminal'       => $schedule['arrival']['terminal'] ?? null,
                ],
                'duration'                => $schedule['elapsedTime'] ?? 0,
                'duration_formatted'      => $this->helper->formatDuration($schedule['elapsedTime'] ?? 0),
                'miles'                   => $schedule['totalMilesFlown'] ?? 0,
                'aircraft'                => $carrier['equipment']['code'] ?? null,
                'aircraft_type_first_leg' => $carrier['equipment']['typeForFirstLeg'] ?? null,
                'aircraft_type_last_leg'  => $carrier['equipment']['typeForLastLeg'] ?? null,
                'aircraft_name'           => $this->helper->getAircraftName($carrier['equipment']['code'] ?? null),
                'meal_code'               => $segmentFareInfo['meal_code'] ?? null,
                'meal_description'        => $this->helper->getMealDescription($segmentFareInfo['meal_code'] ?? null),
                'eTicketable'             => $schedule['eTicketable'] ?? false,
                'stop_count'              => $schedule['stopCount'] ?? 0,
                'frequency'               => $schedule['frequency'] ?? null,
                'fare_info' => $segmentFareInfo ? [
                    'fare_basis_code'   => $segmentFareInfo['fare_basis_code'],
                    'booking_code'      => $segmentFareInfo['booking_code'],
                    'cabin_code'        => $segmentFareInfo['cabin_code'],
                    'cabin_name'        => $this->helper->getCabinName($segmentFareInfo['cabin_code']),
                    'seats_available'   => $segmentFareInfo['seats_available'],
                    'availability_break'=> $segmentFareInfo['availability_break'],
                ] : null,
                'alliances'           => $carrier['alliances'] ?? null,
                'disclosure'          => $carrier['disclosure'] ?? null,
                'message'             => $schedule['message'] ?? null,
                'message_type'        => $schedule['messageType'] ?? null,
                'traffic_restriction' => $schedule['trafficRestriction'] ?? null,
            ];
        })->toArray();
    }
    private function formatTaxBreakdown(array $passengerInfoList, array $taxDescs): array
    {
        $taxes = [];
        $taxMap = collect($taxDescs)->keyBy('id')->toArray();

        foreach ($passengerInfoList as $passenger) {
            $taxInfos = $passenger['passengerInfo']['taxes'] ?? [];
            foreach ($taxInfos as $tax) {
                $ref = $tax['ref'] ?? null;
                if ($ref && isset($taxMap[$ref])) {
                    $taxDetail = $taxMap[$ref];
                    $code = $taxDetail['code'] ?? 'TAX';

                    if (!isset($taxes[$code])) {
                        $taxes[$code] = [
                            'code' => $code,
                            'description' => $taxDetail['description'] ?? null,
                            'amount' => 0,
                            'currency' => $taxDetail['currency'] ?? 'BDT',
                            'published_amount' => $taxDetail['publishedAmount'] ?? null,
                            'published_currency' => $taxDetail['publishedCurrency'] ?? null,
                            'station' => $taxDetail['station'] ?? null,
                            'country' => $taxDetail['country'] ?? null,
                        ];
                    }
                    $taxes[$code]['amount'] += $taxDetail['amount'] ?? 0;
                }
            }
        }

        return array_values($taxes);
    }

    /**
     * Extract fare components info
     */
    private function extractFareComponentsInfo(array $fareComponents): array
    {
        $fareInfo = [];
        foreach ($fareComponents as $component) {
            $segments = $component['segments'] ?? [];

            foreach ($segments as $segment) {
                $segmentData = $segment['segment'] ?? [];

                $fareInfo[] = [
                    'fare_basis_code'    => $component['fareBasisCode'] ?? null,
                    'booking_code'       => $segmentData['bookingCode'] ?? null,
                    'cabin_code'         => $segmentData['cabinCode'] ?? null,
                    'meal_code'          => $segmentData['mealCode'] ?? null,
                    'seats_available'    => $segmentData['seatsAvailable'] ?? null,
                    'availability_break' => $segmentData['availabilityBreak'] ?? false,
                ];
            }
        }

        return $fareInfo;
    }

    /**
     * Get cabin name from code
     */
    private function getCabinName(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        $cabins = [
            'Y' => 'Economy',
            'W' => 'Premium Economy',
            'C' => 'Business',
            'F' => 'First Class',
        ];

        return $cabins[$code] ?? $code;
    }

    /**
     * Get Sabre auth token
     */
    private function getAuthToken(): string
    {
        return Cache::remember(
            'sabre_api_token',
            now()->addMinutes(config('sabre.token_cache_minutes')),
            function () {
                try {
                    $credentials = base64_encode(
                        config('sabre.rest.client_id') . ':' . config('sabre.rest.client_secret')
                    );

                    $response = Http::timeout(60)
                        ->connectTimeout(30)
                        ->retry(3, 1000)
                        ->withOptions([
                            'verify' => !app()->environment('local'),
                        ])
                        ->asForm()
                        ->withHeaders([
                            'Authorization' => "Basic {$credentials}",
                            'Accept' => 'application/json',
                        ])
                        ->post(config('sabre.rest.base_url') . '/v3/auth/token', [
                            'grant_type' => 'password',
                            'username' => config('sabre.rest.username'),
                            'password' => config('sabre.rest.password'),
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        Log::info('Sabre Auth Success', [
                            'expires_in' => $data['expires_in'] ?? 0,
                        ]);

                        return $data['access_token'] ?? null;
                    }

                    Log::error('Sabre Auth Failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    throw new \Exception('Sabre authentication failed');

                } catch (\Exception $e) {
                    Log::error('Sabre Auth Exception', [
                        'message' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        );
    }
}
