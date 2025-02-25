<?php

namespace Database\Seeders;

use App\Models\reservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        reservation::factory()->count(5)->create();
    }
}
