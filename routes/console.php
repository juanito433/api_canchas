<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//Programar ejecución automática del comando de cancelación de reservas
Schedule::command('reservations:cancel-expired')
    ->everyMinute() // Perfecto para pruebas locales/staging
    // ->hourly() // Alternativa si lo quieres ejecutar solo una vez por hora
    // ->onOneServer() // Recomendado para entornos de producción con múltiples servidores
    ->environments(['local', 'production']) // Aplicar solo en estos entornos, o eliminar esta línea para que corra en todos.
    ->runInBackground(); // (Opcional) Recomendado si tienes varios comandos scheduler

Schedule::command('notify:pending-reservations')
    ->everyMinute()
    ->environments(['local', 'production'])
    ->runInBackground(false)
    ->appendOutputTo(storage_path('logs/notificaciones_reserva.log'))
    ->runInBackground();

Schedule::command('reservations:check-finished')
    ->everyMinute()
    ->environments(['local', 'production'])
    ->runInBackground();

Schedule::command('horarios:reset')
    ->dailyAt('00:00')
    ->environments(['local', 'production'])
    ->runInBackground();
