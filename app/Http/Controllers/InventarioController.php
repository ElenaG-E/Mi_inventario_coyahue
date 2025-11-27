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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf; // Implementación para PDF real (Dompdf)

class InventarioController extends Controller
{
    /**
     * Muestra el listado de inventario con filtros aplicados.
     */
    public function index(Request $request)
    {
        $categoria     = $request->get('categoria', '');
        $tipo          = $request->get('tipo', '');
        $estado        = $request->get('estado', '');
        $usuario       = $request->get('usuario', '');
        $proveedor     = $request->get('proveedor', '');
        $sucursal      = $request->get('sucursal', '');
        $precioMin     = $request->get('precio_min', '');
        $precioMax     = $request->get('precio_max', '');
        $fechaTipo     = $request->get('fecha_tipo', 'registro');
        $fechaDesde    = $request->get('fecha_desde', '');
        $fechaHasta    = $request->get('fecha_hasta', '');
        $buscar        = $request->get('buscar', '');

        // Asegurar que precioMin y precioMax no estén invertidos
        if ($precioMin !== '' && $precioMax !== '' && $precioMin > $precioMax) {
            [$precioMin, $precioMax] = [$precioMax, $precioMin];
        }

        // --- Consulta de EQUIPOS ---
        $equipos = Equipo::with(['tipoEquipo','estadoEquipo','proveedor','usuarioAsignado.usuario','sucursal'])
            ->when($tipo, fn($q) => $q->whereHas('tipoEquipo', fn($t) => $t->where('nombre', $tipo)))
            ->when($estado, function($q) use ($estado) {
                if ($estado === 'Baja') {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','Baja'));
                } elseif ($estado) {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre',$estado));
                }
            }, fn($q) => $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','!=','Baja')))
            ->when($usuario === 'Sin asignar', fn($q) => $q->whereDoesntHave('usuarioAsignado'))
            ->when($usuario && $usuario !== 'Sin asignar', fn($q) => $q->whereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre', $usuario)))
            ->when($proveedor, fn($q) => $q->whereHas('proveedor', fn($p) => $p->where('nombre', $proveedor)))
            ->when($sucursal, fn($q) => $q->whereHas('sucursal', fn($s) => $s->where('nombre', $sucursal)))
            ->when($precioMin, fn($q) => $q->where('precio', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('precio', '<=', $precioMax))
            ->when($fechaDesde, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '>=', $fechaDesde))
            ->when($fechaHasta, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '<=', $fechaHasta))
            ->when($buscar, function($q) use ($buscar) {
                $q->where(fn($sub) => $sub->where('marca', 'like', "%$buscar%")
                    ->orWhere('modelo', 'like', "%$buscar%")
                    ->orWhere('numero_serie', 'like', "%$buscar%")
                    ->orWhereHas('tipoEquipo', fn($t) => $t->where('nombre','like',"%$buscar%"))
                    ->orWhereHas('estadoEquipo', fn($e) => $e->where('nombre','like',"%$buscar%"))
                    ->orWhereHas('proveedor', fn($p) => $p->where('nombre','like',"%$buscar%"))
                    ->orWhereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre','like',"%$buscar%")));
            })
            ->orderBy('fecha_registro','desc')
            ->get();

        // --- Consulta de INSUMOS ---
        $insumos = Insumo::with(['estadoEquipo','proveedor','usuarioAsignado.usuario','sucursal'])
            ->when($tipo, fn($q) => $q->where('nombre', $tipo))
            ->when($estado, function($q) use ($estado) {
                if ($estado === 'Baja') {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','Baja'));
                } elseif ($estado) {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre',$estado));
                }
            }, fn($q) => $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','!=','Baja')))
            ->when($usuario === 'Sin asignar', fn($q) => $q->whereDoesntHave('usuarioAsignado'))
            ->when($usuario && $usuario !== 'Sin asignar', fn($q) => $q->whereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre', $usuario)))
            ->when($proveedor, fn($q) => $q->whereHas('proveedor', fn($p) => $p->where('nombre', $proveedor)))
            ->when($sucursal, fn($q) => $q->whereHas('sucursal', fn($s) => $s->where('nombre', $sucursal)))
            ->when($precioMin, fn($q) => $q->where('precio', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('precio', '<=', $precioMax))
            ->when($fechaDesde, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '>=', $fechaDesde))
            ->when($fechaHasta, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '<=', $fechaHasta))
            ->when($buscar, function($q) use ($buscar) {
                $q->where(fn($sub) => $sub->where('nombre', 'like', "%$buscar%")
                    ->orWhereHas('estadoEquipo', fn($e) => $e->where('nombre','like',"%$buscar%"))
                    ->orWhereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre','like',"%$buscar%"))
                    ->orWhereHas('proveedor', fn($p) => $p->where('nombre','like',"%$buscar%"))
                    ->orWhereHas('sucursal', fn($s) => $s->where('nombre','like',"%$buscar%")));
            })
            ->orderBy('fecha_registro','desc')
            ->get();

        // --- Datos para los selectores de filtro ---
        $tipos          = TipoEquipo::pluck('nombre')->unique();
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

    // =========================================================================
    // MÉTODO AUXILIAR: Obtener datos filtrados para exportación
    // =========================================================================
    
    private function obtenerDatosFiltrados(Request $request)
    {
        $categoria     = $request->get('categoria', '');
        $tipo          = $request->get('tipo', '');
        $estado        = $request->get('estado', '');
        $usuario       = $request->get('usuario', '');
        $proveedor     = $request->get('proveedor', '');
        $sucursal      = $request->get('sucursal', '');
        $precioMin     = $request->get('precio_min', '');
        $precioMax     = $request->get('precio_max', '');
        $fechaTipo     = $request->get('fecha_tipo', 'registro');
        $fechaDesde    = $request->get('fecha_desde', '');
        $fechaHasta    = $request->get('fecha_hasta', '');
        $buscar        = $request->get('buscar', '');

        // Lógica de filtrado de EQUIPOS
        $equiposQuery = Equipo::with(['tipoEquipo','estadoEquipo','proveedor','usuarioAsignado.usuario','sucursal'])
            ->when($tipo, fn($q) => $q->whereHas('tipoEquipo', fn($t) => $t->where('nombre', $tipo)))
            ->when($estado, function($q) use ($estado) {
                if ($estado === 'Baja') {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','Baja'));
                } elseif ($estado) {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre',$estado));
                }
            }, fn($q) => $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','!=','Baja')))
            ->when($usuario === 'Sin asignar', fn($q) => $q->whereDoesntHave('usuarioAsignado'))
            ->when($usuario && $usuario !== 'Sin asignar', fn($q) => $q->whereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre', $usuario)))
            ->when($proveedor, fn($q) => $q->whereHas('proveedor', fn($p) => $p->where('nombre', $proveedor)))
            ->when($sucursal, fn($q) => $q->whereHas('sucursal', fn($s) => $s->where('nombre', $sucursal)))
            ->when($precioMin, fn($q) => $q->where('precio', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('precio', '<=', $precioMax))
            ->when($fechaDesde, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '>=', $fechaDesde))
            ->when($fechaHasta, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '<=', $fechaHasta))
            ->when($buscar, fn($q) => $q->where(fn($sub) => $sub->where('marca', 'like', "%$buscar%")
                ->orWhere('modelo', 'like', "%$buscar%")
                ->orWhere('numero_serie', 'like', "%$buscar%")
                ->orWhereHas('tipoEquipo', fn($t) => $t->where('nombre','like',"%$buscar%"))
                ->orWhereHas('estadoEquipo', fn($e) => $e->where('nombre','like',"%$buscar%"))
                ->orWhereHas('proveedor', fn($p) => $p->where('nombre','like',"%$buscar%"))
                ->orWhereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre','like',"%$buscar%"))));
        
        // Lógica de filtrado de INSUMOS
        $insumosQuery = Insumo::with(['estadoEquipo','proveedor','usuarioAsignado.usuario','sucursal'])
            ->when($tipo, fn($q) => $q->where('nombre', $tipo))
            ->when($estado, function($q) use ($estado) {
                if ($estado === 'Baja') {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','Baja'));
                } elseif ($estado) {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre',$estado));
                }
            }, fn($q) => $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','!=','Baja')))
            ->when($usuario === 'Sin asignar', fn($q) => $q->whereDoesntHave('usuarioAsignado'))
            ->when($usuario && $usuario !== 'Sin asignar', fn($q) => $q->whereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre', $usuario)))
            ->when($proveedor, fn($q) => $q->whereHas('proveedor', fn($p) => $p->where('nombre', $proveedor)))
            ->when($sucursal, fn($q) => $q->whereHas('sucursal', fn($s) => $s->where('nombre', $sucursal)))
            ->when($precioMin, fn($q) => $q->where('precio', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('precio', '<=', $precioMax))
            ->when($fechaDesde, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '>=', $fechaDesde))
            ->when($fechaHasta, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '<=', $fechaHasta))
            ->when($buscar, fn($q) => $q->where(fn($sub) => $sub->where('nombre', 'like', "%$buscar%")
                ->orWhereHas('estadoEquipo', fn($e) => $e->where('nombre','like',"%$buscar%"))
                ->orWhereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre','like',"%$buscar%"))
                ->orWhereHas('proveedor', fn($p) => $p->where('nombre','like',"%$buscar%"))
                ->orWhereHas('sucursal', fn($s) => $s->where('nombre','like',"%$buscar%"))));

        return [
            'equipos' => ($categoria == 'Insumo' ? collect() : $equiposQuery->orderBy('fecha_registro','desc')->get()),
            'insumos' => ($categoria == 'Equipo' ? collect() : $insumosQuery->orderBy('fecha_registro','desc')->get()),
            'fechaTipo' => $fechaTipo,
            'categoriaFiltro' => $categoria,
            'usuarioFiltro' => $usuario,
            'proveedorFiltro' => $proveedor,
            'sucursalFiltro' => $sucursal,
            'estadoFiltro' => $estado,
        ];
    }

    /**
     * Autocompletado del buscador principal.
     */
    public function autocomplete(Request $request)
    {
        $term = trim($request->get('term', ''));
        
        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        $termLike = "%{$term}%";
        $results = [];

        try {
            // Búsquedas combinadas (Marca, Modelo, Tipo, Estado, Proveedor, Sucursal, Usuario)
            $equipos = Equipo::select('marca', 'modelo')->where('marca', 'LIKE', $termLike)
                ->orWhere('modelo', 'LIKE', $termLike)
                ->get();
            
            foreach ($equipos as $equipo) {
                $results[] = ['label' => $equipo->marca, 'value' => $equipo->marca, 'tipo' => 'marca', 'campo' => 'buscar'];
                $results[] = ['label' => $equipo->modelo, 'value' => $equipo->modelo, 'tipo' => 'modelo', 'campo' => 'buscar'];
            }

            $modelosDeBusqueda = [TipoEquipo::class => 'tipo', EstadoEquipo::class => 'estado', Proveedor::class => 'proveedor', Sucursal::class => 'sucursal', Usuario::class => 'usuario'];
            
            foreach ($modelosDeBusqueda as $modelClass => $type) {
                $items = $modelClass::where('nombre', 'LIKE', $termLike)->limit(5)->get();
                foreach ($items as $item) {
                    $results[] = [
                        'label' => $item->nombre,
                        'value' => $item->nombre,
                        'tipo' => $type,
                        'campo' => $type
                    ];
                }
            }

            // Búsqueda en insumos - nombre
            $insumos = Insumo::where('nombre', 'LIKE', $termLike)->limit(10)->get();
            foreach ($insumos as $insumo) {
                $results[] = ['label' => $insumo->nombre, 'value' => $insumo->nombre, 'tipo' => 'insumo', 'campo' => 'buscar', 'id' => $insumo->id];
            }

            // Limpiar y limitar resultados
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

    /**
     * Maneja la solicitud de exportación de reportes desde el modal.
     */
    public function exportar(Request $request)
    {
        $tipoReporte = $request->input('tipo_reporte');
        $formato = $request->input('formato');
        
        if (empty($tipoReporte) || empty($formato)) {
            return response()->json(['error' => 'Debe seleccionar el tipo y formato del reporte.'], 400);
        }

        $datos = $this->obtenerDatosFiltrados($request);

        $filename = "reporte_{$tipoReporte}_" . now()->format('Ymd_His');

        if ($formato === 'pdf') {
            // ----------------------------------------------------
            // LÓGICA REAL DE GENERACIÓN DE PDF (Requiere Dompdf)
            // ----------------------------------------------------
            
            // ASUMIR VISTA: Se debe crear 'reportes.inventario_pdf' con el HTML del reporte.
            try {
                $pdf = Pdf::loadView('reportes.inventario_pdf', $datos);
                return $pdf->download($filename . '.pdf');
            } catch (\Exception $e) {
                Log::error('Error generando PDF: ' . $e->getMessage());
                // Devolver un error JSON al usuario en lugar de un archivo corrupto.
                return response()->json(['error' => 'Error interno al generar el reporte PDF. Asegúrese de que la librería DomPDF esté instalada y la vista exista.'], 500);
            }

        } elseif ($formato === 'excel') {
            // ----------------------------------------------------
            // LÓGICA DE EXCEL/CSV BÁSICA
            // ----------------------------------------------------
            
            $headers = ['Tipo/Nombre', 'Marca', 'Modelo/Cantidad', 'N° Serie', 'Precio', 'Estado', 'Sucursal'];
            $rows = collect();
            
            if ($datos['equipos']->isNotEmpty()) {
                $rows = $datos['equipos']->map(fn($e) => [
                    $e->tipoEquipo->nombre ?? '-',
                    $e->marca,
                    $e->modelo,
                    $e->numero_serie,
                    number_format($e->precio, 0, ',', '.'),
                    $e->estadoEquipo->nombre ?? '-',
                    $e->sucursal->nombre ?? '-',
                ]);
            } elseif ($datos['insumos']->isNotEmpty()) {
                 $rows = $datos['insumos']->map(fn($i) => [
                    $i->nombre,
                    'N/A',
                    $i->cantidad,
                    'N/A',
                    number_format($i->precio, 0, ',', '.'),
                    $i->estadoEquipo->nombre ?? '-',
                    $i->sucursal->nombre ?? '-',
                ]);
            }
            
            $rows->prepend($headers);

            // Generar un archivo CSV simple para simular Excel
            $csv = $rows->map(fn($row) => implode(';', $row))->implode("\n"); // Usamos ';' como separador para mejor compatibilidad CSV
            
            $filename .= '.csv';
            
            return Response::make($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
            
        } else {
            return response()->json(['error' => 'Formato de exportación no soportado.'], 400);
        }
    }

    /**
     * Aplica asignaciones, desasignaciones o cambios de sucursal de forma masiva.
     */
    public function storeAsignaciones(Request $request)
    {
        $request->validate([
            'tipo_asignacion' => 'required|in:usuario,sucursal,ninguno',
            'items' => 'required|array',
            'destino_id' => 'nullable|integer', // Requerido solo si tipo_asignacion != ninguno
        ]);

        $tipoAsignacion = $request->tipo_asignacion;
        $destinoId = $request->destino_id;
        $items = $request->items;

        try {
            DB::beginTransaction(); // Inicia la transacción para operaciones masivas
            
            foreach ($items as $itemId) {
                // Asumimos que los IDs son únicos y pueden ser equipos o insumos.
                $equipo = Equipo::find($itemId);
                
                if ($equipo) {
                    $this->procesarAsignacionEquipo($equipo, $tipoAsignacion, $destinoId);
                } else {
                    $insumo = Insumo::find($itemId);
                    if ($insumo) {
                        $this->procesarAsignacionInsumo($insumo, $tipoAsignacion, $destinoId);
                    }
                }
            }

            DB::commit(); // Confirma las operaciones
            
            $mensaje = $this->generarMensajeExito($tipoAsignacion, count($items));
            // FIX: Corregido redirect a 'inventario.index'
            return redirect()->route('inventario.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack(); // Deshace los cambios si algo falla
            Log::error('Error en asignaciones masivas: ' . $e->getMessage());
            // FIX: Corregido redirect a 'inventario.index'
            return redirect()->route('inventario.index')->with('error', 'Error al procesar las asignaciones.');
        }
    }

    /**
     * Muestra el detalle del equipo con QR generado dinámicamente
     */
    public function detalleEquipo($id)
    {
        // Cargar equipo y todas las relaciones necesarias
        $equipo = Equipo::with([
            'tipoEquipo',
            'proveedor',
            'estadoEquipo',
            'sucursal',
            'especificacionesTecnicas', // Cargamos las especificaciones IA/técnicas
            'movimientos' => fn($query) => $query->orderBy('fecha_movimiento', 'desc'),
            'asignaciones' => fn($query) => $query->with('usuario')->orderBy('fecha_asignacion', 'desc')
        ])->findOrFail($id);

        // Obtener la última asignación activa
        $usuarioAsignado = $equipo->asignaciones()->whereNull('fecha_fin')->first();
        $equipo->usuarioAsignado = $usuarioAsignado; // Adjuntar al modelo

        // Obtener datos adicionales para el modal de gestión
        $estados = EstadoEquipo::all();
        $usuarios = Usuario::all();
        $proveedores = Proveedor::all();
        $sucursales = Sucursal::all();

        // Generar QR (Copia de la lógica de RegistroEquipoController)
        $qrUrl = route('inventario.equipo', $equipo->id);
        try {
            $qrCode = QrCode::format('png')->size(250)->generate($qrUrl); 
            $qrBase64 = 'data:image/png;base64,' . base64_encode($qrCode);
        } catch (\Exception $e) {
            Log::error("QR Generation Failed (Detalle): " . $e->getMessage());
            $qrBase64 = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrUrl);
        }


        return view('detalle_equipo', compact(
            'equipo',
            'usuarios',
            'estados',
            'proveedores',
            'sucursales',
            'qrBase64' // <-- PASAR EL QR ALMACENADO
        ));
    }

    /**
     * Muestra el detalle del insumo (Se asume lógica similar para movimientos)
     */
    public function detalleInsumo($id)
    {
        $insumo = Insumo::with([
            'estadoEquipo',
            'proveedor',
            'sucursal',
            'movimientos' => fn($query) => $query->orderBy('fecha_movimiento', 'desc'),
            'asignaciones' => fn($query) => $query->with('usuario')->orderBy('fecha_asignacion', 'desc')
        ])->findOrFail($id);
        
        $usuarios = Usuario::all(); 
        $estados = EstadoEquipo::all(); 
        $proveedores = Proveedor::all();
        $sucursales = Sucursal::all(); 

        // Generar QR (Copia de la lógica de RegistroEquipoController)
        $qrUrl = route('inventario.insumo', $insumo->id);
        try {
            $qrCode = QrCode::format('png')->size(250)->generate($qrUrl); 
            $qrBase64 = 'data:image/png;base64,' . base64_encode($qrCode);
        } catch (\Exception $e) {
            Log::error("QR Generation Failed (Detalle): " . $e->getMessage());
            $qrBase64 = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrUrl);
        }


        return view('detalle_insumo', compact(
            'insumo', 
            'usuarios', 
            'estados', 
            'proveedores',
            'sucursales',
            'qrBase64'
        ));
    }


    // =========================================================================
    // MÉTODOS PRIVADOS AUXILIARES (para asignaciones masivas)
    // =========================================================================

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
}
