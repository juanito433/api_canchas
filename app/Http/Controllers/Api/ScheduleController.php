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
                'mode_color' => $mode->color,
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
        // Validación inicial
        $validator = Validator::make($request->all(), [
            'days' => 'required',
            'sportcourt_id' => 'required|integer',
            'mode_id' => 'required|integer',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 422,
            ], 422);
        }

        // Variables de entrada
        $day = $request->days;
        $court = $request->sportcourt_id;
        $start = $request->start_time;
        $end = $request->end_time;

        // Buscar horarios que se crucen con el nuevo rango
        $conflict = schedules::where('days', $day)
            ->where('sportcourt_id', $court)
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                        ->where('end_time', '>', $start);
                });
            })
            ->first();

        if ($conflict) {
            return response()->json([
                'message' => 'No se puede registrar el horario porque se cruza con otro existente.',
                'conflict_schedule' => $conflict,
                'status' => 409, // 409 = conflicto
            ], 409);
        }

        // Si no hay conflictos, crear el nuevo horario
        $schedule = schedules::create([
            'days' => $day,
            'sportcourt_id' => $court,
            'mode_id' => $request->mode_id,
            'start_time' => $start,
            'end_time' => $end,
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
        // Buscar el deporte por su ID
        $sport = Sport::find($id);
        if (!$sport) {
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ], 404);
        }

        // Obtener las canchas asociadas
        $courts = SportCourt::where('sport_id', $sport->id)->get();

        if ($courts->isEmpty()) {
            return response()->json([
                'sport_id' => $sport->id,
                'sport_name' => $sport->name,
                'total_courts' => 0,
                'total_schedules' => 0,
                'schedules' => [],
                'empty' => true,
                'message' => 'No hay canchas registradas o disponibles para este deporte aún.',
            ], 200);
        }

        $courtIds = $courts->pluck('id');

        // Obtener horarios y modos asociados incluyendo el color
        $schedules = SportCourt::whereIn('sportcourts.id', $courtIds)
            ->leftJoin('schedules', 'sportcourts.id', '=', 'schedules.sportcourt_id')
            ->leftJoin('modes', 'schedules.mode_id', '=', 'modes.id')
            ->select(
                'sportcourts.id as court_id',
                'sportcourts.num_sportcourt as court_number',
                'schedules.id as schedule_id',
                'schedules.days',
                'schedules.start_time',
                'schedules.end_time',
                'schedules.status',
                'modes.name as mode_name',
                'modes.color as mode_color' // <--- agregado
            )
            ->orderBy('sportcourts.num_sportcourt')
            ->orderBy('schedules.start_time')
            ->get();

        $formatted = $schedules->map(function ($item) use ($sport) {
            return [
                'id' => $item->schedule_id,
                'day' => $item->days,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'court_number' => $item->court_number,
                'sport' => $sport->name,
                'mode' => $item->mode_name,
                'mode_color' => $item->mode_color, // <--- agregado
                'status' => $item->status,
            ];
        });

        return response()->json([
            'sport_id' => $sport->id,
            'sport_name' => $sport->name,
            'total_courts' => $courts->count(),
            'total_schedules' => $formatted->count(),
            'schedules' => $formatted,
            'empty' => false,
        ], 200);
    }
}
