<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstadoEquipo extends Model
{
    use HasFactory;

    protected $table = 'estados_equipo';

    protected $fillable = ['nombre'];

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'estado_equipo_id');
    }
}