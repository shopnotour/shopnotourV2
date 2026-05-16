<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingReissue extends Model
{
    protected $table = 'booking_reissues';

    protected $fillable = [
        'booking_id',
        'passenger_ids',
        'passenger_fare_details',
        'old_pnr',
        'new_pnr',
        'reissue_type',
        'reissue_charges',
        'service_charge',
        'fare_difference',
        'total_amount',
        'status',
        'old_flight_details',
        'new_flight_details',
        'reason',
        'airline_response',
        'requested_by',
        'processed_by',
        'requested_at',
        'processed_at'
    ];

    protected $casts = [
        'passenger_ids' => 'array',
        'old_flight_details' => 'array',
        'new_flight_details' => 'array',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }
}
