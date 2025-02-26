<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'days' => $this->faker->randomElement(["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"]),
            'sportcourt_id' => $this->faker->numberBetween(1, 3),
            'mode_id' => $this->faker->numberBetween(1, 6),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'status' => $this->faker->randomElement(["Libre", "Ocupado"]),

        ];
    }
}
