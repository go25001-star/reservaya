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
        Schema::create('tipo_habitaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 250);
            $table->text('descripcion')->nullable();
            $table->foreignId('hotel_id')->constrained('hoteles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_habitaciones');
    }
};
