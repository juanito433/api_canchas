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
    public function all()
    {
        return response()->json(sportcourt::all(), 200);
    }
    //Obtener las canchas con los deportes 
    public function showAllCourts()
    {
        $courts = sportcourt::all();
        $sports = sport::whereIn('id', $courts->pluck('sport_id'))->get();

        return view('sportcourt.all-courts', compact('courts', 'sports'));
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
    // Actualizar una cancha
    public function update(Request $request)
    {
        $court = sportcourt::find($request->id);

        if (!$court) {
            return response()->json([
                'message' => 'Cancha no encontrada',
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

        $court->num_sportcourt = $request->num_sportcourt;
        $court->save();

        return response()->json([
            'court' => $court,
            'message' => 'Cancha actualizada correctamente',
            'status' => 200,
        ], 200);
    }
    // Eliminar una cancha
    public function destroy($id)
    {
        $court = sportcourt::find($id);

        if (!$court) {
            return response()->json([
                'message' => 'Cancha no encontrada',
                'status' => 404,
            ], 404);
        }

        $court->delete();

        return response()->json([
            'message' => 'Cancha eliminada correctamente',
            'status' => 200,
        ], 200);
    }
}
