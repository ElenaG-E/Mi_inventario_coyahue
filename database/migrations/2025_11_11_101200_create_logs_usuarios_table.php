<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_usuarios', function (Blueprint $table) {
            // PK
            $table->id('id');

            // FK
            $table->unsignedBigInteger('usuario_id');

            // Campos propios
            $table->string('accion', 50);               // Ej: creación, edición, eliminación
            $table->string('tabla_afectada', 50);       // Ej: usuarios, equipos, asignaciones
            $table->string('registro_afectado', 50);    // Ej: ID o identificador del registro
            $table->timestamp('fecha_accion');          // Cuándo ocurrió la acción

            // Clave foránea
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_usuarios');
    }
};