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
        Schema::create('asignaciones_insumo', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->unsignedBigInteger('insumo_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('equipo_id')->nullable(); // opcional

            // Datos de asignación
            $table->integer('cantidad_entregada');
            $table->date('fecha_entrega');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // Claves foráneas
            $table->foreign('insumo_id')->references('id')->on('insumos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('equipo_id')->references('id')->on('equipos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_insumo');
    }
};