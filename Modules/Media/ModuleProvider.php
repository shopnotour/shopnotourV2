<?php
namespace Modules\Media;

use Modules\ModuleServiceProvider;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        PermissionHelper::add([
            'media_upload',
            'banner',
            'cache_clear'

        ]);
    }



    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouterServiceProvider::class);
    }

    public static function getAdminMenu()
    {
        return [
            'media'=>[
                'position'=>56,
                'title'=>__("Media"),
                'icon'=>"fa fa-picture-o",
                "url"=>route('media.admin.index'),
                'permission' => 'media_upload',
                "group"=>"content"
            ],
            'media_banner'=>[
                'position'=>57,
                'title'=>__("Banner"),
                'icon'=>"fa fa-picture-o",
                "url"=>route('banner.admin.index'),
                'permission' => 'banner',
                "group"=>"content"
            ],
            'Notice'=>[
                "position"=>58,
                'url'        => route('announcements.index'),
                'title'      => __('Notice Scroll'),
                'icon'       => 'ion ion-md-notifications',
                'permission' => 'notice_scroll',
            ],
//            'cache_clear'=>[
//                'position'=>58,
//                'title'=>__("Cache clear"),
//                'icon'=>"fa fa-picture-o",
//                "url"=>route('banner.admin.clearCache'),
//                'permission' => 'cache_clear',
//                "group"=>"content"
//            ]
        ];
    }
}
