<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movimiento extends Model
{
    use HasFactory;

    protected $table = 'movimientos';

    protected $fillable = [
        'equipo_id',
        'insumo_id',
        'usuario_id',
        'sucursal_id',
        'fecha_movimiento',
        'tipo_movimiento',
        'comentario',
    ];

    public $timestamps = false;

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}