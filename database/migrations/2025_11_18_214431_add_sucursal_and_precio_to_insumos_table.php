<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->unsignedBigInteger('insumo_id')->nullable()->after('equipo_id');

            $table->foreign('insumo_id')
                  ->references('id')
                  ->on('insumos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropForeign(['insumo_id']);
            $table->dropColumn('insumo_id');
        });
    }
};