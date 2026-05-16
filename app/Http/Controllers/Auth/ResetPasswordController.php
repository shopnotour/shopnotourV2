<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    protected string $redirectTo = '/user/profile';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showResetForm(Request $request, ?string $token = null)
    {
        return view('auth.passwords.reset', [
            'page_title' => __('নতুন পাসওয়ার্ড সেট করুন'),
            'token'      => $token,
            'email'      => $request->query('email', ''),
        ]);
    }

    /* ══════════════════════════════════════════
       RESET PASSWORD SUBMIT
       Route: POST /shopno/password/reset  (shopno.password.update)
    ══════════════════════════════════════════ */
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::min(4)],
        ], [
            'token.required'        => 'রিসেট টোকেন পাওয়া যায়নি।',
            'email.required'        => 'ইমেইল ঠিকানা লিখুন।',
            'email.email'           => 'সঠিক ইমেইল ঠিকানা লিখুন।',
            'password.required'     => 'নতুন পাসওয়ার্ড লিখুন।',
            'password.confirmed'    => 'দুটো পাসওয়ার্ড মিলছে না। আবার দেখুন।',
            'password.min'          => 'পাসওয়ার্ড কমপক্ষে ৪ অক্ষরের হতে হবে।',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if ($request->wantsJson()) {
                return new JsonResponse([
                    'message'  => 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে! এখন নতুন পাসওয়ার্ড দিয়ে লগইন করুন।',
                    'redirect' => $this->redirectTo,
                ], 200);
            }
            return redirect($this->redirectTo)->with('status', __($status));
        }

        $banglaError = match($status) {
            Password::INVALID_TOKEN => 'রিসেট লিংকটি মেয়াদ উত্তীর্ণ বা অবৈধ। নতুন রিসেট লিংকের জন্য আবার চেষ্টা করুন।',
            Password::INVALID_USER  => 'এই ইমেইলে কোনো অ্যাকাউন্ট পাওয়া যায়নি।',
            default                 => 'পাসওয়ার্ড পরিবর্তন করা সম্ভব হয়নি। আবার চেষ্টা করুন।',
        };

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $banglaError,
                'errors'  => ['email' => [$banglaError]],
            ], 422);
        }

        throw ValidationException::withMessages(['email' => [$banglaError]]);
    }
}
