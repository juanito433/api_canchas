<?php

use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\SportCourtController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/reservation/{id}', [ReservationController::class, 'showReservationDetails'])->name('reservation.details');
Route::get('/profile/{id}/member', [MemberController::class, 'showMember']);
Route::get('/court', [SportCourtController::class, 'showAllCourts']);


Route::get('/reservations/{id}', [ReservationController::class, 'showReservationDetails']);
