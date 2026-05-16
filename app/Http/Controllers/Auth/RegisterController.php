<?php
//
//namespace App\Http\Controllers\Auth;
//
//use App\Http\Controllers\Controller;
//use App\User;
//use Carbon\Carbon;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Hash;
//use Illuminate\Support\Facades\Log;
//use Illuminate\Support\Facades\Validator;
//use Modules\User\Events\SendMailUserRegistered;
//
//class RegisterController extends Controller
//{
//    protected string $redirectTo = '/';
//
//    public function __construct()
//    {
//        $this->middleware('guest');
//    }
//
//    public function showRegistrationForm()
//    {
//        return view('auth.register', ['page_title' => __('Register')]);
//    }
//
//    public function register(Request $request)
//    {
//        $rules = [
//            'first_name' => ['required', 'string', 'max:100'],
//            'last_name'  => ['nullable', 'string', 'max:100'],
//            'phone'      => ['nullable', 'string', 'max:20'],
//            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
//            'password'   => ['required', 'string', 'min:6', 'confirmed'],
//            'term'       => ['accepted'],
//        ];
//
//        if (setting_item('user_enable_register_recaptcha')) {
//            $rules['g-recaptcha-response'] = ['required'];
//        }
//
//        $messages = [
//            'first_name.required'           => 'First name is required.',
//            'first_name.max'                => 'First name may not be greater than 100 characters.',
//            'last_name.max'                 => 'Last name may not be greater than 100 characters.',
//            'phone.max'                     => 'Phone number is too long.',
//            'email.required'                => 'Email address is required.',
//            'email.email'                   => 'Please enter a valid email address.',
//            'email.max'                     => 'Email address is too long.',
//            'email.unique'                  => 'This email is already registered. Please log in or use a different email.',
//            'password.required'             => 'Password is required.',
//            'password.min'                  => 'Password must be at least 6 characters.',
//            'password.confirmed'            => 'Password confirmation does not match.',
//            'term.accepted'                 => 'You must accept the terms and conditions.',
//            'g-recaptcha-response.required' => 'Please verify that you are not a robot.',
//        ];
//
//        $validator = Validator::make($request->all(), $rules, $messages);
//
//        if ($validator->fails()) {
//            return response()->json([
//                'message' => $validator->errors()->first(),
//                'errors'  => $validator->errors()->toArray(),
//            ], 422);
//        }
//
//        $firstName = trim($request->input('first_name'));
//        $lastName  = trim($request->input('last_name', ''));
//
//        $user = new User();
//        $user->first_name        = $firstName;
//        $user->last_name         = $lastName;
//        $user->name              = trim($firstName . ' ' . $lastName);
//        $user->email             = strtolower(trim($request->input('email')));
//        $user->password          = Hash::make($request->input('password'));
//        $user->status            = 'publish';
//        $user->email_verified_at = Carbon::now();
//        $user->save();
//
//        if ($request->filled('phone')) {
//            $user->addMeta('phone', $request->input('phone'));
//        }
//
//        $user->assignRole(setting_item('user_role'));
//
//        try {
//            event(new SendMailUserRegistered($user));
//        } catch (\Exception $e) {
//            Log::warning('SendMailUserRegistered: ' . $e->getMessage());
//        }
//
//        Auth::login($user);
//
//        return response()->json([
//            'message'  => 'Account created successfully! Welcome.',
//            'redirect' => $this->redirectTo,
//        ], 201);
//    }
//}


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Media\Models\MediaFile;
use Modules\User\Events\NewVendorRegistered;
use Modules\User\Events\SendMailUserRegistered;
use Modules\Vendor\Models\VendorRequest;

class RegisterController extends Controller
{
    protected string $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register', ['page_title' => __('Register')]);
    }

    public function register(Request $request)
    {
        $userType = $request->input('user_type', 'b2c');
        $isB2B = $userType === 'b2b';

        // ── Validation Rules ──────────────────────────────────────────────────
        $rules = [
            'user_type' => ['required', 'in:b2c,b2b'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'term' => ['accepted'],
            'nid_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];

        if ($isB2B) {
            $rules['company_name'] = ['required', 'string', 'max:255'];
            $rules['trade_license'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        }

        if (setting_item('user_enable_register_recaptcha')) {
            $rules['g-recaptcha-response'] = ['required'];
        }

        // ── Validation Messages ───────────────────────────────────────────────
        $messages = [
            'user_type.required' => 'Account type is required.',
            'user_type.in' => 'Invalid account type selected.',
            'first_name.required' => 'First name is required.',
            'first_name.max' => 'First name may not exceed 100 characters.',
            'last_name.max' => 'Last name may not exceed 100 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered. Please log in or use a different email.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.max' => 'Phone number is too long.',
            'term.accepted' => 'You must accept the Terms & Privacy Policy.',
            'nid_document.required' => 'NID document is required.',
            'nid_document.file' => 'NID must be a valid file.',
            'nid_document.mimes' => 'NID must be a JPG, PNG, or PDF file.',
            'nid_document.max' => 'NID file size must not exceed 5MB.',
            'company_name.required' => 'Company name is required for corporate accounts.',
            'company_name.max' => 'Company name may not exceed 255 characters.',
            'trade_license.required' => 'Trade license document is required for corporate accounts.',
            'trade_license.file' => 'Trade license must be a valid file.',
            'trade_license.mimes' => 'Trade license must be a JPG, PNG, or PDF file.',
            'trade_license.max' => 'Trade license file size must not exceed 5MB.',
            'g-recaptcha-response.required' => 'Please verify that you are not a robot.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        // ── Create User ───────────────────────────────────────────────────────
        $firstName = trim($request->input('first_name'));
        $lastName = trim($request->input('last_name', ''));

        $user = new User();
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->name = trim($firstName . ' ' . $lastName);
        $user->user_name = $this->generateUniqueUsername($firstName, $lastName);
        $user->business_name = $isB2B ? trim($request->input('company_name')) : null;
        $user->email = strtolower(trim($request->input('email')));
        $user->password = Hash::make($request->input('password'));
        $user->phone = $request->input('phone') ? trim($request->input('phone')) : null;
        $user->address = $request->input('address') ? trim($request->input('address')) : null;
        $user->city = $request->input('city') ? trim($request->input('city')) : null;
        $user->country = $request->input('country') ? trim($request->input('country')) : null;
        $user->status = 'publish';
        $user->email_verified_at = Carbon::now();

        // ── NID Upload (B2C & B2B উভয়ের জন্য) ──────────────────────────────
        if ($request->hasFile('nid_document')) {
            $nidMediaId = $this->saveToMedia($request->file('nid_document'), 'agency/iata');
            $user->iata_file_id = $nidMediaId; // NID → iata_file_id column এ save হচ্ছে
        }

        // ── Trade License Upload (B2B only) ───────────────────────────────────
        if ($isB2B && $request->hasFile('trade_license')) {
            $tlMediaId = $this->saveToMedia($request->file('trade_license'), 'agency/trade');
            $user->trade_license_file_id = $tlMediaId;
        }

        $user->save();

        // ── Role Assignment ───────────────────────────────────────────────────
        // assignRole() এই project এ User model এর custom method —
        // এটাই users.role_id column set করে (admin controller এও same pattern)।
        $user->assignRole(setting_item('user_role'));

        // ── B2B → VendorRequest তৈরি করো ────────────────────────────────────
        if ($isB2B) {
            $this->createVendorRequest($user);
        }

        // ── Welcome Email ─────────────────────────────────────────────────────
        try {
            event(new SendMailUserRegistered($user));
        } catch (\Exception $e) {
            Log::warning('SendMailUserRegistered: ' . $e->getMessage());
        }

        Auth::login($user);

        // ── Response ──────────────────────────────────────────────────────────
        $message = $isB2B
            ? __('Account created successfully! Your B2B account is under review. Please wait for admin approval before accessing B2B features.')
            : __('Account created successfully! Welcome.');

        return response()->json([
            'message' => $message,
            'redirect' => $this->redirectTo,
        ], 201);
    }

    /**
     * B2B registration এর পর VendorRequest তৈরি করে।
     * Admin approval দিলে upgradeVendor() এর মতো vendor_role (Agent) assign হবে।
     */
    private function createVendorRequest(User $user): void
    {
        // আগে কোনো pending request আছে কিনা check
        $exists = VendorRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return;
        }

        $vendorAutoApproved = setting_item('vendor_auto_approved');
        $vendorRole = setting_item('vendor_role'); // Vendor Settings → Vendor Role (Agent)

        $data = [
            'user_id' => $user->id,
            'role_request' => $vendorRole,
        ];

        if ($vendorAutoApproved) {
            // Auto-approve চালু — সাথে সাথে vendor role (Agent) দাও
            if ($vendorRole) {
                $user->assignRole($vendorRole); // role_id ও set হবে
            }
            $data['status'] = 'approved';
            $data['approved_time'] = now();
        } else {
            // Manual approval — admin panel এ pending হিসেবে দেখাবে
            $data['status'] = 'pending';
        }

        $vendorRequest = VendorRequest::create($data);

        try {
            event(new NewVendorRegistered($user, $vendorRequest));
        } catch (\Exception $e) {
            Log::warning('NewVendorRegistered (B2B Register): ' . $e->getMessage());
        }
    }

    /**
     * Unique user_name generate করে।
     * Pattern: john_doe → john_doe_1 → john_doe_2 → ...
     * যদি last_name না থাকে: john → john_1 → john_2 → ...
     */
    private function generateUniqueUsername(string $firstName, string $lastName): string
    {
        $base = $lastName
            ? Str::slug($firstName . '_' . $lastName, '_')
            : Str::slug($firstName, '_');

        $username = $base;
        $counter = 1;

        while (\App\User::where('user_name', $username)->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * UserController::saveToMedia() এর সাথে হুবহু same pattern।
     */
    private function saveToMedia($file, string $collection): ?int
    {
        // ── move() করার আগেই সব metadata নাও — পরে file আর valid থাকে না ──
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $fileMime = $file->getMimeType();

        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $folderPath = public_path('uploads/user-docs/' . $collection);

        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        $file->move($folderPath, $fileName); // এরপর $file আর valid না

        $media = MediaFile::create([
            'file_name' => $originalName,
            'file_path' => 'user-docs/' . $collection . '/' . $fileName,
            'file_size' => $fileSize,
            'file_type' => $fileMime,
            'file_extension' => $extension,
            'driver' => 'uploads',
            'is_private' => 0,
        ]);

        return $media->id;
    }
}
