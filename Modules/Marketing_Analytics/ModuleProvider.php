<?php
namespace Modules\Marketing_Analytics;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Helpers\SitemapHelper;
use Modules\ModuleServiceProvider;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot(SitemapHelper $sitemapHelper)
    {
//        return 'sarowar';
//        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
//
//        if(is_installed() and Tour::isEnable()){
//            $sitemapHelper->add("tour",[app()->make(Tour::class),'getForSitemap']);
//        }

        PermissionHelper::add([
            // Tour
            'report_marketing',

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

    public static function getBookableServices()
    {
//        if(!Tour::isEnable()) return [];
        return [
//            'tour' => Tour::class,
        ];
    }

    public static function getAdminMenu()
    {
        $res = [];
//        if(Tour::isEnable()){

            $res['marketing_report'] = [
                "position"=>400,
                'url'        => route('market.admin.index'),
                'title'      => __("marketing_report"),
                'icon'       => 'icon ion-md-umbrella',
                'permission' => 'report_marketing',
//                'children'   => [
//                    'tour_view'=>[
//                        'url'        => route('tour.admin.index'),
//                        'title'      => __('All Tours'),
//                        'permission' => 'tour_view',
//                    ],
//                    'tour_create'=>[
//                        'url'        => route('tour.admin.create'),
//                        'title'      => __("Add Tour"),
//                        'permission' => 'tour_create',
//                    ],
//                    'tour_category'=>[
//                        'url'        => route('tour.admin.category.index'),
//                        'title'      => __('Categories'),
//                        'permission' => 'tour_manage_others',
//                    ],
//                    'tour_attribute'=>[
//                        'url'        => route('tour.admin.attribute.index'),
//                        'title'      => __('Attributes'),
//                        'permission' => 'tour_manage_attributes',
//                    ],
//                    'tour_availability'=>[
//                        'url'        => route('tour.admin.availability.index'),
//                        'title'      => __('Availability'),
//                        'permission' => 'tour_create',
//                    ],
//                    'tour_booking'=>[
//                        'url'        => route('tour.admin.booking.index'),
//                        'title'      => __('Booking Calendar'),
//                        'permission' => 'tour_create',
//                    ],
//                    'recovery'=>[
//                        'url'        => route('tour.admin.recovery'),
//                        'title'      => __('Recovery'),
//                        'permission' => 'tour_view',
//                    ],
//                ]
            ];
//        }
        return $res;
    }


    public static function getUserMenu()
    {
//        $res = [];
//        if(Tour::isEnable()){
//            $res['tour'] = [
//                'url'   => route('tour.vendor.index'),
//                'title'      => __("Manage Tour"),
//                'icon'       => Tour::getServiceIconFeatured(),
//                'permission' => 'tour_view',
//                'position'   => 40,
//                'children'   => [
//                    [
//                        'url'   => route('tour.vendor.index'),
//                        'title' => __("All Tours"),
//                    ],
//                    [
//                        'url'        => route('tour.vendor.create'),
//                        'title'      => __("Add Tour"),
//                        'permission' => 'tour_create',
//                    ],
//                    [
//                        'url'        => route('tour.vendor.availability.index'),
//                        'title'      => __("Availability"),
//                        'permission' => 'tour_create',
//                    ],
//                    [
//                        'url'   => route('tour.vendor.recovery'),
//                        'title'      => __("Recovery"),
//                        'permission' => 'tour_create',
//                    ],
//                ]
//            ];
//        }
        return [

        ];
    }

    public static function getMenuBuilderTypes()
    {


        return [
        ];
    }

    public static function getTemplateBlocks(){
//        if(!Tour::isEnable()) return [];

        return [
//            'list_tours'=>"\\Modules\\Tour\\Blocks\\ListTours",
//            'form_search_tour'=>"\\Modules\\Tour\\Blocks\\FormSearchTour",
//            'box_category_tour'=>"\\Modules\\Tour\\Blocks\\BoxCategoryTour",
        ];
    }
}
