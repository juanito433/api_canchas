<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\penalty;
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

        // --- 1. Obtención de Reservaciones ---
        $reservations = Reservation::with([
            'user',
            'schedule',
            'schedule.sportcourt.sport',
            'schedule.mode',
        ])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'ASC')
            ->get();

        // Conteo de reservas por deporte (Se mantiene la lógica para la gráfica)
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

        // Agrupación por deporte para la tabla de reservas
        $reservasPorDeporte = $reservations->groupBy(function ($item) {
            return $item->schedule->sportcourt->sport->name ?? 'N/A';
        });

        // --- 2. Obtención y Procesamiento de Penalizaciones (NUEVA LÓGICA) ---
        $penalties = penalty::with([
            'user', // Relación para obtener el nombre del usuario
            // Relación para obtener el deporte a través de la Reserva
            'reservation.schedule.sportcourt.sport'
        ])
            // Filtramos las penalizaciones cuya fecha de aplicación ('date')
            // esté dentro del rango del reporte.
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'ASC')
            ->get()
            // Mapeamos para estructurar los datos para la tabla del reporte
            ->map(function ($penalty) {
                // Lógica para obtener el nombre del deporte desde la reserva, si existe
                $sportName = 'N/A';
                if (
                    $penalty->reservation &&
                    $penalty->reservation->schedule &&
                    $penalty->reservation->schedule->sportcourt &&
                    $penalty->reservation->schedule->sportcourt->sport
                ) {

                    $sportName = $penalty->reservation->schedule->sportcourt->sport->name;
                } elseif ($penalty->reservation_id) {
                    $sportName = 'Reserva Eliminada'; // Caso donde el ID existe pero la reserva no
                } else {
                    $sportName = 'Sin Reserva Asociada'; // Si reservation_id es NULL
                }

                return [
                    'folio'             => $penalty->id,
                    'user_id'           => $penalty->user->id ?? 'N/A', // ¡NUEVO CAMPO AÑADIDO!
                    'user_name'         => $penalty->user->name . ' ' . $penalty->user->lastname ?? 'Usuario Eliminado',
                    'penalty_type'      => $penalty->penalty,
                    'sport'             => $sportName,
                    'reservation_id'    => $penalty->reservation_id ?? 'N/A',
                    'date'              => $penalty->date,
                    'expiration_date'   => $penalty->expiration_date,
                ];
            });


        // --- 3. Generación del PDF ---
        $pdf = Pdf::loadView('reports.reportGeneral', [
            'reservations'        => $reservations,
            'chartImage'          => $chartImage,
            'reservasPorDeporte'  => $reservasPorDeporte,
            'startDate'           => $startDate,
            'endDate'             => $endDate,
            'penalties'           => $penalties, // Pasar la colección de penalizaciones procesada
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

        // --- 1. Obtener Reservas del deporte específico ---
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

        // Conteo para gráfica (Lógica se mantiene)
        $chartData = [];
        foreach ($reservations as $r) {
            $court = "Cancha " . ($r->schedule->sportcourt->num_sportcourt ?? 'N/A');
            $chartData[$court] = ($chartData[$court] ?? 0) + 1;
        }

        if (empty($chartData)) {
            $chartData['Sin registros'] = 1;
        }

        // Paleta y configuración de la gráfica (Lógica se mantiene)
        $colors = [
            'rgba(255, 206, 86, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(253, 216, 53, 0.8)',
            'rgba(255, 235, 59, 0.8)',
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

        // --- 2. Obtener Penalizaciones relacionadas con este Deporte ---

        $penalties = Penalty::with([
            'user',
            'reservation.schedule.sportcourt.sport'
        ])
            ->whereBetween('date', [$startDate, $endDate])
            // FILTRO CLAVE: Solo penalizaciones cuya reserva esté relacionada
            // con una cancha que a su vez esté relacionada con el Sport ID actual.
            ->whereHas('reservation.schedule.sportcourt', function ($q) use ($sportId) {
                $q->where('sport_id', $sportId);
            })
            ->orderBy('date', 'ASC')
            ->get()
            ->map(function ($penalty) {
                // Sabemos que la penalización tiene una reserva y un deporte asociado
                // gracias al whereHas de arriba.
                $sportName = $penalty->reservation->schedule->sportcourt->sport->name ?? 'N/A';

                return [
                    'folio'             => $penalty->id,
                    'user_id'           => $penalty->user->id ?? 'N/A',
                    'user_name'         => $penalty->user->name . ' ' . $penalty->user->lastname ?? 'Usuario Eliminado',
                    'penalty_type'      => $penalty->penalty,
                    'sport'             => $sportName,
                    'reservation_id'    => $penalty->reservation_id ?? 'N/A',
                    'date'              => $penalty->date,
                    'expiration_date'   => $penalty->expiration_date,
                ];
            });


        // 🔹 SE ENVÍA EL NOMBRE DEL DEPORTE Y LAS PENALIZACIONES A LA VISTA
        $pdf = Pdf::loadView('reports.reportBySport', [
            'reservations' => $reservations,
            'chartImage'   => $chartImage,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'sportName'    => $sportName,
            'penalties'    => $penalties, // ¡NUEVO!
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
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        // Datos del deporte y la cancha
        $sport      = Sport::find($sportId);
        $court      = SportCourt::find($courtId);

        $sportName  = $sport->name ?? "Desconocido";
        $courtNum   = $court->num_sportcourt ?? "N/A";

        // --- 1. Obtener Reservas del deporte y cancha específicos ---
        $reservations = Reservation::with([
            'user',
            'schedule',
            'schedule.sportcourt.sport',
            'schedule.mode',
        ])
            ->whereHas('schedule.sportcourt', function ($q) use ($sportId, $courtId) {
                $q->where('sport_id', $sportId)
                    ->where('id', $courtId); // Filtra por la cancha ID específica
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'ASC')
            ->get();

        // Gráfica por horario (Lógica se mantiene)
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

        // --- 2. Obtener Penalizaciones relacionadas con este Deporte y Cancha ---

        $penalties = Penalty::with([
            'user',
            'reservation.schedule.sportcourt.sport'
        ])
            ->whereBetween('date', [$startDate, $endDate])
            // FILTRO CLAVE: Solo penalizaciones cuya reserva esté relacionada
            // con la Cancha ID específica.
            ->whereHas('reservation.schedule', function ($q) use ($courtId) {
                $q->where('sportcourt_id', $courtId);
            })
            ->orderBy('date', 'ASC')
            ->get()
            ->map(function ($penalty) use ($sportName) {

                // Aquí no necesitamos la lógica compleja de detección de deporte,
                // ya que el filtro `whereHas` lo garantiza.

                return [
                    'folio'             => $penalty->id,
                    'user_id'           => $penalty->user->id ?? 'N/A',
                    'user_name'         => $penalty->user->name . ' ' . $penalty->user->lastname ?? 'Usuario Eliminado',
                    'penalty_type'      => $penalty->penalty,
                    'sport'             => $sportName, // Usamos el nombre del deporte ya obtenido
                    'reservation_id'    => $penalty->reservation_id ?? 'N/A',
                    'date'              => $penalty->date,
                    'expiration_date'   => $penalty->expiration_date,
                ];
            });


        // 🔹 Se actualiza la vista con las penalizaciones
        $pdf = Pdf::loadView('reports.reportBySportCourt', [
            'reservations' => $reservations,
            'chartImage'   => $chartImage,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'sportName'    => $sportName,
            'courtNumber'  => $courtNum,
            'penalties'    => $penalties, // ¡NUEVO!
        ]);

        return $pdf->stream("reporte_deporte_cancha.pdf");
    }
}
