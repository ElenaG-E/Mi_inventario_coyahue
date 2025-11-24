<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionInsumo extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'asignaciones_insumo';

    public $timestamps = false; // ✅ ya no usas created_at / updated_at

    protected $fillable = [
        'insumo_id',
        'usuario_id',
        'cantidad',
        'fecha_asignacion',
        'fecha_fin',
        'motivo'
    ];

    // Relaciones
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // ✅ Helper: saber si la asignación está activa
    public function isActiva()
    {
        return is_null($this->fecha_fin);
    }

    // ✅ Scope: obtener solo asignaciones activas
    public function scopeActivas($query)
    {
        return $query->whereNull('fecha_fin');
    }
}