<?php

namespace Modules\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingSsr extends Model
{
    protected $table = 'booking_ssrs';

    protected $fillable = [
        'booking_id',
        'passenger_id',
        'ssr_type',
        'ssr_code',
        'description',
        'amount',
        'status',
        'airline_reference',
        'ssr_details',
        'added_by',
        'confirmed_at'
    ];


    protected $casts = [
        'ssr_details' => 'array',
        'confirmed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * ============== RELATIONSHIPS ==============
     */

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function passenger()
    {
        return $this->belongsTo(BookingPassenger::class, 'passenger_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // ✅ Customer সরাসরি পেতে (booking এর মাধ্যমে)
    public function getCustomerAttribute()
    {
        return $this->booking?->customer;
    }

    /**
     * ============== STATUS HELPERS ==============
     */

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isWaitingUserApproval()
    {
        return $this->status === 'waiting_user_approval';
    }

    public function isUserApproved()
    {
        return $this->status === 'user_approved';
    }

    public function isUserRejected()
    {
        return $this->status === 'user_rejected';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    // ✅ Admin action নেওয়া যাবে কিনা
    public function canSetAmount()
    {
        return $this->status === 'pending';
    }

    public function canFinalApprove()
    {
        return $this->status === 'user_approved';
    }

    public function canReject()
    {
        return in_array($this->status, ['pending', 'waiting_user_approval']);
    }

    // ✅ User action নেওয়া যাবে কিনা
    public function canUserApprove()
    {
        return $this->status === 'waiting_user_approval';
    }

    /**
     * ============== SCOPES ==============
     */

    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('ssr_type', $type);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->whereHas('booking', function ($q) use ($customerId) {
            $q->where('customer_id', $customerId);
        });
    }

    public function scopePendingForAdmin($query)
    {
        return $query->whereIn('status', ['pending', 'user_approved']);
    }

    public function scopePendingForUser($query)
    {
        return $query->where('status', 'waiting_user_approval');
    }

    /**
     * ============== ACCESSORS ==============
     */

    // ✅ Status badge color
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'waiting_user_approval' => 'info',
            'user_approved' => 'primary',
            'user_rejected' => 'danger',
            'confirmed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    // ✅ SSR type badge color
    public function getSsrTypeColorAttribute()
    {
        return match($this->ssr_type) {
            'baggage' => 'primary',
            'meal' => 'success',
            'seat' => 'info',
            'insurance' => 'warning',
            'wheelchair' => 'secondary',
            default => 'secondary',
        };
    }

    // ✅ Formatted status
    public function getStatusTextAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    // ✅ Formatted amount
    public function getFormattedAmountAttribute()
    {
        return format_money_main($this->amount);
    }
}
