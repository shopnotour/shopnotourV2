<?php

namespace Modules\Booking\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'user_message',
        'status',
        'admin_reply',
        'replied_by',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSING = 'processing';

    // ✅ Available statuses
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PROCESSING => 'Processing',
        ];
    }

    // ✅ Get status badge color
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_PROCESSING => 'info',
            default => 'secondary',
        };
    }

    // ✅ Get status label
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Unknown';
    }

    // ✅ Check if replied
    public function isReplied(): bool
    {
        return !empty($this->admin_reply) && !is_null($this->replied_at);
    }

    // ✅ Check if pending
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // ✅ Check if approved
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    // ✅ Check if rejected
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * =====================================
     * RELATIONSHIPS
     * =====================================
     */

    // ✅ Booking relationship
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // ✅ User who created the request
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ✅ Admin who replied
    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * =====================================
     * SCOPES
     * =====================================
     */

    // ✅ Scope: Only pending requests
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // ✅ Scope: Only approved requests
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    // ✅ Scope: Only rejected requests
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    // ✅ Scope: Requests with replies
    public function scopeReplied($query)
    {
        return $query->whereNotNull('admin_reply')->whereNotNull('replied_at');
    }

    // ✅ Scope: Latest first
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
