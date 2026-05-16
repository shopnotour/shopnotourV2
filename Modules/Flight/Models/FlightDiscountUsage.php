<?php

namespace Modules\Flight\Models;

use Illuminate\Database\Eloquent\Model;

class FlightDiscountUsage extends Model
{
    protected $table = 'flight_discount_usages';

    protected $fillable = [
        'discount_id',
        'user_id',
        'booking_id',
        'discount_amount',
        'original_price',
        'final_price',
        'airline_code',
        'route',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function discount()
    {
        return $this->belongsTo(FlightDiscount::class, 'discount_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(\Modules\Booking\Models\Booking::class, 'booking_id');
    }
}
