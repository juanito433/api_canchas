<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\mode;
use App\Models\sportcourt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModeController extends Controller
{
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
    // Agregar una nueva modalidad a un cancha
    public function store(Request $request, $sportcourt_id)
    {
        $sportcourt = sportcourt::find($sportcourt_id);

        if (!$sportcourt) {
            return response()->json([
                'message' => 'Cancha no encontrado',
                'status' => 404,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'date' => 'required|date',
            'start_time'=> 'required|date_format:H:i:s',
            'end_time'=> 'required|date_format:H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        $mode = Mode::create([
            'name' => $request->name,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'sportcourt_id' => $sportcourt->id,
        ]);

        return response()->json([
            'mode' => $mode,
            'message' => 'Cancha agregada correctamente',
            'status' => 201,
        ], 201);
    }
}
