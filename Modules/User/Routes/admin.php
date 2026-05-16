<?php
use \Illuminate\Support\Facades\Route;
use Modules\User\Admin\AnnouncementController;
use Modules\User\Admin\UserDetailsController;

//use Modules\User\Admin\VisitorTrackingController;

Route::get('/getForSelect2', 'UserController@getForSelect2')->name('user.admin.getForSelect2');
Route::get('/', 'UserController@index')->name('user.admin.index');
Route::get('/create', 'UserController@create')->name('user.admin.create');
Route::get('/edit/{id}', 'UserController@edit')->name('user.admin.detail');
Route::post('/store/{id}', 'UserController@store')->name('user.admin.store');
Route::post('/bulkEdit', 'UserController@bulkEdit')->name('user.admin.bulkEdit');
Route::get('/password/{id}','UserController@password')->name('user.admin.password');
Route::post('/changepass/{id}','UserController@changepass')->name('user.admin.changepass');
Route::get('/verify-email/{id}','UserController@verifyEmail')->name('user.admin.verifyEmail');

Route::post('/set-reference/{id}', ['UserController@setReference'])->name('user.admin.setReference');
Route::get('/update/{id}', 'UserController@update')->name('user.admin.details');

Route::get('/userUpgradeRequest', 'UserController@userUpgradeRequest')->name('user.admin.upgrade');
Route::get('/upgrade/{id}','UserController@userUpgradeRequestApprovedId')->name('user.admin.upgradeId');
Route::post('/userUpgradeRequestApproved', 'UserController@userUpgradeRequestApproved')->name('user.admin.userUpgradeRequestApproved');

Route::get('admin/users', [UserDetailsController::class, 'index'])->name('admin.users.index');
Route::get('admin/users/{id}/bookings', [UserDetailsController::class, 'bookings'])->name('admin.users.bookings');
Route::get('admin/users/{id}/transactions', [UserDetailsController::class, 'transactions'])->name('admin.users.transactions');
Route::get('admin/users/{id}/tickets', [UserDetailsController::class, 'tickets'])->name('admin.users.tickets');
Route::get('admin/users/{userId}/bookings/{bookingId}', [UserDetailsController::class, 'bookingDetail'])
    ->name('admin.users.booking.detail');

Route::group(['prefix' => 'bonus'], function () {
    Route::get('/', 'BonusController@index')->name('user.admin.bonus.index');
    Route::post('/store', 'BonusController@store')->name('user.admin.bonus.store');
    Route::get('/transactions', 'BonusController@transactions')->name('user.admin.bonus.transactions');
});
// Announcement Management Routes
Route::prefix('announcements')->group(function () {
    Route::resource('/', AnnouncementController::class)
        ->names('announcements')
        ->parameters(['' => 'announcement']);

    Route::patch('/{announcement}/toggle', [AnnouncementController::class, 'toggleActive'])
        ->name('admin.announcements.toggle');

    Route::post('/bulk-delete', [AnnouncementController::class, 'bulkDelete'])
        ->name('admin.announcements.bulk-delete');
});

Route::prefix('visitor')->name('visitor.')->group(function () {
    Route::post('/page-enter', 'VisitorTrackingController@pageEnter')->name('page.enter');
    Route::post('/page-exit',  'VisitorTrackingController@pageExit')->name('page.exit');
    Route::post('/activity',   'VisitorTrackingController@trackActivity')->name('activity');
});

Route::group(['prefix' => 'role'], function () {
    Route::get('/', 'RoleController@index')->name('user.admin.role.index');
    Route::get('/verifyFields', 'RoleController@verifyFields')->name('user.admin.role.verifyFields');
    Route::get('/permission_matrix', 'RoleController@permission_matrix')->name('user.admin.role.permission_matrix');
    Route::get('/create', 'RoleController@create')->name('user.admin.role.create');
    Route::get('/edit/{id}', 'RoleController@edit')->name('user.admin.role.detail');
    Route::post('/store/{id}', 'RoleController@store')->name('user.admin.role.store');
    Route::post('/verifyFieldsStore', 'RoleController@verifyFieldsStore')->name('user.admin.role.verifyFieldsStore');
    Route::get('/verifyFieldsEdit/{id}', 'RoleController@verifyFieldsEdit')->name('user.admin.role.verifyFieldsEdit');
    Route::post('/bulkEdit', 'RoleController@bulkEdit')->name('user.admin.role.bulkEdit');
    Route::post('/save_permissions', 'RoleController@save_permissions')->name('user.admin.role.save_permissions');
    Route::get('/getForSelect2','RoleController@getForSelect2')->name('user.admin.role.getForSelect2');

});

Route::group(['prefix' => 'verification'], function () {
    Route::get('/', 'VerificationController@index')->name('user.admin.verification.index');
    Route::get('detail/{id}', 'VerificationController@detail')->name('user.admin.verification.detail');
    Route::post('store/{id}', 'VerificationController@store')->name('user.admin.verification.store');
    Route::post('/bulkEdit', 'VerificationController@bulkEdit')->name('user.admin.verification.bulkEdit');
});


Route::group(['prefix'=>'wallet'],function (){
    Route::get('/add-credit/{id}','WalletController@addCredit')->name('user.admin.wallet.addCredit');
    Route::post('/add-credit/{id}','WalletController@store')->name('user.admin.wallet.store');
    Route::get('/credit_list/{id}','WalletController@creditList')->name('user.admin.wallet.list');
    Route::get('/credit_list_update/{id}','WalletController@statusUpdate')->name('user.admin.wallet.update');
    Route::get('/transactions','WalletController@transactions')->name('user.admin.wallet.transactions');

    Route::post('/transaction/{id}/update-remarks', 'WalletController@updateRemarks')  ->name('user.admin.wallet.update.remarks');
    Route::post('/transaction/{id}/update-amount',  'WalletController@updateAmount')   ->name('user.admin.wallet.update.amount');
    Route::delete('/transaction/{id}/delete',       'WalletController@deleteTransaction')->name('user.admin.wallet.delete');


    Route::get('/status_filter','WalletController@status_filter')->name('user.admin.wallet.status_filter');
    Route::get('/report','WalletController@report')->name('user.admin.wallet.report');
    Route::post('/reportBulkEdit','WalletController@reportBulkEdit')->name('user.admin.wallet.reportBulkEdit');

});


Route::group(['prefix' => 'subscriber'], function () {
    Route::get('/', 'SubscriberController@index')->name('user.admin.subscriber.index');
    Route::get('edit/{id}', 'SubscriberController@edit')->name('user.admin.subscriber.edit');
    Route::post('store', 'SubscriberController@store')->name('user.admin.subscriber.store');
    Route::post('/bulkEdit', 'SubscriberController@bulkEdit')->name('user.admin.subscriber.bulkEdit');
    Route::get('export', 'SubscriberController@export')->name('user.admin.subscriber.export');
});

Route::get('/export', 'UserController@export')->name('user.admin.export');


Route::group(['prefix'=>'plan'],function (){
    Route::get('/','PlanController@index')->name('user.admin.plan.index');
    Route::get('/edit/{id}','PlanController@edit')->name('user.admin.plan.edit');
    Route::post('/store/{id}','PlanController@store')->name('user.admin.plan.store');
    Route::post('/bulkEdit','PlanController@bulkEdit')->name('user.admin.plan.bulkEdit');
    Route::get('/getForSelect2','PlanController@getForSelect2')->name('user.admin.plan.getForSelect2');
});

Route::group(['prefix'=>'plan-request'],function (){
    Route::get('/','PlanRequestController@index')->name('user.admin.plan_request.index');
    Route::post('/bulkEdit','PlanRequestController@bulkEdit')->name('user.admin.plan_request.bulkEdit');
});
Route::group(['prefix'=>'plan-report'],function (){
    Route::get('/','PlanReportController@index')->name('user.admin.plan_report.index');
    Route::post('/bulkEdit','PlanReportController@bulkEdit')->name('user.admin.plan_report.bulkEdit');
});

Route::group(['prefix'     => 'admin/marketing','middleware' => ['auth'],], function () {
    Route::get('dashboard', [\Modules\User\Admin\MarketingDashboardController::class, 'index'])
        ->name('admin.marketing.dashboard');


    // Search Sessions — Level 1 (user list)
    Route::get('search-sessions', [\Modules\User\Admin\MarketingDashboardController::class, 'searchSessions'])
        ->name('admin.marketing.search.sessions');

    // Search Sessions — Level 2 (single user detail)
    Route::get('search-sessions/{userId}', [\Modules\User\Admin\MarketingDashboardController::class, 'searchSessionDetail'])
        ->name('admin.marketing.search.session.detail');

    // Select Sessions — Level 1 (user list)
    Route::get('select-sessions', [\Modules\User\Admin\MarketingDashboardController::class, 'selectSessions'])
        ->name('admin.marketing.select.sessions');
    // Search Sessions — Bulk delete (must be before {userId})
    Route::post('search-sessions/delete', [\Modules\User\Admin\MarketingDashboardController::class, 'deleteSearchSessions'])
        ->name('admin.marketing.search.sessions.delete');
// Select Sessions — Bulk delete (must be before {userId})
    Route::post('select-sessions/delete', [\Modules\User\Admin\MarketingDashboardController::class, 'deleteSelectSessions'])
        ->name('admin.marketing.select.sessions.delete');

    // Select Sessions — Level 2 (single user detail)
    Route::get('select-sessions/{userId}', [\Modules\User\Admin\MarketingDashboardController::class, 'selectSessionDetail'])
        ->name('admin.marketing.select.session.detail');
});
