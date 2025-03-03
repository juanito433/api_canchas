<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\schedules;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    // Visualizar todos los horarios
    public function all()
    {
        return response()->json(schedules::all(), 200);
    }

    // Obtener un horario por su ID
    public function index(Request $request)
    {
        $schedule = schedules::find($request->id);
        if (!$schedule) {
            return response()->json([
                'message' => 'Horario no encontrado',
                'status' => 404,
            ], 404);
        }
        return response()->json($schedule, 200);
    }

    // Registrar un nuevo horario
    public function storage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'required',
            'sportcourt_id' => 'required|integer',
            'mode_id' => 'required|integer',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 422,
            ], 422);
        }

        $schedule = schedules::create([
            'days' => $request->days,
            'sportcourt_id' => $request->sportcourt_id,
            'mode_id' => $request->mode_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'Disponible', 
        ]);

        return response()->json([
            'message' => 'Horario creado correctamente',
            'schedule' => $schedule,
            'status' => 201,
        ], 201);
    }

    // Actualizar un horario
    public function update(Request $request)
    {
        $schedule = schedules::find($request->id);
        if (!$schedule) {
            return response()->json([
                'message' => 'Horario no encontrado',
                'status' => 404,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'days' => 'required',
            'sportcourt_id' => 'required|integer',
            'mode_id' => 'required|integer',
            'start_time' => 'required',
            'end_time' => 'required',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        $schedule->update($request->all());

        return response()->json([
            'message' => 'Horario actualizado correctamente',
            'status' => 200,
        ], 200);
    }

    // Eliminar un horario
    public function destroy(Request $request)
    {
        $schedule = schedules::find($request->id);
        if (!$schedule) {
            return response()->json([
                'message' => 'Horario no encontrado',
                'status' => 404,
            ], 404);
        }

        $schedule->delete();
        return response()->json([
            'message' => 'Horario eliminado exitosamente',
            'status' => 200,
        ], 200);
    }
}
