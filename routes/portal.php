<?php

use Illuminate\Support\Facades\Route;

Route::prefix('portal')->middleware(['guest:client'])->group(function (): void {
});

Route::prefix('portal')->middleware(['auth:client'])->group(function (): void {
});
