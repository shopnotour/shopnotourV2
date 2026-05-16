<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VisitorLog extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'country',
        'country_code',
        'region',
        'city',
        'latitude',
        'longitude',
        'visited_at',
        'last_activity_at',
        'left_at',
        'duration',
        'landing_page',
        'current_page',
        'page_views',
        'is_online',
        'status',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'left_at' => 'datetime',
        'is_online' => 'boolean',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pageLog()
    {
        return $this->hasMany(VisitorPageLog::class, 'visitor_log_id');
    }

    public function activities()
    {
        return $this->hasMany(VisitorActivity::class, 'visitor_log_id');
    }
    /**
     * Scope for online visitors
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', true)
            ->where('last_activity_at', '>=', now()->subMinutes(5));
    }

    /**
     * Scope for guests
     */
    public function scopeGuests($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope for logged in users
     */
    public function scopeLoggedIn($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Get real-time statistics
     */
    public static function getRealTimeStats()
    {
        $onlineThreshold = now()->subMinutes(5);

        return [
            'total_online' => self::where('is_online', true)
                ->where('last_activity_at', '>=', $onlineThreshold)
                ->distinct('session_id')
                ->count(),

            'online_users' => self::where('is_online', true)
                ->where('last_activity_at', '>=', $onlineThreshold)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count(),

            'online_guests' => self::where('is_online', true)
                ->where('last_activity_at', '>=', $onlineThreshold)
                ->whereNull('user_id')
                ->distinct('session_id')
                ->count(),

            'today_total' => self::whereDate('visited_at', today())->count(),

            'today_unique' => self::whereDate('visited_at', today())
                ->distinct('ip_address')
                ->count(),
        ];
    }

    /**
     * Get visitors by location
     */
    public static function getVisitorsByLocation()
    {
        return self::select('country', 'country_code', DB::raw('COUNT(*) as total'))
            ->where('is_online', true)
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->groupBy('country', 'country_code')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Get device statistics
     */
    public static function getDeviceStats()
    {
        return self::select('device_type', DB::raw('COUNT(*) as total'))
            ->where('is_online', true)
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->groupBy('device_type')
            ->get();
    }

    /**
     * Mark visitor as offline
     */
    public function markOffline()
    {
        $this->update([
            'is_online' => false,
            'left_at' => now(),
            'duration' => $this->visited_at->diffInSeconds(now()),
        ]);
    }

    /**
     * Update activity
     */
    public function updateActivity($page = null)
    {
        $data = [
            'last_activity_at' => now(),
            'is_online' => true,
        ];

        if ($page) {
            $data['current_page'] = $page;
            $this->increment('page_views');
        }

        $this->update($data);
    }

    /**
     * Clean old offline records (older than 30 days)
     */
    // VisitorLog.php

    public static function cleanOldRecords($days = 30)
    {
        $cutoff = now()->subDays($days);

//        return $cutoff;
        // VisitorLog ids যেগুলো created_at অনুযায়ী পুরনো
        $ids = self::where('created_at', '>=', now()->subDays($days))
            ->pluck('id');
        if ($ids->isEmpty()) return 0;

        // সঠিক order এ delete করো (child tables আগে)
        VisitorActivity::whereIn('visitor_log_id', $ids)->delete();
        VisitorPageLog::whereIn('visitor_log_id', $ids)->delete();

        return self::whereIn('id', $ids)->delete();
    }

    /**
     * Mark inactive visitors as offline (no activity in last 5 minutes)
     */
    public static function markInactiveAsOffline()
    {
        return self::where('is_online', true)
            ->where('last_activity_at', '<', now()->subMinutes(5))
            ->update([
                'is_online' => false,
                'left_at' => DB::raw('last_activity_at'),
                'duration' => DB::raw('TIMESTAMPDIFF(SECOND, visited_at, last_activity_at)'),
            ]);
    }
}
