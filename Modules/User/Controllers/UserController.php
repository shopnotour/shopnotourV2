<?php
namespace Modules\User\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Matrix\Exception;
use Modules\Boat\Models\Boat;
use Modules\Booking\Models\Service;
use Modules\Car\Models\Car;
use Modules\Event\Models\Event;
use Modules\Flight\Models\Flight;
use Modules\FrontendController;
use Modules\Hotel\Models\Hotel;
use Modules\Media\Models\MediaFile;
use Modules\Media\Traits\HasUpload;
use Modules\Space\Models\Space;
use Modules\Tour\Models\Tour;
use Modules\User\Events\NewVendorRegistered;
use Modules\User\Events\UserSubscriberSubmit;
use Modules\User\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Vendor\Models\VendorRequest;
use Validator;
use Modules\Booking\Models\Booking;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Modules\Booking\Models\Enquiry;
use Illuminate\Support\Str;

class UserController extends FrontendController
{
    use AuthenticatesUsers;
    use HasUpload;

    protected $enquiryClass;
    private Booking $booking;

    public function __construct(Booking $booking, Enquiry $enquiry)
    {
        $this->enquiryClass = $enquiry;
        parent::__construct();
        $this->booking = $booking;
    }

    public function dashboard(Request $request)
    {
        $this->checkPermission('dashboard_vendor_access');
        $user_id = Auth::id();
        $data = [
            'cards_report'       => $this->booking->getTopCardsReportForVendor($user_id),
            'earning_chart_data' => $this->booking->getEarningChartDataForVendor(strtotime('monday this week'), time(), $user_id),
            'page_title'         => __("Vendor Dashboard"),
            'breadcrumbs'        => [
                [
                    'name'  => __('Dashboard'),
                    'class' => 'active'
                ]
            ]
        ];
        return view('User::frontend.dashboard', $data);
    }

    public function reloadChart(Request $request)
    {
        $chart = $request->input('chart');
        $user_id = Auth::id();
        switch ($chart) {
            case "earning":
                $from = $request->input('from');
                $to = $request->input('to');
                return $this->sendSuccess([
                    'data' => $this->booking->getEarningChartDataForVendor(strtotime($from), strtotime($to), $user_id)
                ]);
                break;
        }
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        $data = [
            'dataUser'         => $user,
            'page_title'       => __("Profile"),
            'breadcrumbs'      => [
                [
                    'name'  => __('Setting'),
                    'class' => 'active'
                ]
            ],
            'is_vendor_access' => $this->hasPermission('dashboard_vendor_access')
        ];
        return view('User::frontend.profile', $data);
    }



//    public function profileUpdate(Request $request)
//    {
//        if (is_demo_mode()) {
//            return back()->with('error', "Demo mode: disabled");
//        }
//
//        $user = Auth::user();
//
//        $request->validate([
//            'first_name'            => 'nullable|max:255',
//            'last_name'             => 'nullable|max:255',
//            'email'                 => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
//            'user_name'             => ['nullable', 'max:255', 'min:4', 'string', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
//            'phone' => ['nullable'],
//            'iata_number'           => 'nullable|string|max:100',
//            'civil_aviation_number' => 'nullable|string|max:100',
//            'trade_license_number'  => 'nullable|string|max:100',
//            'avatar_file'           => 'nullable|file|image|max:2048',
//            'iata_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
//            'civil_aviation_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
//            'trade_license_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
//        ], [
//            'user_name.required' => __('The User name field is required.'),
//        ]);
//
//        $input = $request->except(['bio', 'avatar_file', 'iata_file', 'civil_aviation_file', 'trade_license_file']);
//        $user->fill($input);
//        $user->bio = clean($request->input('bio'));
//
//        if ($user->birthday) {
//            $user->birthday = date("Y-m-d", strtotime($user->birthday));
//        }
//
//        if ($request->filled('user_name')) {
//            $user->user_name = Str::slug($request->input('user_name'), "_");
//        }
//
//        // ── Avatar ──
//        if ($request->hasFile('avatar_file')) {
//            $this->deleteOldMedia($user->avatar_id);
//            $user->avatar_id = $this->saveToMedia($request->file('avatar_file'), 'avatar');
//        }
//
//        // ── Agency files ──
//        if ($request->hasFile('iata_file')) {
//            $this->deleteOldMedia($user->iata_file_id);
//            $user->iata_file_id = $this->saveToMedia($request->file('iata_file'), 'agency/iata');
//        }
//
//        if ($request->hasFile('civil_aviation_file')) {
//            $this->deleteOldMedia($user->civil_aviation_file_id);
//            $user->civil_aviation_file_id = $this->saveToMedia($request->file('civil_aviation_file'), 'agency/civil');
//        }
//
//        if ($request->hasFile('trade_license_file')) {
//            $this->deleteOldMedia($user->trade_license_file_id);
//            $user->trade_license_file_id = $this->saveToMedia($request->file('trade_license_file'), 'agency/trade');
//        }
//
//        $user->save();
//
//        // ── Email change — re-verification ──
//        if ($user->wasChanged('email')) {
//            $user->email_verified_at = null;
//            $user->save();
//            $user->sendEmailVerificationNotification();
//            return redirect()->back()->with('success', __('Profile updated. Please check your new email for verification link.'));
//        }
//
//        return redirect()->back()->with('success', __('Update successfully'));
//    }

    public function profileUpdate(Request $request)
    {
        if (is_demo_mode()) {
            return back()->with('error', "Demo mode: disabled");
        }

        $user = Auth::user();

        $request->validate([
            'first_name'            => 'nullable|max:255',
            'last_name'             => 'nullable|max:255',
            'email'                 => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'user_name' => ['nullable', 'max:255', 'min:4', 'string', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable'],
            'iata_number'           => 'nullable|string|max:100',
            'civil_aviation_number' => 'nullable|string|max:100',
            'trade_license_number'  => 'nullable|string|max:100',
            'avatar_file'           => 'nullable|file|image|max:2048',
            'iata_file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'civil_aviation_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'trade_license_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'user_name.required' => __('The User name field is required.'),
        ]);

        $input = $request->except(['bio', 'avatar_file', 'iata_file', 'civil_aviation_file', 'trade_license_file']);
        $user->fill($input);
        $user->bio = clean($request->input('bio'));

        if ($user->birthday) {
            $user->birthday = date("Y-m-d", strtotime($user->birthday));
        }

        $userNameInput = $request->input('user_name');

        // If user_name is provided
        if (!empty($userNameInput)) {
            $user->user_name = Str::of($userNameInput)
                ->trim()
                ->squish()              // removes extra spaces inside string
                ->replace(' ', '_');    // convert spaces to underscore
        } else {
            // fallback: first_name + last_name
            $first = $request->input('first_name');
            $last  = $request->input('last_name');

            if ($first || $last) {
                $user->user_name = Str::of(trim($first . ' ' . $last))
                    ->squish()
                    ->replace(' ', '_');
            }
        }

        // ── Avatar ──
        if ($request->hasFile('avatar_file')) {
            $this->deleteOldMedia($user->avatar_id);
            $user->avatar_id = $this->saveToMedia($request->file('avatar_file'), 'avatar');
        }

        // ── Agency files ──
        if ($request->hasFile('iata_file')) {
            $this->deleteOldMedia($user->iata_file_id);
            $user->iata_file_id = $this->saveToMedia($request->file('iata_file'), 'agency/iata');
        }

        if ($request->hasFile('civil_aviation_file')) {
            $this->deleteOldMedia($user->civil_aviation_file_id);
            $user->civil_aviation_file_id = $this->saveToMedia($request->file('civil_aviation_file'), 'agency/civil');
        }

        if ($request->hasFile('trade_license_file')) {
            $this->deleteOldMedia($user->trade_license_file_id);
            $user->trade_license_file_id = $this->saveToMedia($request->file('trade_license_file'), 'agency/trade');
        }

        $user->save();

        // ── Email change — re-verification ──
        if ($user->wasChanged('email')) {
            $user->email_verified_at = null;
            $user->save();
            $user->sendEmailVerificationNotification();
            return redirect()->back()->with('success', __('Profile updated. Please check your new email for verification link.'));
        }

        return redirect()->back()->with('success', __('Update successfully'));
    }

    private function deleteOldMedia(?int $mediaId): void
    {
        if (!$mediaId) return;

        $media = MediaFile::find($mediaId);
        if (!$media) return;

        @unlink(public_path('uploads/' . $media->file_path));
        $media->delete();
    }

    private function saveToMedia($file, string $collection): ?int
    {
        $extension    = $file->getClientOriginalExtension();
        $fileName     = time() . '_' . uniqid() . '.' . $extension;
        $folderPath   = public_path('uploads/user-docs/' . $collection);
        $originalName = $file->getClientOriginalName();
        $fileSize     = $file->getSize();
        $fileMime     = $file->getMimeType();

        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        $file->move($folderPath, $fileName);

        $media = MediaFile::create([
            'file_name'      => $originalName,
            'file_path'      => 'user-docs/' . $collection . '/' . $fileName,
            'file_size'      => $fileSize,
            'file_type'      => $fileMime,
            'file_extension' => $extension,
            'driver'         => 'uploads',
            'is_private'     => 0,
        ]);

        return $media->id;
    }


//    public function profileUpdate(Request $request){
//
////        MediaFile::
//        if(is_demo_mode()){
//            return back()->with('error',"Demo mode: disabled");
//        }
//        $user = Auth::user();
//        $messages = [
//            'user_name.required'      => __('The User name field is required.'),
//        ];
//        $request->validate([
//            'first_name' => 'required|max:255',
//            'last_name'  => 'required|max:255',
//            'email'      => [
//                'required',
//                'email',
//                'max:255',
//                Rule::unique('users')->ignore($user->id)
//            ],
//            'user_name'=> [
//                'required',
//                'max:255',
//                'min:4',
//                'string',
//                'alpha_dash',
//                Rule::unique('users')->ignore($user->id)
//            ],
//            'phone'       => [
//                'required',
//                Rule::unique('users')->ignore($user->id)
//            ],
//        ],$messages);
//        $input = $request->except('bio');
//        $user->fill($input);
//        $user->bio = clean($request->input('bio'));
//        $user->birthday = date("Y-m-d", strtotime($user->birthday));
//        $user->user_name = Str::slug( $request->input('user_name') ,"_");
//        $user->save();
//        return redirect()->back()->with('success', __('Update successfully'));
//    }

//    public function bookingHistory(Request $request)
//    {
//        $user_id = Auth::id();
//        $data = [
//            'bookings' => $this->booking->getBookingHistory($request->input('status'), $user_id),
//            //status =all booking status filter
//            'statues'     => config('booking.statuses'),
//            'breadcrumbs' => [
//                [
//                    'name'  => __('Booking History'),
//                    'class' => 'active'
//                ]
//            ],
//            'page_title'  => __("Booking History"),
//        ];
////        return $data;
//        return view('User::frontend.bookingHistory', $data);
//    }

    public function bookingHistory(Request $request)
    {
        $user_id = Auth::id();

        $statuses = Booking::where('customer_id', $user_id)
            ->where('status', '!=', 'draft')
            ->where('object_model', 'flight')
            ->distinct()
            ->pluck('status')
            ->toArray();

        $data = [
            'bookings' => $this->booking->getBookingHistory($request->input('status'), $user_id),
            // pnr, from_date, to_date আর pass করছি না - DataTable করবে
            'statues'  => $statuses,
            'breadcrumbs' => [['name' => __('Booking History'), 'class' => 'active']],
            'page_title'  => __("Booking History"),
        ];

        return view('User::frontend.userBookings', $data);
    }

    public function bookingHistorynew(Request $request)
    {
//       return $request;
        $user_id = Auth::id();

        $statuses = Booking::distinct()
            ->pluck('status')
            ->toArray();

        $data = [
            'bookings' => $this->booking->getBookingHistory(
                $request->input('status'),
                $user_id,
                $request->input('pnr'),
                $request->input('from_date'),
                $request->input('to_date'),
            ),
            'statues'     => $statuses,
            'breadcrumbs' => [['name' => __('Booking History'), 'class' => 'active']],
            'page_title'  => __("Booking History"),
        ];
return $data;
        return view('User::frontend.bookingHistory', $data);
    }

    public function subscribe(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255'
        ]);
        $check = Subscriber::withTrashed()->where('email', $request->input('email'))->first();
        if ($check) {
            if ($check->trashed()) {
                $check->restore();
                return $this->sendSuccess([], __('Thank you for subscribing'));
            }
            return $this->sendError(__('You are already subscribed'));
        } else {
            $a = new Subscriber();
            $a->email = $request->input('email');
            $a->first_name = $request->input('first_name');
            $a->last_name = $request->input('last_name');
            $a->save();

            event(new UserSubscriberSubmit($a));

            return $this->sendSuccess([], __('Thank you for subscribing'));
        }
    }

    public function upgradeVendor(Request $request){
        $user = Auth::user();
        $vendorRequest = VendorRequest::query()->where("user_id",$user->id)->where("status","pending")->first();
        if(!empty($vendorRequest)){
            return redirect()->back()->with('warning', __("You have just done the become vendor request, please wait for the Admin's approved"));
        }
        // check vendor auto approved
        $vendorAutoApproved = setting_item('vendor_auto_approved');
         $dataVendor['role_request'] = setting_item('vendor_role');
        if ($vendorAutoApproved) {
            if ($dataVendor['role_request']) {
                $user->assignRole($dataVendor['role_request']);
            }
            $dataVendor['status'] = 'approved';
            $dataVendor['approved_time'] = now();
        } else {
            $dataVendor['status'] = 'pending';
        }
        $vendorRequestData = $user->vendorRequest()->save(new VendorRequest($dataVendor));
        try {
            event(new NewVendorRegistered($user, $vendorRequestData));
        } catch (Exception $exception) {
            Log::warning("NewVendorRegistered: " . $exception->getMessage());
        }
        return redirect()->back()->with('success', __('Request vendor success!'));
    }



    public function permanentlyDelete(Request $request){
        if(is_demo_mode()){
            return back()->with('error',"Demo mode: disabled");
        }
        if(!empty(setting_item('user_enable_permanently_delete')))
        {
            $user = Auth::user();
            \DB::beginTransaction();
            try {
                Service::where('author_id',$user->id)->delete();
                Tour::where('author_id',$user->id)->delete();
                Car::where('author_id',$user->id)->delete();
                Space::where('author_id',$user->id)->delete();
                Hotel::where('author_id',$user->id)->delete();
                Event::where('author_id',$user->id)->delete();
                Boat::where('author_id',$user->id)->delete();
                Flight::where('author_id',$user->id)->delete();
                $user->sendEmailPermanentlyDelete();
                $user->delete();
                \DB::commit();
                Auth::logout();
                if(is_api()){
                    return $this->sendSuccess([],'Deleted');
                }
                return redirect(route('home'));
            }catch (\Exception $exception){
                \DB::rollBack();
            }
        }
        if(is_api()){
            return $this->sendError('Error. You can\'t permanently delete');
        }
        return back()->with('error',__('Error. You can\'t permanently delete'));

    }


}
