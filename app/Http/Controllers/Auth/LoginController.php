<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\User\Events\SendMailUserRegistered;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    protected string $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /* ══════════════════════════════════════════
       LOGIN FORM SHOW
    ══════════════════════════════════════════ */
    public function showLoginForm()
    {
        return view('auth.login', ['page_title' => __('লগইন')]);
    }

    /* ══════════════════════════════════════════
       LOGIN SUBMIT
       Route: POST /shopno/login  (shopno.login.submit)

       সব error JSON এ return করা হচ্ছে।
       wantsJson() ব্যবহার করা হচ্ছে না কারণ
       Bravo theme এর AJAX handler সবসময়
       reliably header পাঠায় না।
    ══════════════════════════════════════════ */
    public function login(Request $request)
    {
        // ── Manual Validation (exception throw করবে না) ──
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'ইমেইল ঠিকানা লিখুন।',
            'email.email'       => 'সঠিক ইমেইল ঠিকানা লিখুন। যেমন: example@gmail.com',
            'password.required' => 'পাসওয়ার্ড লিখুন।',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        // ── Rate limiting ──
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $msg = "{$seconds} সেকেন্ড পরে আবার চেষ্টা করুন। বারবার ভুল পাসওয়ার্ড দেওয়ায় সাময়িকভাবে বন্ধ করা হয়েছে।";

            return response()->json([
                'message' => $msg,
                'errors'  => ['email' => [$msg]],
            ], 429);
        }

        // ── Attempt login ──
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            // email আছে কিনা আলাদা করে check করে specific message
            $emailExists = User::where('email', $request->input('email'))->exists();

            if (!$emailExists) {
                return response()->json([
                    'message' => 'এই ইমেইলে কোনো অ্যাকাউন্ট পাওয়া যায়নি।',
                    'errors'  => [
                        'email' => ['এই ইমেইলে কোনো অ্যাকাউন্ট পাওয়া যায়নি।'],
                    ],
                ], 422);
            }

            return response()->json([
                'message' => 'পাসওয়ার্ড সঠিক হয়নি। আবার চেষ্টা করুন।',
                'errors'  => [
                    'password' => ['পাসওয়ার্ড সঠিক হয়নি। আবার চেষ্টা করুন।'],
                ],
            ], 422);
        }

        // ── Blocked user check ──
        $user = Auth::user();

        if ($user->deleted == 1 || in_array($user->status, ['blocked'])) {
            Auth::logout();
            $msg = 'আপনার অ্যাকাউন্টটি বন্ধ করা হয়েছে। সহায়তার জন্য যোগাযোগ করুন।';
            return response()->json([
                'message' => $msg,
                'errors'  => ['email' => [$msg]],
            ], 403);
        }

        // ── Success ──
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $redirectUrl = $request->input('redirect') ?: $this->getRedirectTo();

        return response()->json([
            'message'  => 'লগইন সফল হয়েছে!',
            'redirect' => $redirectUrl,
        ], 200);
    }

    /* ══════════════════════════════════════════
       LOGOUT
    ══════════════════════════════════════════ */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /* ══════════════════════════════════════════
       REDIRECT URL
    ══════════════════════════════════════════ */
    public function getRedirectTo(): string
    {
        if (Auth::check() && Auth::user()->hasPermission('dashboard_access')) {
            return '/admin';
        }

        $url = session()->pull('url.intended', $this->redirectTo);

        if (in_array($url, [route('login'), url('/')])) {
            return $this->redirectTo;
        }

        return $url;
    }

    /* ══════════════════════════════════════════
       SOCIAL LOGIN
    ══════════════════════════════════════════ */
    public function socialLogin(string $provider)
    {
        $this->initConfigs($provider);
        session()->put('url.intended', request()->server('HTTP_REFERER', url('/')));
        return Socialite::driver($provider)->redirect();
    }

    public function socialCallBack(string $provider)
    {
        try {
            $this->initConfigs($provider);
            $socialUser = Socialite::driver($provider)->user();

            if (empty($socialUser)) {
                return redirect()->route('login')->with('error', 'সোশ্যাল লগইন করা সম্ভব হয়নি।');
            }

            $existUser = User::getUserBySocialId($provider, $socialUser->getId());

            if (empty($existUser)) {
                UserMeta::query()
                    ->where('name', 'social_' . $provider . '_id')
                    ->where('val', $socialUser->getId())
                    ->delete();

                $email = $socialUser->getEmail() ?: $socialUser->getId() . '@' . $provider;

                if (User::query()->where('email', $email)->exists()) {
                    return redirect()->route('login')->with('error',
                        "'{$email}' ইমেইলটি দিয়ে আগেই অ্যাকাউন্ট আছে। সরাসরি লগইন করুন।"
                    );
                }

                $newUser = new User();
                $newUser->email              = $email;
                $newUser->password           = Hash::make(uniqid() . time());
                $newUser->name               = $socialUser->getName();
                $newUser->first_name         = $socialUser->getName();
                $newUser->status             = 'publish';
                $newUser->email_verified_at  = Carbon::now();
                $newUser->save();

                $newUser->addMeta('social_' . $provider . '_id',     $socialUser->getId());
                $newUser->addMeta('social_' . $provider . '_email',  $email);
                $newUser->addMeta('social_' . $provider . '_name',   $socialUser->getName());
                $newUser->addMeta('social_' . $provider . '_avatar', $socialUser->getAvatar());
                $newUser->addMeta('social_meta_avatar',              $socialUser->getAvatar());
                $newUser->assignRole(setting_item('user_role'));

                try {
                    event(new SendMailUserRegistered($newUser));
                } catch (\Exception $e) {
                    Log::warning('SendMailUserRegistered: ' . $e->getMessage());
                }

                Auth::login($newUser);
            } else {
                if ($existUser->deleted == 1 || in_array($existUser->status, ['blocked'])) {
                    return redirect()->route('login')->with('error', 'আপনার অ্যাকাউন্টটি বন্ধ করা হয়েছে।');
                }
                Auth::login($existUser);
            }

            $redirectTo = session()->pull('url.intended', url('/'));
            return redirect($redirectTo);

        } catch (\Exception $e) {
            $message = $e->getMessage() ?: (request()->get('error_message') ?: $e->getCode());
            return redirect()->route('login')->with('error', $message);
        }
    }

    protected function initConfigs(string $provider): void
    {
        if (in_array($provider, ['facebook', 'google', 'twitter'])) {
            config()->set([
                'services.' . $provider . '.client_id'     => setting_item($provider . '_client_id'),
                'services.' . $provider . '.client_secret' => setting_item($provider . '_client_secret'),
                'services.' . $provider . '.redirect'      => '/social-callback/' . $provider,
            ]);
        }
    }
}
