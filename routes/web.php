<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/reservation/{id}', [ReservationController::class, 'showReservationDetails'])->name('reservation.details');
