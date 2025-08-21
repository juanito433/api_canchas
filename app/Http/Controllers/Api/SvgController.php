<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\reservation;
use Illuminate\Http\Request;

class SvgController extends Controller
{
    public function generateSvgChart()
    {
        // 1️⃣ Obtener datos
        $reservationsgv = reservation::with('schedule.sportcourt.sport')->get();

        $chartData = [];
        foreach ($reservationsgv as $res) {
            $sport = $res->schedule->sportcourt->sport->name ?? 'N/A';
            $chartData[$sport] = ($chartData[$sport] ?? 0) + 1;
        }

        // 2️⃣ Preparar JSON de Chart.js
        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => array_keys($chartData),
                'datasets' => [
                    [
                        'label' => 'Reservas por deporte',
                        'data' => array_values($chartData),
                        'backgroundColor' => 'rgba(54, 162, 235, 0.7)',
                    ]
                ]
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
            ],
        ];

        // 3️⃣ Generar URL de QuickChart en SVG
        $chartUrl = 'https://quickchart.io/chart?format=svg&c=' . urlencode(json_encode($chartConfig));

        // 4️⃣ Retornar la URL de la imagen SVG
        return view('reports.SvgCharts', compact('chartUrl'));
    }
}
