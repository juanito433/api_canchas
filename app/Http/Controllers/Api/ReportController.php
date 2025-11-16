<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Sport;
use App\Models\SportCourt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // ============================================================
    //  🔸 1) REPORTE GENERAL (YA EXISTENTE)
    // ============================================================
    public function GenerateReport(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate'
        ]);

        $startDate = $request->startDate;
        $endDate   = $request->endDate;

        $reservations = Reservation::with([
            'user',
            'schedule',
            'schedule.sportcourt.sport',
            'schedule.mode',
        ])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'ASC')
            ->get();

        // Conteo de reservas por deporte
        $chartData = [];
        foreach ($reservations as $reservation) {
            $sport = $reservation->schedule->sportcourt->sport->name ?? 'N/A';
            $chartData[$sport] = ($chartData[$sport] ?? 0) + 1;
        }

        if (empty($chartData)) {
            $chartData['Sin reservas'] = 1;
        }

        // Paleta amarilla
        $colorPalette = [
            'rgba(255, 206, 86, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(253, 216, 53, 0.8)',
            'rgba(255, 235, 59, 0.8)',
            'rgba(249, 168, 37, 0.8)',
        ];

        $labels = array_keys($chartData);
        $values = array_values($chartData);

        $backgroundColors = [];
        foreach ($labels as $index => $label) {
            $backgroundColors[] = $colorPalette[$index % count($colorPalette)];
        }

        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Reservas por deporte',
                        'data'  => $values,
                        'backgroundColor' => $backgroundColors,
                    ]
                ]
            ]
        ];

        // URL de la gráfica
        $pngUrl = 'https://quickchart.io/chart?format=png&width=700&height=400&c='
            . urlencode(json_encode($chartConfig));

        $chartImage = base64_encode(file_get_contents($pngUrl));

        // Agrupación por deporte para la tabla
        $reservasPorDeporte = $reservations->groupBy(function ($item) {
            return $item->schedule->sportcourt->sport->name ?? 'N/A';
        });

        $pdf = Pdf::loadView('reports.reportGeneral', [
            'reservations'        => $reservations,
            'chartImage'          => $chartImage,
            'reservasPorDeporte'  => $reservasPorDeporte,
            'startDate'           => $startDate,
            'endDate'             => $endDate,
        ]);

        return $pdf->stream('reporte_reservas.pdf');
    }


    // ============================================================
    //  🔸 2) REPORTE POR UN SOLO DEPORTE
    // ============================================================
    public function ReportBySport(Request $request)
    {
        $request->validate([
            'sport_id'  => 'required|integer',
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate'
        ]);

        $sportId   = $request->sport_id;
        $startDate = $request->startDate;
        $endDate   = $request->endDate;

        // Obtener nombre del deporte
        $sport = Sport::find($sportId);
        $sportName = $sport->name ?? "Desconocido";

        // Obtener reservas del deporte
        $reservations = Reservation::with([
            'user',
            'schedule',
            'schedule.sportcourt.sport',
            'schedule.mode',
        ])
            ->whereHas('schedule.sportcourt', function ($q) use ($sportId) {
                $q->where('sport_id', $sportId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'ASC')
            ->get();

        // Conteo para gráfica
        $chartData = [];
        foreach ($reservations as $r) {
            $court = "Cancha " . ($r->schedule->sportcourt->num_sportcourt ?? 'N/A');
            $chartData[$court] = ($chartData[$court] ?? 0) + 1;
        }

        if (empty($chartData)) {
            $chartData['Sin registros'] = 1;
        }

        // Paleta amarilla
        $colors = [
            'rgba(255, 206, 86, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(253, 216, 53, 0.8)',
        ];

        $labels = array_keys($chartData);
        $values = array_values($chartData);

        $backgroundColors = [];
        foreach ($labels as $i => $x) {
            $backgroundColors[] = $colors[$i % count($colors)];
        }

        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Uso por cancha',
                        'data'  => $values,
                        'backgroundColor' => $backgroundColors
                    ]
                ]
            ]
        ];

        $pngUrl = "https://quickchart.io/chart?format=png&width=700&height=400&c="
            . urlencode(json_encode($chartConfig));

        $chartImage = base64_encode(file_get_contents($pngUrl));

        // 🔹 SE ENVÍA EL NOMBRE DEL DEPORTE A LA VISTA
        $pdf = Pdf::loadView('reports.reportBySport', [
            'reservations' => $reservations,
            'chartImage'   => $chartImage,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'sportName'    => $sportName,
        ]);

        return $pdf->stream('reporte_por_deporte.pdf');
    }


    // ============================================================
    //  🔸 3) REPORTE POR DEPORTE + CANCHA
    // ============================================================
    public function ReportBySportAndCourt(Request $request)
    {
        $request->validate([
            'sport_id'   => 'required|integer',
            'court_id'   => 'required|integer',
            'startDate'  => 'required|date',
            'endDate'    => 'required|date|after_or_equal:startDate'
        ]);

        $sportId = $request->sport_id;
        $courtId = $request->court_id;

        // Datos del deporte y la cancha
        $sport      = Sport::find($sportId);
        $court      = SportCourt::find($courtId);

        $sportName  = $sport->name ?? "Desconocido";
        $courtNum   = $court->num_sportcourt ?? "N/A";

        $reservations = Reservation::with([
            'user',
            'schedule',
            'schedule.sportcourt.sport',
            'schedule.mode',
        ])
            ->whereHas('schedule.sportcourt', function ($q) use ($sportId, $courtId) {
                $q->where('sport_id', $sportId)
                    ->where('id', $courtId);
            })
            ->whereBetween('date', [$request->startDate, $request->endDate])
            ->orderBy('date', 'ASC')
            ->get();

        // Gráfica por horario
        $chartData = [];
        foreach ($reservations as $r) {
            $hour = $r->schedule->start_time . " - " . $r->schedule->end_time;
            $chartData[$hour] = ($chartData[$hour] ?? 0) + 1;
        }

        if (empty($chartData)) {
            $chartData["Sin datos"] = 1;
        }

        $chartConfig = [
            "type" => "bar",
            "data" => [
                "labels" => array_keys($chartData),
                "datasets" => [
                    [
                        "label" => "Movimientos por horario",
                        "data"  => array_values($chartData),
                        "backgroundColor" => "rgba(255, 193, 7, 0.8)"
                    ]
                ]
            ]
        ];

        $pngUrl = "https://quickchart.io/chart?format=png&width=700&height=400&c="
            . urlencode(json_encode($chartConfig));

        $chartImage = base64_encode(file_get_contents($pngUrl));

        // 🔹 SE ENVÍA NOMBRE DEL DEPORTE Y NÚMERO DE CANCHA
        $pdf = Pdf::loadView('reports.reportBySportCourt', [
            'reservations' => $reservations,
            'chartImage'   => $chartImage,
            'startDate'    => $request->startDate,
            'endDate'      => $request->endDate,
            'sportName'    => $sportName,
            'courtNumber'  => $courtNum,
        ]);

        return $pdf->stream("reporte_deporte_cancha.pdf");
    }
}
