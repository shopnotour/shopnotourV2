<?php

namespace Modules\Flight\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'ait_charge',
        'service_charge',
        'segment_discount',
    ];

    protected $casts = [
        'ait_charge' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'segment_discount' => 'decimal:2',
    ];

    // Helper methods to get charges by type
    public static function getDomesticCharges()
    {
        return self::where('type', 'domestic')->first();
    }

    public static function getInternationalCharges()
    {
        return self::where('type', 'international')->first();
    }

    public static function getChargesByType(string $type)
    {
        return self::where('type', $type)->first();
    }
}
