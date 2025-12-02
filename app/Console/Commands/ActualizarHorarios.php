<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\schedules; // Tu modelo (respetando minúsculas como lo tienes)

class ActualizarHorarios extends Command
{
    protected $signature = 'horarios:reset';
    protected $description = 'Libera todos los horarios ocupados a las 00:00';

    public function handle()
    {
        // AL NO TENER FECHA:
        // Simplemente le decimos a la base de datos: 
        // "Busca todo lo que esté 'Ocupado' y cámbialo a 'Disponible' de nuevo".

        $afectados = schedules::where('status', 'Ocupado')
            ->update(['status' => 'Disponible']);

        // Nota: Corregí tu typo de "Diponible" a "Disponible". 
        // Asegúrate de usar la palabra exacta que espera tu App.

        $this->info("Reinicio completado. Se liberaron {$afectados} horarios.");
    }
}
