<?php

namespace Modules\Flight\Service\AirArabia;

use Modules\Flight\Service\FlightChargesService;
use Modules\Flight\Service\FlightDiscountService;

class AirArabiaPriceParser
{

    private array                $flight;
    private FlightDiscountService $discountService;
    private FlightChargesService  $chargesService;

    public function __construct(array $flight)
    {
        $this->flight          = $flight;
        $this->discountService = app(FlightDiscountService::class);
        $this->chargesService  = app(FlightChargesService::class);
    }
    public function checkAndPrepare(string $priceXml): array
    {
        try {
            // ==========================================
            // XML Parse — DOMDocument দিয়ে
            // ==========================================
            $dom = new \DOMDocument();
            $dom->loadXML($priceXml);
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('ns1', 'http://www.opentravel.org/OTA/2003/05');

            // ==========================================
            // 1. Success check
            // ==========================================
            $errors = $xpath->query('//ns1:Errors/ns1:Error');
            if ($errors->length > 0) {
                return ['status' => 'error', 'message' => $errors->item(0)->nodeValue];
            }

            // ==========================================
            // 2. TransactionIdentifier + RPH
            // ==========================================
            $transactionId = $xpath->evaluate('string(//ns1:OTA_AirPriceRS/@TransactionIdentifier)');

            $rphs = [];
            foreach ($xpath->query('//ns1:FlightSegment/@RPH') as $rph) {
                $rphs[] = $rph->nodeValue;
            }

            // ==========================================
            // 3. Price parse
            // ==========================================
            $apiBaseFare = (float)$xpath->evaluate('string(//ns1:ItinTotalFare/ns1:BaseFare/@Amount)');
            $apiTotal    = (float)$xpath->evaluate('string(//ns1:ItinTotalFare/ns1:TotalFare/@Amount)');
            $currency    = $xpath->evaluate('string(//ns1:ItinTotalFare/ns1:TotalFare/@CurrencyCode)');

            // ==========================================
            // 4. Per passenger breakdown
            // ==========================================
            $flightPax = collect($this->flight['passengers'])->keyBy('type');
                $passengerInfoList = [];
            foreach ($xpath->query('//ns1:PTC_FareBreakdown') as $breakdown) {
                $paxType  = $xpath->evaluate('string(ns1:PassengerTypeQuantity/@Code)', $breakdown);
                $quantity  = (int)($flightPax[$paxType]['count'] ?? 1);
//                $quantity = (int)$xpath->evaluate('string(ns1:PassengerTypeQuantity/@Quantity)', $breakdown);
                $base      = (float)$xpath->evaluate('string(ns1:PassengerFare/ns1:BaseFare/@Amount)', $breakdown);
                $total     = (float)$xpath->evaluate('string(ns1:PassengerFare/ns1:TotalFare/@Amount)', $breakdown);
                $taxTotal  = (float)$xpath->evaluate('sum(ns1:PassengerFare/ns1:Taxes/ns1:Tax/@Amount)', $breakdown);
                $feesTotal = (float)$xpath->evaluate('sum(ns1:PassengerFare/ns1:Fees/ns1:Fee/@Amount)', $breakdown);

// gross = base + tax (AIT এর জন্য)
                $grossFare = $base + $taxTotal;

                $passengerInfoList[] = [
                    'passengerInfo' => [
                        'passengerType'      => $paxType,
                        'passengerNumber'    => $quantity,
                        'passengerTotalFare' => [
                            'equivalentAmount' => $base + $feesTotal,        // ← base fare (AIT base)
                            'totalTaxAmount'   => $taxTotal,    // ← tax only (AIT base এর অংশ)
                            'feesAmount'       => $feesTotal,   // ← fees (AIT এ নেই)
                            'totalFare'        => $total,       // ← base + tax + fees (api_total)
                        ],
                    ],
                ];
            }

            // ==========================================
            // 5. Tax breakdown
            // ==========================================
            $taxesBreakdown = [];
            $firstBreakdown = $xpath->query('//ns1:PTC_FareBreakdown')->item(0);
            if ($firstBreakdown) {
                foreach ($xpath->query('ns1:PassengerFare/ns1:Taxes/ns1:Tax', $firstBreakdown) as $tax) {
                    $taxesBreakdown[] = [
                        'code'        => $tax->getAttribute('TaxCode'),
                        'description' => $tax->getAttribute('TaxName'),
                        'amount'      => (float)$tax->getAttribute('Amount'),
                        'currency'    => $tax->getAttribute('CurrencyCode'),
                    ];
                }
            }

            // ==========================================
            // 6. Bundle options
            // ==========================================
            $bundleOptions = [];
            foreach ($xpath->query('//ns1:AABundledServiceExt') as $bundleExt) {
                $bundles = [];
                foreach ($xpath->query('ns1:bundledService', $bundleExt) as $bundle) {
                    $services = [];
                    foreach ($xpath->query('ns1:includedServies', $bundle) as $service) {
                        $services[] = $service->nodeValue;
                    }
                    $bundles[] = [
                        'id'            => $xpath->evaluate('string(ns1:bunldedServiceId)', $bundle),
                        'name'          => $xpath->evaluate('string(ns1:bundledServiceName)', $bundle),
                        'fee_per_pax'   => (float)$xpath->evaluate('string(ns1:perPaxBundledFee)', $bundle),
                        'booking_class' => $xpath->evaluate('string(ns1:bookingClasses)', $bundle),
                        'services'      => $services,
                    ];
                }
                $bundleOptions[] = [
                    'ond'      => $bundleExt->getAttribute('applicableOnd'),
                    'sequence' => (int)$bundleExt->getAttribute('applicableOndSequence'),
                    'bundles'  => $bundles,
                ];
            }

            // ==========================================
            // 7. থেকে শেষ পর্যন্ত — আগের মতোই
            // ==========================================
            $validatingCarrier = substr($rphs[0] ?? 'G9', 0, 2);
            $departureCode     = $this->flight['legs'][0]['departure']['airport_code'] ?? null;
            $arrivalCode       = $this->flight['legs'][0]['arrival']['airport_code']   ?? null;
            $totalSegments     = array_sum(array_column($this->flight['legs'], 'total_segments'));

            $flightDiscountInfo = $this->discountService->calculate(
                $validatingCarrier,
                $departureCode,
                $arrivalCode,
                $passengerInfoList,
                $totalSegments,
                'airarabia'
            );

            $grandTotal           = $flightDiscountInfo['grand_total'];
            $priceBeforeDiscounts = $grandTotal['api_subtotal']
                + $grandTotal['total_ait']
                + $grandTotal['total_service_charge'];
            $finalPrice = $priceBeforeDiscounts
                - $grandTotal['total_user_discount']
                - $grandTotal['total_user_seg_discount'];

            $searchPrice    = (float)($this->flight['price']['total'] ?? 0);
            $confirmedPrice = round($finalPrice, 2);

//            if (abs($searchPrice - $confirmedPrice) > 50) {
//                return [
//                    'status'    => 'mismatch',
//                    'new_price' => $confirmedPrice,
//                    'old_price' => $searchPrice,
//                ];
//            }

            $totalFeesAllPax = array_sum(array_map(
                fn($p) => ($p['passengerInfo']['passengerTotalFare']['feesAmount'] ?? 0)
                    * $p['passengerInfo']['passengerNumber'],
                $passengerInfoList
            ));

// সব pax এর tax sum
            $totalTaxAllPax = array_sum(array_map(
                fn($p) => $p['passengerInfo']['passengerTotalFare']['totalTaxAmount']
                    * $p['passengerInfo']['passengerNumber'],
                $passengerInfoList
            ));

            return [
                'status' => 'ok',
                'data'   => [
                    // ✅ Search response এর মতো same top-level structure
                    'id'                        => $this->flight['id'],
                    'source'                    => 'air_arabia',
                    'legs'                      => $this->flight['legs'],
                    'transaction_id'            => $transactionId,
                    'rphs'                      => $rphs,

                    'price' => [
                        'api_base_fare'  => $apiBaseFare + $totalFeesAllPax, // ← base + fees একসাথে
                        'api_tax'        => $totalTaxAllPax,                  // ← শুধু tax
                        'api_fees'       => $totalFeesAllPax,
                        'api_subtotal'             => $grandTotal['api_subtotal'],
                        'ait_amount'               => $grandTotal['total_ait'],
                        'service_charge'           => $grandTotal['total_service_charge'],
                        'subtotal_before_discount' => round($priceBeforeDiscounts, 2),
                        'flight_discount'          => $grandTotal['total_user_discount'],
                        'segment_discount'         => $grandTotal['total_user_seg_discount'],
                        'total_discounts'          => round($grandTotal['total_user_discount'] + $grandTotal['total_user_seg_discount'], 2),
                        'total'                    => $confirmedPrice,
                        'currency'                 => $currency,
                        'base_currency'            => 'AED',
                        'own_discount'             => $grandTotal['total_own_discount'],
                        'own_seg_discount'         => $grandTotal['total_own_seg_discount'],
                        'total_commission'         => $grandTotal['total_commission'],
                        'own_cost'                 => $grandTotal['total_own_cost'],
                        'gross_profit'             => $grandTotal['gross_profit'],
                    ],

                    'flight_discount_details'   => $flightDiscountInfo,
                    'passenger_price_breakdown' => $flightDiscountInfo['passenger_breakdowns'],

                    'charges_details' => [
                        'ait_charge_percentage'        => $flightDiscountInfo['ait_charge_percentage'],
                        'ait_amount'                   => $grandTotal['total_ait'],
                        'service_charge'               => $grandTotal['total_service_charge'],
                        'segment_discount_per_segment' => $flightDiscountInfo['segment_discount_per_segment'],
                        'segment_discount_total'       => $grandTotal['total_user_seg_discount'],
                    ],

                    'passengers' => collect($this->flight['passengers'])->map(function ($pax) use ($passengerInfoList) {
                        $found = collect($passengerInfoList)->first(
                            fn($p) => $p['passengerInfo']['passengerType'] === $pax['type']
                        );
                        if ($found) {
                            $fare = $found['passengerInfo']['passengerTotalFare'];
                            $pax['tax_amount']        = $fare['totalTaxAmount'];
                            $pax['fees_amount']       = $fare['feesAmount'] ?? 0;
                            $pax['total_fare']        = $fare['totalFare'];           // ← 33,191 (full)
                            $pax['equivalent_amount'] = $fare['equivalentAmount'];
                        }
                        return $pax;
                    })->toArray(),

                    'refundable'         => null,
                    'eTicketable'        => true,
                    'validating_carrier' => $validatingCarrier,
                    'vita'               => null,
                    'last_ticket_date'   => null,
                    'last_ticket_time'   => null,
                    'pricing_source'     => 'air_arabia',
                    'taxes_breakdown'    => $taxesBreakdown,
                    'bundle_options'     => $bundleOptions,
                    'fare_family'        => $this->flight['fare_family']    ?? null,
                    'booking_classes'    => $this->flight['booking_classes'] ?? [],
                    'provider'           => 'air_arabia',
                ],
            ];

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

}
