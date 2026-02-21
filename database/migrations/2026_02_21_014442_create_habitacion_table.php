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
        Schema::create('habitacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_habitacion', 250);
            $table->string('estado', 50);
            $table->string('num_habitacion', 25);
            $table->decimal('precio', 10, 2);
            $table->integer('capacidad');
            $table->foreignId('tipo_habitacion_id')->constrained();
            $table->foreignId('hotel_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitacion');
    }
};