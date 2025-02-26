<?php

namespace Database\Seeders;

use App\Models\schedules;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchedulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        schedules::factory()->count(10)->create();
    }
}
