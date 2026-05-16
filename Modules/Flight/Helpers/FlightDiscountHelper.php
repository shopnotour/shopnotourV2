<?php

namespace Modules\Flight\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Flight\Models\FlightDiscount;
use Modules\Flight\Models\FlightDiscountUsage;
class FlightDiscountHelper
{
    /**
     * Flight search response theke matching discounts ber kora
     *
     * @param array $searchResponse Flight search API response
     * @param int|null $userId User ID (optional, guest user hole null)
     * @return Collection Applicable discounts collection
     */
    public static function getApplicableDiscounts(array $searchResponse, ?int $userId = null): Collection
    {
        $applicableDiscounts = collect();

        // Search response theke route info extract kora
        foreach ($searchResponse['flights'] ?? [] as $flight) {
            $airlineCode = $flight['airline_code'] ?? null;
            $route = $flight['route'] ?? null;
            $departureCode = $flight['departure_airport'] ?? null;
            $arrivalCode = $flight['arrival_airport'] ?? null;
            $price = $flight['price'] ?? 0;

            // Database theke matching discounts খুঁজে বের করা
            $discounts = self::findMatchingDiscounts(
                $airlineCode,
                $route,
                $departureCode,
                $arrivalCode,
                $price,
                $userId
            );

            if ($discounts->isNotEmpty()) {
                $applicableDiscounts = $applicableDiscounts->merge($discounts->map(function ($discount) use ($flight, $price) {
                    return [
                        'flight_info' => $flight,
                        'discount' => $discount,
                        'calculated_discount' => self::calculateDiscount($discount, $price),
                        'final_price' => self::getFinalPrice($discount, $price)
                    ];
                }));
            }
        }

        return $applicableDiscounts;
    }

    /**
     * Database theke matching discounts খুঁজে বের করা
     * Priority order: Airline+Departure+Arrival > Airline+Route > Only Airline
     */
    private static function findMatchingDiscounts(
        ?string $airlineCode,
        ?string $route,
        ?string $departureCode,
        ?string $arrivalCode,
        float $price,
        ?int $userId
    ): Collection {
        // Base query - common conditions
        $baseQuery = function() use ($price, $userId) {
            $q = FlightDiscount::where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('valid_to')
                        ->orWhere('valid_to', '>=', now());
                })
                ->where(function ($query) use ($price) {
                    $query->where('min_purchase', '<=', $price)
                        ->orWhere('min_purchase', 0);
                })
                ->where(function ($query) {
                    $query->whereNull('usage_limit')
                        ->orWhereRaw('usage_count < usage_limit');
                });

            // Per user limit check
            if ($userId) {
                $q->where(function ($query) use ($userId) {
                    $query->whereNull('per_user_limit')
                        ->orWhereDoesntHave('usages', function ($subQuery) use ($userId) {
                            $subQuery->where('user_id', $userId)
                                ->groupBy('discount_id')
                                ->havingRaw('COUNT(*) >= (SELECT per_user_limit FROM flight_discounts WHERE id = discount_id)');
                        });
                });
            }

            return $q;
        };

        $discounts = collect();

        // Priority 1: Airline + Departure + Arrival (সবচেয়ে specific)
        if ($airlineCode && $departureCode && $arrivalCode) {
            $specificDiscounts = $baseQuery()
                ->where('airline_code', $airlineCode)
                ->where('departure_code', $departureCode)
                ->where('arrival_code', $arrivalCode)
                ->get();

            if ($specificDiscounts->isNotEmpty()) {
                $discounts = $discounts->merge($specificDiscounts);
            }
        }

        // Priority 2: Airline + Route (medium specific)
        if ($airlineCode && $route) {
            $routeDiscounts = $baseQuery()
                ->where('airline_code', $airlineCode)
                ->where('route', $route)
                ->whereNull('departure_code')
                ->whereNull('arrival_code')
                ->get();

            if ($routeDiscounts->isNotEmpty()) {
                $discounts = $discounts->merge($routeDiscounts);
            }
        }

        // Priority 3: Only Airline (least specific - je kono route e applicable)
        if ($airlineCode) {
            $airlineOnlyDiscounts = $baseQuery()
                ->where('airline_code', $airlineCode)
                ->whereNull('route')
                ->whereNull('departure_code')
                ->whereNull('arrival_code')
                ->get();

            if ($airlineOnlyDiscounts->isNotEmpty()) {
                $discounts = $discounts->merge($airlineOnlyDiscounts);
            }
        }

        // Duplicate remove kora (same discount multiple priority te ashte pare)
        return $discounts->unique('id');
    }

    /**
     * Discount amount calculate kora
     */
    public static function calculateDiscount($discount, float $originalPrice): float
    {
        $discountAmount = 0;

        if ($discount->type === 'percentage') {
            $discountAmount = ($originalPrice * $discount->value) / 100;
        } elseif ($discount->type === 'fixed') {
            $discountAmount = $discount->value;
        }

        // Max amount check kora
        if ($discount->max_amount && $discountAmount > $discount->max_amount) {
            $discountAmount = $discount->max_amount;
        }

        return round($discountAmount, 2);
    }

    /**
     * Final price calculate kora after discount
     */
    public static function getFinalPrice($discount, float $originalPrice): float
    {
        $discountAmount = self::calculateDiscount($discount, $originalPrice);
        return round($originalPrice - $discountAmount, 2);
    }

    /**
     * Booking time e discount apply kora এবং usage table e save kora
     */
    public static function applyDiscount(
        int $discountId,
        int $bookingId,
        ?int $userId,
        string $airlineCode,
        string $route,
        float $originalPrice
    ): ?array {
        $discount = FlightDiscount::find($discountId);

        if (!$discount) {
            return [
                'success' => false,
                'message' => 'Discount not found'
            ];
        }

        // Validate discount eligibility
        $validation = self::validateDiscount($discount, $userId, $originalPrice);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        // Calculate discount
        $discountAmount = self::calculateDiscount($discount, $originalPrice);
        $finalPrice = self::getFinalPrice($discount, $originalPrice);

        // Save usage
        $usage = FlightDiscountUsage::create([
            'discount_id' => $discountId,
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'discount_amount' => $discountAmount,
            'original_price' => $originalPrice,
            'final_price' => $finalPrice,
            'airline_code' => $airlineCode,
            'route' => $route,
            'used_at' => now()
        ]);

        // Increment usage count
        $discount->increment('usage_count');

        return [
            'success' => true,
            'usage' => $usage,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice
        ];
    }

    /**
     * Discount validate kora before applying
     */
    public static function validateDiscount($discount, ?int $userId, float $price): array
    {
        // Status check
        if ($discount->status !== 'active') {
            return ['valid' => false, 'message' => 'Discount is not active'];
        }

        // Date validity check
        if ($discount->valid_from && Carbon::parse($discount->valid_from)->isFuture()) {
            return ['valid' => false, 'message' => 'Discount not yet valid'];
        }

        if ($discount->valid_to && Carbon::parse($discount->valid_to)->isPast()) {
            return ['valid' => false, 'message' => 'Discount has expired'];
        }

        // Minimum purchase check
        if ($discount->min_purchase > $price) {
            return ['valid' => false, 'message' => "Minimum purchase amount is {$discount->min_purchase}"];
        }

        // Usage limit check
        if ($discount->usage_limit && $discount->usage_count >= $discount->usage_limit) {
            return ['valid' => false, 'message' => 'Discount usage limit reached'];
        }

        // Per user limit check
        if ($userId && $discount->per_user_limit) {
            $userUsageCount = FlightDiscountUsage::where('discount_id', $discount->id)
                ->where('user_id', $userId)
                ->count();

            if ($userUsageCount >= $discount->per_user_limit) {
                return ['valid' => false, 'message' => 'You have reached the usage limit for this discount'];
            }
        }

        return ['valid' => true, 'message' => 'Discount is valid'];
    }

    /**
     * Promo code দিয়ে discount খুঁজে বের করা
     */
    public static function findByPromoCode(string $code): ?FlightDiscount
    {
        return FlightDiscount::where('code', $code)
            ->where('status', 'active')
            ->first();
    }

    /**
     * User এর জন্য available discounts দেখানো
     */
    public static function getUserAvailableDiscounts(?int $userId = null): Collection
    {
        $query = FlightDiscount::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereRaw('usage_count < usage_limit');
            });

        return $query->get();
    }

    /**
     * Best discount খুঁজে বের করা for a specific flight
     */
    public static function getBestDiscount(array $applicableDiscounts): ?array
    {
        if (empty($applicableDiscounts)) {
            return null;
        }

        return collect($applicableDiscounts)->sortByDesc(function ($item) {
            return $item['calculated_discount'];
        })->first();
    }

    /**
     * Format discount for display
     */
    public static function formatDiscountDisplay($discount): string
    {
        if ($discount->type === 'percentage') {
            $display = "{$discount->value}% OFF";
            if ($discount->max_amount) {
                $display .= " (Max: ৳{$discount->max_amount})";
            }
        } else {
            $display = "৳{$discount->value} OFF";
        }

        if ($discount->code) {
            $display .= " - Code: {$discount->code}";
        }

        return $display;
    }
}
