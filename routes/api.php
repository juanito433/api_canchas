<?php

use App\Http\Controllers\Api\ModeController;
use App\Http\Controllers\Api\SportController;
use App\Http\Controllers\Api\SportCourtController;
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


//Rutas para el controlador ModeController

Route::get('/courts/{court}/modes', [ModeController::class, 'index']);
Route::post('/courts/{court}/modes', [ModeController::class, 'store']);



Route::get('/canchas', [SportcourtController::class, 'getCanchas']);
Route::post('/sport/upload/{id}', function (Request $request, $id) {
    $sport = sport::find($id);

    if (!$sport) {
        return response()->json(['message' => 'Deporte no encontrado'], 404);
    }

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $path = $image->store('sports_images', 'public'); // Guardar en storage/app/public/sports_images

        // Guardar ruta en la BD
        $sport->image = url("storage/$path");
        $sport->save();

        return response()->json([
            'message' => 'Imagen subida correctamente',
            'image_url' => $sport->image
        ], 200);
    }

    return response()->json(['message' => 'No se recibió una imagen'], 400);
});
