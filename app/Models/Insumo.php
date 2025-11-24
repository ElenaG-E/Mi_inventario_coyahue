<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumos';

    protected $fillable = [
        'nombre',
        'cantidad',
        'estado_equipo_id',
        'fecha_registro',
        'fecha_compra',
        'proveedor_id',
        'sucursal_id',
        'precio',
    ];

    public $timestamps = false;

    /* ============================
     * Relaciones base
     * ============================ */
    public function estadoEquipo()
    {
        return $this->belongsTo(EstadoEquipo::class, 'estado_equipo_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /* ============================
     * Asignaciones
     * ============================ */
    // Historial completo de asignaciones
    public function asignaciones()
    {
        return $this->hasMany(AsignacionInsumo::class, 'insumo_id');
    }

    // Última asignación activa (usuario actual)
    public function usuarioAsignado()
    {
        return $this->hasOne(AsignacionInsumo::class, 'insumo_id')
                    ->whereNull('fecha_fin')
                    ->latestOfMany('fecha_asignacion')
                    ->with('usuario'); // 👈 carga el usuario asociado
    }

    // Helper para Blade: nombre del usuario asignado actual
    public function getNombreUsuarioAsignadoAttribute()
    {
        return $this->usuarioAsignado?->usuario?->nombre ?? 'Sin asignar';
    }

    // Helper: saber si el insumo está asignado actualmente
    public function estaAsignado()
    {
        return !is_null($this->usuarioAsignado);
    }

    /* ============================
     * Documentos
     * ============================ */
    public function documentos()
    {
        return $this->belongsToMany(
            Documento::class,
            'documento_equipo',
            'insumo_id',
            'documento_id'
        );
    }

    /* ============================
     * Movimientos
     * ============================ */
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'insumo_id');
    }

    // Registrar movimiento
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