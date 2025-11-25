<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Documento; // Asegurar que Documento esté importado
use App\Models\Asignacion; // Asegurar que Asignacion esté importado
use App\Models\Movimiento; // Asegurar que Movimiento esté importado
use App\Models\EspecificacionTecnica; // Asegurar que EspecificacionTecnica esté importado
// Aseguramos que Ticket y LogEquipo existan si los usas en el namespace global
// use App\Models\Ticket; 
// use App\Models\LogEquipo; 


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

    /**
     * Registra un nuevo movimiento en la tabla de historial (Automatizado).
     *
     * @param string $tipo Tipo de movimiento (Ej: Asignación, Cambio de Sucursal, Baja)
     * @param string|null $comentario Comentario descriptivo
     * @return \App\Models\Movimiento
     */
    public function registrarMovimiento(string $tipo, ?string $comentario = null)
    {
        // Obtiene la sucursal actual del equipo (FK de la tabla equipos)
        $currentSucursalId = $this->sucursal_id ?? null; 
        
        return $this->movimientos()->create([
            'equipo_id'        => $this->id,
            'insumo_id'        => null, // Es un equipo, insumo_id debe ser NULL
            'tipo_movimiento'  => $tipo,
            'comentario'       => $comentario,
            'usuario_id'       => auth()->id(), // Usuario que realiza el movimiento
            'fecha_movimiento' => now(),
            'sucursal_id'      => $currentSucursalId, // Registrar la ubicación en el momento del movimiento
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

    // Nota: Asume que el modelo Ticket existe.
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'equipo_id');
    }

    // Nota: Asume que el modelo LogEquipo existe.
    public function logs()
    {
        return $this->hasMany(LogEquipo::class, 'equipo_id');
    }
}
