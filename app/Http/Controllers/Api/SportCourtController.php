<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\sport;
use App\Models\sportcourt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SportCourtController extends Controller
{

    #listar todas las canchas
    public function all (){
        return response()->json(sportcourt::all(), 200);

    }

    // Listar todas las canchas de un deporte específico
    public function index($sport_id)
    {
        $sport = Sport::find($sport_id);

        if (!$sport) {
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ], 404);
        }

        return response()->json($sport->courts, 200);
    }

    // Agregar una nueva cancha a un deporte
    public function store(Request $request, $sport_id)
    {
        $sport = sport::find($sport_id);

        if (!$sport) {
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'num_sportcourt' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        $court = sportcourt::create([
            'num_sportcourt' => $request->num_sportcourt,
            'sport_id' => $sport->id,
        ]);

        return response()->json([
            'court' => $court,
            'message' => 'Cancha agregada correctamente',
            'status' => 201,
        ], 201);
    }
}
