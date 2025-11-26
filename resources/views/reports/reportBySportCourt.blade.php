<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte por Deporte y Cancha</title>

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

        /* Estilo para la sección de penalizaciones */
        .penalties-section th {
            background: #b71c1c;
            /* Rojo para penalizaciones */
        }

        .penalties-section table {
            margin-top: 15px;
            border: 1px solid #b71c1c;
        }

        .penalties-section td {
            border: 1px solid #e0e0e0;
        }

        /* Título de sección general */
        .section-title {
            margin-top: 35px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Título de sección de penalizaciones */
        .penalties-section .section-title {
            color: #b71c1c;
            border-bottom: 2px solid #b71c1c;
            padding-bottom: 5px;
        }

        .chart {
            margin-top: 25px;
            text-align: center;
        }

        /* Filas sin datos */
        .empty-row {
            color: #777;
            font-style: italic;
            background-color: #fcf8f8;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <header>
        <h1>Haciendas Family & Fitness Club</h1>
        <h3>Reporte por Deporte y Cancha</h3>
    </header>

    <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    <p><strong>Deporte:</strong> {{ $sportName }}</p>
    <p><strong>Cancha:</strong> {{ $courtNumber }}</p>
    <p><strong>Período:</strong> {{ $startDate }} a {{ $endDate }}</p>
    <p><strong>Total de reservas:</strong> {{ $reservations->count() }}</p>
    <p><strong>Total de penalizaciones:</strong> {{ $penalties->count() }}</p> <!-- Agregado el total de penalizaciones -->

    <hr>

    <h2 class="section-title">Reservas del deporte {{ $sportName }} - Cancha {{ $courtNumber }}</h2>

    <table>
        <thead>
            <tr>
                <th>Folio Reserva</th>
                <th>Folio Usuario</th>
                <th>Fecha Reservada</th>
                <th>Fecha de Registro</th>
                <th>Horario</th>
                <th>Modalidad</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($reservations as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->user->id }}</td>
                <td>{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
                <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $r->schedule->start_time }} - {{ $r->schedule->end_time }}</td>
                <td>{{ $r->schedule->mode->name }}</td>
                <td>{{ ucfirst($r->status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #777;" class="empty-row">
                    No hay reservas registradas para esta cancha en el período seleccionado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2 class="section-title">Gráfica de movimientos por horario</h2>

    <div class="chart">
        <img src="data:image/png;base64,{{ $chartImage }}" style="width: 80%; height: auto;">
    </div>

    <!-- INICIO: SECCIÓN DE PENALIZACIONES -->
    <div class="page-break"></div>
    <div class="penalties-section">

        <h2 class="section-title">Registro de Penalizaciones (Relacionadas con Cancha {{ $courtNumber }})</h2>

        <table>
            <thead>
                <tr>
                    <th>Folio de Usuario</th>
                    <th>Usuario Penalizado</th>
                    <th>Tipo de Penalización</th>
                    <th>Deporte Asociado</th>
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
                    <td colspan="7" style="text-align: center; color: #777;" class="empty-row">
                        No se registraron penalizaciones relacionadas con esta cancha en el período seleccionado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- FIN: SECCIÓN DE PENALIZACIONES -->

    <footer>
        <p style="text-align: center; font-size: 10px; color: #999;">
            Reporte generado automáticamente por el sistema de gestión del club.
        </p>
    </footer>

</body>

</html>