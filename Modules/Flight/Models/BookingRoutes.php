<?php


    namespace Modules\Flight\Models;


    use App\BaseModel;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Modules\Booking\Models\Booking;

    class BookingRoutes extends BaseModel
    {
        use SoftDeletes;
        protected $slugField = false;
        protected $slugFromField = false;
        protected $table ='bravo_booking_routes';
        protected $fillable = [
            'booking_id',
            'departure_iata_code',
            'departure_at',
            'arrival_iata_code',
            'arrival_at',
            'carrier_code',
            'aircraft_code',
            'duration',
            'flight_number',
            'class',
            'meta'
        ];

        protected $casts = [
            'meta' => 'array'
        ];

        public function booking(){
            return $this->belongsTo(Booking::class,'booking_id');
        }
    }
