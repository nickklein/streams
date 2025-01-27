<?php

use Illuminate\Support\Facades\Route;
use NickKlein\Streams\Controllers\StreamController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/streams', [StreamController::class, 'index'])->name('streams.index');
    Route::get('/streams/{id}', [StreamController::class, 'getProfile'])->name('streams.get-profile');
});
