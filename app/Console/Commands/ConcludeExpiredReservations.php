<?php

namespace App\Console\Commands;

use App\Models\reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ConcludeExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /*     protected $signature = 'app:conclude-expired-reservations';
 */
    /**
     * The console command description.
     *
     * @var string
     */
    /*     protected $description = 'Command description';
 */
    /**
     * Execute the console command.
     */


    protected $signature = 'reservations:conclude-expired';
    protected $description = 'Concluir automáticamente las reservaciones vencidas y liberar horarios';
    public function handle()
    {
        $now = Carbon::now();

        $expiredReservations = reservation::where('status', 'activa')
            ->whereDate('date', '<=', $now->toDateString())
            ->get();

        $count = 0;

        foreach ($expiredReservations as $reservation) {
            $endTime = Carbon::parse("{$reservation->date} {$reservation->schedule->end_time}");

            if ($now->greaterThan($endTime)) {
                // Cambiar estado de reservación
                $reservation->status = 'concluida';
                $reservation->save();

                // Liberar horario
                $schedule = $reservation->schedule;
                $schedule->status = 'Libre';
                $schedule->save();

                $count++;
            }
        }

        $this->info("Reservaciones concluidas: {$count}");
    }
}
