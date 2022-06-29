<?php

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

//Route::prefix('base')->group(function() {
//    Route::get('/', 'BaseController@index');
//});

Route::prefix('base')->middleware(['whf.private'])->group(function() {
    Route::get('/terms', 'TermController@index');
});

//Route::group(['namespace' => '\Modules\Base\Proxies\Controllers'], function()
//{
//    Route::prefix('base')->group(function() {
//        Route::get('/terms', 'TermController@index');
//    });
//
////    Route::get('/', [
////        'as' => 'api.home',
////        'uses' => 'PagesController@index'
////    ]);
//
//});
