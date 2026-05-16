<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorPageLog extends Model
{
    protected $fillable = [
        'visitor_log_id',
        'session_id',
        'user_id',
        'page_url',
        'page_title',
        'referrer_url',
        'entered_at',
        'left_at',
        'time_spent',
        'scroll_depth',
        'click_count',
        'session_snapshot',
    ];

    protected $casts = [
        'entered_at'       => 'datetime',
        'left_at'          => 'datetime',
        'session_snapshot' => 'array',
    ];

    // ─── Relations ───────────────────────────────────────────────

    public function visitorLog(): BelongsTo
    {
        return $this->belongsTo(VisitorLog::class, 'visitor_log_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(VisitorActivity::class, 'page_log_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Page এ ঢোকার সময় record তৈরি করো
     */
    public static function startTracking(array $data): self
    {
        return static::create([
            'visitor_log_id'   => $data['visitor_log_id'],
            'session_id'       => $data['session_id'],
            'user_id'          => $data['user_id'] ?? null,
            'page_url'         => $data['page_url'],
            'page_title'       => $data['page_title'] ?? null,
            'referrer_url'     => $data['referrer_url'] ?? null,
            'entered_at'       => now(),
            'session_snapshot' => $data['session_snapshot'] ?? null,
        ]);
    }


    public static function cleanOldRecords($days = 30): int
    {
        $cutoff = now()->subDays($days);

        $ids = self::where('created_at', '>=', now()->subDays($days))
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        VisitorActivity::where('page_log_id', $ids)->delete();

        return self::whereIn('id', $ids)->delete();
    }
    /**
     * Page ছাড়ার সময় update করো
     */
    public function finishTracking(int $timeSpent, int $scrollDepth = 0): void
    {
        $this->update([
            'left_at'      => now(),
            'time_spent'   => $timeSpent,
            'scroll_depth' => $scrollDepth,
        ]);
    }

    /**
     * Click count বাড়াও
     */
    public function incrementClicks(): void
    {
        $this->increment('click_count');
    }
}
