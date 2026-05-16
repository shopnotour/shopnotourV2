<?php
//
//namespace Modules\User\Listeners;
//
//use Illuminate\Support\Facades\Mail;
//use Modules\User\Emails\RegisteredEmail;
//use Modules\User\Emails\VendorApprovedEmail;
//use Modules\User\Events\SendMailUserRegistered;
//use Modules\User\Events\VendorApproved;
//use Modules\User\Models\User;
//use Modules\Vendor\Models\VendorRequest;
//
//class SendVendorApprovedMail
//{
//    /**
//     * Create the event listener.
//     *
//     * @return void
//     */
//    public $user;
//    public $vendorRequest;
//
//    const CODE = [
//        'first_name' => '[first_name]',
//        'last_name'  => '[last_name]',
//        'name'       => '[name]',
//        'email'      => '[email]',
//    ];
//
//    public function __construct(User $user, VendorRequest $vendorRequest)
//    {
//        $this->user = $user;
//        $this->vendorRequest = $vendorRequest;
//        //
//    }
//
//    /**
//     * Handle the event.
//     *
//     * @param Event $event
//     * @return void
//     */
//    public function handle(VendorApproved $event)
//    {
//        if($event->user->locale){
//            $old = app()->getLocale();
//            app()->setLocale($event->user->locale);
//        }
//
//        Mail::to($event->user->email)->send(new VendorApprovedEmail($event->user));
//
//        if(!empty($old)){
//            app()->setLocale($old);
//        }
//
//    }
//
//}


namespace Modules\User\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\User\Emails\VendorApprovedEmail;
use Modules\User\Events\VendorApproved;

class SendVendorApprovedMail
{
    const CODE = [
        'first_name' => '[first_name]',
        'last_name' => '[last_name]',
        'name' => '[name]',
        'email' => '[email]',
    ];

    // constructor এ কোনো dependency নেই — Laravel event থেকে নেব
    public function __construct()
    {
    }

    public function handle(VendorApproved $event)
    {
        $user = $event->user;
        if (!$user || !$user->email) return;

        if ($user->locale) {
            $old = app()->getLocale();
            app()->setLocale($user->locale);
        }

        Mail::to($user->email)->send(new VendorApprovedEmail($user));

        if (!empty($old)) {
            app()->setLocale($old);
        }
    }
}
