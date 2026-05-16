<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $timestamps = false;
    protected $table = 'countries';

    protected $fillable = [
        'capital',
        'name',
        'phone_code',
        'code',
        'code3',
        'passport_min',
        'passport_max',
        'passport_pattern',
        'passport_hint',
        'flag_emoji',
        'is_active',
    ];

    protected $casts = [
        'passport_min' => 'integer',
        'passport_max' => 'integer',
    ];

    // ── Helpers ─────────────────────────────────────────

    // সব country passport rules JSON হিসেবে — JS এ pass করার জন্য
    public static function getPassportRulesJson(): string
    {
        return static::select('code', 'passport_min', 'passport_max', 'passport_pattern', 'passport_hint')
            ->get()
            ->keyBy('code')
            ->map(fn($c) => [
                'min'     => $c->passport_min,
                'max'     => $c->passport_max,
                'pattern' => $c->passport_pattern,
                'hint'    => $c->passport_hint,
            ])
            ->toJson();
    }

    // code দিয়ে একটা country খোঁজা
    public static function findByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))->first();
    }
}
