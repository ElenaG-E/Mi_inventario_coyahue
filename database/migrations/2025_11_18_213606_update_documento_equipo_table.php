<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documento_equipo', function (Blueprint $table) {
            // Quitar timestamps si existen
            if (Schema::hasColumn('documento_equipo', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('documento_equipo', 'updated_at')) {
                $table->dropColumn('updated_at');
            }

            // Agregar insumo_id
            if (!Schema::hasColumn('documento_equipo', 'insumo_id')) {
                $table->unsignedBigInteger('insumo_id')->nullable()->after('equipo_id');

                // Foreign key opcional
                $table->foreign('insumo_id')
                      ->references('id')
                      ->on('insumos')
                      ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('documento_equipo', function (Blueprint $table) {
            // Eliminar la foreign key y la columna insumo_id
            if (Schema::hasColumn('documento_equipo', 'insumo_id')) {
                $table->dropForeign(['insumo_id']);
                $table->dropColumn('insumo_id');
            }

            // Restaurar timestamps si quieres revertir
            if (!Schema::hasColumn('documento_equipo', 'created_at')) {
                $table->timestamps();
            }
        });
    }
};