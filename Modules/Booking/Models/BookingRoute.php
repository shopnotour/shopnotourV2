<?php


    namespace Modules\Booking\Models;


    use App\BaseModel;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class Bookingroute extends BaseModel
    {
        use SoftDeletes;
        protected $slugField = false;
        protected $slugFromField = false;
        protected $table ='bravo_booking_routes';

        protected $fillable = [
            'booking_id',
            'fare_basis',
            'cabin',
            'create_user',
            'created_at',
            'departure_iata_code',
            'departure_at',
            'arrival_iata_code',
            'arrival_at',
            'arrival_terminal',
            'departure_terminal',
            'meta',
            'carrier_code',
            'aircraft_code',
            'duration',
            'flight_number',
            'class'
        ];

        protected $casts = [
            'meta' => 'array'
        ];

        public function booking(){
            return $this->belongsTo(Booking::class,'booking_id');
        }
    }
