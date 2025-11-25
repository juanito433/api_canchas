<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedules;
use App\Models\Reservation;
use Carbon\Carbon;

class StatisticController extends Controller
{
    public function dailyStatistics()
    {
        $today = Carbon::today();

        // 1. Reservas del día
        $reservationsToday = Reservation::whereDate('date', $today)->count();

        // 2. Horarios ocupados
        $occupiedSchedulesCount = Schedules::where('status', 'Ocupado')->count();

        // 3. Horarios totales para calcular porcentaje
        $totalSchedules = Schedules::count();

        $occupationPercent = $totalSchedules > 0
            ? round(($occupiedSchedulesCount / $totalSchedules) * 100, 2)
            : 0;

        // 4. Nivel de ocupación
        if ($occupationPercent < 40) {
            $occupationLevel = 'Bajo';
        } elseif ($occupationPercent <= 70) {
            $occupationLevel = 'Medio';
        } else {
            $occupationLevel = 'Alto';
        }

        // 5. Reservas activas (status 'En curso')
        $activeReservations = Reservation::where('status', 'En curso')
            ->whereDate('date', $today)
            ->count();

        // 6. Reservas pendientes (confirmation 'Pendiente')
        $pendingReservations = Reservation::where('confirmation', 'Pendiente')
            ->whereDate('date', $today)
            ->count();

        return response()->json([
            'occupied_schedules' => $occupiedSchedulesCount,
            'occupation_percent' => $occupationPercent,
            'occupation_level' => $occupationLevel, // agregamos nivel
            'active_reservations' => $activeReservations,
            'pending_reservations' => $pendingReservations,
            'reservations_today' => $reservationsToday,
        ]);
    }
}
