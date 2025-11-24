<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EspecificacionTecnica extends Model
{
    use HasFactory;

    protected $table = 'especificaciones_tecnicas';
    public $timestamps = false;

    protected $fillable = [
        'equipo_id',
        // --- CAMPO AGREGADO ---
        'resumen_ia', 
        // ----------------------
        'procesador',
        'ram_gb',
        'almacenamiento_gb',
        'tipo_almacenamiento',
        'tarjeta_grafica',
        'sistema_operativo',
        'pantalla_pulgadas',
        'resolucion_pantalla',
        'tipo_panel',
        'frecuencia_hz',
        'bateria_mah',
        'tecnologia_impresion',
        'color_impresion',
        'resolucion_dpi',
        'conectividad',
        'tipo_tinta',
        'puertos_red',
        'bandas',
        'tipo_switch',
        'camara_frontal_mp',
        'almacenamiento_expansible',
        'puertos_monitor',
        'lumenes',
        'resolucion_nativa',
        'tecnologia_proyector',
        'entradas_video',
        'otros_datos',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }
}
