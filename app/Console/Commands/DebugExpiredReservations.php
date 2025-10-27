<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use Carbon\Carbon;

class DebugExpiredReservations extends Command
{
    protected $signature = 'reservations:debug-expired';
    protected $description = 'Depura reservas pendientes para ver cuáles deberían expirar';

    public function handle()
    {
        $now = Carbon::now();
        $this->info("Fecha y hora actual: {$now->toDateTimeString()}");

        $reservations = Reservation::where('confirmation', 'Pendiente')
            ->where('status', 'Reservada')
            ->with('schedule')
            ->get();

        if ($reservations->isEmpty()) {
            $this->info("No se encontraron reservas pendientes o reservadas.");
            return;
        }

        foreach ($reservations as $res) {
            if (!$res->schedule) {
                $this->warn("Reserva ID {$res->id} sin schedule asignado.");
                continue;
            }

            $reservationEnd = Carbon::parse($res->date)
                ->setTimeFromTimeString($res->schedule->end_time);

            $this->info("Reserva ID {$res->id}: fecha {$res->date}, hora fin {$res->schedule->end_time}, combinada: {$reservationEnd->toDateTimeString()}");

            if ($reservationEnd->lt($now)) {
                $this->info("→ Esta reserva debería ser cancelada (expirada).");
            } else {
                $this->info("→ Esta reserva aún es válida.");
            }
        }
    }
}
