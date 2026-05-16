<?php

namespace Modules\Flight\Service;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlightDiscountService
{
    public function calculate(
        ?string $airline,
        ?string $departure,
        ?string $arrival,
        array   $passengerInfoList,
        int     $totalSegments,
        string  $provider
    ): array {
        try {
            // ✅ Role check
            $user       = Auth::user();
            $role       = $user?->role ?? 'guest';
            $isCustomer = $role === 'customer';

            $discount = $this->findBestDiscount($airline, $departure, $arrival, $provider);

            $passengerBreakdowns       = [];
            $grandTotalApiSubtotal     = 0;
            $grandTotalUserPayable     = 0;
            $grandTotalOwnCost         = 0;
            $grandTotalAit             = 0;
            $grandTotalServiceCharge   = 0;
            $grandTotalUserDiscount    = 0;
            $grandTotalOwnDiscount     = 0;
            $grandTotalUserSegDiscount = 0;
            $grandTotalOwnSegDiscount  = 0;
            $grandTotalCommission      = 0;

            foreach ($passengerInfoList as $passengerItem) {
                $info           = $passengerItem['passengerInfo'] ?? [];
                $fare           = $info['passengerTotalFare'] ?? [];
                $passengerType  = $info['passengerType'] ?? 'ADT';
                $passengerCount = $info['passengerNumber'] ?? 1;

                $perPaxBaseFare = $fare['equivalentAmount'] ?? 0;
                $perPaxTax      = $fare['totalTaxAmount'] ?? 0;
                $perPaxGross    = $perPaxBaseFare + $perPaxTax;
                $perPaxApiTotal = $fare['totalFare'] ?? $perPaxGross;

                if (!$discount) {
                    // ✅ Default fallback: AIT 0.3%
                    $breakdown = $this->buildPassengerBreakdown(
                        $passengerType, $passengerCount,
                        $perPaxBaseFare, $perPaxTax, $perPaxGross, $perPaxApiTotal,
                        round(($perPaxGross * 0.3) / 100, 2), 0,
                        0, 0, 0, 0, 0, $totalSegments
                    );
                } else {
                    // ✅ AIT
                    $perPaxAit = $discount->ait_charge
                        ? round(($perPaxGross * $discount->ait_charge) / 100, 2)
                        : 0;

                    // ✅ Service charge
                    $perPaxServiceCharge = $discount->service_charge ?? 0;

                    // ✅ Role অনুযায়ী discount value
                    $userDiscountValue = $isCustomer
                        ? $discount->user_value
                        : $discount->b2b_user_value;

                    // ✅ User discount (type অনুযায়ী percentage/amount)
                    $perPaxUserDiscount = $this->calculateDiscountAmount(
                        $discount->type,
                        $userDiscountValue,
                        $perPaxBaseFare,
                        $discount->max_amount ?? null,
                        $airline,
                        $discount->airline_code
                    );

                    // ✅ Own discount (সবসময় value column)
                    $perPaxOwnDiscount = $this->calculateDiscountAmount(
                        $discount->type,
                        $discount->value,
                        $perPaxBaseFare,
                        $discount->max_amount ?? null,
                        $airline,
                        $discount->airline_code
                    );

                    // ✅ Segment discount (role অনুযায়ী)
                    $userSegDiscountValue = $isCustomer
                        ? ($discount->user_seg_discount ?? 0)
                        : ($discount->user_seg_discount ?? 0); // b2b seg discount আলাদা column থাকলে এখানে change করো

                    $perPaxUserSegDiscount = $userSegDiscountValue * $totalSegments;
                    $perPaxOwnSegDiscount  = ($discount->segment_discount ?? 0) * $totalSegments;
                    $perPaxCommission      = $this->calculateCommission($discount, $perPaxBaseFare);

                    $breakdown = $this->buildPassengerBreakdown(
                        $passengerType, $passengerCount,
                        $perPaxBaseFare, $perPaxTax, $perPaxGross, $perPaxApiTotal,
                        $perPaxAit, $perPaxServiceCharge,
                        $perPaxUserDiscount, $perPaxOwnDiscount,
                        $perPaxUserSegDiscount, $perPaxOwnSegDiscount,
                        $perPaxCommission, $totalSegments
                    );
                }

                $passengerBreakdowns[] = $breakdown;

                $count = $passengerCount;
                $grandTotalApiSubtotal     += $breakdown['per_pax']['api_total']         * $count;
                $grandTotalAit             += $breakdown['per_pax']['ait_amount']        * $count;
                $grandTotalServiceCharge   += $breakdown['per_pax']['service_charge']    * $count;
                $grandTotalUserDiscount    += $breakdown['per_pax']['user_discount']     * $count;
                $grandTotalOwnDiscount     += $breakdown['per_pax']['own_discount']      * $count;
                $grandTotalUserSegDiscount += $breakdown['per_pax']['user_seg_discount'] * $count;
                $grandTotalOwnSegDiscount  += $breakdown['per_pax']['own_seg_discount']  * $count;
                $grandTotalCommission      += $breakdown['per_pax']['commission']        * $count;
                $grandTotalUserPayable     += $breakdown['per_pax']['user_payable']      * $count;
                $grandTotalOwnCost         += $breakdown['per_pax']['own_cost']          * $count;
            }

            $grandProfit = $grandTotalUserPayable - $grandTotalOwnCost;

            return [
                'applicable'           => (bool)$discount,
                'discount_id'          => $discount->id ?? null,
                'discount_code'        => $discount->code ?? null,
                'discount_name'        => $discount->name ?? null,
                'discount_type'        => $discount->type ?? null,
                'passenger_breakdowns' => $passengerBreakdowns,
                'grand_total' => [
                    'api_subtotal'            => round($grandTotalApiSubtotal, 2),
                    'total_ait'               => round($grandTotalAit, 2),
                    'total_service_charge'    => round($grandTotalServiceCharge, 2),
                    'total_user_discount'     => round($grandTotalUserDiscount, 2),
                    'total_user_seg_discount' => round($grandTotalUserSegDiscount, 2),
                    'total_user_payable'      => round($grandTotalUserPayable, 2),
                    'total_own_discount'      => round($grandTotalOwnDiscount, 2),
                    'total_own_seg_discount'  => round($grandTotalOwnSegDiscount, 2),
                    'total_commission'        => round($grandTotalCommission, 2),
                    'total_own_cost'          => round($grandTotalOwnCost, 2),
                    'gross_profit'            => round($grandProfit, 2),
                ],
                'ait_amount'                   => round($grandTotalAit, 2),
                'service_charge'               => round($grandTotalServiceCharge, 2),
                'flight_discount_amount'       => round($grandTotalUserDiscount, 2),
                'segment_discount_total'       => round($grandTotalUserSegDiscount, 2),
                'total_discounts'              => round($grandTotalUserDiscount + $grandTotalUserSegDiscount, 2),
                'ait_charge_percentage'        => $discount->ait_charge ?? 0,
                'segment_discount_per_segment' => $discount->user_seg_discount ?? 0,
                'match_specificity'            => $this->getMatchSpecificity($discount, $airline, $departure, $arrival),
                'priority'                     => $discount->priority ?? null,
                'user_role'                    => $role,
                'is_customer'                  => $isCustomer,
                'flight_discount_label' => $discount
                    ? ($discount->type === 'percentage'
                        ? number_format((float)($userDiscountValue ?? 0), 2) . '%'
                        : '৳' . number_format((float)($userDiscountValue ?? 0), 0))
                    : null,
                'segment_discount_label' => $discount && ($discount->user_seg_discount ?? 0) > 0
                    ? '৳' . number_format((float)$discount->user_seg_discount, 0) . '/seg'
                    : null,
            ];

        } catch (\Exception $e) {
            Log::error('Flight discount calculation error', [
                'error'    => $e->getMessage(),
                'airline'  => $airline,
                'route'    => "$departure-$arrival",
                'provider' => $provider,
            ]);
            return $this->noDiscountResponse($passengerInfoList);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FIND BEST DISCOUNT
    //
    //  Matching Priority:
    //    1. airline + departure + arrival   (score: 111)
    //    2. airline + departure             (score: 110)
    //    3. airline generic                 (score: 100)
    //    4. all generic (all null)          (score: 0, but has ait/service)
    //    5. fallback: AIT 0.3%
    // ═══════════════════════════════════════════════════════════════════════

    private function findBestDiscount(
        ?string $airline,
        ?string $departure,
        ?string $arrival,
        string  $provider
    ) {
        $today        = now()->format('Y-m-d');
        $providerNorm = strtolower(trim($provider));

        Log::info('[FlightDiscount] Input', [
            'airline'       => $airline,
            'departure'     => $departure,
            'arrival'       => $arrival,
            'provider_raw'  => $provider,
            'provider_norm' => $providerNorm,
            'today'         => $today,
        ]);

        $discounts = DB::table('flight_discounts')
            ->where('status', 'active')
            ->where(function ($q) use ($providerNorm) {
                $q->whereNull('gds_type')
                    ->orWhereRaw('LOWER(TRIM(gds_type)) = ?', [$providerNorm]);
            })
            ->where(function ($q) use ($airline) {
                $q->whereNull('airline_code')
                    ->orWhere('airline_code', $airline);
            })
            ->where(function ($q) use ($today) {
                $q->where(function ($q2) use ($today) {
                    $q2->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
                })->where(function ($q2) use ($today) {
                    $q2->whereNull('valid_to')->orWhere('valid_to', '>=', $today);
                });
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereRaw('usage_count < usage_limit');
            })
            ->get();

        Log::info('[FlightDiscount] DB rows found', [
            'count'     => $discounts->count(),
            'row_names' => $discounts->pluck('name')->toArray(),
        ]);

        if ($discounts->isEmpty()) return null;

        // ✅ Score করো এবং invalid গুলো বাদ দাও
        $candidates = $discounts
            ->map(function ($discount) use ($airline, $departure, $arrival) {
                $discount->match_score = $this->calculateMatchScore(
                    $discount, $airline, $departure, $arrival
                );
                return $discount;
            })
            ->filter(function ($discount) {
                if ($discount->match_score < 0) return false;

                return $discount->match_score > 0
                    || ($discount->ait_charge        ?? 0) > 0
                    || ($discount->service_charge    ?? 0) > 0
                    || ($discount->segment_discount  ?? 0) > 0
                    || ($discount->user_seg_discount ?? 0) > 0;
            });

        Log::info('[FlightDiscount] Candidates after filter', [
            'count' => $candidates->count(),
            'rows'  => $candidates->map(fn($d) => [
                'name'       => $d->name,
                'airline'    => $d->airline_code,
                'departure'  => $d->departure_code,
                'arrival'    => $d->arrival_code,
                'user_value' => $d->user_value,
                'b2b_value'  => $d->b2b_user_value,
                'score'      => $d->match_score,
            ])->values()->toArray(),
        ]);

        if ($candidates->isEmpty()) return null;

        // ✅ Priority 1: airline + departure + arrival exact match
        $exactFull = $candidates->filter(
            fn($d) => !is_null($d->airline_code)
                && !is_null($d->departure_code)
                && !is_null($d->arrival_code)
                && $d->airline_code    === $airline
                && $d->departure_code  === $departure
                && $d->arrival_code    === $arrival
        );
        if ($exactFull->isNotEmpty()) {
            return $exactFull->sortByDesc(fn($d) => (float)($d->user_value ?? 0))->first();
        }

        // ✅ Priority 2: airline + departure match
        $exactDep = $candidates->filter(
            fn($d) => !is_null($d->airline_code)
                && !is_null($d->departure_code)
                && is_null($d->arrival_code)
                && $d->airline_code   === $airline
                && $d->departure_code === $departure
        );
        if ($exactDep->isNotEmpty()) {
            return $exactDep->sortByDesc(fn($d) => (float)($d->user_value ?? 0))->first();
        }
        
        $exactArr = $candidates->filter(
            fn($d) => !is_null($d->airline_code)
                && is_null($d->departure_code)
                && !is_null($d->arrival_code)
                && $d->airline_code  === $airline
                && $d->arrival_code  === $arrival
            );
            if ($exactArr->isNotEmpty()) {
                return $exactArr->sortByDesc(fn($d) => (float)($d->user_value ?? 0))->first();
            }

        // ✅ Priority 3: airline generic (departure + arrival null)
        $airlineGeneric = $candidates->filter(
            fn($d) => !is_null($d->airline_code)
                && is_null($d->departure_code)
                && is_null($d->arrival_code)
                && $d->airline_code === $airline
        );
        if ($airlineGeneric->isNotEmpty()) {
            return $airlineGeneric->sortByDesc(fn($d) => (float)($d->user_value ?? 0))->first();
        }

        // ✅ Priority 4: all generic (airline + departure + arrival null)
        $allGeneric = $candidates->filter(
            fn($d) => is_null($d->airline_code)
                && is_null($d->departure_code)
                && is_null($d->arrival_code)
        );
        if ($allGeneric->isNotEmpty()) {
            return $allGeneric->sortByDesc(fn($d) => (float)($d->user_value ?? 0))->first();
        }

        return null;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  MATCH SCORE
    // ═══════════════════════════════════════════════════════════════════════

        private function calculateMatchScore($discount, ?string $airline, ?string $departure, ?string $arrival): int
        {
            $score = 0;
        
            if (!is_null($discount->airline_code)) {
                if ($discount->airline_code !== $airline) return -1;
                $score += 100;
            }
        
            if (!is_null($discount->departure_code)) {
                if ($discount->departure_code !== $departure) return -1;
                $score += 10;
            }
        
            if (!is_null($discount->arrival_code)) {
                if ($discount->arrival_code !== $arrival) return -1;
                $score += 1;
            }
        
            return $score;
        }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildPassengerBreakdown(
        string $passengerType,
        int    $passengerCount,
        float  $baseFare,
        float  $tax,
        float  $grossFare,
        float  $apiTotal,
        float  $aitAmount,
        float  $serviceCharge,
        float  $userDiscount,
        float  $ownDiscount,
        float  $userSegDiscount,
        float  $ownSegDiscount,
        float  $commission,
        int    $totalSegments
    ): array {
        $userPayable   = $apiTotal + $aitAmount + $serviceCharge - $userDiscount - $userSegDiscount;
        $segmentProfit = $ownSegDiscount - $userSegDiscount;
        $ownCost       = $apiTotal + $aitAmount - $ownDiscount - $segmentProfit - $commission;

        return [
            'passenger_type'  => $passengerType,
            'passenger_count' => $passengerCount,
            'per_pax' => [
                'base_fare'         => round($baseFare, 2),
                'tax'               => round($tax, 2),
                'gross_fare'        => round($grossFare, 2),
                'api_total'         => round($apiTotal, 2),
                'ait_amount'        => round($aitAmount, 2),
                'service_charge'    => round($serviceCharge, 2),
                'user_discount'     => round($userDiscount, 2),
                'user_seg_discount' => round($userSegDiscount, 2),
                'user_payable'      => round($userPayable, 2),
                'own_discount'      => round($ownDiscount, 2),
                'own_seg_discount'  => round($ownSegDiscount, 2),
                'commission'        => round($commission, 2),
                'own_cost'          => round($ownCost, 2),
                'profit_per_pax'    => round($userPayable - $ownCost, 2),
            ],
            'total' => [
                'base_fare'         => round($baseFare * $passengerCount, 2),
                'tax'               => round($tax * $passengerCount, 2),
                'api_total'         => round($apiTotal * $passengerCount, 2),
                'ait_amount'        => round($aitAmount * $passengerCount, 2),
                'service_charge'    => round($serviceCharge * $passengerCount, 2),
                'user_discount'     => round($userDiscount * $passengerCount, 2),
                'user_seg_discount' => round($userSegDiscount * $passengerCount, 2),
                'user_payable'      => round($userPayable * $passengerCount, 2),
                'own_discount'      => round($ownDiscount * $passengerCount, 2),
                'own_seg_discount'  => round($ownSegDiscount * $passengerCount, 2),
                'commission'        => round($commission * $passengerCount, 2),
                'own_cost'          => round($ownCost * $passengerCount, 2),
                'profit'            => round(($userPayable - $ownCost) * $passengerCount, 2),
            ],
        ];
    }

    private function calculateDiscountAmount(
        ?string $type,
        ?float  $value,
        float   $baseFare,
        ?float  $maxAmount,
        ?string $airline,
        ?string $discountAirlineCode
    ): float {
        $value = (float)($value ?? 0);
        if ($value <= 0) return 0;

        if (!is_null($discountAirlineCode) && $discountAirlineCode !== $airline) return 0;

        if ($type === 'percentage') {
            $amount = ($baseFare * $value) / 100;
            if ($maxAmount && (float)$maxAmount > 0 && $amount > (float)$maxAmount) {
                $amount = (float)$maxAmount;
            }
        } else {
            $amount = $value;
        }

        return round(min($amount, $baseFare), 2);
    }

    private function calculateCommission($discount, float $baseFare): float
    {
        $commissionValue = (float)($discount->commission_value ?? 0);
        if ($commissionValue <= 0) return 0;

        if ($discount->commission_type === 'percentage') {
            return round(($baseFare * $commissionValue) / 100, 2);
        }

        return round($commissionValue, 2);
    }

    private function getMatchSpecificity($discount, ?string $airline, ?string $departure, ?string $arrival): string
    {
        if (!$discount) return 'none';

        $matches = [];
        if (!is_null($discount->airline_code)   && $discount->airline_code   === $airline)   $matches[] = 'Airline';
        if (!is_null($discount->departure_code) && $discount->departure_code === $departure) $matches[] = 'Departure';
        if (!is_null($discount->arrival_code)   && $discount->arrival_code   === $arrival)   $matches[] = 'Arrival';

        return empty($matches) ? 'Generic' : implode(' + ', $matches);
    }

    private function noDiscountResponse(array $passengerInfoList): array
    {
        $passengerBreakdowns = [];
        $grandApiSubtotal    = 0;
        $grandUserPayable    = 0;

        foreach ($passengerInfoList as $passengerItem) {
            $info           = $passengerItem['passengerInfo'] ?? [];
            $fare           = $info['passengerTotalFare'] ?? [];
            $passengerType  = $info['passengerType'] ?? 'ADT';
            $passengerCount = $info['passengerNumber'] ?? 1;

            $perPaxBaseFare = $fare['equivalentAmount'] ?? 0;
            $perPaxTax      = $fare['totalTaxAmount'] ?? 0;
            $perPaxGross    = $perPaxBaseFare + $perPaxTax;
            $perPaxApiTotal = $fare['totalFare'] ?? $perPaxGross;

            // ✅ Fallback: AIT 0.3%
            $breakdown = $this->buildPassengerBreakdown(
                $passengerType, $passengerCount,
                $perPaxBaseFare, $perPaxTax, $perPaxGross, $perPaxApiTotal,
                round(($perPaxGross * 0.3) / 100, 2), 0,
                0, 0, 0, 0, 0, 0
            );

            $passengerBreakdowns[] = $breakdown;
            $grandApiSubtotal += $perPaxApiTotal * $passengerCount;
            $grandUserPayable += $breakdown['per_pax']['user_payable'] * $passengerCount;
        }

        return [
            'applicable'           => false,
            'discount_id'          => null,
            'discount_code'        => null,
            'discount_name'        => null,
            'discount_type'        => null,
            'passenger_breakdowns' => $passengerBreakdowns,
            'grand_total' => [
                'api_subtotal'            => round($grandApiSubtotal, 2),
                'total_ait'               => 0,
                'total_service_charge'    => 0,
                'total_user_discount'     => 0,
                'total_user_seg_discount' => 0,
                'total_user_payable'      => round($grandUserPayable, 2),
                'total_own_discount'      => 0,
                'total_own_seg_discount'  => 0,
                'total_commission'        => 0,
                'total_own_cost'          => round($grandUserPayable, 2),
                'gross_profit'            => 0,
            ],
            'ait_amount'                   => 0,
            'service_charge'               => 0,
            'flight_discount_amount'       => 0,
            'segment_discount_total'       => 0,
            'total_discounts'              => 0,
            'ait_charge_percentage'        => 0,
            'segment_discount_per_segment' => 0,
            'match_specificity'            => 'none',
            'priority'                     => null,
            'flight_discount_label'        => null,
            'segment_discount_label'       => null,
        ];
    }

    public function incrementUsage(int $discountId): bool
    {
        try {
            DB::table('flight_discounts')->where('id', $discountId)->increment('usage_count');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to increment discount usage', [
                'discount_id' => $discountId,
                'error'       => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    public function getRouteMatchScore(
    ?string $airline,
    ?string $departure,
    ?string $arrival,
    string  $provider
): int {
    $today        = now()->format('Y-m-d');
    $providerNorm = strtolower(trim($provider));

    $discounts = DB::table('flight_discounts')
        ->where('status', 'active')
        ->where(function ($q) use ($providerNorm) {
            $q->whereNull('gds_type')
                ->orWhereRaw('LOWER(TRIM(gds_type)) = ?', [$providerNorm]);
        })
        ->where(function ($q) use ($airline) {
            $q->whereNull('airline_code')
                ->orWhere('airline_code', $airline);
        })
        ->where(function ($q) use ($today) {
            $q->where(function ($q2) use ($today) {
                $q2->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
            })->where(function ($q2) use ($today) {
                $q2->whereNull('valid_to')->orWhere('valid_to', '>=', $today);
            });
        })
        ->get();

    if ($discounts->isEmpty()) return 0;

    $best = 0;
    foreach ($discounts as $discount) {
        $score = $this->calculateMatchScore($discount, $airline, $departure, $arrival);
        if ($score > $best) {
            $best = $score;
        }
    }

    return $best;
}
}
