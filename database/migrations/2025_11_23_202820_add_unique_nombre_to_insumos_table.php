<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // No hacer nada, el índice no existe
        // O puedes dejarlo vacío
    }

    public function down()
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->unique('nombre');
        });
    }
};
