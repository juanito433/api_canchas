<?php

namespace Database\Seeders;

use App\Models\penalty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PenaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        penalty::factory()->count(10)->create();
    }
}
