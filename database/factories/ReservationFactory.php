<?php

namespace Database\Factories;

use App\Models\member;
use App\Models\mode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => member::inRandomOrder()->first()?->id ?? null,
            'modes_id' => mode::inRandomOrder()->first()?->id ?? null,
            'teammates' =>json_encode( member::inRandomOrder()->limit(rand(1, 2))->pluck('id')->toArray()), 
            'date' => $this->faker->date,
            'confirmation' => $this->faker->randomElement(["Confirmado", "Pendiente"]),
        ];
    }
}
