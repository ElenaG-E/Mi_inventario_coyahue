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
        Schema::create('usuarios', function (Blueprint $table) {
            // PK
            $table->id('id');

            // Campos propios
            $table->string('nombre', 100);
            $table->string('email', 100);
            $table->string('password'); 

            // FK hacia roles
            $table->unsignedBigInteger('rol_id');

            // Estado del usuario
            $table->string('estado', 20);

            // Clave foránea
            $table->foreign('rol_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};