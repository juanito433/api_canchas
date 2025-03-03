<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\member;
use App\Models\reservation;
use App\Models\schedules;
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
    public function show($id)
    {
        $reservation = reservation::find($id);
        return response()->json($reservation);
    }

    //Función para visualizar las reservaciones de un miembro
    public function memberReservations($id)
    {
        $reservations = reservation::where('member_id', $id)->get();
        return response()->json($reservations);
    }

    //Realizar una reservacion de una cancha
    public function storage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required',
            'schedule_id' => 'required', // Corregido 'require' -> 'required'
            'date' => 'required|date',
            'teammates' => 'required',
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
            DB::transaction(function () use ($request, &$reservation) {
                $reservation = Reservation::create([
                    'member_id' => $request->member_id,
                    'schedule_id' => $request->schedule_id,
                    'date' => $request->date,
                    'teammates' => $request->teammates,
                    'confirmation' => $request->confirmation,
                ]);

                // Actualizar el estado del horario a "ocupado"
                $schedule = schedules::findOrFail($request->schedule_id);
                $schedule->status = 'ocupado';
                $schedule->save();
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
    public function cancelReservation(Request $request)
    {
        $reservation = reservation::find($request->id);
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
    
        return view('example.CancelReservation', compact('reservation', 'teammates', 'schedule', 'member'));
    }
    
}
