<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('privacy-policy', [\App\Http\Controllers\SettingController::class, 'privacyPolicy'])->name('privacyPolicy');
Route::get('terms-conditions', [\App\Http\Controllers\SettingController::class, 'termsConditions'])->name('termsConditions');
Route::get('/contact', [\App\Http\Controllers\SettingController::class, 'contact'])->name('contactus');
Route::get('/share/provider/{id}', [\App\Http\Controllers\AppDownloadController::class, 'showProvider'])->name('app.download.provider');
