<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 7/1/2019
 * Time: 10:02 AM
 */
use Illuminate\Support\Facades\Route;
use Modules\Booking\Controllers\BookingController;

Route::group(['prefix' => 'booking'],function (){
    Route::get('/','BookingController@index')->name('report.admin.booking');
    Route::get('/invoice/{id}','BookingController@invoice')->name('report.admin.booking.invoice');
    Route::get('/ticket/{id}','BookingController@ticket')->name('report.admin.booking.ticket');
    Route::get('/email_preview/{id}','BookingController@email_preview')->name('report.admin.booking.email_preview');
    Route::post('/bulkEdit','BookingController@bulkEdit')->name('report.admin.booking.bulkEdit');
    Route::get('/invoice-booking/{id}','\Modules\Booking\Controllers\BookingController@invoiceBooking')->name('report.admin.booking.invoice.booking');
    Route::get('/issue-ticket/{id}', '\Modules\Booking\Controllers\BookingController@issueTickets')->name('report.admin.booking.issueTicket');
    Route::get('/cancel-ticket/{id}', '\Modules\Booking\Controllers\BookingController@cancelTickets')->name('report.admin.booking.cancelTicket');

    Route::get('/admin/booking/pnr-search', [BookingController::class, 'pnrSearchPage'])->name('booking.pnr.search.page');

    Route::get('/admin/booking/pnr-details', [BookingController::class, 'showBookingDetails'])->name('booking.pnr.search');
    // Route 2: Direct view by PNR (from list/dropdown)
    Route::get('/booking/pnr/{pnr}', [BookingController::class, 'showBookingDetails'])
        ->name('booking.pnr.view');
    // New test route for getPnr method
    Route::get('/get-pnr/{pnr}', [\Modules\Booking\Controllers\BookingController::class,'getPnr'])->name('report.admin.booking.getPnr');
    Route::get('/get_booking_details/{id}', [\Modules\Booking\Controllers\BookingController::class,'get_booking_details'])->name('report.admin.booking.booking_details');

});

// routes/api.php or Modules/Booking/Routes/api.php
//Route::get('bookings/{booking}/revalidate', [BookingRevalidationController::class, 'revalidate']);

Route::get('/enquiry','EnquiryController@index')->name('report.admin.enquiry.index');

Route::post('/enquiry/bulkEdit','EnquiryController@bulkEdit')->name('report.admin.enquiry.bulkEdit');

Route::get('/enquiry/{enquiry}/reply','EnquiryController@reply')->name('report.admin.enquiry.reply');
Route::post('/enquiry/{enquiry}/reply/store','EnquiryController@replyStore')->name('report.admin.enquiry.replyStore');


Route::get('/statistic','StatisticController@index')->name('report.admin.statistic.index');
Route::get('/wallet_statistic','StatisticController@wallet_statistic')->name('report.admin.statistic.wallet_statistic');
Route::match(['get','post'],'/statistic/reloadChart','StatisticController@reloadChart')->name('report.admin.statistic.reloadChart');


Route::get('/getPnr/{id}','BookingController@getPnr')->name('report.admin.booking.getPnr');
