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
        Schema::create('staff_hotel', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_asignacion');
            $table->string('rol',25);
            $table->boolean('estado')->default(true);
            $table->foreignId('hotel_id')->constrained('hoteles');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_hotel');
    }
};
