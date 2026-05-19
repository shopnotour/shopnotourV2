<?php
namespace Modules\Booking;

use Illuminate\Support\Facades\Event;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Events\SetPaidAmountEvent;
use Modules\Booking\Listeners\BookingCreatedListen;
use Modules\Booking\Listeners\BookingUpdateListen;
use Modules\Booking\Listeners\SetPaidAmountListen;
use Modules\Core\Helpers\SitemapHelper;
use Modules\Flight\Models\Flight;
use Modules\ModuleServiceProvider;
use Modules\News\Models\News;
use Modules\Page\Models\Page;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot(SitemapHelper $sitemapHelper)
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        Event::listen(BookingCreatedEvent::class,BookingCreatedListen::class);
        Event::listen(BookingUpdatedEvent::class,BookingUpdateListen::class);
        Event::listen(SetPaidAmountEvent::class,SetPaidAmountListen::class);

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        PermissionHelper::add([
            'booking_manage',
            'booking_user_list_view',
            'booking_view',
//            'booking_requests',
            'booking_users_requests',
            'booking_void',
            'booking_refund',
            'booking_reissue',
            'booking_ssr',
            'booking_cancel',
            'booking_print_ticket',
            'booking_passengers_edit',
            'booking_cancel_ticket',
            'booking_setpaid',
            'booking_issue_ticket',
            'booking_invoice',
            'booking_assin_ticket',
            'booking_pnr_edit',
            'booking_details',
            'booking_pnr_check',
            'booking_edit',
            'booking_duplicate',
            'booking_list',

            'booking_void_request',
            'booking_refund_request',
            'booking_reissue_request',
            'booking_ssr_request',
            'booking_cancel_request',

            'report_account',
            'visitor_view',
            'visitor_ip',
            'visitor_country',
            'notice_scroll',
            'report_transactions',

            'country_view',

            'wallet_view',
            'account_approved_permission',
            'wallet_report',
            'wallet_user',
            'wallet_create',
            'wallet_update',
            'wallet_delete',
            'wallet_manage_others',
            'wallet_manage_attributes',

            'transaction_delete',
            'transaction_edit_amount',
            'transaction_edit_remarks',
            'transaction_print_receipt',
            'transaction_approved',
            'transaction_add',

            'user_booking_view',
            'user_transactions_view',
            'user_tickets_view',
            'user_password_change',

            'user_validation',  //modify by rahat
            'user_vendor_approved',  //modify by rahat
            'user_all',  //modify by rahat
            'user_role',  //modify by rahat

            'allbooking_bookings',
            'allbooking_issue_request',
            'allbooking_booked',
            'allbooking_cancelled',
            'allbooking_issued',
            'allbooking_pending',
            'allbooking_paid',
            'allbooking_ticketed',
            'allbooking_failed',
            'allbooking_refunded',

            'bonus_manage',
            'bonus_transactions',
            'bonus_give',
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
        $this->app->register(EventServiceProvider::class);
    }

    public static function getAdminMenu()
    {
//        if(!Flight::isEnable()) return [];
        return [

            'Account'=>[
                "position"=>39,
                'url'        => route('transactions.index'),
                'title'      => __('Booking Transactions'),
                'icon'       => 'ion ion-md-person',
                'permission' => 'report_account',
            ],
            'itaAccount'=>[
                "position"=>39,
                'url'        => route('admin.itabooking-report.index'),
                'title'      => __('IATA'),
                'icon'       => 'ion ion-md-person',
                'permission' => 'report_account',
            ],
            'Transaction'=>[
                "position"=>40,
                'url'        => route('user.admin.wallet.transactions'),
                'title'      => __('Wallet Transactions'),
                'icon'       => 'ion ion-md-cash',
                'permission' => 'report_transactions',
            ],
            'Wallet'=>[
                "position"=>40,
                'url'        => route('report.admin.statistic.wallet_statistic'),
                'title'      => __('TopUp Report'),
                'icon'       => 'ion ion-md-cash',
                'permission' => 'wallet_report',
            ],
            'Visitor'=>[
                "position"=>41,
                'url'        => route('visitor.index'),
                'title'      => __('Visitors'),
                'icon'       => 'ion ion-md-people',
                'permission' => 'visitor_view',
            ],
            'Marketing'=>[
                "position"=>41,
                'url'        => route('admin.marketing.dashboard'),
                'title'      => __('Monitoring'),
                'icon'       => 'ion ion-md-people',
                'permission' => 'visitor_view',
            ],
            'Users'=>[
                "position"=>41,
                'url'        => route('admin.users.index'),
                'title'      => __('Users'),
                'icon'       => 'ion ion-md-people',
                'permission' => 'booking_user_list_view',
            ],
            'booking'=>[
                "position"=>42,
                'url'        => '#',
                'title'      => __('Booking'),
                'icon'       => 'ion ion-ios-airplane',
                'permission' => 'booking_view',
                'children'   => [
                    'booking'=>[
                        'url'        => route('bookings.index'),
                        'title'      => __('Booking'),
                        'permission' => 'booking_manage',
                    ],
                    'void'=>[
                        'url'        => route('voids.index'),
                        'title'      => __('Void'),
                        'permission' => 'booking_void_request',
                    ],
                    'refund'=>[
                        'url'        => route('refunds.index'),
                        'title'      => __('Refund'),
                        'permission' => 'booking_refund_request',
                    ],
                    'reissue'=>[
                        'url'        => route('reissues.index'),
                        'title'      => __('Reissue'),
                        'permission' => 'booking_reissue_request',
                    ],
                    'ssr'=>[
                        'url'        => route('admin.ssrs.index'),
                        'title'      => __('Add SSR'),
                        'permission' => 'booking_ssr_request',
                    ],
                    'cancel'=>[
                        'url'        => route('cancellations.index'),
                        'title'      => __('Cancel'),
                        'permission' => 'booking_cancel_request',
                    ],
                ]
            ],
            'allBooking'=>[
                "position"=>42,
                'url'        => '#',
                'title'      => __('All Booking'),
                'icon'       => 'ion ion-md-paper-plane',
                'permission' => 'allbooking_bookings',
                'children'   => [
                    'issue_request'=>[
                        'url'        => route('all-booking.issue-request'),
                        'title'      => __('Issue Request'),
                        'permission' => 'allbooking_issue_request',
                    ],
                    'booked'=>[
                        'url'        => route('all-booking.booked'),
                        'title'      => __('Booked'),
                        'permission' => 'allbooking_booked',
                    ],
                    'cancelled'=>[
                        'url'        => route('all-booking.cancelled'),
                        'title'      => __('Cancelled'),
                        'permission' => 'allbooking_cancelled',
                    ],
                    'issued'=>[
                        'url'        => route('all-booking.issued'),
                        'title'      => __('Issued'),
                        'permission' => 'allbooking_issued',
                    ],
                    'pending'=>[
                        'url'        => route('all-booking.pending'),
                        'title'      => __('Pending'),
                        'permission' => 'allbooking_pending',
                    ],
                    'paid'=>[
                        'url'        => route('all-booking.paid'),
                        'title'      => __('Paid'),
                        'permission' => 'allbooking_paid',
                    ],
                    'ticketed'=>[
                        'url'        => route('all-booking.ticketed'),
                        'title'      => __('Ticketed'),
                        'permission' => 'allbooking_ticketed',
                    ],
                    'failed'=>[
                        'url'        => route('all-booking.failed'),
                        'title'      => __('Failed'),
                        'permission' => 'allbooking_failed',
                    ],
                    'refunded'=>[
                        'url'        => route('all-booking.refunded'),
                        'title'      => __('Refunded'),
                        'permission' => 'allbooking_refunded',
                    ],
                ]
            ],
        ];
    }

    public static function getUserMenu()
    {
        $res = [];

//        $res['booking'] = [
//            'url'        => route('flight.vendor.index'),
//            'title'      => __("My Bookings"),
//            'icon'       => 'ion ion-md-airplane',
//            'position'   => 61,
//            'permission' => 'booking_users_requests',
//        ];

        $res['void_requests'] = [
            'url'        => route('user.void.index'),
            'title'      => __("Void Requests"),
            'icon'       => 'ion ion-md-close-circle',
            'position'   => 62,
            'permission' => 'booking_users_requests',
        ];

        $res['refund_requests'] = [
            'url'        => route('user.refund.index'),
            'title'      => __("Refund Requests"),
            'icon'       => 'ion ion-md-cash',
            'position'   => 63,
            'permission' => 'booking_users_requests',
        ];

        $res['reissue_requests'] = [
            'url'        => route('user.reissue.index'),
            'title'      => __("Reissue Requests"),
            'icon'       => 'ion ion-md-refresh',
            'position'   => 64,
            'permission' => 'booking_users_requests',
        ];

        $res['ssr_requests'] = [
            'url'        => route('user.ssr.index'),
            'title'      => __("SSR Requests"),
            'icon'       => 'ion ion-md-document',
            'position'   => 65,
            'permission' => 'booking_users_requests',
        ];

        $res['cancel_requests'] = [
            'url'        => route('user.cancel.index'),
            'title'      => __("Ticket Cancel Requests"),
            'icon'       => 'ion ion-md-trash',
            'position'   => 66,
            'permission' => 'booking_users_requests',
        ];
        $res['top_up'] = [
            'url'        => route('user.wallet.addcredit'),
            'title'      => __("Top Up"),
            'icon'       => 'ion ion-md-card',
            'position'   => 67,
            'permission' => 'wallet_user',
        ];
        $res['transactions'] = [
            'url'        => route('user.wallet.creditTransaction'),
            'title'      => __("Transactions"),
            'icon'       => 'ion ion-md-list',
            'position'   => 68,
            'permission' => 'wallet_user',
        ];

        return $res;
    }

//    public static function getUserMenu()
//    {
//        $res = [];
////        if (Flight::isEnable()) {
//
//
//            $res['booking'] = [
//                'url'        => route('flight.vendor.index'), // user এর booking list route
//                'title'      => __("My Bookings"),
//                'icon'       => 'ion ion-md-airplane',
//                'position'   => 61,
//                'permission' => 'booking_users_requests',
//                'children'   => [
//                    [
//                        'url'        => route('user.void.index'),
//                        'title'      => __("Void Requests"),
//                        'permission' => 'booking_users_requests',
//                    ],
//                    [
//                        'url'        => route('user.refund.index'),
//                        'title'      => __("Refund Requests"),
//                        'permission' => 'booking_users_requests',
//                    ],
//                    [
//                        'url'        => route('user.reissue.index'),
//                        'title'      => __("Reissue Requests"),
//                        'permission' => 'booking_users_requests',
//                    ],
//                    [
//                        'url'        => route('user.ssr.index'),
//                        'title'      => __("SSR Requests"),
//                        'permission' => 'booking_users_requests',
//                    ],
//                    [
//                        'url'        => route('user.cancel.index'),
//                        'title'      => __("Cancel Requests"),
//                        'permission' => 'booking_users_requests',
//                    ],
//                ]
//            ];
////        }
//        return $res;
//    }

}
