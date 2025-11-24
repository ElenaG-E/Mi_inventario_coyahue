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
        Schema::create('equipos', function (Blueprint $table) {
            // PK
            $table->id('id');

            // FKs
            $table->unsignedBigInteger('tipo_equipo_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('estado_equipo_id');

            // Campos propios
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->string('numero_serie', 100);
            $table->date('fecha_compra');
            $table->string('estado', 20);
            $table->string('qr_code')->nullable();

            // Fecha personalizada de registro (automática)
            $table->timestamp('fecha_registro')->useCurrent();

            // Claves foráneas
            $table->foreign('tipo_equipo_id')->references('id')->on('tipos_equipo');
            $table->foreign('proveedor_id')->references('id')->on('proveedores');
            $table->foreign('estado_equipo_id')->references('id')->on('estados_equipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};