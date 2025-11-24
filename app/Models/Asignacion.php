<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'asignaciones';

    public $timestamps = false;

    protected $fillable = [
        'equipo_id',
        'usuario_id',
        'fecha_asignacion',
        'motivo',
        'fecha_fin'
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
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