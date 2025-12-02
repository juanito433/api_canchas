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
use Exception; // <--- Asegúrate de tener esto arriba del archivo
use Illuminate\Support\Facades\Log;

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

    public function storage(Request $request)
    {
        // 1. Validación inicial
        $validator = Validator::make($request->all(), [
            'days' => 'required', // OJO: Verifica si en tu BD es 'days', 'day' o 'date'
            'sportcourt_id' => 'required|integer',
            'mode_id' => 'required|integer',
            'start_time' => 'required|date_format:H:i',
            'force' => 'boolean' // <--- Aceptamos el parámetro opcional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 422,
            ], 422);
        }

        // 2. Obtener modalidad y calcular duración
        $mode = Mode::find($request->mode_id);
        if (!$mode) {
            return response()->json(['message' => 'Modalidad no encontrada', 'status' => 404], 404);
        }

        // Calcular end_time
        $start = Carbon::createFromFormat('H:i', $request->start_time);
        // Asegúrate si duration en tu BD son horas (int) o minutos. 
        // Si es int (ej: 1, 2), addHours está bien.
        $end = (clone $start)->addHours($mode->duration)->format('H:i');

        // 3. BUSCAR CONFLICTOS (Todos, no solo el primero)
        $conflicts = schedules::where('days', $request->days) // <--- Verifica nombre columna
            ->where('sportcourt_id', $request->sportcourt_id)
            ->where(function ($query) use ($request, $end) {
                // Lógica de cruce de horarios
                $query->where('start_time', '<', $end)
                    ->where('end_time', '>', $request->start_time);
            })
            ->get(); // <--- Usamos get() para traer todos los posibles choques

        // 4. LÓGICA DE SOBRESCRITURA
        if ($conflicts->isNotEmpty()) {

            // Si NO se envió la bandera 'force' o es falsa
            if (!$request->boolean('force')) {
                return response()->json([
                    'message' => 'El horario choca con reservas existentes.',
                    'conflict' => true, // Bandera para que React Native muestre la alerta
                    'status' => 409,
                ], 409);
            }

            // Si force es TRUE, eliminamos los estorbos
            $idsToDelete = $conflicts->pluck('id');
            schedules::destroy($idsToDelete);
        }

        // 5. Crear el nuevo horario
        $schedule = schedules::create([
            'days' => $request->days,
            'sportcourt_id' => $request->sportcourt_id,
            'mode_id' => $request->mode_id,
            'start_time' => $request->start_time,
            'end_time' => $end,
            'status' => 'Disponible',
        ]);

        return response()->json([
            'message' => 'Horario creado correctamente',
            'schedule' => $schedule,
            'deleted_ids' => isset($idsToDelete) ? $idsToDelete : [], // Para limpiar visualmente
            'status' => 201,
        ], 201);
    }


    public function update(Request $request)
    {
        $schedule = schedules::find($request->id);
        if (!$schedule) {
            return response()->json([
                'message' => 'Horario no encontrado',
                'status' => 404,
            ], 404);
        }

        // Validación (sin end_time porque se recalculará)
        $validator = Validator::make($request->all(), [
            'days' => 'required',
            'sportcourt_id' => 'required|integer',
            'mode_id' => 'required|integer',
            'start_time' => 'required|date_format:H:i',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        // Obtener modalidad nueva
        $mode = Mode::find($request->mode_id);
        if (!$mode) {
            return response()->json([
                'message' => 'Modalidad no encontrada',
                'status' => 404,
            ], 404);
        }

        // Calcular end_time nuevo
        $start = Carbon::createFromFormat('H:i', $request->start_time);
        $end = (clone $start)->addHours($mode->duration)->format('H:i');

        // Verificar conflictos con otros horarios (excluirse a sí mismo)
        $conflict = schedules::where('days', $request->days)
            ->where('sportcourt_id', $request->sportcourt_id)
            ->where('id', '!=', $schedule->id)
            ->where(function ($query) use ($request, $end) {
                $query->where('start_time', '<', $end)
                    ->where('end_time', '>', $request->start_time);
            })
            ->first();

        if ($conflict) {
            return response()->json([
                'message' => 'No se puede actualizar el horario porque se cruza con otro existente.',
                'conflict_schedule' => $conflict,
                'status' => 409,
            ], 409);
        }

        // Actualizar con datos nuevos
        $schedule->update([
            'days' => $request->days,
            'sportcourt_id' => $request->sportcourt_id,
            'mode_id' => $request->mode_id,
            'start_time' => $request->start_time,
            'end_time' => $end,
            'status' => $request->status,
        ]);

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
    /* Actualizar la modalidad de un horario */
    public function updateMode(Request $request, $id)
    {
        try {
            // LOG PARA DEBUG (Revisa storage/logs/laravel.log si puedes)
            Log::info("Intento de updateMode ID: $id", $request->all());

            // 1. Validar (Sin 'exists' para evitar errores de nombre de tabla por ahora)
            $request->validate([
                'mode_id'  => 'required',
                'end_time' => 'required',
                'force'    => 'nullable' // Aceptamos boolean o null
            ]);

            // 2. Buscar el horario
            $schedule = Schedules::find($id);

            if (!$schedule) {
                return response()->json(['message' => 'Horario no encontrado'], 404);
            }

            // 3. DETECTAR CONFLICTOS
            // IMPORTANTE: Asegúrate que 'date' sea el nombre real de tu columna en la BD.
            $conflicts = Schedules::where('sportcourt_id', $schedule->sportcourt_id)
                ->where('days', $schedule->days) // <--- ¿Tu columna se llama 'date', 'day' o 'fecha'?
                ->where('id', '!=', $id)
                ->where(function ($query) use ($schedule, $request) {
                    // Lógica: (InicioA < FinB) y (FinA > InicioB)
                    $query->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $schedule->start_time);
                })
                ->get();

            if ($conflicts->isNotEmpty()) {

                // Convertimos el parámetro 'force' a booleano real
                $isForced = filter_var($request->force, FILTER_VALIDATE_BOOLEAN);

                // Si NO se está forzando, devolvemos error 409
                if (!$isForced) {
                    return response()->json([
                        'message' => 'El horario choca con otra reserva existente.',
                        'conflict' => true // Bandera para el frontend
                    ], 409);
                }

                // SI SE FUERZA: Eliminamos los conflictos
                $idsToDelete = $conflicts->pluck('id')->toArray(); // <--- Convertir a array es más seguro
                Schedules::destroy($idsToDelete);
            }

            // 4. Actualizar
            $schedule->mode_id  = $request->mode_id;
            $schedule->end_time = $request->end_time;
            $schedule->save();

            // 5. Cargar relación (try-catch interno por si falla el nombre de la relación)
            try {
                $schedule->load('mode');
            } catch (Exception $eRel) {
                // Ignoramos error de relación visual
            }

            return response()->json([
                'message'  => 'Actualizado correctamente',
                'schedule' => $schedule,
                'deleted_ids' => isset($idsToDelete) ? $idsToDelete : []
            ], 200);
        } catch (Exception $e) {
            // ESTO ENVIARÁ EL ERROR EXACTO A TU CELULAR
            Log::error("Error Fatal en updateMode: " . $e->getMessage());

            return response()->json([
                'message' => 'PHP Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
