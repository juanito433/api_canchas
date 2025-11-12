<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\reservation;
use App\Models\sportcourt;
use App\Models\suggestions;
use Illuminate\Http\Request;
use App\Models\User;

use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Retorna estadísticas generales para el panel administrativo.
     */
    public function estadisticas()
    {
        // 📅 Fecha actual (para filtrar reservas del día)
        $today = Carbon::today();

        /* ==========================
         * 🔸 RESERVAS DEL DÍA
         * ========================== */
        $reservasHoy = reservation::whereDate('date', $today)->count();

        /* ==========================
         * 👥 USUARIOS REGISTRADOS
         * ========================== */
        $totalUsuarios = User::count();

        /* ==========================
         * 🏟️ TOTAL DE CANCHAS
         * ========================== */
        $totalCanchas = sportcourt::count();

        /* ==========================
         * 💬 TOTAL DE SUGERENCIAS
         * ========================== */
        $totalSugerencias = suggestions::count();

        /* ==========================
         * 📊 RETORNO DE DATOS
         * ========================== */
        return response()->json([
            'status' => 'success',
            'data' => [
                'reservas_hoy' => $reservasHoy,
                'usuarios' => $totalUsuarios,
                'canchas' => $totalCanchas,
                'sugerencias' => $totalSugerencias,
            ],
        ]);
    }
}
