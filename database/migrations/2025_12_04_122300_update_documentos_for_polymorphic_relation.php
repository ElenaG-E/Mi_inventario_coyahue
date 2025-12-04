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
        // Usamos Schema::table para modificar una tabla existente
        Schema::table('documentos', function (Blueprint $table) {
            
            // 1. Eliminar FK y columna 'equipo_id' (si existían)
            $table->dropForeign(['equipo_id']);
            $table->dropColumn('equipo_id');

            // 2. SOLUCIÓN AL ERROR: Añadir campos polimórficos de forma manual 
            // Esto permite usar ->after('id') solo en la primera columna.
            $table->unsignedBigInteger('documentable_id')->after('id');
            $table->string('documentable_type')->after('documentable_id'); // Se agrega después de documentable_id

            // 3. Modificar usuario_id para ser nullable (si no lo era)
            // Asegúrate de tener instalado 'doctrine/dbal' para usar ->change().
            $table->unsignedBigInteger('usuario_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            
            // 1. Eliminar los campos polimórficos
            $table->dropColumn(['documentable_id', 'documentable_type']); 
            
            // 2. Revertir usuario_id a no nullable (si era el estado original)
            $table->unsignedBigInteger('usuario_id')->nullable(false)->change();

            // 3. Recrear la columna 'equipo_id' original
            $table->unsignedBigInteger('equipo_id')->after('id'); 
            $table->foreign('equipo_id')->references('id')->on('equipos'); 
        });
    }
};
