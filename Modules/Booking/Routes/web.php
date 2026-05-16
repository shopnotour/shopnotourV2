<?php
use Illuminate\Support\Facades\Route;

use Modules\Booking\Controllers\BookingRequestsController;
use Modules\Booking\Controllers\FlightBookingController;
// Flight Booking
Route::group(['prefix' => 'flight'],function (){
    Route::post('/flightToCart',[FlightBookingController::class,'flightToCart'])->name('flightToCart');
    Route::get('/flightCheckout',[FlightBookingController::class,'flightCheckout'])->name('flightCheckout');
    Route::get('/booking/confirmation/{code}', [FlightBookingController::class,'confirmation'])->name('booking.confirmation');
    Route::post('booking/save-draft', [FlightBookingController::class, 'saveDraft'])
        ->name('booking.saveDraft')
        ->middleware('auth');
});

// routes/api.php or Modules/Booking/Routes/api.php
Route::get('bookings/{booking}/revalidate', [\Modules\Booking\Controllers\BookingController::class, 'revalidate']);
Route::get('cancel_booking/{id}', [\Modules\Booking\Controllers\BookingController::class, 'cancelBooking'])->name('booking.cancel');

// Booking
Route::group(['prefix'=>config('booking.booking_route_prefix')],function(){
    Route::post('/addToCart','BookingController@addToCart');
    Route::post('/doCheckout','BookingController@doCheckout')->name('booking.doCheckout');
    Route::get('/confirm/{gateway}','BookingController@confirmPayment')->name('booking.confirm-payment');
    Route::get('/cancel/{gateway}','BookingController@cancelPayment');
    Route::get('/{code}','BookingController@detail');
    Route::get('/{code}/checkout','BookingController@checkout')->name('booking.checkout');
    Route::get('/{code}/check-status','BookingController@checkStatusCheckout');

    //ical
	Route::get('/export-ical/{type}/{id}','BookingController@exportIcal')->name('booking.admin.export-ical');
    //inquiry
    Route::post('/addEnquiry','BookingController@addEnquiry');
    Route::post('/setPaidAmount','BookingController@setPaidAmount')->name('booking.setPaidAmount')->middleware(['auth']);

    Route::get('/modal/{booking}','BookingController@modal')->name('booking.modal');
    Route::get('/booking_details/{booking}','BookingController@booking_details')->name('booking.details');

});

Route::group(['prefix' => 'user', 'middleware' => ['auth']], function () {

    // Void Requests
    Route::get('/void-requests', [BookingRequestsController::class, 'voidIndex'])->name('user.void.index');
    Route::get('/void-requests/{id}/approve', [BookingRequestsController::class, 'voidApprove'])->name('user.void.approve');
    Route::get('/void-requests/{id}/reject', [BookingRequestsController::class, 'voidReject'])->name('user.void.reject');

    // Refund Requests
    Route::get('/refund-requests', [BookingRequestsController::class, 'refundIndex'])->name('user.refund.index');
    Route::get('/refund-requests/{id}/approve', [BookingRequestsController::class, 'refundApprove'])->name('user.refund.approve');
    Route::get('/refund-requests/{id}/reject', [BookingRequestsController::class, 'refundReject'])->name('user.refund.reject');

    // Reissue Requests
    Route::get('/reissue-requests', [BookingRequestsController::class, 'reissueIndex'])->name('user.reissue.index');
    Route::get('/reissue-requests/{id}/approve', [BookingRequestsController::class, 'reissueApprove'])->name('user.reissue.approve');
    Route::get('/reissue-requests/{id}/reject', [BookingRequestsController::class, 'reissueReject'])->name('user.reissue.reject');


    // Cancel Requests (View Only)
    Route::get('/cancel-requests', [BookingRequestsController::class, 'cancelIndex'])->name('user.cancel.index');

    // SSR Requests
    Route::get('/ssr-requests', [BookingRequestsController::class, 'ssrIndex'])->name('user.ssr.index');
    Route::get('/ssr-requests/{id}/approve', [BookingRequestsController::class, 'ssrApprove'])->name('user.ssr.approve');
    Route::get('/ssr-requests/{id}/reject', [BookingRequestsController::class, 'ssrReject'])->name('user.ssr.reject');
});


Route::group(['prefix'=>'gateway'],function(){
    Route::get('/confirm/{gateway}','NormalCheckoutController@confirmPayment')->name('gateway.confirm');
    Route::get('/cancel/{gateway}','NormalCheckoutController@cancelPayment')->name('gateway.cancel');
    Route::get('/info','NormalCheckoutController@showInfo')->name('gateway.info');
    Route::match(['get','post'],'/gateway_callback/{gateway}','BookingController@callbackPayment')->name('gateway.webhook');

    Route::post('/booking/action', [\Modules\Booking\Controllers\NormalCheckoutController::class, 'handleBookingAction'])->name('booking.action');
// web.php বা module routes এ
    Route::get('/booking/{code}/print-ticket', [\Modules\Booking\Controllers\NormalCheckoutController::class, 'printTicket'])->name('booking.print-ticket');
});
