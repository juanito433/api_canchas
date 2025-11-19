<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sportcourts', function (Blueprint $table) {
            // Cambiar de integer a string
            $table->string('num_sportcourt', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sportcourts', function (Blueprint $table) {
            // Revertir al tipo original integer
            $table->integer('num_sportcourt')->change();
        });
    }
};
