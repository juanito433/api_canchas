<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\mode;
use App\Models\schedules;
use App\Models\sport;
use App\Models\sportcourt;
use Illuminate\Http\Request;

class SabanaController extends Controller
{
    public function sabana(Request $request)
    {
        $sport = sport::find($request->id);
        if (!$sport) {
            return response()->json(['error' => 'Deporte no encontrado'], 404);
        }

        $courts = sportcourt::where('sport_id', $sport->id)->get();

        if ($courts->isEmpty()) {
            return response()->json(['error' => 'No hay canchas disponibles para este deporte'], 404);
        }

        $data = [];
        foreach ($courts as $court) {
            $horarios = schedules::where('sportcourt_id', $court->id)
                ->orderBy('start_time', 'asc')
                ->get()
                ->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'mode' => mode::find($schedule->mode_id)?->name,
                        'mode_id' => mode::find($schedule->mode_id)?->id,

                    ];
                });

            if ($horarios->isNotEmpty()) {
                $data[] = [
                    'court_id' => $court->id,
                    'court_name' => $court->num_sportcourt,
                    'schedules' => $horarios,
                ];
            }
        }

        return response()->json([
            'sport' => $sport->name,
            'data' => $data
        ]);
    }
}
