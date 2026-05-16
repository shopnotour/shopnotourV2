<?php
namespace Modules\User\Models\Wallet;

use App\BaseModel;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Booking\Models\Payment;
use Modules\Media\Models\MediaFile;

class Transaction extends BaseModel
{
    use SoftDeletes;

    protected $table = 'credit_transactions';

    protected $fillable = [
        'booking_id',
        'user_id',
        'ref_id',
        'type',
        'transaction_type',
        'amount',
        'attachment_id',
        'status',
        'reference',
        'remarks',
        'meta',
        'create_user',
        'update_user',
        'created_at',
        'deposit_date',
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function payment(){
        return $this->belongsTo(Payment::class,'payment_id')->withDefault();
    }

    public function author(){
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'create_user');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'update_user');
    }

    public function getStatusNameAttribute(){
        if ($this->status === 'confirmed') {
            return __("Confirmed");
        }
        if(!$this->payment_id || !$this->payment){
            return __("Pending");
        }
        return $this->payment->status_name;
    }
    public function getStatusClassAttribute(){
        if ($this->status === 'confirmed') {
            return 'success';
        }
        if($this->payment_id && $this->payment){
            switch ($this->payment->status){
                case "processing":
                    return 'warning';
                    break;
            }
        }
        return 'warning';
    }

    public function confirm()
    {
        if ($this->author and !$this->status != 'confirmed') {
            $this->author->credit_balance += $this->amount;
            $this->author->save();
        }
        $this->status = 'confirmed';
        $this->save();
    }
}
