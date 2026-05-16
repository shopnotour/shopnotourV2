<?php


    namespace Modules\Flight\Models;


    use App\BaseModel;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Modules\Flight\Factories\GdsFactory;

    class Gds extends BaseModel
    {
        use HasFactory;
        use SoftDeletes;

        protected $table ='bravo_gds';
        protected $fillable = ['name','gds_commission','image_id'];

        protected static function newFactory()
        {
            return GdsFactory::new();
        }
    }
