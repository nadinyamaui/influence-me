<?php

use App\Http\Controllers\Auth\FacebookAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');

Route::get('/auth/facebook', [FacebookAuthController::class, 'redirect'])->middleware('guest')->name('auth.facebook');
Route::get('/auth/facebook/callback', [FacebookAuthController::class, 'callback'])->name('auth.facebook.callback');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/portal.php';
