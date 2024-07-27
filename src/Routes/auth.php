<?php

use Illuminate\Support\Facades\Route;
use NickKlein\Stream\Controllers\StreamController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/streams', [StreamController::class, 'index'])->name('streams.index');
});
