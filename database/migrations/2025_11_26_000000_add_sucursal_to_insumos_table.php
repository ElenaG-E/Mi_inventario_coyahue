<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                  ->nullable()
                  ->constrained('sucursales')
                  ->onDelete('set null')
                  ->after('estado_equipo_id');
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sucursal_id');
        });
    }
};
