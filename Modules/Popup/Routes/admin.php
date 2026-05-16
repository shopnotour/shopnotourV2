<?php

use \Illuminate\Support\Facades\Route;
use Modules\Popup\Admin\PopupMessageController;


Route::get('/','PopupController@index')->name('popup.admin.index');
Route::get('/create','PopupController@create')->name('popup.admin.create');
Route::get('/edit/{id}','PopupController@edit')->name('popup.admin.edit');
Route::post('/store/{id}','PopupController@store')->name('popup.admin.store');
Route::post('/bulkEdit','PopupController@bulkEdit')->name('popup.admin.bulkEdit');
Route::get('/recovery','PopupController@recovery')->name('popup.admin.recovery');

Route::get('popup-messages',                          [PopupMessageController::class, 'index'  ])->name('popup.index');
Route::post('popup-messages',                         [PopupMessageController::class, 'store'  ])->name('popup.store');
Route::post('popup-messages/{popupMessage}/update',   [PopupMessageController::class, 'update' ])->name('popup.update');
Route::post('popup-messages/{popupMessage}/delete',   [PopupMessageController::class, 'destroy'])->name('popup.destroy');
Route::post('popup-messages/{popupMessage}/toggle',   [PopupMessageController::class, 'toggle' ])->name('popup.toggle');


//Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // ... আপনার existing admin routes ...

    // Popup Messages
//Route::controller(PopupMessageController::class)
//    ->prefix('popup-messages')
//    ->name('popup.')
//    ->group(function () {
//        Route::get('/',                        'index'  )->name('index');
//        Route::post('/',                       'store'  )->name('store');
//        Route::post('/{popupMessage}/update',  'update' )->name('update');   // ✅ PUT এর বদলে POST
//        Route::post('/{popupMessage}/delete',  'destroy')->name('destroy');  // ✅ DELETE এর বদলে POST
//        Route::post('/{popupMessage}/toggle',  'toggle' )->name('toggle');   // ✅ PATCH এর বদলে POST
//    });
//});
