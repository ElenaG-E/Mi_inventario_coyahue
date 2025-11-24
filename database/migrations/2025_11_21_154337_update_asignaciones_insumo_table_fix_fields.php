<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAsignacionesInsumoTableFixFields extends Migration
{
    public function up()
    {
        Schema::table('asignaciones_insumo', function (Blueprint $table) {
            // 🔹 Renombrar cantidad_entregada → cantidad
            if (Schema::hasColumn('asignaciones_insumo', 'cantidad_entregada')) {
                $table->renameColumn('cantidad_entregada', 'cantidad');
            }

            // 🔹 Renombrar fecha_entrega → fecha_asignacion
            if (Schema::hasColumn('asignaciones_insumo', 'fecha_entrega')) {
                $table->renameColumn('fecha_entrega', 'fecha_asignacion');
            }

            // 🔹 Renombrar observaciones → motivo
            if (Schema::hasColumn('asignaciones_insumo', 'observaciones')) {
                $table->renameColumn('observaciones', 'motivo');
            }

            // 🔹 Agregar fecha_fin
            if (!Schema::hasColumn('asignaciones_insumo', 'fecha_fin')) {
                $table->dateTime('fecha_fin')->nullable()->after('fecha_asignacion');
            }
        });
    }

    public function down()
    {
        Schema::table('asignaciones_insumo', function (Blueprint $table) {
            // 🔹 Revertir cambios
            if (Schema::hasColumn('asignaciones_insumo', 'cantidad')) {
                $table->renameColumn('cantidad', 'cantidad_entregada');
            }

            if (Schema::hasColumn('asignaciones_insumo', 'fecha_asignacion')) {
                $table->renameColumn('fecha_asignacion', 'fecha_entrega');
            }

            if (Schema::hasColumn('asignaciones_insumo', 'motivo')) {
                $table->renameColumn('motivo', 'observaciones');
            }

            if (Schema::hasColumn('asignaciones_insumo', 'fecha_fin')) {
                $table->dropColumn('fecha_fin');
            }
        });
    }
}