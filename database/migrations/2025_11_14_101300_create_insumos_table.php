<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();

            // Datos del insumo
            $table->string('nombre', 100);
            $table->integer('cantidad')->default(0);

            // Estado del insumo (relación con estados_equipo)
            $table->unsignedBigInteger('estado_equipo_id');

            // Fecha explícita de registro
            $table->date('fecha_registro');

            $table->timestamps();

            // Relaciones
            $table->foreign('estado_equipo_id')
                  ->references('id')
                  ->on('estados_equipo')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};