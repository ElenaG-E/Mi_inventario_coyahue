<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipos', function (Blueprint $table) {
            // Si la columna estado existe, modificarla para tener valor por defecto
            if (Schema::hasColumn('equipos', 'estado')) {
                $table->string('estado')->default('activo')->change();
            }
        });
    }

    public function down()
    {
        Schema::table('equipos', function (Blueprint $table) {
            if (Schema::hasColumn('equipos', 'estado')) {
                $table->string('estado')->nullable(false)->change();
            }
        });
    }
};
