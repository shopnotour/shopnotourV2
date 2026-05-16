<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchSession extends Model
{
    protected $table = 'search_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(array $data): static
    {
        return static::create([
            'user_id'    => auth()->id() ?? null,
            'session_id' => session()->getId(),
            'data'       => $data,
        ]);
    }
}
