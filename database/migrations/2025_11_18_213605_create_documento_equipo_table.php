<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documento_equipo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_id');
            $table->unsignedBigInteger('equipo_id');

            // Claves foráneas
            $table->foreign('documento_id')
                  ->references('id')
                  ->on('documentos')
                  ->onDelete('cascade');
            
            $table->foreign('equipo_id')
                  ->references('id')
                  ->on('equipos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documento_equipo');
    }
};
