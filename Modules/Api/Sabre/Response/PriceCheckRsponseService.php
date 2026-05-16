<?php

namespace Modules\Api\Sabre\Response;

use Illuminate\Support\Facades\Log;

class PriceCheckRsponseService
{
    /**
     * Convert Sabre groupedItineraryResponse to TravelPort format
     */
    public function convert(array $sabreResponse): array
    {
        try {
            if (!isset($sabreResponse['groupedItineraryResponse'])) {
                return [
                    'success' => false,
                    'error' => 'Invalid Sabre response structure'
                ];
            }

            $response = $sabreResponse['groupedItineraryResponse'];

            // Build TravelPort format
            return [
                'success' => true,
                'trace_id' => $this->getTraceId($response),
                'transaction_id' => $this->getTransactionId($response),
                'response_time' => 0,
                'messages' => $this->convertMessages($response),
                'itinerary' => $this->convertItinerary($response),
                'price_result' => $this->convertPriceResult($response),
            ];

        } catch (\Exception $e) {
            Log::error('Sabre to TravelPort conversion error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get trace ID from messages
     */
    private function getTraceId(array $response): string
    {
        $messages = $response['messages'] ?? [];
        foreach ($messages as $message) {
            if ($message['type'] === 'SERVER') {
                return $message['text'] ?? '';
            }
        }
        return '';
    }

    /**
     * Get transaction ID from messages
     */
    private function getTransactionId(array $response): string
    {
        $messages = $response['messages'] ?? [];
        foreach ($messages as $message) {
            if ($message['code'] === 'TRANSACTIONID') {
                return $message['text'] ?? '';
            }
        }
        return '';
    }

    /**
     * Convert Sabre messages to TravelPort format
     */
    private function convertMessages(array $response): array
    {
        $messages = [];
        $sabreMessages = $response['messages'] ?? [];

        foreach ($sabreMessages as $msg) {
            $messages[] = [
                'code' => $msg['code'] ?? '',
                'type' => $msg['severity'] ?? '',
                'provider_code' => '',
                'message' => $msg['text'] ?? '',
            ];
        }

        return $messages;
    }

    /**
     * Convert itinerary
     */
    private function convertItinerary(array $response): array
    {
        $schedules = $response['scheduleDescs'] ?? [];
        $segments = [];

        foreach ($schedules as $index => $schedule) {
            $segments[] = [
                'key' => 'SEG_' . ($index + 1),
                'group' => 0,
                'carrier' => $schedule['carrier']['marketing'] ?? '',
                'flight_number' => (string)($schedule['carrier']['marketingFlightNumber'] ?? ''),
                'origin' => $schedule['departure']['airport'] ?? '',
                'destination' => $schedule['arrival']['airport'] ?? '',
                'departure_time' => $this->convertTime($schedule['departure']['time'] ?? ''),
                'arrival_time' => $this->convertTime($schedule['arrival']['time'] ?? ''),
                'class_of_service' => 'Y',
            ];
        }

        return [
            'segments' => $segments,
        ];
    }

    /**
     * Convert time format
     */
    private function convertTime(string $time): string
    {
        // Sabre: "20:00:00+05:30" -> TravelPort: "2026-02-09T20:00:00.000+05:30"
        if (empty($time)) {
            return '';
        }

        return date('Y-m-d') . 'T' . $time;
    }

    /**
     * Convert price result with per-passenger breakdown
     */
    private function convertPriceResult(array $response): array
    {
        $itineraryGroups = $response['itineraryGroups'] ?? [];
        $solutions = [];

        foreach ($itineraryGroups as $group) {
            $itineraries = $group['itineraries'] ?? [];

            foreach ($itineraries as $itinerary) {
                $pricingInformation = $itinerary['pricingInformation'] ?? [];

                foreach ($pricingInformation as $pricing) {
                    $solutions[] = $this->buildPricingSolution($pricing, $response);
                }
            }
        }

        return [
            'solutions' => $solutions,
            'xml' => ''
        ];
    }

    /**
     * Build pricing solution with per-passenger breakdown
     */
    private function buildPricingSolution(array $pricing, array $response): array
    {
        $fare = $pricing['fare'] ?? [];
        $totalFare = $fare['totalFare'] ?? [];
        $passengerInfoList = $fare['passengerInfoList'] ?? [];

        // Get total prices
        $totalPrice = $totalFare['totalPrice'] ?? 0;
        $totalTax = $totalFare['totalTaxAmount'] ?? 0;
        $baseFare = $totalFare['equivalentAmount'] ?? 0;
        $currency = $totalFare['currency'] ?? 'BDT';

        return [
            'key' => 'SOLUTION_1',

            // Total prices for all passengers
            'total_price' => $this->formatPrice($totalPrice, $currency),
            'base_price' => $this->formatPrice($baseFare, $currency),
            'taxes' => $this->formatPrice($totalTax, $currency),
            'fees' => $this->formatPrice(0, $currency),
            'approximate_total_price' => $this->formatPrice($totalPrice, $currency),
            'approximate_base_price' => $this->formatPrice($baseFare, $currency),
            'equivalent_base_price' => $this->formatPrice($baseFare, $currency),
            'approximate_taxes' => $this->formatPrice($totalTax, $currency),
            'quote_date' => date('Y-m-d'),

            'segment_refs' => $this->buildSegmentRefs($response),

            // ✅ Per-passenger pricing breakdown
            'pricing_info' => $this->buildPricingInfoList($passengerInfoList, $response),

            'fare_notes' => [],
            'host_tokens' => [],
        ];
    }

    /**
     * Build segment refs
     */
    private function buildSegmentRefs(array $response): array
    {
        $schedules = $response['scheduleDescs'] ?? [];
        $refs = [];

        foreach ($schedules as $index => $schedule) {
            $refs[] = ['key' => 'SEG_' . ($index + 1)];
        }

        return $refs;
    }

    /**
     * ✅ Build pricing info list with per-passenger breakdown
     * 🔥 NEW: Added passenger_count and all_passengers fields
     */
    private function buildPricingInfoList(array $passengerInfoList, array $response): array
    {
        $pricingInfos = [];

        foreach ($passengerInfoList as $passengerData) {
            $passengerInfo = $passengerData['passengerInfo'] ?? [];

            $passengerType = $passengerInfo['passengerType'] ?? 'ADT';
            $passengerNumber = $passengerInfo['passengerNumber'] ?? 1;

            // Get per-passenger fare
            $passengerFare = $passengerInfo['passengerTotalFare'] ?? [];

            $totalPrice = $passengerFare['totalFare'] ?? 0;
            $totalTax = $passengerFare['totalTaxAmount'] ?? 0;
            $baseFare = $passengerFare['equivalentAmount'] ?? 0;
            $currency = $passengerFare['currency'] ?? 'BDT';

            // ✅ Calculate per-passenger amount (Sabre gives total for all passengers of same type)
            $perPaxTotal = $passengerNumber > 0 ? round($totalPrice / $passengerNumber, 2) : $totalPrice;
            $perPaxTax = $passengerNumber > 0 ? round($totalTax / $passengerNumber, 2) : $totalTax;
            $perPaxBase = $passengerNumber > 0 ? round($baseFare / $passengerNumber, 2) : $baseFare;

            // ✅ NEW: Build all_passengers array
            $allPassengers = [];
            $passengerAge = $this->getPassengerAge($passengerType);

            for ($i = 0; $i < $passengerNumber; $i++) {
                $allPassengers[] = [
                    'code' => $passengerType,
                    'age' => $passengerAge,
                ];
            }

            $pricingInfos[] = [
                'key' => 'PRICING_' . $passengerType,

                // ✅ Passenger info
                'passenger_type' => $passengerType,
                'passenger_age' => $passengerAge,
                'passenger_count' => $passengerNumber, // ✅ NEW: How many passengers of this type
                'all_passengers' => $allPassengers,     // ✅ NEW: Full breakdown of all passengers

                // Per-passenger prices
                'total_price' => $this->formatPrice($perPaxTotal, $currency),
                'base_price' => $this->formatPrice($perPaxBase, $currency),
                'taxes' => $this->formatPrice($perPaxTax, $currency),
                'approximate_total_price' => $this->formatPrice($perPaxTotal, $currency),
                'approximate_base_price' => $this->formatPrice($perPaxBase, $currency),
                'equivalent_base_price' => $this->formatPrice($perPaxBase, $currency),
                'approximate_taxes' => $this->formatPrice($perPaxTax, $currency),

                // Other info
                'latest_ticketing_time' => '',
                'pricing_method' => 'Guaranteed',
                'refundable' => !($passengerInfo['nonRefundable'] ?? true),
                'includes_vat' => false,
                'eticketability' => 'Yes',
                'plating_carrier' => $this->getValidatingCarrier($passengerInfo),
                'provider_code' => '1G',

                // Details
                'fare_info' => $this->buildFareInfos($passengerInfo, $response),
                'booking_info' => $this->buildBookingInfos($passengerInfo),
                'tax_info' => $this->buildTaxInfos($passengerInfo, $response),
                'fare_calc' => null,
                'passenger_types' => $this->buildPassengerTypes($passengerType, $passengerNumber), // ✅ NEW: Build array of all passenger types
                'penalties' => $this->buildPenalties($passengerType),
                'baggage_allowances' => $this->buildBaggageAllowances($passengerInfo, $response),
            ];
        }

        return $pricingInfos;
    }

    /**
     * ✅ NEW: Build passenger types array
     */
    private function buildPassengerTypes(string $passengerType, int $passengerNumber): array
    {
        $types = [];
        $age = $this->getPassengerAge($passengerType);

        for ($i = 0; $i < $passengerNumber; $i++) {
            $types[] = [
                'code' => $passengerType,
                'age' => $age,
            ];
        }

        return $types;
    }

    /**
     * Get passenger age based on type
     */
    private function getPassengerAge(string $type): ?int
    {
        return match($type) {
            'ADT' => null,
            'CNN' => 8,
            'INF' => 1,
            default => null,
        };
    }

    /**
     * Get validating carrier
     */
    private function getValidatingCarrier(array $passengerInfo): string
    {
        $fareComponents = $passengerInfo['fareComponents'] ?? [];
        if (!empty($fareComponents)) {
            // Get from first fare component
            return ''; // Will be set from schedule data
        }
        return '';
    }

    /**
     * Build fare infos
     */
    private function buildFareInfos(array $passengerInfo, array $response): array
    {
        $fareInfos = [];
        $fareComponents = $passengerInfo['fareComponents'] ?? [];
        $fareComponentDescs = $response['fareComponentDescs'] ?? [];

        foreach ($fareComponents as $component) {
            $ref = $component['ref'] ?? null;
            if ($ref === null) continue;

            // Find the fare component description
            $fareDesc = $this->findFareComponentDesc($ref, $fareComponentDescs);
            if (!$fareDesc) continue;

            $fareAmount = $fareDesc['fareAmount'] ?? 0;
            $fareCurrency = $fareDesc['fareCurrency'] ?? 'USD';

            $fareInfos[] = [
                'key' => 'FARE_' . $ref,
                'fare_basis' => $fareDesc['fareBasisCode'] ?? '',
                'passenger_type_code' => $fareDesc['farePassengerType'] ?? 'ADT',
                'origin' => $fareDesc['origin'] ?? '',
                'destination' => $fareDesc['destination'] ?? '',
                'amount' => $this->formatPrice($fareAmount, $fareCurrency),
            ];
        }

        return $fareInfos;
    }

    /**
     * Find fare component description by ref
     */
    private function findFareComponentDesc(int $ref, array $fareComponentDescs): ?array
    {
        foreach ($fareComponentDescs as $desc) {
            if (($desc['id'] ?? null) === $ref) {
                return $desc;
            }
        }
        return null;
    }

    /**
     * Build booking infos
     */
    private function buildBookingInfos(array $passengerInfo): array
    {
        $bookingInfos = [];
        $fareComponents = $passengerInfo['fareComponents'] ?? [];

        foreach ($fareComponents as $component) {
            $segments = $component['segments'] ?? [];

            foreach ($segments as $segmentData) {
                $segment = $segmentData['segment'] ?? [];
                if (empty($segment)) continue;

                $bookingInfos[] = [
                    'booking_code' => $segment['bookingCode'] ?? '',
                    'cabin_class' => $segment['cabinCode'] ?? 'Y',
                    'fare_info_ref' => '',
                    'segment_ref' => '',
                    'host_token_ref' => '',
                ];
            }
        }

        return $bookingInfos;
    }

    /**
     * Build tax infos
     */
    private function buildTaxInfos(array $passengerInfo, array $response): array
    {
        $taxes = [];
        $taxRefs = $passengerInfo['taxes'] ?? [];
        $taxDescs = $response['taxDescs'] ?? [];

        foreach ($taxRefs as $taxRef) {
            $ref = $taxRef['ref'] ?? null;
            if ($ref === null) continue;

            $taxDesc = $this->findTaxDesc($ref, $taxDescs);
            if (!$taxDesc) continue;

            $taxes[] = [
                'category' => $taxDesc['code'] ?? '',
                'amount' => $this->formatPrice(
                    $taxDesc['amount'] ?? 0,
                    $taxDesc['currency'] ?? 'BDT'
                ),
                'key' => 'TAX_' . $ref,
            ];
        }

        return $taxes;
    }

    /**
     * Find tax description by ref
     */
    private function findTaxDesc(int $ref, array $taxDescs): ?array
    {
        foreach ($taxDescs as $desc) {
            if (($desc['id'] ?? null) === $ref) {
                return $desc;
            }
        }
        return null;
    }

    /**
     * Build penalties with defaults
     */
    private function buildPenalties(string $passengerType): array
    {
        $changeFee = $this->getDefaultChangeFee($passengerType);
        $cancelFee = $this->getDefaultCancelFee($passengerType);

        return [
            'change' => [
                'applies' => 'Anytime',
                'amount' => $this->formatPrice($changeFee, 'BDT'),
            ],
            'cancel' => [
                'applies' => 'Anytime',
                'amount' => $this->formatPrice($cancelFee, 'BDT'),
            ],
        ];
    }

    /**
     * Get default change fee by passenger type
     */
    private function getDefaultChangeFee(string $type): float
    {
        return match($type) {
            'ADT' => 5000.00,
            'CNN' => 3000.00,
            'INF' => 1000.00,
            default => 5000.00,
        };
    }

    /**
     * Get default cancel fee by passenger type
     */
    private function getDefaultCancelFee(string $type): float
    {
        return match($type) {
            'ADT' => 10000.00,
            'CNN' => 6000.00,
            'INF' => 2000.00,
            default => 10000.00,
        };
    }

    /**
     * Build baggage allowances
     */
    private function buildBaggageAllowances(array $passengerInfo, array $response): array
    {
        $baggageInfo = $passengerInfo['baggageInformation'] ?? [];
        $baggageDescs = $response['baggageAllowanceDescs'] ?? [];
        $scheduleDescs = $response['scheduleDescs'] ?? [];

        $allowances = [
            'baggage_allowance_info' => [],
            'carry_on_allowance_info' => [],
        ];

        foreach ($baggageInfo as $info) {
            $allowanceRef = $info['allowance']['ref'] ?? null;
            if ($allowanceRef === null) continue;

            $allowanceDesc = $this->findBaggageAllowanceDesc($allowanceRef, $baggageDescs);
            if (!$allowanceDesc) continue;

            $segments = $info['segments'] ?? [];
            $airlineCode = $info['airlineCode'] ?? '';

            foreach ($segments as $segmentRef) {
                $segmentId = $segmentRef['id'] ?? null;
                if ($segmentId === null) continue;

                $schedule = $scheduleDescs[$segmentId] ?? null;
                if (!$schedule) continue;

                $weight = $allowanceDesc['weight'] ?? 30;
                $unit = $allowanceDesc['unit'] ?? 'kg';

                $allowances['baggage_allowance_info'][] = [
                    'traveler_type' => $passengerInfo['passengerType'] ?? 'ADT',
                    'origin' => $schedule['departure']['airport'] ?? '',
                    'destination' => $schedule['arrival']['airport'] ?? '',
                    'carrier' => $airlineCode,
                    'texts' => ["{$weight}{$unit}"],
                ];
            }
        }

        return $allowances;
    }

    /**
     * Find baggage allowance description by ref
     */
    private function findBaggageAllowanceDesc(int $ref, array $baggageDescs): ?array
    {
        foreach ($baggageDescs as $desc) {
            if (($desc['id'] ?? null) === $ref) {
                return $desc;
            }
        }
        return null;
    }

    /**
     * Format price in TravelPort style
     */
    private function formatPrice(float $amount, string $currency): array
    {
        $formattedAmount = number_format($amount, 2, '.', '');

        return [
            'raw' => $currency . $formattedAmount,
            'currency' => $currency,
            'amount' => $amount,
            'formatted' => $currency . ' ' . number_format($amount, 2)
        ];
    }
}
