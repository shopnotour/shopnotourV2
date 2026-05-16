<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorActivity extends Model
{
    protected $fillable = [
        'visitor_log_id',
        'page_log_id',
        'session_id',
        'user_id',
        'page_url',
        'activity_type',
        'element_id',
        'element_text',
        'activity_data',
        'session_data',
        'occurred_at',
    ];

    protected $casts = [
        'activity_data' => 'array',
        'session_data'  => 'array',
        'occurred_at'   => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────────

    public function visitorLog(): BelongsTo
    {
        return $this->belongsTo(VisitorLog::class, 'visitor_log_id');
    }

    public function pageLog(): BelongsTo
    {
        return $this->belongsTo(VisitorPageLog::class, 'page_log_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Activity save করো
     */
    public static function log(array $data): self
    {
        return static::create([
            'visitor_log_id' => $data['visitor_log_id'],
            'page_log_id'    => $data['page_log_id'] ?? null,
            'session_id'     => $data['session_id'],
            'user_id'        => $data['user_id'] ?? null,
            'page_url'       => $data['page_url'],
            'activity_type'  => $data['activity_type'],
            'element_id'     => $data['element_id'] ?? null,
            'element_text'   => $data['element_text'] ?? null,
            'activity_data'  => $data['activity_data'] ?? null,
            'session_data'   => $data['session_data'] ?? null,
            'occurred_at'    => now(),
        ]);
    }

    /**
     * Flight search গুলো আলাদা করে দেখো
     */
    public static function getFlightSearches(string $sessionId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('session_id', $sessionId)
            ->where('activity_type', 'flight_search')
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }
}
