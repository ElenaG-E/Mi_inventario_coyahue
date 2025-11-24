<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // Campo precio
            $table->decimal('precio', 12, 2)->nullable()->after('modelo');

            // Clave foránea sucursal_id
            $table->foreignId('sucursal_id')
                  ->nullable()
                  ->constrained('sucursales')
                  ->onDelete('set null')
                  ->after('estado_equipo_id');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('precio');
            $table->dropConstrainedForeignId('sucursal_id');
        });
    }
};