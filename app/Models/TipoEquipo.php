<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoEquipo extends Model
{
    use HasFactory;

    protected $table = 'tipos_equipo';

    // *** CORRECCIÓN CRÍTICA FINAL ***
    // Desactiva la búsqueda automática de las columnas 'created_at' y 'updated_at'
    // que causaban el error 'Unknown column' en el Seeder.
    public $timestamps = false; 
    // **********************************

    protected $fillable = ['nombre', 'categoria'];

    public function equipos()
    {
        // Se asume que el Modelo Equipo también está importado o definido correctamente
        return $this->hasMany(Equipo::class, 'tipo_equipo_id');
    }
}
