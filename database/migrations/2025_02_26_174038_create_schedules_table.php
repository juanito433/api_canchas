<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('days');
            $table->unsignedBigInteger('sportcourt_id');
            $table->foreign('sportcourt_id')->references('id')->on('sportcourts')->onDelete('cascade');
            $table->unsignedBigInteger('mode_id');
            $table->foreign('mode_id')->references('id')->on('modes')->onDelete('cascade');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
