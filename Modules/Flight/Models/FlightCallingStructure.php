<?php


namespace Modules\Flight\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FlightCallingStructure extends Model
{
    protected $table = 'flight_calling_structures';

    protected $fillable = [
        'departure_code',
        'arrival_code',
        'airline_codes',
        'priority',
        'status',
        'gds',
        'notes',
    ];

    protected $casts = [
        'airline_codes' => 'array',
    ];

    // ────────────────────────────────────────────────────────────────
    //  Relations
    // ────────────────────────────────────────────────────────────────

    public function airlines()
    {
        if (empty($this->airline_codes)) {
            return collect([]);
        }
        return Airline::whereIn('designator', $this->airline_codes)->get();
    }

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_code', 'code');
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_code', 'code');
    }

    // ────────────────────────────────────────────────────────────────
    //  Core Lookup Methods
    // ────────────────────────────────────────────────────────────────

    /**
     * Route এর জন্য best matching config খোঁজো (GDS ছাড়া)।
     * Backward compat এর জন্য রাখা।
     *
     * Precedence:
     *   1. Exact       : DAC → DXB
     *   2. Dep wildcard: DAC → *
     *   3. Arr wildcard: *   → DXB
     *   4. Global      : *   → *
     */
    public static function getConfigForRoute(string $from, string $to): ?self
    {
        $cacheKey = "flight_calling_config_{$from}_{$to}";

        return Cache::remember($cacheKey, 3600, function () use ($from, $to) {
            return self::where('status', 'active')
                ->where(function ($q) use ($from, $to) {
                    $q->where(function ($q2) use ($from, $to) {
                        $q2->where('departure_code', $from)->where('arrival_code', $to);
                    })
                        ->orWhere(function ($q2) use ($from) {
                            $q2->where('departure_code', $from)->whereNull('arrival_code');
                        })
                        ->orWhere(function ($q2) use ($to) {
                            $q2->whereNull('departure_code')->where('arrival_code', $to);
                        })
                        ->orWhere(function ($q2) {
                            $q2->whereNull('departure_code')->whereNull('arrival_code');
                        });
                })
                ->orderBy('priority', 'asc')
                ->first();
        });
    }

    /**
     * Route + specific GDS/provider এর জন্য config খোঁজো।
     *
     * FlightSearchService এর loop এ per-API airline filter এর জন্য use হয়।
     * Returns null → এই GDS এর জন্য এই route এ কোনো config নেই।
     *
     * Precedence:
     *   1. Exact       : DAC → DXB  + gds = sabre
     *   2. Dep wildcard: DAC → *    + gds = sabre
     *   3. Arr wildcard: *   → DXB  + gds = sabre
     *   4. Global      : *   → *    + gds = sabre
     */
    public static function getConfigForRouteAndGds(string $from, string $to, string $provider): ?self
    {
        $cacheKey = "flight_calling_config_{$from}_{$to}_{$provider}";

        return Cache::remember($cacheKey, 3600, function () use ($from, $to, $provider) {
            return self::where('status', 'active')
                ->where('gds', $provider)
                ->where(function ($q) use ($from, $to) {
                    $q->where(function ($q2) use ($from, $to) {
                        $q2->where('departure_code', $from)->where('arrival_code', $to);
                    })
                        ->orWhere(function ($q2) use ($from) {
                            $q2->where('departure_code', $from)->whereNull('arrival_code');
                        })
                        ->orWhere(function ($q2) use ($to) {
                            $q2->whereNull('departure_code')->where('arrival_code', $to);
                        })
                        ->orWhere(function ($q2) {
                            $q2->whereNull('departure_code')->whereNull('arrival_code');
                        });
                })
                ->orderBy('priority', 'asc')
                ->first();
        });
    }

    /**
     * Route এর জন্য allowed airline codes।
     * Empty array = all airlines allowed।
     */
    public static function getAirlinesForRoute(string $from, string $to): array
    {
        $config = self::getConfigForRoute($from, $to);
        return $config?->airline_codes ?? [];
    }

    // ────────────────────────────────────────────────────────────────
    //  Helper Methods
    // ────────────────────────────────────────────────────────────────

    public function hasAirline(string $airlineCode): bool
    {
        return in_array($airlineCode, $this->airline_codes ?? []);
    }

    public static function isAirlineAllowed(string $from, string $to, string $airlineCode): bool
    {
        $allowed = self::getAirlinesForRoute($from, $to);
        return empty($allowed) || in_array($airlineCode, $allowed);
    }

    public static function filterFlights(array $flights, string $from, string $to): array
    {
        $allowed = self::getAirlinesForRoute($from, $to);
        if (empty($allowed)) return $flights;

        return collect($flights)->filter(function ($flight) use ($allowed) {
            $carrier = $flight['validating_carrier'] ?? $flight['airline_code'] ?? null;
            return $carrier && in_array($carrier, $allowed);
        })->values()->all();
    }

    // ────────────────────────────────────────────────────────────────
    //  Cache Management
    // ────────────────────────────────────────────────────────────────

    public static function clearRouteCache(string $from, string $to): void
    {
        Cache::forget("flight_calling_config_{$from}_{$to}");
    }

    public static function clearAllCache(): void
    {
        self::all()->each(function ($route) {
            $from = $route->departure_code ?? '*';
            $to = $route->arrival_code ?? '*';
            Cache::forget("flight_calling_config_{$from}_{$to}");
            if ($route->gds) {
                Cache::forget("flight_calling_config_{$from}_{$to}_{$route->gds}");
            }
        });
    }

    // ────────────────────────────────────────────────────────────────
    //  Scopes
    // ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForRoute($query, string $from, string $to)
    {
        return $query->where('departure_code', $from)
            ->where('arrival_code', $to);
    }

    // ────────────────────────────────────────────────────────────────
    //  Accessors
    // ────────────────────────────────────────────────────────────────

    public function getAirlineNamesAttribute(): string
    {
        return $this->airlines()->pluck('name')->join(', ');
    }

    public function getRouteDisplayAttribute(): string
    {
        return ($this->departure_code ?? '*') . ' → ' . ($this->arrival_code ?? '*');
    }

    // ────────────────────────────────────────────────────────────────
    //  Boot
    // ────────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->departure_code && $model->arrival_code) {
                self::clearRouteCache($model->departure_code, $model->arrival_code);
                if ($model->gds) {
                    Cache::forget("flight_calling_config_{$model->departure_code}_{$model->arrival_code}_{$model->gds}");
                }
            } else {
                // Wildcard → সব clear
                self::clearAllCache();
            }
        });

        static::deleted(function ($model) {
            self::clearAllCache();
        });
    }
}
