<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenaltyController extends Controller
{
    /**
     * 🔹 Obtener todas las penalizaciones (con usuario relacionado)
     */
    public function index()
    {
        $penalties = Penalty::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json($penalties, 200);
    }

    /**
     * Mostrar penalizaciones activas de un usuario (por ID)
     */
    public function show(Request $request)
    {
        $userId = $request->route('id');

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => 404,
            ], 404);
        }

        // Penalizaciones activas (no vencidas)
        $activePenalties = Penalty::where('user_id', $userId)
            ->whereDate('expiration_date', '>=', Carbon::now()->toDateString())
            ->orderBy('expiration_date', 'desc')
            ->get();

        if ($activePenalties->isEmpty()) {
            return response()->json([
                'total_penalties' => 0,
                'latest_expiration' => null,
            ], 200);
        }

        $latestExpiration = $activePenalties->first()->expiration_date;

        return response()->json([
            'total_penalties' => $activePenalties->count(),
            'latest_expiration' => $latestExpiration,
        ], 200);
    }
    public function getPenaltiesByUser($user_id)
    {
        // Obtener penalizaciones con la relación de reserva incluida
        $penalties = Penalty::where('user_id', $user_id)
            ->with('reservation') // incluye datos de la reserva
            ->orderBy('date', 'desc')
            ->get();

        // Verificar si no tiene penalizaciones
        if ($penalties->isEmpty()) {
            return response()->json([
                'message' => 'Este usuario no tiene penalizaciones registradas.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Penalizaciones obtenidas correctamente.',
            'data' => $penalties,
        ], 200);
    }

    public function store(Request $request)
    {
        
        // Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'reservation_id' => 'nullable|integer|exists:reservations,id',
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

        // Crear penalización
        $penalty = Penalty::create([
            'user_id' => $request->user_id,
            'reservation_id' => $request->reservation_id, // 👈 nuevo campo
            'cause' => $request->cause,
            'date' => $request->date,
            'expiration_date' => $request->expiration_date,
            'penalty' => $request->penalty,
        ]);

        return response()->json([
            'message' => 'Penalización creada correctamente',
            'data' => $penalty
        ], 201);
    }


    /**
     * 🔹 Actualizar penalización existente
     */
    public function update(Request $request)
    {
        $penalty = Penalty::find($request->id);

        if (!$penalty) {
            return response()->json([
                'message' => 'Penalización no encontrada',
                'status' => 404,
            ], 404);
        }

        // Validaciones incluyendo reservation_id
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'nullable|integer|exists:reservations,id',
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

        // Actualizar penalización
        $penalty->update([
            'reservation_id' => $request->reservation_id, // 👈 nuevo
            'cause' => $request->cause,
            'date' => $request->date,
            'expiration_date' => $request->expiration_date,
            'penalty' => $request->penalty,
        ]);

        return response()->json([
            'message' => 'Penalización actualizada correctamente',
            'data' => $penalty
        ], 200);
    }


    /**
     * 🔹 Eliminar penalización
     */
    public function destroy(Request $request)
    {
        $penalty = Penalty::find($request->id);

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
