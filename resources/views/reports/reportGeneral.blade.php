<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Reservas - Haciendas Family & Fitness Club</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        header img {
            width: 80px;
            float: left;
        }

        header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }

        header p {
            margin: 2px 0;
            font-size: 12px;
        }

        .report-info {
            margin: 15px 0;
            font-size: 12px;
        }

        .report-info p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #f4c740;
            padding: 6px;
            text-align: center;
        }

        th {
            background: #f4c740;
            color: #fff;
            font-size: 13px;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background: #f3f4f6;
        }

        tr:hover {
            background: #e5e7eb;
        }

        footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #555;
            border-top: 1px solid #aaa;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <!-- Encabezado con logo -->
    <header>
        <img src="{{ public_path('images/logo.png') }}" alt="Logo">
        <h1>Haciendas Family & Fitness Club</h1>
        <p>Reporte de Uso de Canchas Deportivas</p>
    </header>

    <!-- Información del reporte -->
    <section class="report-info">
        <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        <p><strong>Total de reservas:</strong> {{ $reservations->count() }}</p>
        <p><strong>Período:</strong> {{ $startDate ?? 'N/A' }} - {{ $endDate ?? 'N/A' }}</p>
    </section>

    <!-- Tabla de reservas -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Deporte</th>
                <th>Cancha</th>
                <th>Fecha</th>
                <th>Horario</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservations as $reservation)
            <tr>
                <td>{{ $reservation->id }}</td>
                <td>{{ $reservation->user->name }}</td>
                <td>{{ $reservation->schedule->sportcourt->sport->name ?? 'N/A' }}</td>
                <td>{{ $reservation->schedule->sportcourt->num_sportcourt ?? 'N/A' }}</td>
                <td>{{ $reservation->date }}</td>
                <td>{{ $reservation->schedule->start_time }} - {{ $reservation->schedule->end_time }}</td>
                <td>{{ ucfirst($reservation->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="chart">
        <h3>Uso de canchas por deporte</h3>
        {!! $svgContent !!}
    </div>



    <!-- Pie de página -->
    <footer>
        <p>Haciendas Family & Fitness Club © {{ date('Y') }} | Reporte generado automáticamente</p>
        <p>Contacto: info@hffc.com | Tel: (000) 123-4567</p>
    </footer>
</body>

</html>