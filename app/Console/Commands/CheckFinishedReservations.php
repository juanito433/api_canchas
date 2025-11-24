<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\schedules;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class CheckFinishedReservations extends Command
{
    protected $signature = 'reservations:check-finished';
    protected $description = 'Marca como concluidas las reservaciones cuyo horario ya terminó y libera el horario';

    public function handle()
    {
        // Tiempo actual
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        Log::info("CHECK FINISHED COMMAND - Inicio", [
            'now' => $now->toDateTimeString(),
            'date' => $today,
            'hour' => $currentTime
        ]);

        try {
            // Buscar reservaciones confirmadas que ya terminaron
            $reservations = Reservation::where('status', 'Confirmada')
                ->whereDate('date', $today)
                ->whereHas('schedule', function ($query) use ($currentTime) {
                    $query->where('end_time', '<=', $currentTime);
                })
                ->get();

            Log::info("CHECK FINISHED COMMAND - Reservas encontradas", [
                'cantidad' => $reservations->count(),
                'ids' => $reservations->pluck('id')
            ]);

            if ($reservations->isEmpty()) {
                Log::info("CHECK FINISHED COMMAND - No hay reservas para procesar.");
                return;
            }

            foreach ($reservations as $reservation) {
                Log::info("CHECK FINISHED COMMAND - Procesando reserva", [
                    'reservation_id' => $reservation->id,
                    'schedule_id' => $reservation->schedule_id
                ]);

                // Actualizar reservación
                $reservation->status = 'Concluida';
                $reservation->confirmation = 'Concluida';
                $reservation->save();

                Log::info("CHECK FINISHED COMMAND - Reservación marcada como Concluida", [
                    'reservation_id' => $reservation->id
                ]);

                // Liberar horario
                $schedule = schedules::find($reservation->schedule_id);

                if ($schedule) {
                    $schedule->status = 'Disponible';
                    $schedule->save();

                    Log::info("CHECK FINISHED COMMAND - Horario liberado", [
                        'schedule_id' => $schedule->id,
                        'nuevo_status' => $schedule->status
                    ]);
                } else {
                    Log::warning("CHECK FINISHED COMMAND - No se encontró el horario asociado", [
                        'reservation_id' => $reservation->id
                    ]);
                }
            }

            Log::info("CHECK FINISHED COMMAND - Proceso finalizado.", [
                'total_procesadas' => $reservations->count()
            ]);
        } catch (\Exception $e) {
            Log::error("CHECK FINISHED COMMAND - ERROR", [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine()
            ]);
        }
    }
}
