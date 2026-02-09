<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.landing', ['title' => 'Influence Me — The Influencer OS'])->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
