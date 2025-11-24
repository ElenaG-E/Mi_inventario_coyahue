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
        Schema::create('movimientos', function (Blueprint $table) {
            // PK
            $table->id('id');

            // FKs
            $table->unsignedBigInteger('equipo_id');
            $table->unsignedBigInteger('usuario_id');

            // Campos propios
            $table->string('tipo_movimiento', 50);
            $table->dateTime('fecha_movimiento');
            $table->text('comentario')->nullable();

            // Claves foráneas
            $table->foreign('equipo_id')->references('id')->on('equipos');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};