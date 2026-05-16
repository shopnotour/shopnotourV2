<?php
namespace Modules\wallet;
use Modules\Core\Helpers\SitemapHelper;
use Modules\Hotel\RouterServiceProvider;
use Modules\ModuleServiceProvider;
use Modules\Hotel\Models\Hotel;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(SitemapHelper $sitemapHelper){

//        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
//
//        if(is_installed() and Hotel::isEnable()){
//
//            $sitemapHelper->add("hotel",[app()->make(Hotel::class),'getForSitemap']);
//        }
        PermissionHelper::add([
            // Hotel
            'wallet_view',
            'wallet_report',
            'wallet_user',
            'wallet_create',
            'wallet_update',
            'wallet_delete',
            'wallet_manage_others',
            'wallet_manage_attributes',
        ]);
    }
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->register(RouterServiceProvider::class);
    }

    public static function getAdminMenu()
    {
//        if(!Hotel::isEnable()) return [];
        return [
            'hotel'=>[
                "position"=>100,
                'url'        => route('user.admin.wallet.transactions'),
                'title'      => __('Wallet'),
                'icon'       => 'fa fa-building-o',
                'permission' => 'wallet_view',
                'children'   => [
                    'add'=>[
                        'url'        => route('user.admin.wallet.transactions'),
                        'title'      => __('All TopUp'),
                        'permission' => 'wallet_view',
                    ],
                    'view'=>[
                        'url'        => route('report.admin.statistic.wallet_statistic'),
                        'title'      => __('TopUp Report'),
                        'permission' => 'wallet_report',
                    ],
                ]
            ]
        ];
    }

    public static function getBookableServices()
    {
//        if(!Hotel::isEnable()) return [];
        return [
//            'hotel'=>Hotel::class
        ];
    }

    public static function getMenuBuilderTypes()
    {
//        if(!Hotel::isEnable()) return [];
        return [
//            'hotel'=>[
//                'class' => Hotel::class,
//                'name'  => __("Hotel"),
//                'items' => Hotel::searchForMenu(),
//                'position'=>41
//            ]
        ];
    }


    public static function getUserMenu()
    {
//        return [
//            'wallet'=>[
//                "position"=>30,
//                'url'        => '#',
//                'title'      => __('Wallet'),
//                'permission' => 'wallet_user',
//                'icon'       => 'fa fa-wallet',
//                'children'   => [
//                    [
//                        'url'        => route('user.wallet.addcredit'),
//                        'title'      => __('TopUp'),
//                        'permission' => 'wallet_report',
//                    ],[
//                        'url'        => route('user.wallet.creditTransaction'),
//                        'title'      => __('TopUp Transactions'),
//                        'permission' => 'wallet_report',
//                    ]
//                ]
//            ]
//        ];

    }

    public static function getTemplateBlocks(){
//        if(!Hotel::isEnable()) return [];
        return [
//            'form_search_hotel'=>"\\Modules\\Hotel\\Blocks\\FormSearchHotel",
//            'list_hotel'=>"\\Modules\\Hotel\\Blocks\\ListHotel",
        ];
    }
}
