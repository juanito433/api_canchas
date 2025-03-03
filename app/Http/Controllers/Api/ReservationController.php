<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\member;
use App\Models\mode;
use App\Models\reservation;
use App\Models\schedules;
use App\Models\sport;
use App\Models\sportcourt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    //Función para visualizar todas las reservaciones
    public function all()
    {
        $reservations = reservation::all();
        return response()->json($reservations);
    }

    //Función para visualizar una sola reservación
    public function show(Request $request)
    {
        $reservation = reservation::find($request->id);
        return response()->json($reservation);
    }

    //Función para visualizar las reservaciones de un miembro
    public function memberReservations($id)
    {
        $reservations = Reservation::where('member_id', $id)->get();

        if ($reservations->isEmpty()) {
            return response()->json([
                'mensaje' => 'Este miembro no ha realizado alguna reservación',
                'status' => 404,
            ], 404);
        }

        return response()->json($reservations);
    }

    //Realizar una reservacion de una cancha
    public function storage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date',
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

                // **Crear la reservación**
                $reservation = Reservation::create([
                    'member_id' => $request->member_id,
                    'schedule_id' => $request->schedule_id,
                    'date' => $request->date,
                    'teammates' => $request->teammates,
                    'confirmation' => $request->confirmation,
                    'status' => $request->status,
                ]);

                // **Actualizar el estado del horario**
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
        $member = member::find($reservation->member_id);

        // Obtener los datos de los teammates
        $teammates_id = json_decode($reservation->teammates, true);
        $teammates = member::whereIn('id', $teammates_id)->get();

        // Obtener los datos de los schedules
        $schedule = schedules::find($reservation->schedule_id);

        //obtener los datos de las canchas 
        $sportcourt = sportcourt::find($schedule->sportcourt_id);
        
        //obtener los datos de la modalidad 
        $mode = mode::find($schedule->mode_id);

        //obtener datos del deporte
        $sport = sport::find($sportcourt->sport_id);

        return view('example.CancelReservation', compact('reservation', 'teammates', 
        'schedule', 'member', 'sportcourt', 'mode', 'sport'));
    }
}
