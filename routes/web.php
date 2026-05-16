<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Payment\NagadPaymentController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SslCommerzPaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;

//Route::middleware(['web', 'guest'])->group(function () {
//
//    /* ── LOGIN ── */
//    Route::post('/shopno/login', [LoginController::class, 'login'])
//        ->name('shopno.login.submit');
//
//    /* ── REGISTER ── */
//    Route::post('/shopno/register', [RegisterController::class, 'register'])
//        ->name('shopno.register.submit');
//
//    /* ── FORGOT PASSWORD: reset link email পাঠানো ── */
//    Route::post('/shopno/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
//        ->name('shopno.password.email');
//
//    /* ── RESET PASSWORD FORM: email link এ click করলে ── */
//    Route::get('/shopno/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
//        ->name('shopno.password.reset');
//
//    /* ── RESET PASSWORD SUBMIT: নতুন পাসওয়ার্ড save ── */
//    Route::post('/shopno/password/reset', [ResetPasswordController::class, 'reset'])
//        ->name('shopno.password.update');
//
//});

/* ── LOGOUT: auth middleware দরকার ── */
//Route::middleware(['web', 'auth'])->group(function () {
//    Route::post('/shopno/logout', [LoginController::class, 'logout'])
//        ->name('shopno.logout');
//});

Route::get('/', function () {
    return view('welcome');
});


Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])
    ->middleware('guest');


Route::get('/intro', 'LandingpageController@index');
Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index')->name('home');
Route::post('/install/check-db', 'HomeController@checkConnectDatabase');

// Social Login
Route::get('social-login/{provider}', 'Auth\LoginController@socialLogin');
Route::get('social-callback/{provider}', 'Auth\LoginController@socialCallBack');

// Logs
Route::get(config('admin.admin_route_prefix') . '/logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware(['auth', 'dashboard', 'system_log_view'])->name('admin.logs');

Route::get('/install', 'InstallerController@redirectToRequirement')->name('LaravelInstaller::welcome');
Route::get('/install/environment', 'InstallerController@redirectToWizard')->name('LaravelInstaller::environment');
Route::fallback([\Modules\Core\Controllers\FallbackController::class, 'FallBack']);

// Hide page update default
Route::get('/update', 'InstallerController@redirectToHome');
Route::get('/update/overview', 'InstallerController@redirectToHome');
Route::get('/update/database', 'InstallerController@redirectToHome');


// SSLCOMMERZ Start
Route::get('/example1', [SslCommerzPaymentController::class, 'exampleEasyCheckout']);
Route::get('/example2', [SslCommerzPaymentController::class, 'exampleHostedCheckout']);

//Route::get('/pay', [SslCommerzPaymentController::class, 'index'])->name('sslpay');
Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);
Route::post('/ssl/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);
//SSLCOMMERZ END


Route::get('bkash/index', [PaymentController::class, 'index']);
Route::post('bkash/payment', [PaymentController::class, 'createPayment'])->name('payment');
Route::match(['GET', 'POST'], 'success', [PaymentController::class, 'success'])->name('success');
// routes/web.php
Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');


Route::prefix('nagad')->name('nagad.')->group(function () {
    Route::post('/create', [NagadPaymentController::class, 'create'])->name('create');
    Route::get('/callback', [NagadPaymentController::class, 'callback'])->name('callback');
    Route::get('/cancel', [NagadPaymentController::class, 'cancel'])->name('cancel');
});
//require __DIR__.'/shopno_auth.php';
