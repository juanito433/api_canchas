<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Notice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendPendingReservationNotifications extends Command
{
    protected $signature = 'notify:pending-reservations';
    protected $description = 'Notifica reservas pendientes prox 10 min (Con Fix de Logs y Cache)';

    /**
     * FUNCIÓN PARA ARREGLAR EL ERROR DE PERMISOS EN WINDOWS
     * Escribe en un archivo separado para no pelear con laravel.log
     */
    private function customLog($message, $level = 'info')
    {
        try {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/scheduler_notificaciones.log'), // Archivo exclusivo
            ])->$level($message);
        } catch (\Exception $e) {
            // Si falla el log, no detenemos el programa, solo lo mostramos en consola si es posible
            $this->error("Error escribiendo log: " . $e->getMessage());
        }
    }

    public function handle()
    {
        // 1. Definir ventana de tiempo (Ahora -> Ahora + 10 min)
        $now = Carbon::now();
        $tenMinutesFromNow = $now->copy()->addMinutes(10);

        // Usamos customLog en lugar de Log::info
        $this->customLog("🔍 Buscando reservas entre {$now->toTimeString()} y {$tenMinutesFromNow->toTimeString()}");

        // 2. Buscar Reservas
        $pending = Reservation::with(['user', 'schedule.sportcourt'])
            ->where('confirmation', 'Pendiente')
            ->whereDate('date', $now->toDateString())
            ->whereHas('schedule', function ($q) use ($now, $tenMinutesFromNow) {
                // Rango: Que la hora sea mayor a AHORA y menor o igual a AHORA+10
                $q->whereTime('start_time', '>', $now->toTimeString())
                    ->whereTime('start_time', '<=', $tenMinutesFromNow->toTimeString());
            })
            ->get();

        if ($pending->isEmpty()) {
            // Opcional: Descomenta si quieres ver heartbeat cada minuto
            // $this->customLog("No hay reservas pendientes en rango.");
            return;
        }

        foreach ($pending as $reserva) {
            // Validaciones
            if (!$reserva->user || empty($reserva->user->expo_push_token)) {
                continue;
            }

            // 3. EVITAR SPAM CON CACHE
            $cacheKey = "notified_reservation_{$reserva->id}";

            if (Cache::has($cacheKey)) {
                $this->customLog("⏭️ Reserva {$reserva->id} ya notificada. Saltando.");
                continue;
            }

            // Enviar notificación
            $sent = $this->sendNotificationToUser($reserva);

            if ($sent) {
                // 4. BLOQUEAR POR 30 MINUTOS
                // Así aseguramos que solo se envíe 1 vez por reserva
                Cache::put($cacheKey, true, now()->addMinutes(30));
            }
        }

        $this->info('Proceso finalizado.');
    }

    private function sendNotificationToUser($reserva)
    {
        $user = $reserva->user;
        $schedule = $reserva->schedule;
        $cancha = $schedule->sportcourt->name ?? 'Cancha';

        $startTime = Carbon::parse($schedule->start_time);
        $minutesLeft = Carbon::now()->diffInMinutes($startTime, false);
        $horaLegible = $startTime->format('g:i A');

        try {
            // Guardar Aviso en BD
            $notice = Notice::create([
                'title'          => "⏳ ¡Tu juego comienza pronto!",
                'content'        => "Faltan {$minutesLeft} min para tu juego en {$cancha}. ¡Confirma!",
                'user_id'        => 1, // ID Admin
                'date_published' => now(),
            ]);

            $notice->users()->attach($user->id, ['created_at' => now(), 'updated_at' => now()]);

            // Enviar Push a Expo
            Http::post('https://exp.host/--/api/v2/push/send', [
                [
                    'to'    => $user->expo_push_token,
                    'sound' => 'default',
                    'title' => "⏳ ¡Acción Requerida!",
                    'body'  => "Faltan {$minutesLeft} min ({$horaLegible}). Toca para confirmar asistencia.",
                    'data'  => [
                        'screen'         => 'ReservationDetails',
                        'reservation_id' => $reserva->id,
                        'notice_id'      => $notice->id
                    ],
                ]
            ]);

            $this->customLog("✅ Push enviado (Faltan {$minutesLeft} min) - Reserva {$reserva->id}");
            return true;
        } catch (\Exception $e) {
            $this->customLog("❌ Error enviando a Reserva {$reserva->id}: " . $e->getMessage(), 'error');
            return false;
        }
    }
}
