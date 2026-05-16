<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingCancel extends Model
{
    protected $table = 'booking_cancel';  // এই line add করুন
    protected $fillable = [
        'booking_id', 'user_id', 'cancellation_type', 'cancellation_reason',
        'status', 'reviewed_by', 'admin_note', 'reviewed_at', 'created_at'
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function booking() { return $this->belongsTo(Booking::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

}
