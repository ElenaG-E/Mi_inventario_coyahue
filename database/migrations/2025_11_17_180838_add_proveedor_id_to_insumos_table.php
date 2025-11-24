<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            // Agregamos la columna proveedor_id con FK a proveedores
            $table->foreignId('proveedor_id')
                  ->nullable()
                  ->constrained('proveedores')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            // Eliminamos la FK y la columna si se revierte
            $table->dropForeign(['proveedor_id']);
            $table->dropColumn('proveedor_id');
        });
    }
};