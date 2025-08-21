<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function GenerateReport(Request $request)
    {
        $reservations = Reservation::with([
            'user',
            'schedule',
            'schedule.sportcourt.sport',
            'schedule.mode',
        ])->get();

        // Datos de la gráfica: contar reservas por deporte
        $chartData = [];
        foreach ($reservations as $reservation) {
            $sport = $reservation->schedule->sportcourt->sport->name ?? 'N/A';
            if (!isset($chartData[$sport])) {
                $chartData[$sport] = 0;
            }
            $chartData[$sport]++;
        }

        // Si no hay reservas, agregar un valor por defecto
        if (empty($chartData)) {
            $chartData['Sin reservas'] = 1;
        }

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
                'scales' => [
                    'y' => ['beginAtZero' => true]
                ]
            ],
        ];

        // Obtener SVG como string desde QuickChart
        $svgUrl = 'https://quickchart.io/chart?format=svg&c=' . urlencode(json_encode($chartConfig));
        $svgContent = @file_get_contents($svgUrl) ?: '<p>No se pudo generar la gráfica.</p>';

        if ($svgContent) {
            $svgContent = preg_replace('/<\?xml.*\?>/', '', $svgContent); 
            $svgContent = preg_replace('/<!DOCTYPE.*>/', '', $svgContent);
            $svgContent = trim($svgContent);
        } else {
            $svgContent = '<p>No se pudo generar la gráfica.</p>';
        }


        // Pasar datos al Blade
        $pdf = Pdf::loadView('reports.reportGeneral', compact('reservations', 'svgContent'));
        return $pdf->stream('reporte_reserva_detalles.pdf');
    }
}
