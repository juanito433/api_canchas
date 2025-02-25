<?php

namespace Database\Seeders;

use App\Models\sportcourt;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SportcourtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        sportcourt::factory(3)->create();
    }
}
