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
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',250);
            $table->string('estado',50);
            $table->integer('num_habitacion');
            $table->decimal('precio');
            $table->integer('capacidad');
            $table->foreignId('tipo_habitacion_id')->constrained('tipo_habitaciones');
            $table->foreignId('hotel_id')->constrained('hoteles');
            $table->timestamps();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
