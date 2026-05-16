<?php
//
//
//    namespace Modules\Flight\Models;
//
//
//    use App\BaseModel;
//    use Illuminate\Database\Eloquent\Factories\HasFactory;
//    use Illuminate\Database\Eloquent\SoftDeletes;
//    use Modules\Flight\Factories\AirLineFactory;
//    use Modules\Media\Models\MediaFile;
//
//    class Airline extends BaseModel
//    {
//        use HasFactory;
//        use SoftDeletes;
//
//        protected $table ='bravo_airline';
//        protected $fillable = ['name','designator','airline_commission','image_id'];
//
//        protected static function newFactory()
//        {
//            return AirLineFactory::new();
//        }
//
//        public function airlineImage(){
//            return $this->belongsTo(MediaFile::class , 'image_id');
//        }
//    }


namespace Modules\Flight\Models;


use App\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Flight\Factories\AirLineFactory;
use Modules\Media\Models\MediaFile;

class Airline extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'bravo_airline';
    protected $fillable = ['name', 'designator', 'airline_commission', 'image_id'];

    protected static function newFactory()
    {
        return AirLineFactory::new();
    }

    public function airlineImage()
    {
        return $this->belongsTo(MediaFile::class, 'image_id');
    }

    /**
     * ✅ UPDATED: Get all airlines as cached array with eager loading
     */
    public static function getCachedAirlines(): array
    {
        return \Cache::remember('airlines_data', now()->addDay(), function () {
            return self::with('airlineImage') // ✅ Eager load relationship
            ->get()
                ->keyBy('designator')
                ->map(function ($airline) {
                    return [
                        'id' => $airline->id,
                        'code' => $airline->designator,
                        'name' => $airline->name,
                        'image_id' => $airline->image_id,

                        // ✅ Multiple image URLs for different sizes
                        'image_url' => $airline->image_id ? get_file_url($airline->image_id, 'full') : null,
                        'image_thumb' => $airline->image_id ? get_file_url($airline->image_id, 'thumb') : null,
                        'image_medium' => $airline->image_id ? get_file_url($airline->image_id, 'medium') : null,
                        'image_large' => $airline->image_id ? get_file_url($airline->image_id, 'large') : null,

                        // ✅ Direct MediaFile object (if needed)
                        'image' => $airline->airlineImage ? [
                            'id' => $airline->airlineImage->id,
                            'file_name' => $airline->airlineImage->file_name,
                            'file_path' => $airline->airlineImage->file_path,
                            'file_type' => $airline->airlineImage->file_type,
                            'driver' => $airline->airlineImage->driver,
                            'view_url' => $airline->airlineImage->view_url,
                        ] : null,

                        'commission' => (float)($airline->airline_commission ?? 0),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get airline by code
     */
    public static function getByCode(string $code): ?array
    {
        $airlines = self::getCachedAirlines();
        return $airlines[$code] ?? null;
    }

    /**
     * ✅ NEW: Clear cache when airline is updated
     */
    public static function clearCache(): void
    {
        \Cache::forget('airlines_data');
    }

    /**
     * ✅ NEW: Boot method to auto-clear cache
     */
    public static function boot()
    {
        parent::boot();

        // Clear cache on create/update/delete
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
