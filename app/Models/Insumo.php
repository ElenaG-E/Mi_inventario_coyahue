<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Insumo extends Model
{
    use HasFactory;
    
    // Propiedades y fillable existentes
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
    
    public $timestamps = false; // Asumido

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

    /**
     * Registra un nuevo movimiento en la tabla de historial.
     *
     * @param string $tipo Tipo de movimiento (Ej: Asignación, Recepción, Baja)
     * @param string|null $comentario Comentario descriptivo
     * @return \App\Models\Movimiento
     */
    public function registrarMovimiento(string $tipo, ?string $comentario = null)
    {
        // Captura la sucursal actual del insumo para el registro del movimiento
        $currentSucursalId = $this->sucursal_id ?? null;
        
        return $this->movimientos()->create([
            'equipo_id'        => null, // El insumo no es un equipo
            'insumo_id'        => $this->id,
            'tipo_movimiento'  => $tipo,
            'comentario'       => $comentario,
            'usuario_id'       => auth()->id(), 
            'fecha_movimiento' => now(),
            'sucursal_id'      => $currentSucursalId, // <--- CORREGIDO: Se añade la FK de sucursal
        ]);
    }
}
