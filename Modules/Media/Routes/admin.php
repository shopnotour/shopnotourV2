<?php
use Illuminate\Support\Facades\Route;
Route::get('/','MediaController@index')->name('media.admin.index');
Route::post('/getLists','MediaController@getLists')->name('media.admin.getLists')->withoutMiddleware('dashboard');

Route::post('/edit_image','MediaController@editImage')->name('media.admin.edit.image');


Route::group(['prefix' => 'admin/banner', 'middleware' => ['auth']], function () {
    Route::get('/',              'BannerController@index')->name('banner.admin.index');
    Route::get('/create',        'BannerController@create')->name('banner.admin.create');
    Route::get('/edit/{id}',     'BannerController@edit')->name('banner.admin.edit');
    Route::post('/store/{id}',   'BannerController@store')->name('banner.admin.store');
    Route::post('/bulkEdit',     'BannerController@bulkEdit')->name('banner.admin.bulkEdit');
});
