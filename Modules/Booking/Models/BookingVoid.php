<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingVoid extends Model
{
    protected $table = 'booking_voids';

    protected $fillable = [
        'booking_id',
        'pnr',
        'void_charges',
        'status',
        'reason',
        'airline_response',
        'updated_by',
        'voided_by',
        'created_at',
        'voided_at'
    ];

    protected $casts = [
        'voided_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
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
