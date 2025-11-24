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
        Schema::create('tickets', function (Blueprint $table) {
            // PK
            $table->id('id');

            // FK
            $table->unsignedBigInteger('equipo_id');

            // Campos propios
            $table->dateTime('fecha_ticket');
            $table->text('descripcion');
            $table->string('estado', 20);           // Ej: Abierto, En proceso, Cerrado
            $table->string('prioridad', 20);        // Ej: Alta, Media, Baja
            $table->string('usuario_reporta', 100); // Nombre del usuario que reporta

            // Clave foránea
            $table->foreign('equipo_id')->references('id')->on('equipos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};