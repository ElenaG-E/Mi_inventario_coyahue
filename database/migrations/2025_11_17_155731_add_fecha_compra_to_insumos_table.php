<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->date('fecha_compra')->nullable()->after('fecha_registro');
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropColumn('fecha_compra');
        });
    }
};