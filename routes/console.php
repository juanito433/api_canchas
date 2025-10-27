<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Comando "inspire" (de ejemplo)
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Problema #1: ELIMINAR el comando manual redundante
|--------------------------------------------------------------------------
| Este bloque es innecesario. Cuando ejecutas 'php artisan reservations:cancel-expired',
| el comando ya existe en tu clase CancelExpiredReservations y no necesita ser
| declarado aquí. ¡Elimínalo!
|
Artisan::command('reservations:cancel-expired', function () {
    Artisan::call('reservations:cancel-expired');
});
*/


// ✅ Programar el comando para la cancelación de reservas
// ---
// Problema #2: Usar Schedule::command() directamente en lugar de Schedule::call()
// ---
// Esto es mucho más limpio y la forma recomendada en Laravel
Schedule::command('reservations:cancel-expired')
    ->everyMinute() // Perfecto para pruebas locales/staging
    // ->hourly() // Alternativa si lo quieres ejecutar solo una vez por hora
    // ->onOneServer() // Recomendado para entornos de producción con múltiples servidores
    ->environments(['local', 'production']) // Aplicar solo en estos entornos, o eliminar esta línea para que corra en todos.
    ->runInBackground(); // (Opcional) Recomendado si tienes varios comandos scheduler