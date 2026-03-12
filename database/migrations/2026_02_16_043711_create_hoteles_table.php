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
        Schema::create('hoteles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',100);
            $table->text('descripcion');
            $table->string('direccion',250);
            $table->string('imagen',250)->nullable();
            $table->string('email',200);
            $table->string('telefono',25);
            $table->string('telefono2',25)->nullable();
            $table->string('telefono3',25)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoteles');
    }
};
