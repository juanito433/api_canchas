<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte por Deporte</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            margin: 25px;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 6px;
            border: 1px solid #f4c740;
            text-align: center;
        }

        th {
            background: #f4c740;
            color: white;
        }

        .section-title {
            margin-top: 35px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .chart {
            margin-top: 25px;
            text-align: center;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <header>
        <h1>Haciendas Family & Fitness Club</h1>
        <h3>Reporte de Reservas por Deporte</h3>
    </header>

    <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    <p><strong>Deporte seleccionado:</strong> {{ $sportName }}</p>
    <p><strong>Período:</strong> {{ $startDate }} a {{ $endDate }}</p>
    <p><strong>Total de reservas:</strong> {{ $reservations->count() }}</p>

    <hr>

    <h2 class="section-title">Reservas del deporte: {{ $sportName }}</h2>

    <table>
        <thead>
            <tr>
                <th>Folio Reserva</th>
                <th>Folio Usuario</th>
                <th>Cancha</th>
                <th>Fecha Reservada</th>
                <th>Fecha de Registro</th>
                <th>Horario</th>
                <th>Modalidad</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($reservations as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->user->id }}</td>
                <td>{{ $r->schedule->sportcourt->num_sportcourt }}</td>
                <td>{{ $r->date }}</td>
                <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $r->schedule->start_time }} - {{ $r->schedule->end_time }}</td>
                <td>{{ $r->schedule->mode->name }}</td>
                <td>{{ ucfirst($r->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2 class="section-title">Gráfica de uso por cancha</h2>

    <div class="chart">
        <img src="data:image/png;base64,{{ $chartImage }}" style="width: 80%; height: auto;">
    </div>

</body>

</html>