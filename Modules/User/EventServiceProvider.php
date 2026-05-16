<?php
//namespace Modules\User;
//
//
//use Illuminate\Auth\Events\PasswordReset;
//use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
//use Modules\User\Events\AdminUpdateVerificationData;
//use Modules\User\Events\CreatePlanRequest;
//use Modules\User\Events\RequestCreditPurchase;
//use Modules\User\Events\UpdateCreditPurchase;
//use Modules\User\Events\UpdatePlanRequest;
//use Modules\User\Events\UserSubscriberSubmit;
//use Modules\User\Events\UserVerificationSubmit;
//use Modules\User\Listeners\ClearUserTokens;
//use Modules\User\Listeners\SendAdminUpdateVerifyDataEmail;
//use Modules\User\Listeners\SendNotifyCreatePlanRequest;
//use Modules\User\Listeners\SendNotifyRequestCreditPurchase;
//use Modules\User\Listeners\SendNotifyUpdateCreditPurchase;
//use Modules\User\Listeners\SendNotifyUpdatePlanRequest;
//use Modules\User\Listeners\SendNotifyUpdateVerificationData;
//use Modules\User\Listeners\SendNotifyVerificationData;
//use Modules\User\Listeners\SendUserSubmitVerifyDataEmail;
//use Modules\User\Listeners\UserSubscriberSubmitListeners;
//
//class EventServiceProvider extends ServiceProvider
//{
//    /**
//     * The event listener mappings for the application.
//     *
//     * @var array
//     */
//    protected $listen = [
//        UserVerificationSubmit::class => [
//            SendUserSubmitVerifyDataEmail::class,
//            SendNotifyVerificationData::class
//        ],
//        AdminUpdateVerificationData::class => [
//            SendAdminUpdateVerifyDataEmail::class,
//            SendNotifyUpdateVerificationData::class
//        ],
//        RequestCreditPurchase::class => [
//            SendNotifyRequestCreditPurchase::class
//        ],
//        UpdateCreditPurchase::class => [
//            SendNotifyUpdateCreditPurchase::class
//        ],
//        UserSubscriberSubmit::class => [
//            UserSubscriberSubmitListeners::class
//        ],
//        PasswordReset::class=>[
//            ClearUserTokens::class
//        ],
//        CreatePlanRequest::class => [
//            SendNotifyCreatePlanRequest::class
//        ],
//        UpdatePlanRequest::class => [
//            SendNotifyUpdatePlanRequest::class
//        ]
//    ];
//
//    /**
//     * Register any events for your application.
//     *
//     * @return void
//     */
//    public function boot()
//    {
//        parent::boot();
//
//        //
//    }
//}


namespace Modules\User;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\User\Events\AdminUpdateVerificationData;
use Modules\User\Events\CreatePlanRequest;
use Modules\User\Events\NewVendorRegistered;
use Modules\User\Events\RequestCreditPurchase;
use Modules\User\Events\SendMailUserRegistered;
use Modules\User\Events\UpdateCreditPurchase;
use Modules\User\Events\UpdatePlanRequest;
use Modules\User\Events\UserSubscriberSubmit;
use Modules\User\Events\UserVerificationSubmit;
use Modules\User\Events\VendorApproved;
use Modules\User\Listeners\ClearUserTokens;
use Modules\User\Listeners\SendAdminUpdateVerifyDataEmail;
use Modules\User\Listeners\SendMailUserRegisteredListen;
use Modules\User\Listeners\SendNotifyRegistered;
use Modules\User\Listeners\SendNotifyRegisteredListen;
use Modules\User\Listeners\SendVendorRegisterdEmail;
use Modules\User\Listeners\SendVendorApprovedMail;
use Modules\User\Listeners\SendNotifyApproved;
use Modules\User\Listeners\SendNotifyCreatePlanRequest;
use Modules\User\Listeners\SendNotifyRequestCreditPurchase;
use Modules\User\Listeners\SendNotifyUpdateCreditPurchase;
use Modules\User\Listeners\SendNotifyUpdatePlanRequest;
use Modules\User\Listeners\SendNotifyUpdateVerificationData;
use Modules\User\Listeners\SendNotifyVerificationData;
use Modules\User\Listeners\SendUserSubmitVerifyDataEmail;
use Modules\User\Listeners\UserSubscriberSubmitListeners;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ── Existing ──────────────────────────────────────────────────────────
        UserVerificationSubmit::class => [
            SendUserSubmitVerifyDataEmail::class,
            SendNotifyVerificationData::class,
        ],
        AdminUpdateVerificationData::class => [
            SendAdminUpdateVerifyDataEmail::class,
            SendNotifyUpdateVerificationData::class,
        ],
        RequestCreditPurchase::class => [
            SendNotifyRequestCreditPurchase::class,
        ],
        UpdateCreditPurchase::class => [
            SendNotifyUpdateCreditPurchase::class,
        ],
        UserSubscriberSubmit::class => [
            UserSubscriberSubmitListeners::class,
        ],
        PasswordReset::class => [
            ClearUserTokens::class,
        ],
        CreatePlanRequest::class => [
            SendNotifyCreatePlanRequest::class,
        ],
        UpdatePlanRequest::class => [
            SendNotifyUpdatePlanRequest::class,
        ],

        // ── NEW: Registration & Vendor mail + notify events ──────────────────
        SendMailUserRegistered::class => [
            SendMailUserRegisteredListen::class,  // mail → customer (User Settings)
            SendNotifyRegisteredListen::class,    // bell → admin panel notification
        ],
        NewVendorRegistered::class => [
            SendVendorRegisterdEmail::class,      // mail → customer + admin (Vendor Settings)
            SendNotifyRegistered::class,          // bell → admin panel notification
        ],
        VendorApproved::class => [
            SendVendorApprovedMail::class,        // mail → customer approval
            SendNotifyApproved::class,            // bell → vendor panel notification
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
