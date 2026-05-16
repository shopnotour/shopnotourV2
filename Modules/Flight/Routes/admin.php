<?php

use \Illuminate\Support\Facades\Route;


Route::get('/','FlightController@index')->name('flight.admin.index');
Route::get('/create','FlightController@create')->name('flight.admin.create');
Route::get('/edit/{id}','FlightController@edit')->name('flight.admin.edit');
Route::post('/store/{id}','FlightController@store')->name('flight.admin.store');
Route::post('/bulkEdit','FlightController@bulkEdit')->name('flight.admin.bulkEdit');
Route::get('/recovery','FlightController@recovery')->name('flight.admin.recovery');

Route::group(['prefix'=>'{flight_id}/flight-seat'],function (){
    Route::get('/','FlightSeatController@index')->name('flight.admin.flight.seat.index');
    Route::get('edit/{id}','FlightSeatController@edit')->name('flight.admin.flight.seat.edit');
    Route::post('store/{id}','FlightSeatController@store')->name('flight.admin.flight.seat.store');
    Route::post('/bulkEdit','FlightSeatController@bulkEdit')->name('flight.admin.flight.seat.bulkEdit');
});
Route::group(['prefix'=>'airline'],function (){
    Route::get('/','AirlineController@index')->name('flight.admin.airline.index');
    Route::get('edit/{id}','AirlineController@edit')->name('flight.admin.airline.edit');
    Route::post('store/{id}','AirlineController@store')->name('flight.admin.airline.store');
    Route::post('/bulkEdit','AirlineController@bulkEdit')->name('flight.admin.airline.bulkEdit');
});
Route::group(['prefix'=>'gds'],function (){
    Route::get('/','GdsController@index')->name('flight.admin.gds.index');
    Route::get('edit/{id}','GdsController@edit')->name('flight.admin.gds.edit');
    Route::post('store/{id}','GdsController@store')->name('flight.admin.gds.store');
    Route::post('/bulkEdit','GdsController@bulkEdit')->name('flight.admin.gds.bulkEdit');
});
Route::group(['prefix'=>'airport'],function (){
    Route::get('/','AirportController@index')->name('flight.admin.airport.index');
    Route::get('edit/{id}','AirportController@edit')->name('flight.admin.airport.edit');
    Route::post('store/{id}','AirportController@store')->name('flight.admin.airport.store');
    Route::post('/bulkEdit','AirportController@bulkEdit')->name('flight.admin.airport.bulkEdit');
    Route::get('/import-iata','AirportController@importIATA')->name('flight.admin.airport.importIATA')->middleware('signed');

});
Route::group(['prefix'=>'seat-type'],function (){
    Route::get('/','SeatTypeController@index')->name('flight.admin.seat_type.index');
    Route::get('edit/{id}','SeatTypeController@edit')->name('flight.admin.seat_type.edit');
    Route::post('store/{id}','SeatTypeController@store')->name('flight.admin.seat_type.store');
    Route::post('/bulkEdit','SeatTypeController@bulkEdit')->name('flight.admin.seat_type.bulkEdit');

});
Route::group(['prefix'=>'attribute'],function (){
    Route::get('/','AttributeController@index')->name('flight.admin.attribute.index');
    Route::get('edit/{id}','AttributeController@edit')->name('flight.admin.attribute.edit');
    Route::post('store/{id}','AttributeController@store')->name('flight.admin.attribute.store');
    Route::post('/editAttrBulk','AttributeController@editAttrBulk')->name('flight.admin.attribute.bulkEdit');

    Route::get('terms/{id}','AttributeController@terms')->name('flight.admin.attribute.term.index');
    Route::get('term_edit/{id}','AttributeController@term_edit')->name('flight.admin.attribute.term.edit');
    Route::match(['get','post'],'term_store','AttributeController@term_store')->name('flight.admin.attribute.term.store');
    Route::post('/editTermBulk','AttributeController@editTermBulk')->name('flight.admin.attribute.editTermBulk');
});



Route::group(['prefix' => 'discount'], function() {

    // Standard CRUD routes
    Route::get('/', 'DiscountController@index')->name('flight.admin.discount.index');
    Route::get('/create', 'DiscountController@create')->name('flight.admin.discount.create');
    Route::post('/store', 'DiscountController@store')->name('flight.admin.discount.store');
    Route::get('/edit/{id}', 'DiscountController@edit')->name('flight.admin.discount.edit');
    Route::put('/update/{id}', 'DiscountController@update')->name('flight.admin.discount.update');
    Route::get('/status/{id}', 'DiscountController@statusChange')->name('flight.admin.discount.status');
    Route::delete('/delete/{id}', 'DiscountController@destroy')->name('flight.admin.discount.destroy');

    // ===== NEW BULK OPERATIONS ROUTES =====

    /**
     * Show bulk operations form
     * GET: /discount/bulk-form
     * Returns: HTML form for the selected action
     * Query Params: action, ids
     */
    Route::get('/bulk-form', 'DiscountController@showBulkForm')
        ->name('flight.admin.discount.show-bulk-form');

    /**
     * Execute bulk operations
     * POST: /discount/bulk-action
     * Handles: delete, copy, status, update-valid-dates, change-source
     */
    Route::post('/bulk-action', 'DiscountController@bulkAction')
        ->name('flight.admin.discount.bulk-action');

    /**
     * Individual bulk operations (optional - for direct links)
     * DELETE: /discount/bulk-delete
     * POST: /discount/bulk-copy
     * PATCH: /discount/bulk-status
     * PATCH: /discount/bulk-dates
     * PATCH: /discount/bulk-source
     */
    Route::delete('/bulk-delete', 'DiscountController@bulkDelete')
        ->name('flight.admin.discount.bulk-delete');

    Route::post('/bulk-copy', 'DiscountController@bulkCopy')
        ->name('flight.admin.discount.bulk-copy');

    Route::patch('/bulk-status', 'DiscountController@bulkStatus')
        ->name('flight.admin.discount.bulk-status');

    Route::patch('/bulk-dates', 'DiscountController@bulkUpdateValidDates')
        ->name('flight.admin.discount.bulk-dates');

    Route::patch('/bulk-source', 'DiscountController@bulkChangeSource')
        ->name('flight.admin.discount.bulk-source');
});


Route::group(['prefix' => 'admin/module/flight'], function() {
    Route::get('flight-charges', [\Modules\Flight\Admin\FlightChargeController::class, 'index'])
        ->name('flight.admin.flight_charges.index');

    Route::get('flight-charges/{id}/edit', [\Modules\Flight\Admin\FlightChargeController::class, 'edit'])
        ->name('flight.admin.flight_charges.edit');

    Route::put('flight-charges/{id}', [\Modules\Flight\Admin\FlightChargeController::class, 'update'])
        ->name('flight.admin.flight_charges.update');
});

Route::group(['prefix'=>'api'],function (){
    Route::get('/','FlightApiController@index')->name('flight.admin.api.index');
    Route::get('/create','FlightApiController@create')->name('flight.admin.api.create');
    Route::get('/manage','FlightApiController@manage')->name('flight.admin.api.manage');
    Route::post('/api-settings/update', 'FlightApiController@updateSettings')->name('flight.admin.api.updateSettings');
    Route::post('/store', 'FlightApiController@store')->name('flight.admin.api.store');
    Route::put('/update/{id}', 'FlightApiController@update')->name('flight.admin.api.update');
    Route::get('/edit/{id}', 'FlightApiController@edit')->name('flight.admin.api.edit');
    Route::post('/delete/{id}', 'FlightApiController@destroy')->name('flight.admin.api.destroy');
    Route::post('/bulkEdit', 'FlightApiController@bulkEdit')->name('flight.admin.api.bulkEdit');
});

Route::group(['prefix'=>'calling-structure'],function (){
    Route::get('/','CallingStructureController@index')->name('flight.admin.calling.index');
    Route::get('/create','CallingStructureController@create')->name('flight.admin.calling.create');
    Route::post('/store', 'CallingStructureController@store')->name('flight.admin.calling.store');
    Route::get('/edit/{id}', 'CallingStructureController@edit')->name('flight.admin.calling.edit');
    Route::get('/status/{id}', 'CallingStructureController@statusChange')->name('flight.admin.calling.status');
    Route::post('/update/{id}', 'CallingStructureController@update')->name('flight.admin.calling.update');
    Route::delete('/delete/{id}', 'CallingStructureController@destroy')->name('flight.admin.calling.destroy');
    Route::post('/bulkEdit', 'CallingStructureController@bulkEdit')->name('flight.admin.calling.bulkEdit');
});


