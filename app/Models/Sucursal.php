<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'fecha_registro',
    ];

    public $timestamps = false;

    // Relaciones
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'sucursal_id');
    }

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'sucursal_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'sucursal_id');
    }

    // ✅ Helper para registrar movimientos
    public function registrarMovimiento($tipo, $comentario = null)
    {
        return $this->movimientos()->create([
            'tipo_movimiento'  => $tipo,
            'comentario'       => $comentario,
            'usuario_id'       => auth()->id(),
            'fecha_movimiento' => now(),
        ]);
    }
}