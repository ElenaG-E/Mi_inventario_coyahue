<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'nombre_archivo',
        'ruta_s3',
        'clave_s3',
        'tipo',
        'mime_type',
        'tamaño_bytes',
        'fecha_subida',
        'usuario_id',
        'tiempo_garantia_meses',
    ];

    public $timestamps = false;

    // Relación con equipos
    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'documento_equipo', 'documento_id', 'equipo_id')
                    ->withTimestamps();
    }

    // Relación con insumos
    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'documento_equipo', 'documento_id', 'insumo_id');
    }
}