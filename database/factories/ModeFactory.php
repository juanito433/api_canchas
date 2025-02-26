<?php

namespace Database\Factories;

use App\Models\sportcourt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\mode>
 */
class ModeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(["Libre", "Doble", "Single", "Clinica", "Extra single", " Clases particulares"]),
            'description' => $this->faker->sentence(10),
        ];
    }
}
