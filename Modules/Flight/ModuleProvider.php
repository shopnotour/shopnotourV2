<?php
namespace Modules\Flight;
use Modules\ModuleServiceProvider;
use Modules\Flight\Models\Flight;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(){

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        PermissionHelper::add([
            'flight_view',
            'flight_manage',
            'flight_user',
            'flight_All',
            'flight_create',
            'flight_Airline',
            'flight_Airport',
            'flight_seat',
            'flight_update',
            'flight_delete',
            'flight_manage_others',
            'flight_manage_attributes',
//            'flight_commission',
            'flight_charge',
            'flight_api_settings',
            'flight_api_manage_settings',
            'flight_calling_settings',
            'flight_calling_status',
            'flight_pnr_search',
//            'flight_discount_status',

            'commission_view',
            'commission_create',
            'commission_edit',
            'commission_delete',
            'commission_status',
            'commission_bulk_copy',
            'commission_bulk_delete',
            'commission_bulk_status',
            'commission_bulk_dates',
            'commission_bulk_source',

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
        if(!Flight::isEnable()) return [];
        return [
            'flight'=>[
                "position"=>41,
                'url'        => '#',
                'title'      => __('Flight'),
                'icon'       => 'ion ion-md-airplane',
                'permission' => 'flight_view',
                'children'   => [
                    'add'=>[
                        'url'        => route('flight.admin.index'),
                        'title'      => __('All Flights'),
                        'permission' => 'flight_manage',
                    ],
                    'create'=>[
                        'url'        => route('flight.admin.create'),
                        'title'      => __('Add new Flight'),
                        'permission' => 'flight_create',
                    ],
                    'airline'=>[
                        'url'        => route('flight.admin.airline.index'),
                        'title'      => __('flight_Airline'),
                        'permission' => 'flight_Airline',
                    ],
                    'airport'=>[
                        'url'        => route('flight.admin.airport.index'),
                        'title'      => __('flight_Airport'),
                        'permission' => 'flight_Airport',
                    ],
                    'seat_type'=>[
                        'url'        => route('flight.admin.seat_type.index'),
                        'title'      => __('flight_seat'),
                        'permission' => 'flight_seat',
                    ],
                    'attribute'=>[
                        'url'        => route('flight.admin.attribute.index'),
                        'title'      => __('Attributes'),
                        'permission' => 'flight_manage_attributes',
                    ],
                    'commission'=>[
                        'url'        => route('flight.admin.discount.index'),
                        'title'      => __('Commission'),
                        'permission' => 'commission_view',
                    ],
//                    'Charges'=>[
//                        'url'        => route('flight.admin.flight_charges.index'),
//                        'title'      => __('Charges'),
//                        'permission' => 'flight_charge',
//                    ],
                    'api'=>[
                        'url'        => route('flight.admin.api.index'),
                        'title'      => __('Api Create'),
                        'permission' => 'flight_api_settings',
                    ],
                    'api_manage'=>[
                        'url'        => route('flight.admin.api.manage'),
                        'title'      => __('Api Manage'),
                        'permission' => 'flight_api_manage_settings',
                    ],
                    'calling'=>[
                        'url'        => route('flight.admin.calling.index'),
                        'title'      => __('Calling'),
                        'permission' => 'flight_calling_settings',
                    ],
//                    'flight_charge'=>[
//                        'url'        => route('flight.admin.flight_charges.index'),
//                        'title'      => __('Flight Charges'),
//                        'permission' => 'flight_charge',
//                    ],
                    'prn_search'=>[
                        'url'        => route('booking.pnr.search.page'),
                        'title'      => __('Pnr Search'),
                        'permission' => 'flight_pnr_search',
                    ],
                ]
            ]
        ];
    }

    public static function getBookableServices()
    {
        if(!Flight::isEnable()) return [];
        return [
            'flight'=>Flight::class
        ];
    }

    public static function getMenuBuilderTypes()
    {
        return [];
    }

    public static function getUserMenu()
    {
        $res = [];
        if (Flight::isEnable()) {
            $res['flight'] = [
                'url'        => route('flight.vendor.index'),
                'title'      => __("Manage Flight"),
                'icon'       => Flight::getServiceIconFeatured(),
                'position'   => 60,
                'permission' => 'flight_user',
                'children'   => [
                    [
                        'url'   => route('flight.vendor.index'),
                        'title' => __("All Flights"),
                    ],
                    [
                        'url'        => route('flight.vendor.create'),
                        'title'      => __("Add Flights"),
                        'permission' => 'flight_create',
                    ],
                ]
            ];
        }
        return $res;
    }

    public static function getTemplateBlocks(){
        if(!Flight::isEnable()) return [];
        return [
            'form_search_flight'=>"\\Modules\\Flight\\Blocks\\FormSearchFlight",
        ];
    }
}
