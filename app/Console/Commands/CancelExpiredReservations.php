<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penalty;
use App\Models\Reservation; // ¡CORREGIDO: Clase "Reservation" con "R" mayúscula!
use Carbon\Carbon;

class CancelExpiredReservations extends Command
{
    protected $signature = 'reservations:cancel-expired';
    protected $description = 'Cancela las reservas pendientes vencidas y penaliza a los usuarios';

    public function handle()
    {
        $now = Carbon::now();
        $this->info("Iniciando la cancelación de reservas expiradas a las: {$now->toDateTimeString()}");
        $count = 0;

        // 1. Obtener reservas pendientes y reservadas que necesitan confirmación.
        // CORRECCIÓN: Relación 'schedule' debe ser singular si es 1:1.
        $pendingReservations = Reservation::where('confirmation', 'Pendiente')
            ->where('status', 'Reservada') // Usar 'Reservada' como en tu comando de debug
            ->with('schedule') // CORRECCIÓN: Asegúrate de que este sea el nombre correcto de la relación en el modelo Reservation
            ->get();

        if ($pendingReservations->isEmpty()) {
            $this->info("No se encontraron reservas pendientes para evaluar.");
            return;
        }

        $this->info("Analizando " . $pendingReservations->count() . " reservas...");

        foreach ($pendingReservations as $reservation) {
            // 2. Validación de existencia del schedule
            if (!$reservation->schedule) {
                $this->warn("Reserva ID {$reservation->id} omitida: No tiene un schedule asignado.");
                continue;
            }

            // 3. Cálculo de la hora de fin (Lógica de expiración)
            try {
                // Usamos la lógica de la corrección anterior: fecha de reserva + hora de fin del schedule.
                $reservationEnd = Carbon::parse($reservation->date)
                    ->setTimeFromTimeString($reservation->schedule->end_time);
            } catch (\Exception $e) {
                $this->error("Error al parsear el tiempo para la Reserva ID {$reservation->id}: " . $e->getMessage());
                continue;
            }

            // 4. Lógica de cancelación y penalización
            if ($reservationEnd->lt($now)) {

                // 4.1. Actualizar la reserva
                $reservation->update([
                    'status' => 'Penalizada', // Más descriptivo que 'Cancelado' si fue por el sistema
                    'confirmation' => 'Expirada'
                ]);

                // 4.2. Crear la penalización
                Penalty::create([
                    'user_id' => $reservation->user_id,
                    'cause' => 'No confirmó su reserva a tiempo. Reserva Folio: ' . $reservation->id,
                    'date' => $now, // Dejar que Carbon maneje el tipo de dato si la columna es timestamp
                    'expiration_date' => $now->copy()->addDays(15), // Dejar que Carbon maneje el tipo de dato
                    'penalty' => 'Bloqueo temporal para reservar',
                ]);

                $this->info("✅ Reserva ID {$reservation->id} (finaliza: {$reservationEnd->toDateTimeString()}) cancelada y usuario penalizado.");
                $count++;
            } else {
                $this->info("⏸️ Reserva ID {$reservation->id} (finaliza: {$reservationEnd->toDateTimeString()}) aún no expira.");
            }
        }

        $this->info("---");
        $this->info("Comando finalizado. Se procesaron " . $pendingReservations->count() . " reservas.");
        $this->info("Se cancelaron y penalizaron **$count** reservas vencidas.");
    }
}
