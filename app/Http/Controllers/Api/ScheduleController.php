<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\mode;
use App\Models\schedules;
use App\Models\sport;
use App\Models\sportcourt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    // Obtener todos los horarios
    public function index(Request $request)
    {
        $schedules = schedules::all();
        if ($schedules->isEmpty()) {
            return response()->json([
                'message' => 'No hay horarios registrados',
                'status' => 404,
            ], 404);
        }

        $schedulesWithDetails = $schedules->map(function ($schedule) {
            $court = sportcourt::find($schedule->sportcourt_id);
            $sport = sport::find($court->sport_id);
            $mode = mode::find($schedule->mode_id);

            return [
                'schedule' => $schedule,
                'num_court' => $court->num_sportcourt,
                'sport' => $sport->name,
                'mode' => $mode->name,
            ];
        });

        return response()->json($schedulesWithDetails, 200);
    }

    // Obtener un horario por su ID
    public function show(Request $request)
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
            'schedule' => $schedule,
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
    // Obtener horarios por fecha
    public function getSchedulesByDate($date)
    {
        // Convertir la fecha en un objeto Carbon
        $carbonDate = Carbon::parse($date);

        // Obtener el nombre del día en inglés
        $dayName = $carbonDate->format('l');

        // Mapeo de los días de la semana al formato de la base de datos (ajustar si es necesario)
        $daysMap = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo',
        ];

        // Obtener el día en español según el mapeo
        $dayInDb = $daysMap[$dayName] ?? null;

        // Si el día no se encuentra en el mapeo, retornar error
        if (!$dayInDb) {
            return response()->json(['message' => 'Día no válido'], 400);
        }

        // Consultar los horarios disponibles para ese día
        $schedules = schedules::where('days', $dayInDb)->get();

        // Retornar la respuesta en JSON
        return response()->json([
            'date' => $date,
            'day' => $dayInDb,
            'schedules' => $schedules
        ]);
    }
    public function getSchedulesBySport($id)
    {
        // 1️ Buscar el deporte por su ID
        $sport = Sport::find($id);
        if (!$sport) {
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ], 404);
        }

        // 2️ Obtener las canchas asociadas a ese deporte
        $courts = SportCourt::where('sport_id', $sport->id)->get();
        if ($courts->isEmpty()) {
            return response()->json([
                'message' => 'No hay canchas registradas para este deporte',
                'status' => 404,
            ], 404);
        }

        // 3 Obtener todos los horarios de esas canchas
        $courtIds = $courts->pluck('id');

        $schedules = Schedules::whereIn('sportcourt_id', $courtIds)
            ->with([
                'sportcourt.sport',  
                'sportcourt',        
                'mode'               
            ])
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'message' => 'No hay horarios registrados para este deporte',
                'status' => 404,
            ], 404);
        }

        // 4️ Formatear los datos para respuesta clara
        $formatted = $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'day' => $schedule->days,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'court_number' => $schedule->sportcourt ? $schedule->sportcourt->num_sportcourt : null,
                'sport' => $schedule->sportcourt && $schedule->sportcourt->sport
                    ? $schedule->sportcourt->sport->name
                    : null,
                'mode' => $schedule->mode ? $schedule->mode->name : null,
            ];
        });

        // 5️ Respuesta final JSON
        return response()->json([
            'sport_id' => $sport->id,
            'sport_name' => $sport->name,
            'total_schedules' => $formatted->count(),
            'schedules' => $formatted,
        ]);
    }
}
