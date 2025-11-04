<?php

namespace App\Console\Commands;

use App\Models\reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendPendingReservationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:pending-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificaciones a usuarios con reservaciones no confirmadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $pending = reservation::with('user')
            ->where('confirmation', 'Pendiente')
            ->whereDate('date', $now->toDateString())
            ->whereTime('start_time', '>', $now->toTimeString())
            ->get();

        foreach ($pending as $reserva) {
            if (!$reserva->user->expo_push_token) continue;

            Http::post('https://exp.host/--/api/v2/push/send', [
                'to' => $reserva->user->expo_push_token,
                'sound' => 'default',
                'title' => 'Reserva pendiente por confirmar',
                'body' => "Tienes una reserva para la cancha {$reserva->court->name} a las {$reserva->start_time}. Confírmala antes de que expire.",
                'data' => ['reservation_id' => $reserva->id],
            ]);
        }

        $this->info('Notificaciones enviadas correctamente.');
    }
}
