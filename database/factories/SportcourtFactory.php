<?php

namespace Database\Factories;

use App\Models\sport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\sportcourt>
 */
class SportcourtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sport_id' => sport::inRandomOrder()->first()?->id ?? null,
            'num_sportcourt' => $this->faker->randomDigitNotNull,
        ];
    }
}
