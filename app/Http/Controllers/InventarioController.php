<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Insumo;
use App\Models\TipoEquipo;
use App\Models\EstadoEquipo;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\Usuario;
use App\Models\Asignacion;
use App\Models\AsignacionInsumo;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
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

        // Correct precioMin and precioMax to prevent inverted ranges
        if ($precioMin !== '' && $precioMax !== '') {
            if ($precioMin > $precioMax) {
                // Swap values
                $temp = $precioMin;
                $precioMin = $precioMax;
                $precioMax = $temp;
            }
        }

        $equipos = Equipo::with(['tipoEquipo','estadoEquipo','proveedor','usuarioAsignado.usuario','sucursal'])
            ->when($tipo, fn($q) => $q->whereHas('tipoEquipo', fn($t) => $t->where('nombre', $tipo)))
            ->when($estado, function($q) use ($estado) {
                if ($estado === 'Baja') {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','Baja'));
                } elseif ($estado) {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre',$estado));
                }
            }, function($q) {
                $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','!=','Baja'));
            })
            ->when($usuario === 'Sin asignar', fn($q) => $q->whereDoesntHave('usuarioAsignado'))
            ->when($usuario && $usuario !== 'Sin asignar', fn($q) => $q->whereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre', $usuario)))
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
                        ->orWhereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre','like',"%$buscar%"));
                });
            })
            ->orderBy('fecha_registro','desc')
            ->get();

        $insumos = Insumo::with(['estadoEquipo','proveedor','usuarioAsignado.usuario','sucursal'])
            ->when($tipo, fn($q) => $q->where('nombre', $tipo))
            ->when($estado, function($q) use ($estado) {
                if ($estado === 'Baja') {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','Baja'));
                } elseif ($estado) {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre',$estado));
                }
            }, function($q) {
                $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','!=','Baja'));
            })
            ->when($usuario === 'Sin asignar', fn($q) => $q->whereDoesntHave('usuarioAsignado'))
            ->when($usuario && $usuario !== 'Sin asignar', fn($q) => $q->whereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre', $usuario)))
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
                        ->orWhereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre','like',"%$buscar%"))
                        ->orWhereHas('proveedor', fn($p) => $p->where('nombre','like',"%$buscar%"))
                        ->orWhereHas('sucursal', fn($s) => $s->where('nombre','like',"%$buscar%"));
                });
            })
            ->orderBy('fecha_registro','desc')
            ->get();

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
        $term = trim($request->get('term', ''));
        
        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        $termLike = "%{$term}%";
        $results = [];

        try {
            // Buscar marcas únicas
            $marcas = Equipo::where('marca', 'LIKE', $termLike)
                ->distinct()
                ->pluck('marca')
                ->take(5);

            foreach ($marcas as $marca) {
                $results[] = [
                    'label' => $marca,
                    'value' => $marca,
                    'tipo'  => 'marca',
                    'campo' => 'buscar',
                    'id'    => null
                ];
            }

            // Buscar modelos únicos
            $modelos = Equipo::where('modelo', 'LIKE', $termLike)
                ->distinct()
                ->pluck('modelo')
                ->take(5);

            foreach ($modelos as $modelo) {
                $results[] = [
                    'label' => $modelo,
                    'value' => $modelo,
                    'tipo'  => 'modelo',
                    'campo' => 'buscar',
                    'id'    => null
                ];
            }

            // Buscar tipos de equipo
            $tiposEquipo = TipoEquipo::where('nombre', 'LIKE', $termLike)
                ->limit(5)
                ->get();

            foreach ($tiposEquipo as $tipo) {
                $results[] = [
                    'label' => $tipo->nombre,
                    'value' => $tipo->nombre,
                    'tipo'  => 'tipo',
                    'campo' => 'tipo',
                    'id'    => null
                ];
            }

            // Buscar en insumos - nombre
            $insumos = Insumo::where('nombre', 'LIKE', $termLike)
                ->limit(10)
                ->get();

            foreach ($insumos as $insumo) {
                $results[] = [
                    'label' => $insumo->nombre,
                    'value' => $insumo->nombre,
                    'tipo'  => 'insumo',
                    'campo' => 'buscar',
                    'id'    => $insumo->id
                ];
            }

            // Buscar estados
            $estados = EstadoEquipo::where('nombre', 'LIKE', $termLike)
                ->limit(5)
                ->get();

            foreach ($estados as $estado) {
                $results[] = [
                    'label' => $estado->nombre,
                    'value' => $estado->nombre,
                    'tipo'  => 'estado',
                    'campo' => 'estado',
                    'id'    => null
                ];
            }

            // Buscar proveedores
            $proveedores = Proveedor::where('nombre', 'LIKE', $termLike)
                ->limit(5)
                ->get();

            foreach ($proveedores as $proveedor) {
                $results[] = [
                    'label' => $proveedor->nombre,
                    'value' => $proveedor->nombre,
                    'tipo'  => 'proveedor',
                    'campo' => 'proveedor',
                    'id'    => null
                ];
            }

            // Buscar sucursales
            $sucursales = Sucursal::where('nombre', 'LIKE', $termLike)
                ->limit(5)
                ->get();

            foreach ($sucursales as $sucursal) {
                $results[] = [
                    'label' => $sucursal->nombre,
                    'value' => $sucursal->nombre,
                    'tipo'  => 'sucursal',
                    'campo' => 'sucursal',
                    'id'    => null
                ];
            }

            // Buscar usuarios
            $usuarios = Usuario::where('nombre', 'LIKE', $termLike)
                ->limit(5)
                ->get();

            foreach ($usuarios as $usuario) {
                $results[] = [
                    'label' => $usuario->nombre,
                    'value' => $usuario->nombre,
                    'tipo'  => 'usuario',
                    'campo' => 'usuario',
                    'id'    => null
                ];
            }

            // Eliminar duplicados
            $uniqueResults = [];
            $seen = [];
            foreach ($results as $result) {
                $key = $result['label'] . '|' . $result['campo'];
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $uniqueResults[] = $result;
                }
            }

            $finalResults = array_slice($uniqueResults, 0, 20);

            return response()->json($finalResults);

        } catch (\Exception $e) {
            \Log::error('Error en autocomplete: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function storeAsignaciones(Request $request)
    {
        $request->validate([
            'tipo_asignacion' => 'required|in:usuario,sucursal,ninguno',
            'items' => 'required|array',
        ]);

        $tipoAsignacion = $request->tipo_asignacion;
        $destinoId = $request->destino_id;
        $items = $request->items;

        try {
            foreach ($items as $itemId) {
                // Verificar si es equipo
                $equipo = Equipo::find($itemId);
                if ($equipo) {
                    $this->procesarAsignacionEquipo($equipo, $tipoAsignacion, $destinoId);
                    continue;
                }

                // Verificar si es insumo
                $insumo = Insumo::find($itemId);
                if ($insumo) {
                    $this->procesarAsignacionInsumo($insumo, $tipoAsignacion, $destinoId);
                }
            }

            $mensaje = $this->generarMensajeExito($tipoAsignacion, count($items));
            return redirect()->route('inventario')->with('success', $mensaje);

        } catch (\Exception $e) {
            \Log::error('Error en asignaciones masivas: ' . $e->getMessage());
            return redirect()->route('inventario')->with('error', 'Error al procesar las asignaciones.');
        }
    }

    private function procesarAsignacionEquipo(Equipo $equipo, $tipoAsignacion, $destinoId)
    {
        switch ($tipoAsignacion) {
            case 'usuario':
                $this->asignarUsuarioEquipo($equipo, $destinoId);
                break;
                
            case 'sucursal':
                $this->asignarSucursalEquipo($equipo, $destinoId);
                break;
                
            case 'ninguno':
                $this->quitarAsignacionEquipo($equipo);
                break;
        }
    }

    private function procesarAsignacionInsumo(Insumo $insumo, $tipoAsignacion, $destinoId)
    {
        switch ($tipoAsignacion) {
            case 'usuario':
                $this->asignarUsuarioInsumo($insumo, $destinoId);
                break;
                
            case 'sucursal':
                $this->asignarSucursalInsumo($insumo, $destinoId);
                break;
                
            case 'ninguno':
                $this->quitarAsignacionInsumo($insumo);
                break;
        }
    }

    private function asignarUsuarioEquipo(Equipo $equipo, $usuarioId)
    {
        // Cerrar asignación activa anterior
        Asignacion::where('equipo_id', $equipo->id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => now()]);

        // Crear nueva asignación
        if ($usuarioId) {
            Asignacion::create([
                'equipo_id' => $equipo->id,
                'usuario_id' => $usuarioId,
                'fecha_asignacion' => now(),
                'motivo' => 'Asignación masiva desde inventario',
            ]);
            
            $usuarioNombre = Usuario::find($usuarioId)->nombre ?? 'Usuario desconocido';
            $equipo->registrarMovimiento('Asignación de Usuario', "Asignado a: $usuarioNombre (masivo)");
        }
    }

    private function asignarSucursalEquipo(Equipo $equipo, $sucursalId)
    {
        if ($sucursalId && $equipo->sucursal_id != $sucursalId) {
            $sucursalAnterior = $equipo->sucursal->nombre ?? 'N/A';
            $sucursalNueva = Sucursal::find($sucursalId)->nombre ?? 'N/A';
            
            $equipo->update(['sucursal_id' => $sucursalId]);
            $equipo->registrarMovimiento('Cambio de Sucursal', "De: $sucursalAnterior a: $sucursalNueva (masivo)");
        }
    }

    private function quitarAsignacionEquipo(Equipo $equipo)
    {
        // Cerrar asignación activa
        Asignacion::where('equipo_id', $equipo->id)
            ->whereNull('fecha_fin')
            ->update([
                'fecha_fin' => now(),
                'motivo' => 'Desasignación masiva desde inventario'
            ]);
            
        $equipo->registrarMovimiento('Desasignación de Usuario', 'Usuario removido (masivo)');
    }

    private function asignarUsuarioInsumo(Insumo $insumo, $usuarioId)
    {
        // Cerrar asignación activa anterior
        AsignacionInsumo::where('insumo_id', $insumo->id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => now()]);

        // Crear nueva asignación
        if ($usuarioId) {
            AsignacionInsumo::create([
                'insumo_id' => $insumo->id,
                'usuario_id' => $usuarioId,
                'cantidad' => 1, // Cantidad por defecto para asignaciones masivas
                'fecha_asignacion' => now(),
                'motivo' => 'Asignación masiva desde inventario',
            ]);
            
            $usuarioNombre = Usuario::find($usuarioId)->nombre ?? 'Usuario desconocido';
            $insumo->registrarMovimiento('Asignación de Usuario', "Asignado a: $usuarioNombre (masivo)");
        }
    }

    private function asignarSucursalInsumo(Insumo $insumo, $sucursalId)
    {
        if ($sucursalId && $insumo->sucursal_id != $sucursalId) {
            $sucursalAnterior = $insumo->sucursal->nombre ?? 'N/A';
            $sucursalNueva = Sucursal::find($sucursalId)->nombre ?? 'N/A';
            
            $insumo->update(['sucursal_id' => $sucursalId]);
            $insumo->registrarMovimiento('Cambio de Sucursal', "De: $sucursalAnterior a: $sucursalNueva (masivo)");
        }
    }

    private function quitarAsignacionInsumo(Insumo $insumo)
    {
        // Cerrar asignación activa
        AsignacionInsumo::where('insumo_id', $insumo->id)
            ->whereNull('fecha_fin')
            ->update([
                'fecha_fin' => now(),
                'motivo' => 'Desasignación masiva desde inventario'
            ]);
            
        $insumo->registrarMovimiento('Desasignación de Usuario', 'Usuario removido (masivo)');
    }

    private function generarMensajeExito($tipoAsignacion, $cantidad)
    {
        $mensajes = [
            'usuario' => "Asignación de usuario aplicada a $cantidad elementos correctamente",
            'sucursal' => "Cambio de sucursal aplicado a $cantidad elementos correctamente", 
            'ninguno' => "Desasignación aplicada a $cantidad elementos correctamente"
        ];
        
        return $mensajes[$tipoAsignacion] ?? "Operación completada para $cantidad elementos";
    }

    public function detalleEquipo($id)
    {
        $equipo = Equipo::with([
            'tipoEquipo', 
            'estadoEquipo', 
            'proveedor', 
            'sucursal',
            'usuarioAsignado.usuario',
            'asignaciones.usuario',
            'movimientos',
            'documentos'
        ])->findOrFail($id);
        
        $usuarios = Usuario::all();
        $estados = EstadoEquipo::all();
        $sucursales = Sucursal::all();
        $proveedores = Proveedor::all();

        return view('detalle_equipo', compact('equipo', 'usuarios', 'estados', 'sucursales', 'proveedores'));
    }

    public function detalleInsumo($id)
    {
        $insumo = Insumo::with([
            'estadoEquipo', 
            'proveedor', 
            'sucursal',
            'usuarioAsignado.usuario',
            'asignaciones.usuario',
            'movimientos',
            'documentos'
        ])->findOrFail($id);
        
        $usuarios = Usuario::all();
        $estados = EstadoEquipo::all();
        $sucursales = Sucursal::all();
        $proveedores = Proveedor::all();

        return view('detalle_insumo', compact('insumo', 'usuarios', 'estados', 'sucursales', 'proveedores'));
    }
}