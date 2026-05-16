<?php


namespace Modules\Booking\Service\Sabre;

use Modules\Api\Sabre\Response\PriceCheckRsponseService;
use Modules\Api\Sabre\SabreApisServiceClass;
use Modules\Flight\Service\FlightChargesService;
use Modules\Flight\Service\FlightDiscountService;

class SabrePriceCheckPayloadBuilder
{
    protected $pcc;
    protected FlightDiscountService $discountService;
    protected FlightChargesService $chargesService;

    public function __construct()
    {
        $this->pcc = config('sabre.pcc', 'DEFAULT_PCC');
        $this->discountService = app(FlightDiscountService::class);
        $this->chargesService = app(FlightChargesService::class);
    }

    /**
     * Main entry point
     */
    public function getPriceForBooking($data)
    {
        if (!$data) {
            throw new \Exception('Flight data not found');
        }

        $apicall = new SabreApisServiceClass();

        if ($data['is_ndc'] ?? false) {
            $rawIds = $data['offer_item_id'] ?? [];
            $offerItemIds = [];
            foreach ((array)$rawIds as $item) {
                if (is_array($item)) {
                    foreach ($item as $id) {
                        $offerItemIds[] = $id;
                    }
                } else {
                    $offerItemIds[] = $item;
                }
            }

            try {
                $priceResponse = $apicall->ndcOfferPrice(
                    $data['offer_id'],
                    $offerItemIds
                );
            } catch (\Exception $e) {
                return ['status' => 'error', 'message' => 'NDC fare টি এই মুহূর্তে available নেই। অনুগ্রহ করে নতুন করে সার্চ করুন।'];
            }

            return $this->parseAndCalculateNDC($priceResponse, $data);

        } else {
            $tripType = $this->detectTripType($data['legs'] ?? []);
            $payload = $this->buildPayload($data, $tripType);
            $priceResponse = $apicall->priceCheck($payload);

            return $this->parseAndCalculateATPCO($priceResponse, $data);
        }
    }

    private function parseAndCalculateATPCO(array $priceResponse, array $originalFlight): array
    {
        $root = $priceResponse['groupedItineraryResponse'] ?? null;

        if (!$root) {
            return ['status' => 'error', 'message' => 'Invalid response structure।'];
        }

        if (($root['statistics']['itineraryCount'] ?? 0) < 1) {
            return ['status' => 'error', 'message' => 'এই ফ্লাইটটি আর উপলব্ধ নেই।'];
        }

        $itinerary = null;
        foreach ($root['itineraryGroups'] ?? [] as $group) {
            foreach ($group['itineraries'] ?? [] as $itin) {
                if (!empty($itin['currentItinerary'])) {
                    $itinerary = $itin;
                    break 2;
                }
            }
        }
        $itinerary = $itinerary ?? ($root['itineraryGroups'][0]['itineraries'][0] ?? null);

        if (!$itinerary) {
            return ['status' => 'error', 'message' => 'Itinerary খুঁজে পাওয়া যায়নি।'];
        }

        $pricingInfo = $itinerary['pricingInformation'][0] ?? null;
        if (!$pricingInfo) {
            return ['status' => 'error', 'message' => 'Pricing information পাওয়া যায়নি।'];
        }

        $fare = $pricingInfo['fare'] ?? [];
        $totalFare = $fare['totalFare'] ?? [];
        $passengerInfoList = $fare['passengerInfoList'] ?? [];

        if (empty($passengerInfoList)) {
            return ['status' => 'error', 'message' => 'Passenger info পাওয়া যায়নি।'];
        }

        $firstPax = $passengerInfoList[0]['passengerInfo'] ?? [];
        $refundable = !($firstPax['nonRefundable'] ?? true);
        $refundPolicy = $refundable ? 'refundable' : 'non_refundable';

        $apiSubtotal = 0;
        $taxTotal = 0;
        foreach ($passengerInfoList as $p) {
            $pInfo = $p['passengerInfo'] ?? [];
            $pFare = $pInfo['passengerTotalFare'] ?? [];
            $paxCount = $pInfo['passengerNumber'] ?? 1;
            $apiSubtotal += ($pFare['equivalentAmount'] ?? 0) * $paxCount;
            $taxTotal += ($pFare['totalTaxAmount'] ?? 0) * $paxCount;
        }

        $validatingCarrier = $fare['validatingCarrierCode']
            ?? $originalFlight['validating_carrier']
            ?? null;

      // ✅ এটা দাও (দুটো method এই)
        $bestRoute  = ['departure' => null, 'arrival' => null];
        $bestScore  = -1;
        
        foreach ($originalFlight['legs'] as $leg) {
            foreach ($leg['segments'] ?? [] as $segment) {
                if ($segment['carrier'] !== $validatingCarrier) continue;
        
                $dep   = $segment['departure']['airport_code'] ?? null;
                $arr   = $segment['arrival']['airport_code']   ?? null;
                $score = $this->discountService->getRouteMatchScore(
                    $validatingCarrier, $dep, $arr, 'sabre'
                );
        
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestRoute = ['departure' => $dep, 'arrival' => $arr];
                }
            }
        }
        
        // fallback
        if (!$bestRoute['departure']) {
            $bestRoute['departure'] = $originalFlight['legs'][0]['departure']['airport_code'] ?? null;
        }
        if (!$bestRoute['arrival']) {
            $lastLeg = end($originalFlight['legs']);
            $bestRoute['arrival'] = $lastLeg['arrival']['airport_code'] ?? null;
        }
        
        $firstDeparture = $bestRoute['departure'];
        $lastArrival    = $bestRoute['arrival'];
        $totalSegments = array_sum(array_column($originalFlight['legs'], 'total_segments'));

        // ✅ Build segments array for discount calculation
        // $segments = [];
        // foreach ($originalFlight['legs'] as $leg) {
        //     foreach ($leg['segments'] as $segment) {
        //         $segments[] = [
        //             'airline_code' => $segment['carrier'],
        //             'departure_code' => $segment['departure']['airport_code'],
        //             'arrival_code' => $segment['arrival']['airport_code'],
        //             'is_international' => $this->isInternationalSegment(
        //                 $segment['departure']['country_code'] ?? null,
        //                 $segment['arrival']['country_code'] ?? null
        //             )
        //         ];
        //     }
        // }

        $flightDiscountInfo = $this->discountService->calculate(
            $validatingCarrier,
            $firstDeparture,
            $lastArrival,
            $passengerInfoList,
            $totalSegments,
            'sabre'
        );

        $grandTotal = $flightDiscountInfo['grand_total'];
        $priceBeforeDiscount = $grandTotal['api_subtotal']
            + $grandTotal['total_ait']
            + $grandTotal['total_service_charge'];
        $finalPrice = $priceBeforeDiscount
            - $grandTotal['total_user_discount']
            - $grandTotal['total_user_seg_discount'];

        $oldPrice = (float)($originalFlight['price']['total'] ?? 0);
        $newPrice = round($finalPrice, 2);
        $priceDiff = $newPrice - $oldPrice;

        return [
            'status' => 'ok',
            'data' => [
                'id' => $originalFlight['id'],
                'source' => 'sabre',
                'legs' => $originalFlight['legs'],

                'distribution_model' => 'ATPCO',
                'is_ndc' => false,
                'offer_id' => null,
                'offer_item_id' => null,
                'time_to_live' => null,
                'offer_expires' => null,

                'price_changed' => abs($priceDiff) > 1,
                'price_diff' => $priceDiff,
                'price_diff_type' => $priceDiff > 1 ? 'increased' : ($priceDiff < -1 ? 'decreased' : 'same'),
                'old_price' => $oldPrice,

                'price' => [
                    'api_base_fare' => $apiSubtotal,
                    'api_tax' => $taxTotal,
                    'api_subtotal' => $grandTotal['api_subtotal'],
                    'ait_amount' => $grandTotal['total_ait'],
                    'service_charge' => $grandTotal['total_service_charge'],
                    'subtotal_before_discount' => round($priceBeforeDiscount, 2),
                    'flight_discount' => $grandTotal['total_user_discount'],
                    'segment_discount' => $grandTotal['total_user_seg_discount'],
                    'total_discounts' => round($grandTotal['total_user_discount'] + $grandTotal['total_user_seg_discount'], 2),
                    'total' => $newPrice,
                    'currency' => 'BDT',
                    'base_currency' => $totalFare['baseFareCurrency'] ?? 'USD',
                    'own_discount' => $grandTotal['total_own_discount'],
                    'own_seg_discount' => $grandTotal['total_own_seg_discount'],
                    'total_commission' => $grandTotal['total_commission'],
                    'own_cost' => $grandTotal['total_own_cost'],
                    'gross_profit' => $grandTotal['gross_profit'],
                ],

                'flight_discount_details' => $flightDiscountInfo,
                'passenger_price_breakdown' => $flightDiscountInfo['passenger_breakdowns'],

                'charges_details' => [
                    'ait_charge_percentage' => $flightDiscountInfo['ait_charge_percentage'],
                    'ait_amount' => $grandTotal['total_ait'],
                    'service_charge' => $grandTotal['total_service_charge'],
                    'segment_discount_per_segment' => $flightDiscountInfo['segment_discount_per_segment'],
                    'segment_discount_total' => $grandTotal['total_user_seg_discount'],
                ],

                'passengers' => $originalFlight['passengers'],
                'refundable' => $refundable,
                'refund_policy' => $refundPolicy,
                'eTicketable' => $fare['eTicketable'] ?? true,
                'validating_carrier' => $validatingCarrier,
                'vita' => $fare['vita'] ?? false,
                'last_ticket_date' => $fare['lastTicketDate'] ?? null,
                'last_ticket_time' => $fare['lastTicketTime'] ?? null,
                'pricing_source' => 'ADVJR1',
                'taxes_breakdown' => [],
                'provider' => 'Sabre',
                'raw_fare' => $fare,

                'fare_info' => [
                    'fare_basis' => null,
                    'brand_code' => null,
                    'brand_name' => null,
                    'refundable' => $refundable,
                    'cancel_fee' => null,
                    'change_fee' => null,
                ],
                'voluntary_changes' => [],
                'services' => [],
            ],
        ];
    }

    private function parseAndCalculateNDC($priceResponse, $originalFlight): array
    {
        $offer = $priceResponse['response']['offers'][0] ?? [];
        $offerItems = $offer['offerItems'] ?? [];

        $firstItem = $offerItems[0] ?? [];

        $firstPassenger = $firstItem['passengers'][0] ?? [];
        $fareComponents = $firstPassenger['fareComponents'] ?? [];
        $fareComp = $fareComponents[0] ?? [];
        $penalty = $fareComp['fareRules']['penalty'] ?? [];

        $refundable = $this->resolveNdcRefundable($penalty, $fareComp);
        $refundPolicy = match (true) {
            $refundable === true => 'refundable',
            $refundable === false => 'non_refundable',
            default => 'fare_conditions_apply',
        };

        $cancelFee = $this->extractFeeAmount($fareComp, 'Cancel');
        $changeFee = $this->extractFeeAmount($fareComp, 'Change');

        // ── Passenger info list ──────────────────────────────────────────
        $passengerInfoList = [];
        $paxCountMap = [];
        $paxPriceMap = [];

        foreach ($offerItems as $item) {
            foreach ($item['passengers'] ?? [] as $pax) {
                $ptc = $pax['ptc'] ?? 'ADT';
                $paxCountMap[$ptc] = ($paxCountMap[$ptc] ?? 0) + 1;
                if (!isset($paxPriceMap[$ptc])) {
                    $paxPriceMap[$ptc] = $pax['price'] ?? [];
                }
            }
        }

        foreach ($paxCountMap as $ptc => $count) {
            $paxPrice = $paxPriceMap[$ptc] ?? [];

            $baseCur = $paxPrice['baseAmount']['curCode'] ?? 'BDT';
            $baseAmount = (float)($paxPrice['baseAmount']['amount'] ?? 0);
            if ($baseCur !== 'BDT') {
                $exchangeRate = (float)($paxPrice['filingInformation']['exchangeRate'] ?? 1);
                $baseAmount = round($baseAmount * $exchangeRate);
            }

            $taxAmount = (float)($paxPrice['taxes']['total']['amount'] ?? 0);
            $totalAmount = (float)($paxPrice['totalAmount']['amount'] ?? 0);
            $totalCur = $paxPrice['totalAmount']['curCode'] ?? 'BDT';
            if ($totalCur !== 'BDT') {
                $exchangeRate = (float)($paxPrice['filingInformation']['exchangeRate'] ?? 1);
                $totalAmount = round($totalAmount * $exchangeRate);
            }

            $passengerInfoList[] = [
                'passengerInfo' => [
                    'passengerType' => $ptc,
                    'passengerNumber' => $count,
                    'passengerTotalFare' => [
                        'equivalentAmount' => (int)$baseAmount,
                        'baseFareAmount' => (int)$baseAmount,
                        'totalTaxAmount' => (int)$taxAmount,
                        'totalFare' => (int)$totalAmount,
                    ],
                ],
            ];
        }

        // ── Grand totals ─────────────────────────────────────────────────
        $apiSubtotal = 0;
        $taxTotal = 0;
        foreach ($passengerInfoList as $p) {
            $count = $p['passengerInfo']['passengerNumber'];
            $apiSubtotal += $p['passengerInfo']['passengerTotalFare']['equivalentAmount'] * $count;
            $taxTotal += $p['passengerInfo']['passengerTotalFare']['totalTaxAmount'] * $count;
        }

        $validatingCarrier = $originalFlight['validating_carrier'] ?? null;
        $totalSegments = array_sum(array_column($originalFlight['legs'], 'total_segments'));

        // ✅ এটা add করো
      // ✅ এটা দাও (দুটো method এই)
            $bestRoute  = ['departure' => null, 'arrival' => null];
            $bestScore  = -1;
            
            foreach ($originalFlight['legs'] as $leg) {
                foreach ($leg['segments'] ?? [] as $segment) {
                    if ($segment['carrier'] !== $validatingCarrier) continue;
            
                    $dep   = $segment['departure']['airport_code'] ?? null;
                    $arr   = $segment['arrival']['airport_code']   ?? null;
                    $score = $this->discountService->getRouteMatchScore(
                        $validatingCarrier, $dep, $arr, 'sabre'
                    );
            
                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestRoute = ['departure' => $dep, 'arrival' => $arr];
                    }
                }
            }
            
            // fallback
            if (!$bestRoute['departure']) {
                $bestRoute['departure'] = $originalFlight['legs'][0]['departure']['airport_code'] ?? null;
            }
            if (!$bestRoute['arrival']) {
                $lastLeg = end($originalFlight['legs']);
                $bestRoute['arrival'] = $lastLeg['arrival']['airport_code'] ?? null;
            }
            
            $firstDeparture = $bestRoute['departure'];
            $lastArrival    = $bestRoute['arrival'];

        // ✅ Build segments array for discount calculation
        // $segments = [];
        // foreach ($originalFlight['legs'] as $leg) {
        //     foreach ($leg['segments'] as $segment) {
        //         $segments[] = [
        //             'airline_code' => $segment['carrier'],
        //             'departure_code' => $segment['departure']['airport_code'],
        //             'arrival_code' => $segment['arrival']['airport_code'],
        //             'is_international' => $this->isInternationalSegment(
        //                 $segment['departure']['country_code'] ?? null,
        //                 $segment['arrival']['country_code'] ?? null
        //             )
        //         ];
        //     }
        // }

        // ✅ NDC passengerInfoList normalize করো
        $passengerInfoList = array_map(function ($item) {
            $info = $item['passengerInfo'] ?? [];
            $fare = $info['passengerTotalFare'] ?? [];

            if (!isset($info['nonRefundable'])) {
                $item['passengerInfo']['nonRefundable'] = false;
            }
            if (!isset($info['taxes'])) {
                $item['passengerInfo']['taxes'] = [];
            }
            if (!isset($info['currencyConversion'])) {
                $item['passengerInfo']['currencyConversion'] = [
                    'from'             => 'BDT',
                    'to'               => 'BDT',
                    'exchangeRateUsed' => 1,
                ];
            }
            return $item;
        }, $passengerInfoList);

        $flightDiscountInfo = $this->discountService->calculate(
            $validatingCarrier,
            $firstDeparture,
            $lastArrival,
            $passengerInfoList,
            $totalSegments,
            'sabre'
        );

        $grandTotal = $flightDiscountInfo['grand_total'];
        $priceBeforeDiscount = $grandTotal['api_subtotal']
            + $grandTotal['total_ait']
            + $grandTotal['total_service_charge'];
        $finalPrice = $priceBeforeDiscount
            - $grandTotal['total_user_discount']
            - $grandTotal['total_user_seg_discount'];

        $oldPrice = $originalFlight['price']['total'] ?? 0;
        $newPrice = round($finalPrice, 2);
        $priceDiff = $newPrice - $oldPrice;

        // ── Taxes breakdown ──────────────────────────────────────────────
        $firstPaxPrice = $firstPassenger['price'] ?? [];
        $taxesBreakdown = array_map(fn($t) => [
            'code' => $t['taxCode'] ?? null,
            'amount' => (int)($t['amount']['amount'] ?? 0),
            'currency' => $t['amount']['curCode'] ?? 'BDT',
            'description' => $t['description'] ?? null,
        ], $firstPaxPrice['taxes']['breakdown'] ?? []);

        $newOfferItemIds = array_column($offerItems, 'id');
        $voluntaryChanges = $fareComp['voluntaryChangeInformation'] ?? [];

        return [
            'status' => 'ok',
            'data' => [
                'id' => $originalFlight['id'],
                'source' => 'sabre',
                'legs' => $originalFlight['legs'],

                'distribution_model' => 'NDC',
                'is_ndc' => true,
                'offer_id' => $offer['id'] ?? null,
                'offer_item_id' => $newOfferItemIds,
                'time_to_live' => $offer['ttl'] ?? 1200,
                'offer_expires' => $offer['offerExpirationDateTime'] ?? null,

                'price_changed' => abs($priceDiff) > 1,
                'price_diff' => $priceDiff,
                'price_diff_type' => $priceDiff > 1 ? 'increased' : ($priceDiff < -1 ? 'decreased' : 'same'),
                'old_price' => $oldPrice,

                'price' => [
                    'api_base_fare' => $apiSubtotal,
                    'api_tax' => $taxTotal,
                    'api_subtotal' => $grandTotal['api_subtotal'],
                    'ait_amount' => $grandTotal['total_ait'],
                    'service_charge' => $grandTotal['total_service_charge'],
                    'subtotal_before_discount' => round($priceBeforeDiscount, 2),
                    'flight_discount' => $grandTotal['total_user_discount'],
                    'segment_discount' => $grandTotal['total_user_seg_discount'],
                    'total_discounts' => round($grandTotal['total_user_discount'] + $grandTotal['total_user_seg_discount'], 2),
                    'total' => $newPrice,
                    'currency' => 'BDT',
                    'base_currency' => 'BDT',
                    'own_discount' => $grandTotal['total_own_discount'],
                    'own_seg_discount' => $grandTotal['total_own_seg_discount'],
                    'total_commission' => $grandTotal['total_commission'],
                    'own_cost' => $grandTotal['total_own_cost'],
                    'gross_profit' => $grandTotal['gross_profit'],
                ],

                'flight_discount_details' => $flightDiscountInfo,
                'passenger_price_breakdown' => $flightDiscountInfo['passenger_breakdowns'],

                'charges_details' => [
                    'ait_charge_percentage' => $flightDiscountInfo['ait_charge_percentage'],
                    'ait_amount' => $grandTotal['total_ait'],
                    'service_charge' => $grandTotal['total_service_charge'],
                    'segment_discount_per_segment' => $flightDiscountInfo['segment_discount_per_segment'],
                    'segment_discount_total' => $grandTotal['total_user_seg_discount'],
                ],

                'passengers' => $originalFlight['passengers'],
                'refundable' => $refundable,
                'refund_policy' => $refundPolicy,
                'eTicketable' => true,
                'validating_carrier' => $validatingCarrier,
                'vita' => false,
                'last_ticket_date' => $offer['paymentTimeLimitText'] ?? null,
                'last_ticket_time' => null,
                'pricing_source' => 'ADVJR1',
                'taxes_breakdown' => $taxesBreakdown,
                'provider' => 'Sabre',

                'fare_info' => [
                    'fare_basis' => $fareComp['fareBasis']['fareBasisCode'] ?? null,
                    'brand_code' => $fareComp['brand']['code'] ?? null,
                    'brand_name' => $fareComp['brand']['brandName'] ?? null,
                    'refundable' => $refundable,
                    'cancel_fee' => $cancelFee,
                    'change_fee' => $changeFee,
                ],
                'voluntary_changes' => $voluntaryChanges,
                'services' => array_column($firstPassenger['services'] ?? [], 'name'),
            ],
        ];
    }

    private function resolveNdcRefundable(array $penalty, array $fareComp): ?bool
    {
        if (isset($penalty['refundableInd'])) {
            return (bool)$penalty['refundableInd'];
        }

        $voluntaryChanges = $fareComp['voluntaryChangeInformation'] ?? [];
        foreach ($voluntaryChanges as $change) {
            if (($change['type'] ?? '') === 'Refund') {
                return (bool)($change['isAllowed'] ?? false);
            }
        }

        if (isset($penalty['cancelFeeInd'])) {
            return (bool)$penalty['cancelFeeInd'];
        }

        return null;
    }

    private function extractFeeAmount(array $fareComp, string $type): ?string
    {
        $voluntaryChanges = $fareComp['voluntaryChangeInformation'] ?? [];

        foreach ($voluntaryChanges as $change) {
            if (
                ($change['type'] ?? '') === $type &&
                in_array('Before Departure', $change['applicabilityList'] ?? []) &&
                ($change['hasFee'] ?? false)
            ) {
                $amount = $change['feeAmount'] ?? null;
                $currency = $change['feeCurrencyCode'] ?? 'USD';

                return $amount ? "{$amount} {$currency}" : null;
            }
        }

        return null;
    }

    protected function buildPayload($data, $tripType): array
    {
        $isNDC = $data['is_ndc'] ?? false;

        $travelPreferences = [
            'TPA_Extensions' => [
                'VerificationItinCallLogic' => ['Value' => 'B'],
            ]
        ];

        if ($isNDC) {
            $travelPreferences['TPA_Extensions']['DataSources'] = [
                'NDC' => 'Enable',
                'ATPCO' => 'Disable',
                'LCC' => 'Disable',
            ];
        }

        return [
            'OTA_AirLowFareSearchRQ' => [
                'Version' => '4.3.0',
                'TravelPreferences' => $travelPreferences,
                'TravelerInfoSummary' => $this->buildTravelerInfo($data),
                'POS' => $this->buildPOS(),
                'OriginDestinationInformation' => $this->buildOriginDestination($data, $tripType),
                'TPA_Extensions' => $this->buildTPAExtensions(),
            ]
        ];
    }

    protected function buildTravelerInfo($flightData): array
    {
        $passengers = $flightData['passengers'] ?? [];
        $totalSeats = array_sum(array_column($passengers, 'count'));

        $passengerTypeQuantities = [];
        foreach ($passengers as $passenger) {
            if (empty($passenger['type']) || empty($passenger['count'])) continue;

            $passengerTypeQuantities[] = [
                'Code' => $passenger['type'],
                'Quantity' => (int)$passenger['count'],
            ];
        }

        if (empty($passengerTypeQuantities)) {
            throw new \Exception('No valid passenger types found');
        }

        return [
            'SeatsRequested' => [$totalSeats],
            'AirTravelerAvail' => [[
                'PassengerTypeQuantity' => $passengerTypeQuantities
            ]]
        ];
    }

    protected function buildPOS(): array
    {
        return [
            'Source' => [[
                'PseudoCityCode' => $this->pcc,
                'RequestorID' => [
                    'Type' => '1',
                    'ID' => '1',
                    'CompanyName' => ['Code' => 'TN']
                ]
            ]]
        ];
    }

    protected function buildOriginDestination($flightData, $tripType): array
    {
        $originDestinations = [];

        foreach ($flightData['legs'] ?? [] as $index => $leg) {
            $originDestinations[] = [
                'RPH' => (string)($index + 1),
                'DepartureDateTime' => $this->formatDateTime(
                    $leg['departure']['date'],
                    $leg['departure']['time']
                ),
                'OriginLocation' => ['LocationCode' => $leg['departure']['airport_code']],
                'DestinationLocation' => ['LocationCode' => $leg['arrival']['airport_code']],
                'TPA_Extensions' => [
                    'SegmentType' => ['Code' => $this->getSegmentTypeCode($tripType, $index)],
                    'Flight' => $this->buildFlights($leg),
                ]
            ];
        }

        return $originDestinations;
    }

    protected function buildFlights($leg): array
    {
        $flights = [];
        foreach ($leg['segments'] ?? [] as $segment) {
            $flights[] = [
                'Number' => $segment['flight_number'],
                'DepartureDateTime' => $this->formatDateTime(
                    $segment['departure']['date'],
                    $segment['departure']['time']
                ),
                'ArrivalDateTime' => $this->formatDateTime(
                    $segment['arrival']['date'],
                    $segment['arrival']['time']
                ),
                'Type' => 'A',
                'ClassOfService' => $this->getBookingCode($segment),
                'OriginLocation' => ['LocationCode' => $segment['departure']['airport_code']],
                'DestinationLocation' => ['LocationCode' => $segment['arrival']['airport_code']],
                'Airline' => [
                    'Operating' => $segment['operating_carrier'],
                    'Marketing' => $segment['carrier'],
                ]
            ];
        }
        return $flights;
    }

    protected function buildTPAExtensions(): array
    {
        return [
            'IntelliSellTransaction' => [
                'RequestType' => ['Name' => '50ITINS']
            ]
        ];
    }

    private function detectTripType(array $legs): string
    {
        return match (count($legs)) {
            1 => 'one_way',
            2 => 'round_trip',
            default => 'multi_city',
        };
    }

    private function getSegmentTypeCode(string $tripType, int $legIndex): string
    {
        return 'O';
    }

    private function getBookingCode(array $segment): string
    {
        return $segment['fare_info']['booking_code']
            ?? $segment['fare_info']['cabin_code']
            ?? 'Y';
    }

    private function formatDateTime(string $date, string $time): string
    {
        return $date . 'T' . substr($time, 0, 8);
    }

    // ✅ Helper method to check if segment is international
    private function isInternationalSegment(?string $departureCountry, ?string $arrivalCountry): bool
    {
        return $departureCountry !== $arrivalCountry;
    }
}
