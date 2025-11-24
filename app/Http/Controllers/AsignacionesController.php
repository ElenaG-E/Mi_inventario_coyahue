<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\Insumo;
use App\Models\Usuario;
use App\Models\Sucursal;
use App\Models\EstadoEquipo;
use App\Models\TipoEquipo;
use App\Models\Asignacion;
use App\Models\AsignacionInsumo;
use App\Models\Proveedor;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        // Filtros
        $categoria   = $request->get('categoria', '');
        $tipo        = $request->get('tipo', '');
        $estado      = $request->get('estado', '');
        $usuario     = $request->get('usuario', '');
        $proveedor   = $request->get('proveedor', '');
        $sucursal    = $request->get('sucursal', '');
        $precioMin   = $request->get('precio_min', '');
        $precioMax   = $request->get('precio_max', '');
        $fechaTipo   = $request->get('fecha_tipo', 'registro');
        $fechaDesde  = $request->get('fecha_desde', '');
        $fechaHasta  = $request->get('fecha_hasta', '');
        $buscar      = $request->get('buscar', '');

        // Query equipos
        $equipos = Equipo::with(['tipoEquipo','estadoEquipo','proveedor','usuarioAsignado','sucursal'])
            ->when($tipo, fn($q) => $q->whereHas('tipoEquipo', fn($t) => $t->where('nombre', $tipo)))
            ->when($estado, fn($q) => $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre', $estado)))
            ->when(!$estado, fn($q) => $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','!=','Baja')))
            ->when($usuario, fn($q) => $q->whereHas('asignaciones.usuario', fn($u) => $u->where('nombre', $usuario)))
            ->when($proveedor, fn($q) => $q->whereHas('proveedor', fn($p) => $p->where('nombre', $proveedor)))
            ->when($sucursal, fn($q) => $q->whereHas('sucursal', fn($s) => $s->where('nombre', $sucursal)))
            ->when($precioMin, fn($q) => $q->where('precio', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('precio', '<=', $precioMax))
            ->when($fechaDesde, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '>=', $fechaDesde))
            ->when($fechaHasta, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '<=', $fechaHasta))
            ->when($buscar, function($q) use ($buscar) {
                $q->where(function($sub) use ($buscar) {
                    $sub->where('marca', 'like', "%$buscar%")
                        ->orWhere('modelo', 'like', "%$buscar%")
                        ->orWhere('numero_serie', 'like', "%$buscar%")
                        ->orWhereHas('tipoEquipo', fn($t) => $t->where('nombre','like',"%$buscar%"))
                        ->orWhereHas('estadoEquipo', fn($e) => $e->where('nombre','like',"%$buscar%"))
                        ->orWhereHas('proveedor', fn($p) => $p->where('nombre','like',"%$buscar%"))
                        ->orWhereHas('asignaciones.usuario', fn($u) => $u->where('nombre','like',"%$buscar%"));
                });
            })
            ->orderBy('fecha_registro','desc')
            ->get();

        // Query insumos
        $insumos = Insumo::with(['estadoEquipo','proveedor','usuarioAsignado','sucursal'])
            ->when($tipo, fn($q) => $q->where('nombre', $tipo))
            ->when($estado, fn($q) => $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre', $estado)))
            ->when(!$estado, fn($q) => $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','!=','Baja')))
            ->when($usuario, fn($q) => $q->whereHas('asignaciones.usuario', fn($u) => $u->where('nombre', $usuario)))
            ->when($proveedor, fn($q) => $q->whereHas('proveedor', fn($p) => $p->where('nombre', $proveedor)))
            ->when($sucursal, fn($q) => $q->whereHas('sucursal', fn($s) => $s->where('nombre', $sucursal)))
            ->when($precioMin, fn($q) => $q->where('precio', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('precio', '<=', $precioMax))
            ->when($fechaDesde, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '>=', $fechaDesde))
            ->when($fechaHasta, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '<=', $fechaHasta))
            ->when($buscar, function($q) use ($buscar) {
                $q->where(function($sub) use ($buscar) {
                    $sub->where('nombre', 'like', "%$buscar%")
                        ->orWhereHas('estadoEquipo', fn($e) => $e->where('nombre','like',"%$buscar%"))
                        ->orWhereHas('asignaciones.usuario', fn($u) => $u->where('nombre','like',"%$buscar%"))
                        ->orWhereHas('proveedor', fn($p) => $p->where('nombre','like',"%$buscar%"));
                });
            })
            ->orderBy('fecha_registro','desc')
            ->get();

        // Datos auxiliares para filtros
        $tipos          = TipoEquipo::pluck('nombre');
        $nombresInsumos = Insumo::pluck('nombre')->unique();
        $estados        = EstadoEquipo::all();
        $usuarios       = Usuario::all();
        $proveedores    = Proveedor::all();
        $sucursales     = Sucursal::all();

        return view('inventario', compact(
            'equipos',
            'insumos',
            'tipos',
            'nombresInsumos',
            'estados',
            'usuarios',
            'proveedores',
            'sucursales',
            'categoria'
        ));
    }

    public function autocomplete(Request $request)
    {
        $term = $request->get('q', '');
        $tipos = TipoEquipo::where('nombre', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get(['id', 'nombre']);

        return response()->json(
            $tipos->map(fn($t) => ['label' => $t->nombre, 'value' => $t->id])
        );
    }

    public function detalleEquipo($id)
    {
        $equipo = Equipo::with([
            'tipoEquipo',
            'estadoEquipo',
            'proveedor',
            'usuarioAsignado.usuario',
            'sucursal',
            'movimientos',
            'especificacionesTecnicas',
            'asignaciones.usuario'
        ])->findOrFail($id);

        $estados     = EstadoEquipo::all();
        $usuarios    = Usuario::all();
        $sucursales  = Sucursal::all();
        $proveedores = Proveedor::all();

        return view('detalle_equipo', compact('equipo', 'estados', 'usuarios', 'sucursales', 'proveedores'));
    }

    public function detalleInsumo($id)
    {
        $insumo = Insumo::with([
            'estadoEquipo',
            'proveedor',
            'usuarioAsignado.usuario',
            'sucursal',
            'movimientos',
            'asignaciones.usuario'
        ])->findOrFail($id);

        $estados     = EstadoEquipo::all();
        $usuarios    = Usuario::all();
        $sucursales  = Sucursal::all();
        $proveedores = Proveedor::all();

        return view('detalle_insumo', compact('insumo', 'estados', 'usuarios', 'sucursales', 'proveedores'));
    }

    /**
     * Procesar asignaciones múltiples (equipos e insumos).
     */
    public function storeAsignaciones(Request $request)
    {
        $request->validate([
            'tipo_asignacion' => 'required|in:usuario,sucursal,ninguno',
            'items'           => 'required|array',
        ]);

        $tipo      = $request->tipo_asignacion;
        $destinoId = $request->destino_id ?? null;
        $items     = $request->items;

        foreach ($items as $id) {
            $equipo = Equipo::find($id);
            $insumo = Insumo::find($id);

            if ($equipo) {
                if ($tipo === 'usuario') {
                    // Cerrar asignación activa
                    Asignacion::where('equipo_id', $equipo->id)->whereNull('fecha_fin')
                        ->update(['fecha_fin' => now()]);

                    // Crear nueva asignación
                    Asignacion::create([
                        'equipo_id'        => $equipo->id,
                        'usuario_id'       => $destinoId,
                        'fecha_asignacion' => now(),
                        'motivo'           => 'Asignación múltiple',
                    ]);

                    // Cambiar estado a Asignado
                    $equipo->update(['estado_equipo_id' => EstadoEquipo::where('nombre','Asignado')->first()->id]);
                } elseif ($tipo === 'sucursal') {
                    $equipo->update([
                        'sucursal_id'      => $destinoId,
                        'estado_equipo_id' => EstadoEquipo::where('nombre','Asignado')->first()->id
                    ]);

                    $sucursalDestino = Sucursal::find($destinoId);
                    $equipo->registrarMovimiento(
                        'Cambio de Sucursal',
                        'Asignado en lote a sucursal: ' . ($sucursalDestino->nombre ?? 'Desconocida')
                    );
                } elseif ($tipo === 'ninguno') {
                    // Quitar asignación → Disponible
                    $equipo->update([
                        'usuario_id'       => null,
                        'estado_equipo_id' => EstadoEquipo::where('nombre','Disponible')->first()->id
                    ]);
                }
            }

            if ($insumo) {
                if ($tipo === 'usuario') {
                    AsignacionInsumo::where('insumo_id', $insumo->id)->whereNull('fecha_fin')
                        ->update(['fecha_fin' => now()]);

                    AsignacionInsumo::create([
                        'insumo_id'        => $insumo->id,
                        'usuario_id'       => $destinoId,
                        'cantidad'         => 1,
                        'fecha_asignacion' => now(),
                        'motivo'           => 'Asignación múltiple',
                    ]);

                    $insumo->update(['estado_equipo_id' => EstadoEquipo::where('nombre','Asignado')->first()->id]);
                } elseif ($tipo === 'sucursal') {
                    $insumo->update([
                        'sucursal_id'      => $destinoId,
                        'estado_equipo_id' => EstadoEquipo::where('nombre','Asignado')->first()->id
                    ]);

                    $sucursalDestino = Sucursal::find($destinoId);
                    $insumo->registrarMovimiento(
                        'Cambio de Sucursal',
                        'Asignado en lote a sucursal: ' . ($sucursalDestino->nombre ?? 'Desconocida')
                    );
                } elseif ($tipo === 'ninguno') {
                    $insumo->update([
                        'usuario_id'       => null,
                        'estado_equipo_id' => EstadoEquipo::where('nombre','Disponible')->first()->id
                    ]);
                }
            }
        }

        return redirect()->route('inventario')
            ->with('success', 'Asignaciones realizadas correctamente.');
    }
}