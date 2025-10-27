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

class HistoryController extends Controller
{
    public function show(Request $request)
    {
        try {
            $reservations = reservation::where('user_id', $request->id)->get();

            if ($reservations->isEmpty()) {
                return response()->json([
                    'mensaje' => 'Este miembro no ha realizado alguna reservación',
                    'status' => 404,
                ], 404);
            }

            $reservationsWithTeammates = [];

            foreach ($reservations as $reservation) {
                $teammates = [];

                if (!empty($reservation->teammates) && is_string($reservation->teammates)) {
                    // Reemplazar llaves por corchetes
                    $formatted_teammates = str_replace(['{', '}'], ['[', ']'], $reservation->teammates);

                    // Convertir a array
                    $teammates_id = json_decode($formatted_teammates, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($teammates_id)) {
                        $teammates = User::whereIn('id', $teammates_id)->pluck('name');
                    }
                }
                $schedule = schedules::find($reservation->schedule_id);
                $court = sportcourt::find($schedule->sportcourt_id);
                $sport = sport::find($court->sport_id);
                $mode = mode::find($schedule->mode_id);

                $reservationsWithTeammates[] = [
                    'id' => $reservation->id,
                    'member_id' => $reservation->member_id,
                    'schedule_id' => $reservation->schedule_id,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'num_court' => $court->num_sportcourt,
                    'sport' => $sport->name,
                    'mode' => $mode->name,
                    'date' => $reservation->date,
                    'reserved_at' => $reservation->created_at,
                    'status' => $reservation->status,
                    'confirmation' => $reservation->confirmation,
                    'teammates' => $teammates,
                ];
            }

            return response()->json([
                'status' => 200,
                'mensaje' => 'Historial de reservaciones obtenido correctamente',
                'data' => $reservationsWithTeammates
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
