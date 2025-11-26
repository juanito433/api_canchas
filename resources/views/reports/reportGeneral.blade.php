<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Reservas y Penalizaciones</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            margin: 25px;
        }

        header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #f4c740;
            /* Línea de color de marca */
            padding-bottom: 10px;
        }

        h1 {
            color: #333;
            font-size: 24px;
            margin: 0;
        }

        h3 {
            color: #777;
            font-size: 16px;
            margin: 5px 0 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ddd;
            /* Borde más suave */
            text-align: left;
            /* Alineación a la izquierda por defecto */
        }

        /* Ajuste para centrar en las tablas de reservas */
        .reservas-table th,
        .reservas-table td {
            text-align: center;
        }

        th {
            background-color: #f4c740;
            /* Color de marca */
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
            /* Rayado para mejor lectura */
        }

        .section-title {
            margin-top: 35px;
            font-size: 18px;
            font-weight: bold;
            color: #444;
            border-left: 5px solid #f4c740;
            padding-left: 10px;
            margin-bottom: 15px;
        }

        .summary p {
            margin: 5px 0;
        }

        .chart {
            margin-top: 25px;
            text-align: center;
        }

        /* 🔥 SALTO DE PÁGINA REAL PARA DOMPDF */
        .page-break {
            page-break-before: always;
        }

        .penalties-section {
            margin-top: 40px;
        }

        .penalties-section h2 {
            color: #b71c1c;
            /* Color de advertencia o rojo para penalizaciones */
            border-left-color: #b71c1c;
        }

        .penalties-table th,
        .penalties-table td {
            text-align: center;
        }
    </style>
</head>

<body>

    <header>
        <h1>Haciendas Family & Fitness Club</h1>
        <h3>Reporte General de Reservas y Penalizaciones</h3>
    </header>

    <div class="summary">
        <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p><strong>Total de reservas en el período:</strong> {{ $reservations->count() }}</p>
        <p><strong>Total de penalizaciones aplicadas:</strong> {{ $penalties->count() }}</p>
    </div>

    <hr style="border: 1px solid #eee; margin: 20px 0;">

    {{-- TABLAS DETALLADAS POR CADA DEPORTE --}}
    @foreach($reservasPorDeporte as $deporte => $items)

    <h2 class="section-title">Deporte: {{ $deporte }} (Total: {{ $items->count() }})</h2>

    <table class="reservas-table">
        <thead>
            <tr>
                <th>Folio Reserva</th>
                <th>Folio Usuario</th>
                <th>Cancha</th>
                <th>Fecha Reservada</th>
                <th>Horario</th>
                <th>Modalidad</th>
                <th>Estado</th>
                <th>Registro</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($items as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->user->id }}</td>
                <td>{{ $r->schedule->sportcourt->num_sportcourt }}</td>
                <td>{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
                <td>{{ $r->schedule->start_time }} - {{ $r->schedule->end_time }}</td>
                <td>{{ $r->schedule->mode->name }}</td>
                <td>{{ ucfirst($r->status) }}</td>
                <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="page-break"></div>


    @endforeach

    <!-- 🔥 FORZAR HOJA NUEVA ANTES DE LA GRÁFICA -->
    <!--     <div class="page-break"></div>
 -->
    {{-- GRÁFICA DE RESERVAS --}}
    <h2 class="section-title">Análisis de Reservas por Deporte</h2>

    <div class="chart">
        <img src="data:image/png;base64,{{ $chartImage }}" style="width: 80%; height: auto;">
    </div>

    <!-- 🔥 SECCIÓN DE PENALIZACIONES (NUEVA) -->
    <div class="page-break"></div>
    <div class="penalties-section">

        <h2 class="section-title">Registro de Penalizaciones</h2>

        <table class="penalties-table">
            <thead>
                <tr>
                    <th>Folio de Usuario</th>
                    <th>Usuario Penalizado</th>
                    <th>Tipo de Penalización</th>
                    <th>Deporte</th>
                    <th>Folio Reserva</th>
                    <th>Aplicación</th>
                    <th>Expiración</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($penalties as $penalty)
                <tr>
                    <td>#{{ $penalty['user_id'] }}</td>
                    <td>{{ $penalty['user_name'] }}</td>
                    <td>{{ $penalty['penalty_type'] }}</td>
                    <td>{{ $penalty['sport'] }}</td>
                    <td>{{ $penalty['reservation_id'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($penalty['date'])->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($penalty['expiration_date'])->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #777;">
                        No se registraron penalizaciones en el período del {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <footer>
        <p style="text-align: center; font-size: 10px; color: #999;">
            Reporte generado automáticamente por el sistema de gestión de reservas del club.
        </p>
    </footer>

</body>

</html>