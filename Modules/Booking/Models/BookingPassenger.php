<?php


    namespace Modules\Booking\Models;


    use App\BaseModel;
    use App\User;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Modules\Media\Models\MediaFile;


    class BookingPassenger extends BaseModel
    {
        use SoftDeletes;
        protected $slugField = false;
        protected $slugFromField = false;
        protected $table ='bravo_booking_passengers';

        protected $fillable = [
            'booking_id',
            'included_checked_bags_unit',
            'included_checked_bags',
            'title',
            'fare_basis',
            'fare_option',
            'create_user',
            'created_at',
            'seat_type',
            'email',
            'first_name',
            'last_name',
            'phone',
            'dob',
            'price',
            'id_card',
            'gender',
            'passport_number',
            'passport_expiry_date',
            'meta',
            'update_user',
            'traveler_type',
            'zip_code',
            'object_model',
            'cabin',
            'class',
            'total',
            'base',
            'tax',
            'gross_fare',
            'ait_amount',
            'service_charge',
            'user_discount',
            'user_seg_discount',
            'user_payable',
            'own_discount',
            'own_seg_discount',
            'commission',
            'own_cost',
            'profit',
            'country',
            'passenger_type_code',
            'ticket_number',
        'pnr',
        'ticket_amount',
        'currency',
        'ticket_issued_at',
        'status',
        'document_type',
        'issuing_location',
            'city',
            'address',
            'passport_media_id',
            'visa_media_id',
        ];

        protected $casts = [
            'meta' => 'array'
        ];

        public function booking(){
            return $this->belongsTo(Booking::class,'booking_id');
        }

        public function passportMedia()
        {
            return $this->belongsTo(MediaFile::class, 'passport_media_id');
        }

        public function visaMedia()
        {
            return $this->belongsTo(MediaFile::class, 'visa_media_id');
        }
        public function updatedByUser()
        {
            return $this->belongsTo(User::class, 'update_user');
        }
    }
