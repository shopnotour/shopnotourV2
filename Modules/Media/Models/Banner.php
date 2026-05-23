<?php

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Media\Models\MediaFile;

class Banner extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'title',
        'image_id',
        'video',
        'type',
        'link',
        'order',
        'status',
        'create_user',
        'update_user'
    ];

    public static function getActive()
    {
        return self::where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();
    }

    public function getImageUrl()
    {
        return get_file_url($this->image_id, 'full');
    }
    
    public function getVideoUrl()
    {
        if ($this->video && file_exists(public_path('uploads/video/' . $this->video))) {
            return asset('uploads/video/' . $this->video);
        }
        return null;
    }
}