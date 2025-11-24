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
        Schema::create('proveedores', function (Blueprint $table) {
            // PK
            $table->id('id');

            // Campos propios
            $table->string('rut', 20);
            $table->string('nombre', 100);
            $table->string('telefono', 20);
            $table->string('correo', 100);
            $table->string('estado', 20);
            $table->string('direccion', 100);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};