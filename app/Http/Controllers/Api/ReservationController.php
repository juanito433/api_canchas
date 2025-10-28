<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\mode;
use App\Models\reservation;
use App\Models\schedules;
use App\Models\sport;
use App\Models\sportcourt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    //Función para visualizar todas las reservaciones
    public function all()
    {
        $reservations = Reservation::with([
            'user',
            'schedule.sportcourt.sport',
            'schedule.mode',
        ])->get();

        $reservations->each(function ($reservation) {
            $teammateIds = json_decode($reservation->teammates, true);

            if (is_array($teammateIds) && count($teammateIds) > 0) {
                // Consultar la tabla members para obtener los datos de los teammates
                $teammates = user::whereIn('id', $teammateIds)->get();
                $reservation->teammates_data = $teammates;
            } else {
                $reservation->teammates_data = [];
            }
        });

        return response()->json($reservations);
    }
    /* Reservaciones del dia, se cunetas vcunatas hay */
    public function todayReservations(Request $request)
    {
        $userId = $request->user()->id; // Usuario autenticado
        $today = now()->format('Y-m-d');

        $reservations = Reservation::with([
            'schedule.sportcourt.sport',
            'schedule.mode',
        ])
            ->where('user_id', $userId)
            ->where('date', $today)
            ->get();

        $reservations->each(function ($reservation) {
            $teammateIds = json_decode($reservation->teammates, true) ?? [];
            $reservation->teammates_data = !empty($teammateIds)
                ? User::whereIn('id', $teammateIds)->get(['id', 'name'])
                : [];
        });

        return response()->json($reservations);
    }



    //Función para visualizar una sola reservación
    public function show(Request $request)
    {
        $reservation = reservation::find($request->id);
        return response()->json($reservation);
    }

    public function memberReservations($id)
    {
        try {
            $reservations = Reservation::where('user_id', $id)->get();

            if ($reservations->isEmpty()) {
                return response()->json([
                    'mensaje' => 'Este miembro no ha realizado alguna reservación',
                    'status' => 404,
                ], 404);
            }

            $allTeammates = [];

            foreach ($reservations as $reservation) {
                if (!empty($reservation->teammates) && is_string($reservation->teammates)) {
                    // Reemplazamos las llaves { } por corchetes [ ]
                    $formatted_teammates = str_replace(['{', '}'], ['[', ']'], $reservation->teammates);

                    // Convertimos la cadena en un array de IDs
                    $teammates_id = json_decode($formatted_teammates, true);

                    // Validamos que $teammates_id sea un array antes de hacer la consulta
                    if (json_last_error() === JSON_ERROR_NONE && is_array($teammates_id) && !empty($teammates_id)) {
                        $teammates = User::whereIn('id', $teammates_id)->pluck('name'); // Obtener solo nombres
                        $allTeammates[$reservation->id] = $teammates;
                    } else {
                        $allTeammates[$reservation->id] = [];
                    }
                } else {
                    $allTeammates[$reservation->id] = [];
                }
            }

            return response()->json([
                'reservations' => $reservations,
                'teammates' => $allTeammates,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //Realizar una reservacion de una cancha
    public function storage(Request $request)
    {
        // === 1️⃣ Validar los datos recibidos ===
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date|date_format:Y-m-d',
            'teammates' => 'nullable|array',
            'confirmation' => 'required|string',
            'status' => 'required|string',
        ]);

        Log::info('Payload recibido:', $request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos.',
                'errors' => $validator->errors(),
                'data_recibida' => $request->all(),
                'status' => 422,
            ], 422);   
        }

        try {
            // === 2️⃣ Crear la reserva dentro de una transacción para seguridad ===
            $reservation = DB::transaction(function () use ($request) {

                // Buscar el horario y bloquearlo para evitar reservas duplicadas
                $schedule = Schedules::where('id', $request->schedule_id)
                    ->where('status', 'Disponible')
                    ->lockForUpdate()
                    ->first();

                if (!$schedule) {
                    throw new \Exception('Horario no disponible o ya reservado.');
                }

                // Crear la reserva
                $reservation = Reservation::create([
                    'user_id' => $request->user_id,
                    'schedule_id' => $request->schedule_id,
                    'date' => $request->date,
                    'teammates' => json_encode($request->teammates ?? []),
                    'confirmation' => $request->confirmation,
                    'status' => $request->status,
                ]);

                // Cambiar el estado del horario a ocupado
                $schedule->status = 'Ocupado';
                $schedule->save();

                return $reservation;
            });

            // === 3️⃣ Respuesta exitosa ===
            return response()->json([
                'message' => 'Reservación registrada correctamente.',
                'reservation' => $reservation,
                'status' => 201,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando reservación:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al registrar la reservación.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }




    //cancelar la reservación
    public function cancelReservation($id)
    {
        try {
            $result = DB::transaction(function () use ($id) {
                // Buscar la reservación
                $reservation = Reservation::find($id);

                if (!$reservation) {
                    return response()->json([
                        'message' => 'Reservación no encontrada',
                        'status' => 404,
                    ], 404);
                }

                // Buscar el horario vinculado
                $schedule = schedules::find($reservation->schedule_id);

                if ($schedule && $schedule->status === 'ocupado') {
                    $schedule->status = 'disponible';
                    $schedule->save();
                }

                // Eliminar la reservación
                $reservation->status = 'cancelada';
                $reservation->save();

                return response()->json([
                    'message' => 'Reservación cancelada correctamente',
                    'status' => 200,
                ], 200);
            });

            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cancelar la reservación',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function showReservationDetails(Request $request)
    {
        $reservation = reservation::find($request->id);

        if (!$reservation) {
            return response()->json([
                'message' => 'Reservación no encontrada',
            ], 404);
        }

        // Obtener el miembro que realizó la reservación
        $member = user::find($reservation->member_id);

        // Obtener los datos de los teammates
        $teammates_id = json_decode($reservation->teammates, true);
        $teammates = user::whereIn('id', $teammates_id)->get();

        // Obtener los datos de los schedules
        $schedule = schedules::find($reservation->schedule_id);

        //obtener los datos de las canchas 
        $sportcourt = sportcourt::find($schedule->sportcourt_id);

        //obtener los datos de la modalidad 
        $mode = mode::find($schedule->mode_id);

        //obtener datos del deporte
        $sport = sport::find($sportcourt->sport_id);

        return view('example.insert-reservation', compact(
            'reservation',
            'teammates',
            'schedule',
            'member',
            'sportcourt',
            'mode',
            'sport'
        ));
    }

    public function registro(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date',
            'teammates' => 'required|array|max:4',
            'confirmation' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al validar los datos ingresados',
                'errors' => $validator->errors(),
                'status' => 422,
            ], 422);
        }

        try {
            $reservation = DB::transaction(function () use ($request) {
                // 🔒 **Bloqueo pesimista:** Verificar disponibilidad con SELECT ... FOR UPDATE
                $schedule = schedules::where('id', $request->schedule_id)
                    ->where('status', 'Libre')
                    ->lockForUpdate()
                    ->first();

                if (!$schedule) {
                    throw new \Exception('La cancha ya ha sido reservada recientemente.');
                }

                //  **Crear la reservación**
                $reservation = Reservation::create([
                    'member_id' => $request->member_id,
                    'schedule_id' => $request->schedule_id,
                    'date' => $request->date,
                    'teammates' => json_encode($request->teammates),
                    'confirmation' => $request->confirmation,
                    'status' => 'Reservado',
                ]);

                //  **Actualizar el estado del horario a ocupado**
                $schedule->status = 'Ocupado';
                $schedule->save();

                return $reservation;
            });

            return response()->json([
                'message' => 'Reservación registrada correctamente',
                'reservation' => $reservation,
                'status' => 201,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar la reservación',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
    public function formReservations(Request $request)
    {
        $member = User::find($request->id);
        $sports = sport::all();
        $sportsCourts = sportcourt::all();
        $modes = mode::all();
        $schedules = schedules::all();

        return response()->json([
            'member' => $member,
            'sports' => $sports,
            'sportsCourts' => $sportsCourts,
            'modes' => $modes,
            'schedules' => $schedules,
        ]);
    }

    /* Actualización del satatus de la reservación  */
    public function updateStatusReservation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Cancelada,concluida,Reservado',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 422,
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($request, $id) {
                $reservation = Reservation::find($id);

                if (!$reservation) {
                    return response()->json([
                        'message' => 'Reservación no encontrada',
                        'status' => 404,
                    ], 404);
                }

                $newStatus = strtolower($request->status);

                // Si el nuevo estado es cancelada o concluida, liberar el horario
                if (in_array($newStatus, ['cancelada', 'concluida'])) {
                    $schedule = schedules::find($reservation->schedule_id);
                    if ($schedule && $schedule->status === 'Ocupado') {
                        $schedule->status = 'Libre';
                        $schedule->save();
                    }
                }

                $reservation->status = $newStatus;
                $reservation->save();

                return response()->json([
                    'message' => 'Estado de reservación actualizado correctamente',
                    'reservation' => $reservation,
                    'status' => 200,
                ]);
            });

            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el estado de la reservación',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }



    public function getReservationOptions(Request $request, $id)
    {
        // 1️⃣ Buscar el deporte
        $sport = sport::find($id);
        if (!$sport) {
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ]);
        }

        // 2️⃣ Obtener canchas
        $courts = sportcourt::where('sport_id', $sport->id)->get();
        if ($courts->isEmpty()) {
            return response()->json([
                'message' => 'No hay canchas registradas para este deporte',
                'status' => 404,
            ]);
        }
        $courtIds = $courts->pluck('id');

        // 3️⃣ Determinar el día siguiente y su categoría
        $tomorrow = now()->addDay();
        $weekDay = strtolower($tomorrow->format('l')); // monday, tuesday, friday, etc.

        // 4️⃣ Traducir al formato de tu BD
        $dayToFilter = match ($weekDay) {
            'friday' => 'friday',
            'saturday', 'sunday' => 'weekend_holiday',
            default => 'weekday',
        };

        // 5️⃣ Capturar modalidad seleccionada (si se envía)
        $modeId = $request->query('mode_id');

        // 🔍 Log para depuración
        Log::info('🔍 FILTROS:', [
            'sport_id' => $id,
            'mode_id' => $modeId,
            'dayToFilter' => $dayToFilter,
            'courtIds' => $courtIds,
        ]);

        try {
            // 6️⃣ Obtener horarios del deporte para el día filtrado
            $query = schedules::whereIn('sportcourt_id', $courtIds)
                ->whereRaw('LOWER(TRIM(days)) = ?', [strtolower($dayToFilter)]);

            if ($modeId) {
                $query->where('mode_id', (int) $modeId);
            }

            $availableSchedules = $query->get();

            // 🔍 Log de los horarios obtenidos
            Log::info('🕒 Horarios encontrados:', ['count' => $availableSchedules->count()]);

            if ($availableSchedules->isEmpty()) {
                return response()->json([
                    'status' => 200,
                    'sport' => $sport,
                    'days' => $dayToFilter,
                    'date' => $tomorrow->toDateString(),
                    'message' => 'No hay horarios disponibles para este día o modalidad',
                    'modes' => [],
                    'schedules' => [],
                    'courts' => $courts,
                ]);
            }

            // 7️⃣ Obtener las modalidades relacionadas
            $modesMap = mode::whereIn('id', $availableSchedules->pluck('mode_id')->unique())->get()->keyBy('id');

            // 8️⃣ Obtener IDs de horarios reservados
            $reservedIds = reservation::where('status', 'Reservado')->pluck('schedule_id')->toArray();

            // 9️⃣ Formatear los horarios
            $schedulesFormatted = $availableSchedules->map(function ($schedule) use ($reservedIds, $modesMap) {
                $isReserved = in_array($schedule->id, $reservedIds);
                $mode = $modesMap[$schedule->mode_id] ?? null;

                return [
                    'id' => $schedule->id,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'status' => $isReserved ? 'Reservado' : $schedule->status,
                    'mode' => $mode ? [
                        'id' => $mode->id,
                        'name' => $mode->name,
                        'description' => $mode->description,
                    ] : null,
                ];
            });

            // 10️⃣ Devolver respuesta final
            return response()->json([
                'status' => 200,
                'sport' => $sport,
                'days' => $dayToFilter,
                'date' => $tomorrow->toDateString(),
                'modes' => $modesMap->values(),
                'schedules' => $schedulesFormatted,
                'courts' => $courts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener horarios',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
