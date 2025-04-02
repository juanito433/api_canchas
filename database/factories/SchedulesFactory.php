<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\schedules>
 */
class SchedulesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseHour = $this->faker->numberBetween(8, 20); // Genera una hora base entre 8 y 20 (8:00 AM - 8:00 PM)
        $baseMinute = $this->faker->randomElement([0, 30]); // Genera minutos 00 o 30
        $duration = $this->faker->randomElement([60, 90, 120]); // Genera duración de 1, 1.5, o 2 horas en minutos

        $startTime = Carbon::createFromTime($baseHour, $baseMinute, 0);
        $endTime = $startTime->copy()->addMinutes($duration);

        return [
            'days' => $this->faker->randomElement(["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"]),
            'sportcourt_id' => $this->faker->numberBetween(1, 3),
            'mode_id' => $this->faker->numberBetween(1, 6),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'status' => $this->faker->randomElement(["Libre", "Ocupado"]),
        ];
    }
}
