<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ModeController;
use App\Http\Controllers\Api\PenaltyController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\SportController;
use App\Http\Controllers\Api\SportCourtController;
use App\Http\Controllers\Api\ScheduleController;
use App\Models\sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
 */


//Rutas para el controlador SportController 
//Usadas por El admin y el de consulta por el miembro 
Route::get('/sports', [SportController::class, 'index']);
Route::get('/sports/{id}', [SportController::class, 'show']);
Route::post('/sports', [SportController::class, 'store']);
Route::put('/sports/{id}', [SportController::class, 'update']);
Route::delete('/sports/{id}', [SportController::class, 'destroy']);

//Rutas para el Controlador SportCourtController
//Administradas por el admin y consultadas por los miembros
Route::get('/sports/{sport}/courts', [SportCourtController::class, 'index']);

Route::post('/sports/{sport}/courts', [SportCourtController::class, 'store']);

#todas las canchas
Route::get('/sportcourt', [SportcourtController::class, 'all']);
#actualizar imagen de las canchas
Route::post('/sport/upload/{id}', [SportController::class, 'imageUpload']);





//Rutas para el controlador MemberController

//Ruta para visualizar todos los miembros
Route::get('/members', [MemberController::class, 'index']);
//Ruta para visualizar un solo miembro
Route::get('/members/{id}', [MemberController::class, 'show']);
//Ruta para registrar un miembro
Route::post('/members', [MemberController::class, 'store']);
//Ruta para loguear un miembro
Route::post('/members/login', [MemberController::class, 'login']);
//Ruta para cerrar sesión de un miembro
Route::post('/members/logout', [MemberController::class, 'logout']);
//Ruta para eliminar un miembro
Route::delete('/members/{id}', [MemberController::class, 'destroy']);
//Ruta para actualizar un miembro
Route::put('/members/{id}', [MemberController::class, 'update']);


//Rutas para el controlador ModeController
//Ruta para visualizar todas las modalidades
Route::get('/modes', [ModeController::class, 'all']);
//Rutas para visualizar una sola modalidad
Route::get('/modes/{id}', [ModeController::class, 'show']);
//Ruta para visualizar todas las modalidades de una cancha
Route::get('/courts/{court}/modes', [ModeController::class, 'index']);
//Ruta para registrar una modalidad
Route::post('/courts/{court}/modes', [ModeController::class, 'store']);
//Ruta para actualizar una modalidad
Route::put('/courts/{court}/modes/{id}', [ModeController::class, 'update']);
//Ruta para eliminar una modalidad
Route::delete('/modes/{id}', [ModeController::class, 'destroy']);



//rutas para los schedules
//obtener todos los hoarios
Route::get('/schedules', [ScheduleController::class, 'all']);
//obtener un horario por el id
Route::get('/schedules/{id}', [ScheduleController::class, 'index']);
//registrar un horario
Route::post('/schedules/{court}/court/{mode}/mode', [ScheduleController::class, 'storage']);
#Actualizar un horario
Route::put('/schedules/{court}/court/{mode}/mode/{id}', [ScheduleController::class, 'update']);
#eliminar un horario
Route::delete('schedules/{id}', [ScheduleController::class, 'destroy']);


//Rutas para el administrador

#obtener todos los administradores 
Route::get('/admin', [AdminController::class, 'all']);
#obtener el admin con su id
Route::get('/admin/{id}', [AdminController::class, 'show']);
#registrar a un admin
Route::post('admin', [AdminController::class, 'store']);
#actualizar un addministrador
Route::put('admin/{id}', [AdminController::class, 'update']);
#eliminar a un administrador 
Route::delete('admin/{id}', [AdminController::class, 'destroy']);


//Rutas de las resrvaciones
#obtener todas las reservaciones
Route::get('/reservation', [ReservationController::class, 'all']);
#obtener una reservación por su id
Route::get('/reservation/{id}', [ReservationController::class, 'show']);
#obtener un reservación hecha por un miembro 
Route::get('/reservation/{id}/member', [ReservationController::class, 'memberReservations']);
#Regitrar una reservación
Route::post('/reservation/{member}/{schedule}/registrer', [ReservationController::class, 'storage']);
#Cancelar una reservación
Route::put('/reservation/{id}', [ReservationController::class, 'cancelReservation']);


//Rutas de las penalizaciones 
#obtener todas la penalizaciones 
Route::get('/penalties', [PenaltyController::class, 'all']);
#obtener una penalización por su id
Route::get('/penalties/{id}', [PenaltyController::class, 'show']);
