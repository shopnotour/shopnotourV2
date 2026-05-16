<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingRefund extends Model
{
    protected $table = 'booking_refunds';

    protected $fillable = [
        'booking_id',
        'pnr',
        'refund_type',
        'passenger_id',
        'refund_amount',
        'refund_charges',
        'service_charge',
        'net_refund_amount',
        'status',
        'requested_by',
        'approved_by',
        'reason',
        'rejection_reason',
        'airline_response',
        'requested_at',
        'approved_at'
    ];

    protected $casts = [
        'passenger_id' => 'array',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    // ✅ Get passengers (multiple)
    public function passengers()
    {
        if (empty($this->passenger_id)) {
            return collect([]);
        }

        return BookingPassenger::whereIn('id', $this->passenger_id)->get();
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
