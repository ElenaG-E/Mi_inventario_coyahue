<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            if (Schema::hasColumn('insumos', 'usuario_id')) {
                $table->dropForeign(['usuario_id']); // si existe FK
                $table->dropColumn('usuario_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->nullable()->after('precio');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
        });
    }
};