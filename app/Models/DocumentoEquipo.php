<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoEquipo extends Model
{
    protected $table = 'documento_equipo';

    protected $fillable = [
        'equipo_id',
        'insumo_id',
        'documento_id',
    ];

    public $timestamps = false;

    // Relaciones
    public function documento()
    {
        return $this->belongsTo(Documento::class, 'documento_id');
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}