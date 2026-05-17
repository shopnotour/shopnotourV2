<?php
use Illuminate\Support\Facades\Route;
use Modules\Booking\Admin\BookingManagementController;
use Modules\Booking\Admin\TransactionController;
use Modules\Booking\Admin\AllBookingController;

//Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {

// ✅ Booking Management
Route::group(['prefix' => 'bookings'], function () {
    Route::get('/', [\Modules\Booking\Admin\BookingManagementController::class, 'index'])->name('bookings.index');
    Route::get('/{id}', [\Modules\Booking\Admin\BookingManagementController::class, 'show'])->name('admin.bookings.show');
    Route::post('/{id}/set-paid', [\Modules\Booking\Admin\BookingManagementController::class, 'setPaid'])->name('admin.bookings.setPaid');
    Route::post('/bulk-action', [\Modules\Booking\Admin\BookingManagementController::class, 'bulkAction'])->name('admin.bookings.bulkAction');
    Route::get('/bookings/{id}/modal', [\Modules\Booking\Admin\BookingManagementController::class, 'modal'])->name('booking.modal');
    Route::post('/assign/tickets', [\Modules\Booking\Admin\BookingManagementController::class, 'assignTickets'])->name('assign-tickets');

    Route::get('/price-check/{id}', [\Modules\Booking\Admin\BookingManagementController::class, 'priceCheck'])->name('admin.booking.priceCheck');
    Route::post('/booking/{id}/price/update', [\Modules\Booking\Admin\BookingManagementController::class, 'updatePrice'])->name('booking.price.update');
    Route::get('/pnr-check/{id}', [\Modules\Booking\Admin\BookingManagementController::class, 'retrievePNR'])->name('admin.booking.pnrcheck');

    // Single route for both purposes
    Route::get('/admin/booking/pnr-search', [\Modules\Booking\Admin\BookingManagementController::class, 'retrievePNR'])
        ->name('pnr.booking.search');

    Route::get('/admin/booking/pnr-check/{id}', [\Modules\Booking\Admin\BookingManagementController::class, 'retrievePNR'])
        ->name('admin.booking.pnrcheck');

    Route::post('/admin/booking/assign-tickets', [BookingManagementController::class, 'assignTicketsFromPNR'])
        ->name('booking.assign.tickets');

    // ✅ Passenger passport + image update
    Route::post('/passenger/{id}/update-passport', [\Modules\Booking\Admin\BookingManagementController::class, 'updatePassengerPassport'])
        ->name('admin.passenger.updatePassport');

// ✅ Ticket Cancel (reason optional, booking_id required)
    Route::post('/{id}/ticket-cancel', [\Modules\Booking\Admin\BookingManagementController::class, 'ticketCancel'])
        ->name('admin.bookings.ticketCancel');

// ✅ PNR & Source & Status Edit
    Route::post('/{id}/update-pnr', [\Modules\Booking\Admin\BookingManagementController::class, 'updatePnr'])
        ->name('admin.bookings.updatePnr');
});

Route::get('/bookings/{id}/edit',        [\Modules\Booking\Admin\BookingManagementController::class, 'edit'])
    ->name('admin.bookings.edit');

Route::put('/bookings/{id}',             [\Modules\Booking\Admin\BookingManagementController::class, 'update'])
    ->name('admin.bookings.update');

Route::post('/bookings/{id}/duplicate',  [\Modules\Booking\Admin\BookingManagementController::class, 'duplicate'])
    ->name('admin.bookings.duplicate');


Route::group(['prefix' => 'all-booking',], function () {

    // All bookings
    Route::get('/', [AllBookingController::class, 'index'])->name('all-booking.index');

    // Issue Request
    Route::get('/issue-request', [AllBookingController::class, 'issueRequest'])->name('all-booking.issue-request');
    Route::get('/booked', [AllBookingController::class, 'booked'])->name('all-booking.booked');
    Route::get('/cancelled', [AllBookingController::class, 'cancelled'])->name('all-booking.cancelled');
    Route::get('/issued', [AllBookingController::class, 'issued'])->name('all-booking.issued');
    Route::get('/pending', [AllBookingController::class, 'pending'])->name('all-booking.pending');
    Route::get('/paid', [AllBookingController::class, 'paid'])->name('all-booking.paid');
    Route::get('/ticketed', [AllBookingController::class, 'ticketed'])->name('all-booking.ticketed');
    Route::get('/failed', [AllBookingController::class, 'failed'])->name('all-booking.failed');
    Route::get('/refunded', [AllBookingController::class, 'refunded'])->name('all-booking.refunded');
    Route::get('/{id}', [AllBookingController::class, 'show'])->name('all-booking.show');
    Route::get('/{id}/modal', [AllBookingController::class, 'modal'])->name('all-booking.modal');
    Route::post('/{id}/set-paid', [AllBookingController::class, 'setPaid'])->name('all-booking.setPaid');
    Route::post('/bulk-action', [AllBookingController::class, 'bulkAction'])->name('all-booking.bulkAction');

    // Update PNR
    Route::post('/{id}/update-pnr', [AllBookingController::class, 'updatePnr'])->name('all-booking.updatePnr');
    Route::post('/{id}/duplicate', [AllBookingController::class, 'duplicate'])->name('all-booking.duplicate');
});

Route::prefix('transactions')->group(function () {

    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('booking-report', [TransactionController::class, 'itaindex'])
        ->name('admin.itabooking-report.index');

});


    // ✅ Void Management
//Route::group(['prefix' => 'voids'], function () {
//    Route::get('/', [\Modules\Booking\Admin\VoidManagementController::class, 'index'])->name('voids.index');
//    Route::get('/{id}', [\Modules\Booking\Admin\VoidManagementController::class, 'show'])->name('admin.voids.show');
//
//    // ✅ POST routes - same format as above
//    Route::post('/{id}/approve', [\Modules\Booking\Admin\VoidManagementController::class, 'approve'])->name('admin.voids.approve');
//    Route::post('/{id}/reject', [\Modules\Booking\Admin\VoidManagementController::class, 'reject'])->name('admin.voids.reject');
//    Route::post('/bulk-action', [\Modules\Booking\Admin\VoidManagementController::class, 'bulkAction'])->name('admin.voids.bulkAction');
//});

Route::group(['prefix' => 'voids'], function () {
    Route::get('/', [\Modules\Booking\Admin\VoidManagementController::class, 'index'])->name('voids.index');
    Route::get('/{id}', [\Modules\Booking\Admin\VoidManagementController::class, 'show'])->name('admin.voids.show');
    Route::post('/{id}/set-amount', [\Modules\Booking\Admin\VoidManagementController::class, 'setAmount'])->name('admin.voids.setAmount');
    Route::post('/{id}/approve', [\Modules\Booking\Admin\VoidManagementController::class, 'approve'])->name('admin.voids.approve');
    Route::post('/{id}/reject', [\Modules\Booking\Admin\VoidManagementController::class, 'reject'])->name('admin.voids.reject');
    Route::post('/bulk-action', [\Modules\Booking\Admin\VoidManagementController::class, 'bulkAction'])->name('admin.voids.bulkAction');
});
//// ✅ Refund Management
//Route::group(['prefix' => 'refunds'], function () {
//    Route::get('/', [\Modules\Booking\Admin\RefundManagementController::class, 'index'])->name('refunds.index');
//    Route::get('/{id}', [\Modules\Booking\Admin\RefundManagementController::class, 'show'])->name('admin.refunds.show');
//    Route::post('/{id}/approve', [\Modules\Booking\Admin\RefundManagementController::class, 'approve'])->name('admin.refunds.approve');
//    Route::post('/{id}/reject', [\Modules\Booking\Admin\RefundManagementController::class, 'reject'])->name('admin.refunds.reject');
//    Route::post('/bulk-action', [\Modules\Booking\Admin\RefundManagementController::class, 'bulkAction'])->name('admin.refunds.bulkAction');
//    Route::get('/{id}/passenger-amount', [\Modules\Booking\Admin\RefundManagementController::class,'getPassengerAmount'])->name('admin.refunds.passengerAmount'); // ✅ Moved before {id}
//
//
//});

Route::group(['prefix' => 'refunds'], function () {
    Route::get('/', [\Modules\Booking\Admin\RefundManagementController::class, 'index'])->name('refunds.index');
    Route::get('/{id}', [\Modules\Booking\Admin\RefundManagementController::class, 'show'])->name('admin.refunds.show');
    Route::get('/{id}/passenger-amount', [\Modules\Booking\Admin\RefundManagementController::class, 'getPassengerAmount'])->name('admin.refunds.passengerAmount');
    Route::post('/{id}/set-amount', [\Modules\Booking\Admin\RefundManagementController::class, 'setAmount'])->name('admin.refunds.setAmount');
    Route::post('/{id}/approve', [\Modules\Booking\Admin\RefundManagementController::class, 'approve'])->name('admin.refunds.approve');
    Route::post('/{id}/reject', [\Modules\Booking\Admin\RefundManagementController::class, 'reject'])->name('admin.refunds.reject');
    Route::post('/bulk-action', [\Modules\Booking\Admin\RefundManagementController::class, 'bulkAction'])->name('admin.refunds.bulkAction');
});

// ✅ Reissue Management
//Route::group(['prefix' => 'reissues'], function () {
//    Route::get('/', [\Modules\Booking\Admin\ReissueManagementController::class, 'index'])->name('reissues.index');
//    Route::get('/{id}', [\Modules\Booking\Admin\ReissueManagementController::class, 'show'])->name('admin.reissues.show');
//    Route::post('/{id}/approve', [\Modules\Booking\Admin\ReissueManagementController::class, 'approve'])->name('admin.reissues.approve');
//    Route::post('/{id}/reject', [\Modules\Booking\Admin\ReissueManagementController::class, 'reject'])->name('admin.reissues.reject');
//    Route::post('/bulk-action', [\Modules\Booking\Admin\ReissueManagementController::class, 'bulkAction'])->name('admin.reissues.bulkAction');
//});

//Route::group(['prefix' => 'reissues'], function () {
//    Route::get('/', [\Modules\Booking\Admin\ReissueManagementController::class, 'index'])->name('reissues.index');
//    Route::get('/{id}', [\Modules\Booking\Admin\ReissueManagementController::class, 'show'])->name('admin.reissues.show');
//    Route::post('/{id}/set-amount', [\Modules\Booking\Admin\ReissueManagementController::class, 'setAmount'])->name('admin.reissues.setAmount');
//    Route::post('/{id}/approve', [\Modules\Booking\Admin\ReissueManagementController::class, 'approve'])->name('admin.reissues.approve');
//    Route::post('/{id}/reject', [\Modules\Booking\Admin\ReissueManagementController::class, 'reject'])->name('admin.reissues.reject');
//    Route::post('/bulk-action', [\Modules\Booking\Admin\ReissueManagementController::class, 'bulkAction'])->name('admin.reissues.bulkAction');
//    Route::get('/{id}/passengers', [\Modules\Booking\Admin\ReissueManagementController::class, 'getPassengers'])->name('admin.reissues.passengers');
//});

Route::group(['prefix' => 'reissues'], function () {
    Route::get('/', [\Modules\Booking\Admin\ReissueManagementController::class, 'index'])->name('reissues.index');
    Route::post('/bulk-action', [\Modules\Booking\Admin\ReissueManagementController::class, 'bulkAction'])->name('admin.reissues.bulkAction');
    Route::get('/{id}/passengers', [\Modules\Booking\Admin\ReissueManagementController::class, 'getPassengers'])->name('admin.reissues.passengers');
    Route::post('/{id}/set-amount', [\Modules\Booking\Admin\ReissueManagementController::class, 'setAmount'])->name('admin.reissues.setAmount');
    Route::post('/{id}/approve', [\Modules\Booking\Admin\ReissueManagementController::class, 'approve'])->name('admin.reissues.approve');
    Route::post('/{id}/reject', [\Modules\Booking\Admin\ReissueManagementController::class, 'reject'])->name('admin.reissues.reject');
    Route::get('/{id}', [\Modules\Booking\Admin\ReissueManagementController::class, 'show'])->name('admin.reissues.show');
});

Route::group(['prefix' => 'cancel'], function () {
    Route::get('/cancellations', [\Modules\Booking\Admin\BookingCancelController::class, 'index'])->name('cancellations.index');
    Route::post('/cancellations/{id}/update-status', [\Modules\Booking\Admin\BookingCancelController::class, 'updateStatus'])->name('cancellations.updateStatus');
});

    // ✅ SSR Management
Route::group(['prefix' => 'ssrs'], function () {
    // List
    Route::get('/', [\Modules\Booking\Admin\SSRManagementController::class, 'index'])->name('admin.ssrs.index');

    // View Details
    Route::get('/{id}', [\Modules\Booking\Admin\SSRManagementController::class, 'show'])->name('admin.ssrs.show');

    // ✅ নতুন: Admin amount সেট করবে (pending → waiting_user_approval)
    Route::post('/{id}/set-amount', [\Modules\Booking\Admin\SSRManagementController::class, 'setAmount'])->name('admin.ssrs.setAmount');

    // Final Approve (user_approved → confirmed + wallet deduct)
    Route::post('/{id}/approve', [\Modules\Booking\Admin\SSRManagementController::class, 'approve'])->name('admin.ssrs.approve');

    // Reject
    Route::post('/{id}/reject', [\Modules\Booking\Admin\SSRManagementController::class, 'reject'])->name('admin.ssrs.reject');

    // Bulk Action
    Route::post('/bulk-action', [\Modules\Booking\Admin\SSRManagementController::class, 'bulkAction'])->name('admin.ssrs.bulkAction');
});
//});
