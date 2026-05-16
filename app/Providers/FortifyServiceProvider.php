<?php
//
//namespace App\Providers;
//
//use App\Actions\Fortify\CreateNewUser;
//use App\Actions\Fortify\ResetUserPassword;
//use App\Actions\Fortify\UpdateUserPassword;
//use App\Actions\Fortify\UpdateUserProfileInformation;
//use App\Models\User;
//use Illuminate\Cache\RateLimiting\Limit;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Hash;
//use Illuminate\Support\Facades\RateLimiter;
//use Illuminate\Support\ServiceProvider;
//use Laravel\Fortify\Fortify;
//use Laravel\Fortify\Contracts\SendPasswordResetLinkResponse as SendPasswordResetLinkResponseContract;
//use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedResponseContract;
//use Illuminate\Http\JsonResponse;
//class FortifyServiceProvider extends ServiceProvider
//{
//    /**
//     * Register any application services.
//     *
//     * @return void
//     */
//    public function register()
//    {
//        // ✅ Success response
//        $this->app->singleton(SendPasswordResetLinkResponseContract::class, function () {
//            return new class implements SendPasswordResetLinkResponseContract {
//                public function toResponse($request)
//                {
//                    return back()->with('status', 'রিসেট লিংক আপনার ইমেইলে পাঠানো হয়েছে। ইনবক্স চেক করুন।');
//                }
//            };
//        });
//
//        // ✅ Failed response
//        $this->app->singleton(FailedResponseContract::class, function () {
//            return new class implements FailedResponseContract {
//                public function toResponse($request)
//                {
//                    $status = session('_old_input') ?? '';
//                    return back()->withErrors([
//                        'email' => 'এই ইমেইলে কোনো অ্যাকাউন্ট পাওয়া যায়নি।'
//                    ]);
//                }
//            };
//        });
//    }
//
//    /**
//     * Bootstrap any application services.
//     *
//     * @return void
//     */
//    public function boot()
//    {
//        Fortify::createUsersUsing(CreateNewUser::class);
//        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
//        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
//        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
//
//        Fortify::requestPasswordResetLinkView(function () {
//            return view('auth.passwords.email');
//        });
//
//        Fortify::confirmPasswordView(function () {
//            return view('auth.confirm-password');
//        });
//
//        Fortify::twoFactorChallengeView(function () {
//            return view('auth.two-factor-challenge');
//        });
//
//
//        Fortify::loginView(function () {
//
//            return view('auth.login');
//        });
//
//        Fortify::verifyEmailView(function () {
//            return view('auth.verify');
//        });
//        Fortify::resetPasswordView(function () {
//            return view('auth.passwords.reset');
//        });
//
//        Fortify::authenticateUsing(function (Request $request) {
//
//            $user = User::where('email', $request->email)->first();
////            return $user;
////            dd($user);
//            if ($user && Hash::check($request->password, $user->password)) {
//                return $user;
//
//            }
//        });
//
//        RateLimiter::for('login', function (Request $request) {
//            $email = (string) $request->email;
//            return Limit::perMinute(5)->by($email.$request->ip());
//        });
//
//        RateLimiter::for('two-factor', function (Request $request) {
//            return Limit::perMinute(5)->by($request->session()->get('login.id'));
//        });
//    }
//}


namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.passwords.email');
        });


        Fortify::confirmPasswordView(function () {
            return view('auth.confirm-password');
        });

        Fortify::twoFactorChallengeView(function () {
            return view('auth.two-factor-challenge');
        });


        Fortify::loginView(function () {
            $redirect = request()->query('redirect');
            if ($redirect) {
                session(['url.intended' => $redirect]);
            }
            return view('auth.login');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify');
        });
        Fortify::resetPasswordView(function () {
            return view('auth.passwords.reset');
        });

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();
            if ($user && Hash::check($request->password, $user->password) and $user->status == "publish") {
                return $user;
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string)$request->email;
            return Limit::perMinute(5)->by($email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
