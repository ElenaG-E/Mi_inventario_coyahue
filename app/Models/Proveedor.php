<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = ['nombre', 'rut', 'direccion', 'telefono', 'correo'];

    public $timestamps = false;

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }
}