<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\modeController;
use App\Http\Controllers\Api\PenaltyController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SportController;
use App\Http\Controllers\Api\SportCourtController;
use App\Http\Controllers\Api\SuggestionsController;
use App\Http\Controllers\Api\SvgController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\NotificationController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/* Iniciar y cerrar Sesión de un usuario */
// Crear un nuevo usuario
/* Route::post('/users', [AuthController::class, 'store']);
 */ // Iniciar sesión
Route::post('/login', [AuthController::class, 'login']);
// Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


/* Usuarios*/

// Consultar los usuarios
Route::get('/users', [UserController::class, 'index']);
// Consultar un usuario por id
Route::get('/user/{id}', [UserController::class, 'show']);
// Crear un nuevo usuario
Route::post('/users', [UserController::class, 'store']);
// Actualizar un usuario
Route::put('/user/{id}', [UserController::class, 'update']);
// Eliminar un usuario
Route::delete('/user/{id}', [UserController::class, 'destroy']);

// Buscar usuarios
Route::get('/users/search', [UserController::class, 'Search']);
//Buscar usuarios por rol member
Route::get('/users/search/member', [UserController::class, 'SearchMember']);

/* Deportes */

// Consultar los deportes
Route::get('/sports', [SportController::class, 'index']);
// Consultar un deporte por id
Route::get('/sport/{id}', [SportController::class, 'show']);
// Crear un nuevo deporte
Route::post('/sport', [SportController::class, 'store'])/* ->middleware('auth:sanctum') */;
// Actualizar un deporte
Route::put('/sport/update/{id}', [SportController::class, 'update']);
// Eliminar un deporte
Route::delete('/sport/delete/{id}', [SportController::class, 'destroy']);

/* Canchas */
// Consultar las canchas
Route::get('/courts', [SportCourtController::class, 'all']);
// Consultar las canchas de un deporte específico
Route::get('/sport/{sport_id}/courts', [SportCourtController::class, 'index']);
// Consultar las canchas con los deportes
Route::get('/courts/sports', [SportCourtController::class, 'showAllCourts']);
// Crear una nueva cancha para asignarla a un deporte
Route::post('/sport/{sport_id}/court', [SportCourtController::class, 'store']);
// Actualizar una cancha
Route::put('/court/{id}', [SportCourtController::class, 'update']);
// Eliminar una cancha
Route::delete('/court/{id}', [SportCourtController::class, 'destroy']);




/* Modalidad */
// Consultar las modalidades
Route::get('/modalities', [modeController::class, 'all']);
// Consultar las modalidades por el id
Route::get('/mode/{id}', [modeController::class, 'show']);
// Consultar las modalidades de un deporte
Route::get('/sport/modes/{id}', [modeController::class, 'showModesBySport']);
// Crear una nueva modalidad
Route::post('/mode', [modeController::class, 'store']);
// Actualizar una modalidad
Route::put('/mode/{id}', [modeController::class, 'update'])->middleware('auth:sanctum');
// Eliminar una modalidad
Route::delete('/mode/{id}', [modeController::class, 'destroy'])->middleware('auth:sanctum');




/* Horarios */
// Consultar los horarios
Route::get('/schedules', [ScheduleController::class, 'index']);
// Consultar un horario por id
Route::get('/schedule/{id}', [ScheduleController::class, 'show']);
// Registrar un nuevo horario
Route::post('/schedule', [ScheduleController::class, 'storage']);
// Actualizar un horario
Route::put('/schedule/{id}', [ScheduleController::class, 'update'])->middleware('auth:sanctum');
// Eliminar un horario
Route::delete('/schedule/{id}', [ScheduleController::class, 'destroy'])->middleware('auth:sanctum');
/* Consulta de los horarios de un deporte especifico */
Route::get('/schedule/sport/{id}', [ScheduleController::class, 'getSchedulesBySport']);


/* Reservas */
// Consultar las reservas
Route::get('/reservations', [ReservationController::class, 'all']);
// Consutar las reservas por id 
Route::get('/reservations/{id}', [ReservationController::class, 'show']);
// Consultar las reservas por medio del id del usuario
Route::get('/user/reservation/{id}', [ReservationController::class, 'memberReservations']);
// Realizar una reservación
Route::post('/reservation/registrer', [ReservationController::class, 'storage']);
// Cancela una reservacion
Route::put('cancel/reservation/{id}', [ReservationController::class, 'cancelReservation']);
//Ruta para obtener los datos para el formulario de las reservaciones; 
Route::get('/reservations/options/{id}', [ReservationController::class, 'getReservationOptions']);
/* Reservacion del dia */
Route::get('/reservations/today/{id}', [ReservationController::class, 'todayReservations']);


/* Historial */
//Ruta para obtener el historial de un miembro
Route::get('/history/{id}', [HistoryController::class, 'show']);




/* Sugerencias */
//Ruta para crear una sugerencia
Route::post('/suggestions', [SuggestionsController::class, 'createSuggestion']);
//Ruta para obtener todas las sugerencias
Route::get('/suggestions', [suggestionsController::class, 'getAllSuggestions']);
//Ruta para obtener las sugerencias de un miembro
Route::get('/suggestions/member/{id}', [suggestionsController::class, 'getSuggestionByMember']);
//Ruta para obtener una sugerencia por el issuse
Route::get('/suggestions/issuse/{issuse}', [suggestionsController::class, 'getSuggestionByIssuse']);


//Penalizaciones 
Route::get('/penalties', [PenaltyController::class, 'index']);
Route::get('/penalty/{id}', [PenaltyController::class, 'show']);




/*  Reportes */
Route::get('/reports/reservations', [ReportController::class, 'GenerateReport']);

/* Grafica */
Route::get('/reports/sport-usage', [SvgController::class, 'generateSvgChart']);


/* forgot de contraseñas  */
// En routes/api.php
Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
/* reset de contraseñas  */
Route::post('/auth/reset-password', [ResetPasswordController::class, 'reset']);


/* Notificaciones */

Route::post('/save-push-token', [NotificationController::class, 'savePushToken']);
Route::post('/send-test-notification', [NotificationController::class, 'sendTestNotification']);