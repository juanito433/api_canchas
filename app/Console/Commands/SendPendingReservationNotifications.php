<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Notice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPendingReservationNotifications extends Command
{
    protected $signature = 'notify:pending-reservations';
    protected $description = 'Envía notificaciones 10 minutos antes de una reserva pendiente por confirmar';

    public function handle()
    {
        // 1. Obtener la hora actual y sumar 10 minutos exactos
        // Importante: Ajusta la timezone si es necesario. Ej: 'America/Mexico_City'
        // Si tu APP_TIMEZONE en .env es correcta, usa solo Carbon::now()
        $targetTime = Carbon::now()->addMinutes(10)->seconds(0);

        $targetDateString = $targetTime->toDateString(); // Ej: 2023-11-24
        $targetTimeString = $targetTime->format('H:i:s'); // Ej: 14:30:00

        Log::info('NOTIFY COMMAND - Buscando reservas para:', [
            'fecha_objetivo' => $targetDateString,
            'hora_inicio_objetivo' => $targetTimeString
        ]);

        // 2. Buscar reservas
        // Coincidencia: Que la fecha sea hoy Y la hora del horario sea igual a (Ahora + 10min)
        $pending = Reservation::with(['user', 'schedule.sportcourt'])
            ->where('confirmation', 'Pendiente')
            ->whereDate('date', $targetDateString)
            ->whereHas('schedule', function ($q) use ($targetTimeString) {
                $q->where('start_time', $targetTimeString);
            })
            ->get();

        Log::info("Reservas encontradas: " . $pending->count());

        foreach ($pending as $reserva) {
            // Validar Token
            if (!$reserva->user || !$reserva->user->expo_push_token) {
                Log::warning("Usuario sin token o no encontrado. Reserva ID: {$reserva->id}");
                continue;
            }

            $this->sendNotificationToUser($reserva);
        }

        $this->info('Proceso finalizado.');
    }

    private function sendNotificationToUser($reserva)
    {
        $user = $reserva->user;
        $schedule = $reserva->schedule;
        $cancha = $schedule->sportcourt->name ?? 'Cancha desconocida';

        // Formatear la hora para que se vea bien (ej: 14:00)
        $horaLegible = Carbon::parse($schedule->start_time)->format('H:i');

        try {
            // 1. Crear el aviso en la BD (Notice)
            // Asumimos que user_id=1 es el sistema o admin.
            $notice = Notice::create([
                'title'          => "⏳ Confirma tu reserva",
                'content'        => "Tu juego en {$cancha} comienza a las {$horaLegible}. ¡Confirma asistencia ahora!",
                'user_id'        => 1, // ID del creador del aviso (Admin/Sistema)
                'date_published' => now(),
            ]);

            // 2. Relacionar aviso con el usuario ESPECÍFICO (Tabla Pivote notice_user)
            // Esto hace que la notificación sea privada para este usuario
            $notice->users()->attach($user->id, ['created_at' => now(), 'updated_at' => now()]);

            Log::info("Aviso guardado en BD para usuario {$user->id}");

            // 3. Enviar Push a Expo
            $response = Http::post('https://exp.host/--/api/v2/push/send', [
                [
                    'to'    => $user->expo_push_token,
                    'sound' => 'default',
                    'title' => "⏳ Confirma tu reserva",
                    'body'  => "Faltan 10 min para tu juego en {$cancha}. Toca para confirmar.",
                    'data'  => [
                        'screen' => 'ReservationDetails', // Opcional: Para navegar en React Native
                        'reservation_id' => $reserva->id,
                        'notice_id' => $notice->id
                    ],
                ]
            ]);

            Log::info('Push enviado', ['status' => $response->status(), 'user' => $user->id]);
        } catch (\Exception $e) {
            Log::error("Error enviando notificación: " . $e->getMessage());
        }
    }
}
