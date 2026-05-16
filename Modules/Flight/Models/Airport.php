<?php
//
//
//    namespace Modules\Flight\Models;
//
//
//    use App\BaseModel;
//    use Illuminate\Database\Eloquent\Factories\HasFactory;
//    use Illuminate\Database\Eloquent\SoftDeletes;
//    use Modules\Flight\Factories\AirportFactory;
//    use Modules\Location\Models\Location;
//
//    class Airport extends BaseModel
//    {
//        use HasFactory;
//
//        protected $table = 'bravo_airport';
//
//        protected $fillable=[
//            'name',
//            'code',
//            'location_id',
//            'description',
//            'address',
//            'map_lat',
//            'map_lng',
//            'map_zoom',
//        ];
//
//        protected static function newFactory()
//        {
//            return AirportFactory::new();
//        }
//        public function location(){
//            return $this->belongsTo(Location::class,'location_id');
//        }
//    }


namespace Modules\Flight\Models;


use App\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Flight\Factories\AirportFactory;
use Modules\Location\Models\Location;

class Airport extends BaseModel
{
    use HasFactory;

    protected $table = 'bravo_airport';

    protected $fillable = [
        'name',
        'code',
        'location_id',
        'description',
        'address',
        'map_lat',
        'map_lng',
        'map_zoom',
    ];

    protected static function newFactory()
    {
        return AirportFactory::new();
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * ✅ Get all airports as cached array
     */
    public static function getCachedAirports(): array
    {
        return \Cache::remember('airports_data', now()->addDay(), function () {
            return self::whereNotNull('code') // Only airports with IATA codes
            ->get()
                ->keyBy('code')
                ->map(function ($airport) {
                    return [
                        'id' => $airport->id,
                        'code' => $airport->code,
                        'name' => $airport->name,
                        'address' => $airport->address,
                        'country' => $airport->country,
//                            'location_id' => $airport->location_id,
//                            'description' => $airport->description,
//                            'coordinates' => [
//                                'lat' => $airport->map_lat,
//                                'lng' => $airport->map_lng,
//                            ],
                    ];
                })
                ->toArray();
        });
    }

    /**
     * ✅ Get airport by code
     */
    public static function getByCode(string $code): ?array
    {
        $airports = self::getCachedAirports();
        return $airports[$code] ?? null;
    }

    /**
     * ✅ Clear cache
     */
    public static function clearCache(): void
    {
        \Cache::forget('airports_data');
    }

    /**
     * ✅ Auto-clear cache on model changes
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
