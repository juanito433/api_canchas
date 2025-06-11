<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\penalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenaltyController extends Controller
{
    //obtener todas la penalizaciones 
    public function all()
    {
        return response()->json(penalty::all(), 200);
    }
    //obtener una penalizaci+on por su id
    public function show(Request $request)
    {
        $penalty = penalty::find($request->id);
        if (!$penalty) {
            return response()->json([
                'message' => 'Penalización no encontrada',
                'status' => 404,
            ], 404);
        }
        return response()->json($penalty, 200);
    }
    //asignar una penalización a un usuario con el role de member
    public function store(Request $request)
    {
        // Verificar si el usuario tiene el rol de admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'No tienes permiso para asignar penalizaciones',
                'status' => 403,
            ], 403);
        }
        // Verificar si el usuario penalizado es un member
        if ($request->user_id && $request->user()->role !== 'member') {
            return response()->json([
                'message' => 'El usuario penalizado debe tener el rol de member',
                'status' => 403,
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'cause' => 'required|string|max:255',
            'date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:date',
            'penalty' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 422,
            ], 422);
        }
        $penalty = penalty::create([
            'user_id' => $request->user_id,
            'cause' => $request->cause,
            'date' => $request->date,
            'expiration_date' => $request->expiration_date,
            'penalty' => $request->penalty,
        ]);
        return response()->json($penalty, 201);
    }
    //actualizar una penalización
    public function update(Request $request)
    {
        $penalty = penalty::find($request->id);
        if (!$penalty) {
            return response()->json([
                'message' => 'Penalización no encontrada',
                'status' => 404,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'cause' => 'required|string|max:255',
            'date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:date',
            'penalty' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 422,
            ], 422);
        }

        $penalty->update($request->all());
        return response()->json($penalty, 200);
    }
    //eliminar una penalización
    public function destroy(Request $request)
    {
        $penalty = penalty::find($request->id);
        if (!$penalty) {
            return response()->json([
                'message' => 'Penalización no encontrada',
                'status' => 404,
            ], 404);
        }
        $penalty->delete();
        return response()->json([
            'message' => 'Penalización eliminada correctamente',
            'status' => 200,
        ], 200);
    }
}

