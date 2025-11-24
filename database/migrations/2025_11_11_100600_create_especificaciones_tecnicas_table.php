<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especificaciones_tecnicas', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('equipo_id');

            // --- Campo agregado para el resumen de la IA ---
            $table->text('resumen_ia')->nullable(); 
            // ----------------------------------------------
            
            $table->string('procesador', 100)->nullable();
            $table->integer('ram_gb')->nullable();
            $table->integer('almacenamiento_gb')->nullable();
            $table->string('tipo_almacenamiento', 20)->nullable();
            $table->string('tarjeta_grafica', 100)->nullable();
            $table->string('sistema_operativo', 50)->nullable();
            $table->decimal('pantalla_pulgadas', 3, 1)->nullable();
            $table->string('resolucion_pantalla', 20)->nullable();
            $table->string('tipo_panel', 20)->nullable();
            $table->integer('frecuencia_hz')->nullable();
            $table->integer('bateria_mah')->nullable();
            $table->string('tecnologia_impresion', 20)->nullable();
            $table->string('color_impresion', 10)->nullable();
            $table->string('resolucion_dpi', 20)->nullable();
            $table->string('conectividad', 100)->nullable();
            $table->string('tipo_tinta', 50)->nullable();
            $table->integer('puertos_red')->nullable();
            $table->string('bandas', 50)->nullable();
            $table->string('tipo_switch', 50)->nullable();
            $table->string('camara_frontal_mp', 20)->nullable();
            $table->boolean('almacenamiento_expansible')->nullable();
            $table->string('puertos_monitor', 100)->nullable();
            $table->integer('lumenes')->nullable();
            $table->string('resolucion_nativa', 20)->nullable();
            $table->string('tecnologia_proyector', 20)->nullable();
            $table->string('entradas_video', 100)->nullable();
            $table->string('otros_datos', 200)->nullable();

            $table->foreign('equipo_id')->references('id')->on('equipos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especificaciones_tecnicas');
    }
};
