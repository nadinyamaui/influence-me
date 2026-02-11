<?php

use App\Http\Controllers\Auth\FacebookAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/auth/facebook', [FacebookAuthController::class, 'redirect'])->middleware('guest')->name('auth.facebook');
Route::get('/auth/facebook/callback', [FacebookAuthController::class, 'callback'])->name('auth.facebook.callback');
Route::get('/auth/instagram', [FacebookAuthController::class, 'redirect'])->middleware('guest')->name('auth.instagram');
Route::get('/auth/instagram/callback', [FacebookAuthController::class, 'callback'])->name('auth.instagram.callback');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
