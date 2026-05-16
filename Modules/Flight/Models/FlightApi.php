<?php

namespace Modules\Flight\Models;

use Illuminate\Database\Eloquent\Model;

class FlightApi extends Model
{
    protected $table = 'flight_apis';

    protected $fillable = [
        'name',
        'provider',
        'api_key',
        'api_secret',
        'api_url',
        'endpoint',
        'status',
        'configuration',
        'description',
        'priority'
    ];

    protected $casts = [
        'configuration' => 'array'
    ];
}
