<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';

    protected $fillable = [
        'tipo_equipo_id',
        'proveedor_id',
        'estado_equipo_id',
        'marca',
        'modelo',
        'numero_serie',
        'fecha_compra',
        'qr_code',
        'fecha_registro',
        'sucursal_id',
        'precio',
        'estado',
    ];

    public $timestamps = false;

    /* ============================
     * Relaciones base
     * ============================ */
    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function estadoEquipo()
    {
        return $this->belongsTo(EstadoEquipo::class, 'estado_equipo_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function especificacionesTecnicas()
    {
        return $this->hasOne(EspecificacionTecnica::class, 'equipo_id');
    }

    /* ============================
     * Asignaciones
     * ============================ */
    // Historial completo de asignaciones
    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'equipo_id');
    }

    // Última asignación activa (usuario actual)
    public function usuarioAsignado()
    {
        return $this->hasOne(Asignacion::class, 'equipo_id')
                    ->whereNull('fecha_fin')
                    ->latestOfMany('fecha_asignacion')
                    ->with('usuario'); // 👈 carga el usuario asociado
    }

    // Helper para Blade: nombre del usuario asignado actual
    public function getNombreUsuarioAsignadoAttribute()
    {
        return $this->usuarioAsignado?->usuario?->nombre ?? 'Sin asignar';
    }

    // Helper: saber si el equipo está asignado actualmente
    public function estaAsignado()
    {
        return !is_null($this->usuarioAsignado);
    }

    /* ============================
     * Movimientos
     * ============================ */
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'equipo_id');
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

    /* ============================
     * Documentos, Tickets y Logs
     * ============================ */
    public function documentos()
    {
        return $this->belongsToMany(
            Documento::class,
            'documento_equipo',
            'equipo_id',
            'documento_id'
        );
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'equipo_id');
    }

    public function logs()
    {
        return $this->hasMany(LogEquipo::class, 'equipo_id');
    }
}