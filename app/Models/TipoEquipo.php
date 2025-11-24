<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoEquipo extends Model
{
    use HasFactory;

    protected $table = 'tipos_equipo';

    protected $fillable = ['nombre', 'categoria'];

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'tipo_equipo_id');
    }
}