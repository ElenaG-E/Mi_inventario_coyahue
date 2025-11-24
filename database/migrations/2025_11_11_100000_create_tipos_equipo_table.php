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
        // Asegúrate de que el nombre de la tabla coincida exactamente con el seeder
        Schema::create('tipos_equipo', function (Blueprint $table) {
            // PK
            $table->id('id');

            // Campos propios
            $table->string('nombre', 50)->unique(); // <--- CORRECCIÓN: Agregar unique
            $table->string('categoria', 50); // 'equipo' o 'insumo'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_equipo');
    }
};
