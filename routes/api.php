<?php

use App\Http\Controllers\Api\ModeController;
use App\Http\Controllers\Api\SportController;
use App\Http\Controllers\Api\SportCourtController;
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


//Rutas para el controlador ModeController

Route::get('/courts/{court}/modes', [ModeController::class, 'index']);
Route::post('/courts/{court}/modes', [ModeController::class, 'store']);

