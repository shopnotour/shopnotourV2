<?php


    namespace Modules\Flight\Models;


    use App\BaseModel;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Modules\Booking\Models\Booking;

    class BookingPassengers extends BaseModel
    {
        use SoftDeletes;
        protected $slugField = false;
        protected $slugFromField = false;
        protected $table ='bravo_booking_passengers';
        protected $fillable = [
            'flight_id',
            'flight_seat_id',
            'booking_id',
            'seat_type',
            'email',
            'first_name',
            'last_name',
            'phone',
            'dob',
            'price',
            'id_card',
            'traveler_type',
            'fare_option',
            'currency',
            'total',
            'base',
            'included_checked_bags',
            'included_checked_bags_unit',
            'cabin',
            'fare_basis',
            'class',
            'country',
            'zip_code',
            'gender',
            'passport_number',
            'passport_expiry_date',
            'meta'
        ];
        
        protected $casts = [
            'meta' => 'array'
        ];
        
        public function booking(){
            return $this->belongsTo(Booking::class,'booking_id');
        }
    }
