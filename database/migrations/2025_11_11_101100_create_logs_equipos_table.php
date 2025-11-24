<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_equipos', function (Blueprint $table) {
            // PK
            $table->id('id');

            // FKs
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('equipo_id');

            // Campos propios
            $table->string('accion', 50);
            $table->timestamp('fecha_accion');

            // Claves foráneas
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('equipo_id')->references('id')->on('equipos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_equipos');
    }
};