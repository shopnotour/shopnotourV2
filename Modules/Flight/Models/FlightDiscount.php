<?php

namespace Modules\Flight\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightDiscount extends Model
{
    use SoftDeletes;

    protected $table = 'flight_discounts';

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'user_value',
        'b2b_user_value',
        'max_amount',
        'min_purchase',
        'airline_code',
        'departure_code',
        'arrival_code',
        'valid_from',
        'valid_to',
        'gds_type',
        'status',
        'priority',
        'ait_charge',
        'service_charge',
        'segment_discount',
        'user_seg_discount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'description',
        'conditions',
    ];

    protected $casts = [
        'conditions' => 'array',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    /**
     * Get discount usages
     */
    public function usages()
    {
        return $this->hasMany(FlightDiscountUsage::class, 'discount_id');
    }

    /**
     * Scope: Get active discounts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Get by GDS type
     */
    public function scopeByGds($query, $gdsType)
    {
        return $query->where(function ($q) use ($gdsType) {
            $q->whereNull('gds_type')
                ->orWhere('gds_type', $gdsType);
        });
    }

    /**
     * Get discount for user type
     */
    public function getDiscountForUserType($userType = 'regular')
    {
        if ($userType === 'b2b') {
            return $this->b2b_user_value ?? 0;
        }
        return $this->user_value ?? 0;
    }

    /**
     * Check if discount is valid
     */
    public function isValid()
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now < $this->valid_from) {
            return false;
        }

        if ($this->valid_to && $now > $this->valid_to) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can use this discount
     */
    public function canUserUse($userId)
    {
        if (!$this->isValid()) {
            return false;
        }

        if (!$this->per_user_limit) {
            return true;
        }

        $userUsageCount = $this->usages()
            ->where('user_id', $userId)
            ->count();

        return $userUsageCount < $this->per_user_limit;
    }
}
