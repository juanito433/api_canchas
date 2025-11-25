<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\mode;
use App\Models\schedules;
use App\Models\sport;
use App\Models\sportcourt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class modeController extends Controller
{
    //listar todas las modalidades
    public function all()
    {
        $mode = mode::all();
        return response()->json($mode, 200);
    }

    //listar todas la modalidades de una cancha especifica
    public function index($sportcourt_id)
    {
        $sportcourt = sportcourt::find($sportcourt_id);

        if (!$sportcourt) {
            return response()->json([
                'message' => 'Modalidad no encontrado',
                'status' => 404,
            ], 404);
        }

        return response()->json($sportcourt->courts, 200);
    }

    /* Todas las modalidades de un deporte*/
    public function showModesBySport(Request $request)
    {
        $sport = sport::find($request->sport_id);

        if (is_null($sport)) {
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ], 404);
        }
        $sportcourts =  sportcourt::where('sport_id', $sport->id)->get();

        if ($sportcourts->isEmpty()) {
            return response()->json([
                'message' => 'No hay canchas registradas para este deporte',
                'status' => 404,
            ], 404);
        }

        $schedule = schedules::whereIn('sportcourt_id', $sportcourts->pluck('id'))->get();

        if ($schedule->isEmpty()) {
            return response()->json([
                'message' => 'No hay modalidades registradas para este deporte',
                'status' => 404,
            ], 404);
        }

        $modesid = $schedule->pluck('mode_id')->unique();
        $modes = mode::whereIn('id', $modesid)->get();

        if ($modes->isEmpty()) {
            return response()->json([
                'message' => 'No hay modalidades registradas para este deporte',
                'status' => 404,
            ], 404);
        }

        return response()->json([
            'sport' => $sport,
            'modes' => $modes,
        ]);
    }

    //Mostrar un modalidad por id
    public function show($id)
    {
        $mode = mode::find($id);
        if (!$mode) {
            return response()->json([
                'message' => 'Modalidad no encontrado',
                'status' => 404,
            ], 404);
        }
        return response()->json($mode, 200);
    }

    //Crear una modalidad
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'required',
            'color' => 'nullable|string|max:20', // Validación del color
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        $mode = mode::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color, // Guardamos el color
        ]);

        return response()->json($mode, 201);
    }

    // Actualizar una modalidad
    public function update(Request $request)
    {
        $mode = mode::find($request->id);
        if (!$mode) {
            return response()->json([
                'message' => 'Modalidad no encontrada',
                'status' => 404,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'required',
            'color' => 'nullable|string|max:20', // Validación del color
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        $mode->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color, // Actualizamos el color
        ]);

        return response()->json([
            'message' => 'Modalidad actualizada correctamente',
            'status' => 200,
        ], 200);
    }

    //eliminar una modalidad
    public function destroy(Request $request)
    {
        $mode = mode::find($request->id);
        if (!$mode) {
            return response()->json([
                'message' => 'Modalidad no encontrado',
                'status' => 404,
            ], 404);
        }
        $mode->delete();
        return response()->json([
            'message' => 'Modalidad eliminado correctamente',
            'status' => 200,
        ], 200);
    }
}
