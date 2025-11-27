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

// Componentes de Laravel
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
        // Obtener todos los filtros que podrían venir de la tabla principal o del modal
        $categoria      = $request->get('categoria', $request->get('filtro_categoria', ''));
        $tipo           = $request->get('tipo', $request->get('filtro_tipo', ''));
        $estado         = $request->get('estado', $request->get('filtro_estado', ''));
        $usuario        = $request->get('usuario', $request->get('filtro_usuario', ''));
        $proveedor      = $request->get('proveedor', $request->get('filtro_proveedor', ''));
        $sucursal       = $request->get('sucursal', $request->get('filtro_sucursal', ''));
        $precioMin      = $request->get('precio_min', $request->get('filtro_precio_min', ''));
        $precioMax      = $request->get('precio_max', $request->get('filtro_precio_max', ''));
        $fechaTipo      = $request->get('fecha_tipo', 'registro');
        $fechaDesde     = $request->get('fecha_desde', '');
        $fechaHasta     = $request->get('fecha_hasta', '');
        $buscar         = $request->get('buscar', $request->get('filtro_buscar', ''));

        // Filtro específico del modal de exportación
        $tipoReporte    = $request->input('tipo_reporte', 'general'); 
        
        // Query base para Equipos
        $equiposQuery = Equipo::with(['tipoEquipo','estadoEquipo','proveedor','usuarioAsignado.usuario','sucursal', 'asignaciones.usuario']);

        // Query base para Insumos
        $insumosQuery = Insumo::with(['estadoEquipo','proveedor','sucursal', 'asignaciones.usuario']);

        // =====================================================================
        // APLICAR FILTROS GLOBALES (de la tabla principal) a ambas queries
        // =====================================================================
        
        // Lógica de filtrado de EQUIPOS
        $equiposQuery->when($tipo, fn($q) => $q->whereHas('tipoEquipo', fn($t) => $t->where('nombre', $tipo)))
            ->when($estado, function($q) use ($estado) {
                if ($estado === 'Baja') {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','Baja'));
                } elseif ($estado) {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre',$estado));
                }
            })
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
                ->orWhere('numero_serie', 'like', "%$buscar%")));

        // Lógica de filtrado de INSUMOS
        $insumosQuery->when($tipo, fn($q) => $q->where('nombre', $tipo))
            ->when($estado, function($q) use ($estado) {
                if ($estado === 'Baja') {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre','Baja'));
                } elseif ($estado) {
                    $q->whereHas('estadoEquipo', fn($e) => $e->where('nombre',$estado));
                }
            })
            ->when($usuario === 'Sin asignar', fn($q) => $q->whereDoesntHave('usuarioAsignado'))
            ->when($usuario && $usuario !== 'Sin asignar', fn($q) => $q->whereHas('usuarioAsignado.usuario', fn($u) => $u->where('nombre', $usuario)))
            ->when($proveedor, fn($q) => $q->whereHas('proveedor', fn($p) => $p->where('nombre', $proveedor)))
            ->when($sucursal, fn($q) => $q->whereHas('sucursal', fn($s) => $s->where('nombre', $sucursal)))
            ->when($precioMin, fn($q) => $q->where('precio', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('precio', '<=', $precioMax))
            ->when($fechaDesde, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '>=', $fechaDesde))
            ->when($fechaHasta, fn($q) => $q->whereDate($fechaTipo == 'compra' ? 'fecha_compra' : 'fecha_registro', '<=', $fechaHasta))
            ->when($buscar, fn($q) => $q->where('nombre', 'like', "%$buscar%"));

        // =====================================================================
        // APLICAR LÓGICA ESPECÍFICA DEL TIPO DE REPORTE SOLICITADO
        // =====================================================================
        $equipos = collect();
        $insumos = collect();

        switch ($tipoReporte) {
            case 'general':
            case 'asignaciones':
            case 'estadisticas':
            case 'sucursales':
                $equipos = $equiposQuery->orderBy('fecha_registro', 'desc')->get();
                $insumos = $insumosQuery->orderBy('fecha_registro', 'desc')->get();
                break;
            case 'equipos':
                $equipos = $equiposQuery->orderBy('fecha_registro', 'desc')->get();
                break;
            case 'insumos':
                $insumos = $insumosQuery->orderBy('fecha_registro', 'desc')->get();
                break;
        }

        return [
            'equipos' => $equipos,
            'insumos' => $insumos,
            'fechaTipo' => $fechaTipo,
            'tipoReporte' => $tipoReporte,
            'filtros' => $request->except(['_token', 'tipo_reporte', 'formato']),
            'metadata' => ['titulo' => $this->generarTituloReporte($tipoReporte, $sucursal)],
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

        // Crear una nueva instancia de Request que combine los filtros del index con los del modal.
        $combinedRequestData = array_merge($request->query(), $request->all());
        $combinedRequest = Request::create('/inventario/exportar', 'GET', $combinedRequestData); // Usar GET para imitar los filtros de URL

        $datos = $this->obtenerDatosFiltrados($combinedRequest);

        $filename = "reporte_{$tipoReporte}_" . now()->format('Ymd_His');

        if ($formato === 'pdf') {
            // LÓGICA REAL DE GENERACIÓN DE PDF (Requiere Dompdf)
            $vista = 'reportes.inventario_pdf'; // Vista por defecto para inventario general
            if ($tipoReporte === 'asignaciones') {
                $vista = 'reportes.asignaciones_pdf';
            } elseif ($tipoReporte === 'estadisticas') {
                $vista = 'reportes.estadisticas_pdf';
            } elseif ($tipoReporte === 'equipos') {
                $vista = 'reportes.equipos_pdf'; 
            } elseif ($tipoReporte === 'insumos') {
                $vista = 'reportes.insumos_pdf'; 
            } elseif ($tipoReporte === 'sucursales') {
                 $vista = 'reportes.sucursales_pdf'; 
            }

            try {
                $pdf = Pdf::loadView($vista, $datos);
                return $pdf->download($filename . '.pdf');
            } catch (\Exception $e) {
                Log::error('Error generando PDF: ' . $e->getMessage());
                return response()->json(['error' => 'Error interno al generar el reporte PDF. Asegúrese de que la librería DomPDF esté instalada y la vista Blade (`' . $vista . '`) exista. Mensaje: ' . $e->getMessage()], 500);
            }

        } elseif ($formato === 'excel') {
            // LÓGICA DE EXCEL/CSV
            $rows = $this->generarFilasCSV($datos);
            
            // Si no hay filas de datos aparte del encabezado, devolver un mensaje
            if ($rows->count() <= 1 && ($tipoReporte === 'equipos' || $tipoReporte === 'insumos')) {
                 return response()->json(['error' => 'No hay datos para el tipo de reporte seleccionado y los filtros aplicados.'], 404);
            }

            $csv = $rows->map(fn($row) => implode(';', $row))->implode("\n");
            
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
    // MÉTODOS PRIVADOS AUXILIARES (para asignaciones masivas y reportes)
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

    private function generarTituloReporte($tipo, $sucursal) {
        $titulo = [
            'general' => 'Inventario Completo',
            'equipos' => 'Reporte de Equipos',
            'insumos' => 'Reporte de Insumos',
            'asignaciones' => 'Reporte de Asignaciones por Usuario',
            'sucursales' => 'Inventario por Sucursal: ' . ($sucursal ?: 'Todas'),
            'estadisticas' => 'Estadísticas Generales de Inventario',
        ];
        return ($titulo[$tipo] ?? 'Reporte Desconocido') . ' - ' . now()->format('d/m/Y H:i');
    }

    private function generarFilasCSV(array $datos)
    {
        $rows = collect();
        $equipos = $datos['equipos'];
        $insumos = $datos['insumos'];
        $tipoReporte = $datos['tipoReporte'];

        // Encabezados y datos según el tipo de reporte
        switch ($tipoReporte) {
            case 'general':
            case 'equipos':
            case 'insumos':
            case 'sucursales':
                // Encabezados para inventario general/equipos/insumos/sucursales
                $commonHeaders = ['Categoría', 'Tipo/Nombre', 'Marca', 'Modelo', 'N° Serie / Cantidad', 'Precio', 'Estado', 'Usuario Asignado', 'Sucursal'];
                
                if ($rows->isEmpty()) { // Añadir encabezado solo si no hay datos previamente mergeados
                    $rows->push($commonHeaders);
                }

                // Datos de Equipos
                if ($equipos->isNotEmpty() && ($tipoReporte === 'general' || $tipoReporte === 'equipos' || $tipoReporte === 'sucursales')) {
                    $rows = $rows->merge($equipos->map(fn($e) => [
                        'Equipo',
                        $e->tipoEquipo->nombre ?? '-',
                        $e->marca,
                        $e->modelo,
                        $e->numero_serie,
                        number_format($e->precio, 0, ',', '.'),
                        $e->estadoEquipo->nombre ?? '-',
                        $e->usuarioAsignado?->usuario->nombre ?? 'Sin asignar',
                        $e->sucursal->nombre ?? '-',
                    ]));
                }

                // Datos de Insumos
                if ($insumos->isNotEmpty() && ($tipoReporte === 'general' || $tipoReporte === 'insumos' || $tipoReporte === 'sucursales')) {
                    $rows = $rows->merge($insumos->map(fn($i) => [
                        'Insumo',
                        $i->nombre,
                        'N/A', // Los insumos no tienen marca
                        'N/A', // Los insumos no tienen modelo
                        $i->cantidad, // Usamos 'Cantidad' en lugar de N° Serie
                        number_format($i->precio, 0, ',', '.'),
                        $i->estadoEquipo->nombre ?? '-',
                        $i->usuarioAsignado?->usuario->nombre ?? 'Sin asignar',
                        $i->sucursal->nombre ?? '-',
                    ]));
                }
                break;

            case 'asignaciones':
                $rows->push(['Usuario', 'Item Asignado', 'Tipo de Item', 'Fecha Asignación', 'Fecha Fin', 'Motivo']);
                
                // Asignaciones de equipos
                $rows = $rows->merge($equipos->flatMap(fn($e) => $e->asignaciones->map(fn($a) => [
                    $a->usuario->nombre ?? 'N/A',
                    ($e->marca ? $e->marca . ' ' : '') . $e->modelo . ' (S/N: ' . $e->numero_serie . ')',
                    'Equipo',
                    $a->fecha_asignacion ? $a->fecha_asignacion->format('Y-m-d H:i') : 'N/A',
                    $a->fecha_fin ? $a->fecha_fin->format('Y-m-d H:i') : 'Activa',
                    $a->motivo,
                ])));

                // Asignaciones de insumos (si tu modelo AsignacionInsumo tiene una relación similar)
                $rows = $rows->merge($insumos->flatMap(fn($i) => $i->asignaciones->map(fn($ai) => [
                    $ai->usuario->nombre ?? 'N/A',
                    $i->nombre . ' (Cantidad: ' . $ai->cantidad . ')',
                    'Insumo',
                    $ai->fecha_asignacion ? $ai->fecha_asignacion->format('Y-m-d H:i') : 'N/A',
                    $ai->fecha_fin ? $ai->fecha_fin->format('Y-m-d H:i') : 'Activa',
                    $ai->motivo,
                ])));
                break;

            case 'estadisticas':
                // Esto es un placeholder. Para estadísticas reales, necesitarías procesar los datos
                // (e.g., contar por estado, por tipo, por sucursal, etc.)
                $rows = collect([
                    ['Estadística', 'Valor'],
                    ['Total Equipos', $equipos->count()],
                    ['Equipos en Uso', $equipos->filter(fn($e) => $e->estadoEquipo->nombre === 'En Uso')->count()],
                    ['Equipos en Reparación', $equipos->filter(fn($e) => $e->estadoEquipo->nombre === 'En Reparación')->count()],
                    ['Equipos en Almacén', $equipos->filter(fn($e) => $e->estadoEquipo->nombre === 'En Almacén')->count()],
                    ['Equipos dados de Baja', $equipos->filter(fn($e) => $e->estadoEquipo->nombre === 'Baja')->count()],
                    ['Total Insumos', $insumos->count()],
                    ['Insumos Disponibles', $insumos->filter(fn($i) => $i->estadoEquipo->nombre === 'En Almacén')->count()],
                ]);
                break;

            default:
                // Si no se reconoce el tipo de reporte, genera un reporte general
                $rows->push(['Categoría', 'Tipo/Nombre', 'Marca', 'Modelo', 'N° Serie / Cantidad', 'Precio', 'Estado', 'Usuario Asignado', 'Sucursal']);
                
                $rows = $rows->merge($equipos->map(fn($e) => [
                    'Equipo',
                    $e->tipoEquipo->nombre ?? '-',
                    $e->marca,
                    $e->modelo,
                    $e->numero_serie,
                    number_format($e->precio, 0, ',', '.'),
                    $e->estadoEquipo->nombre ?? '-',
                    $e->usuarioAsignado?->usuario->nombre ?? 'Sin asignar',
                    $e->sucursal->nombre ?? '-',
                ]));
                $rows = $rows->merge($insumos->map(fn($i) => [
                    'Insumo',
                    $i->nombre,
                    'N/A',
                    'N/A',
                    $i->cantidad,
                    number_format($i->precio, 0, ',', '.'),
                    $i->estadoEquipo->nombre ?? '-',
                    $i->usuarioAsignado?->usuario->nombre ?? 'Sin asignar',
                    $i->sucursal->nombre ?? '-',
                ]));
                break;
        }
        
        return $rows;
    }
}
