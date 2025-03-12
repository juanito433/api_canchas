<?php

namespace Database\Factories;

use App\Models\admin;
use App\Models\member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\penalty>
 */
class PenaltyFactory extends Factory
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
            'admin_id' => admin::inRandomOrder()->first()?->id ?? null,
            'cause' => $this->faker->sentence(10),
            'penalty' => $this->faker->sentence(10),
            'date' => $this->faker->date(),
            'expiration_date' =>$this->faker->date(),
        ];
    }
}
