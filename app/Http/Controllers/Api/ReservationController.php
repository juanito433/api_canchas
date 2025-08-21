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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date|date_format:Y-m-d',
            'teammates' => 'required',
            'confirmation' => 'required',
            'status' => 'required',
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
                // **Bloqueo pesimista:** Verificar disponibilidad con SELECT ... FOR UPDATE
                $schedule = schedules::where('id', $request->schedule_id)
                    ->where('status', 'Libre')
                    ->lockForUpdate()
                    ->first();

                if (!$schedule) {
                    // Si el horario ya fue tomado recientemente, obtener el tiempo exacto de la reserva
                    $lastReservation = Reservation::where('schedule_id', $request->schedule_id)
                        ->latest()
                        ->first();

                    return response()->json([
                        'message' => 'Lo sentimos, esta cancha ya ha sido reservada',
                        'reserved_at' => $lastReservation ? $lastReservation->created_at->diffForHumans() : 'Hace unos momentos',
                        'status' => 409,
                    ], 409);
                }

                // Crear la reservación
                $reservation = Reservation::create([
                    'user_id' => $request->user_id,
                    'schedule_id' => $request->schedule_id,
                    'date' => $request->date,
                    'teammates' => json_encode($request->teammates),
                    'confirmation' => $request->confirmation,
                    'status' => $request->status,
                ]);

                // Actualizar el estado del horario
                $schedule->status = 'ocupado';
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
        $sport = sport::find($id);

        if (!$sport) {
            return response()->json([
                'message' => 'Deporte no encontrado',
                'status' => 404,
            ]);
        }

        $courts = sportcourt::where('sport_id', $sport->id)->get();

        if ($courts->isEmpty()) {
            return response()->json([
                'message' => 'No hay canchas registradas para este deporte',
                'status' => 404,
            ]);
        }

        $courtIds = $courts->pluck('id');

        // Obtener horarios disponibles generales
        $availableDaysWithSchedules = collect();

        try {
            $allSchedules = schedules::whereIn('sportcourt_id', $courtIds)
                ->where('status', 'Libre')
                ->get();

            $availableDaysWithSchedules = $allSchedules
                ->groupBy('days')
                ->map(function ($dayGroup, $day) {
                    return [
                        'day' => $day,
                        'schedules' => $dayGroup->map(function ($schedule) {
                            return [
                                'id' => $schedule->id,
                                'start_time' => $schedule->start_time,
                                'end_time' => $schedule->end_time,
                                'court_id' => $schedule->sportcourt_id,
                                'mode_id' => $schedule->mode_id,
                            ];
                        })->values()
                    ];
                })
                ->values();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los días disponibles con horarios',
                'error' => $e->getMessage(),
            ], 500);
        }

        if ($request->has('days')) {
            $selectedDay = trim(strtolower($request->input('days')));

            $schedules = schedules::whereIn('sportcourt_id', $courtIds)
                ->whereRaw('LOWER(TRIM(days)) = ?', [$selectedDay])
                ->get(); // Trae todos, sin filtrar por 'Libre'

            if ($schedules->isEmpty()) {
                return response()->json([
                    'status' => 200,
                    'sport' => $sport,
                    'day_selected' => ucfirst($selectedDay),
                    'message' => 'No hay horarios registrados para este día',
                    'courts' => $courts,
                ]);
            }

            // Mapear courts y modes para acceder por ID
            $courtsMap = sportcourt::whereIn('id', $schedules->pluck('sportcourt_id')->unique())->get()->keyBy('id');
            $modesMap = mode::whereIn('id', $schedules->pluck('mode_id')->unique())->get()->keyBy('id');

            // Obtener IDs de horarios reservados
            $reservedScheduleIds = reservation::where('status', 'Reservado')->pluck('schedule_id')->toArray();

            $schedulesFormatted = $schedules->map(function ($schedule) use ($courtsMap, $modesMap, $reservedScheduleIds) {
                $court = $courtsMap[$schedule->sportcourt_id] ?? null;
                $mode = $modesMap[$schedule->mode_id] ?? null;

                $isReserved = in_array($schedule->id, $reservedScheduleIds);

                return [
                    'id' => $schedule->id,
                    'day' => $schedule->days,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'status' => $schedule->status,
                    'court' => $court ? [
                        'id' => $court->id,
                        'num' => $court->num_sportcourt,
                    ] : null,
                    'mode' => $mode ? [
                        'id' => $mode->id,
                        'name' => $mode->name,
                        'description' => $mode->description,
                    ] : null,
                ];
            });
        };



        return response()->json([
            'status' => 200,
            'sport' => $sport,
            'day_selected' => ucfirst($selectedDay),
            'schedules' => $schedulesFormatted,
            'courts' => $courts,
            'modes' => $modesMap->values(),
        ]);
    }
}
