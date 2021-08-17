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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/external-cmd', function() {
    Artisan::call('migrate:refresh');
    Artisan::call('db:seed');
    Artisan::call('optimize:clear');
    return "Cache is cleared";
});