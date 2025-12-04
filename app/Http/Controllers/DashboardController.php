<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\TipoEquipo;
use App\Models\EstadoEquipo;
use App\Models\Insumo;
use App\Models\Proveedor; 
use App\Models\Sucursal; 
use App\Models\Usuario; 
use App\Models\Asignacion; 
use App\Models\AsignacionInsumo; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response; // Se mantiene por si acaso, aunque no lo usa para CSV
use Barryvdh\DomPDF\Facade\Pdf; 
use Maatwebsite\Excel\Facades\Excel; // <--- IMPORTANTE: Importa el Facade de Excel
use App\Exports\DashboardExport;    // <--- IMPORTANTE: Importa la clase Export que debes crear

class DashboardController extends Controller
{
    /**
     * Muestra la vista principal del dashboard con los KPIs y gráficos.
     */
    public function index()
    {
        // Obtener el ID del estado "Baja"
        $estadoBajaId = EstadoEquipo::where('nombre', 'Baja')->value('id');

        // KPIs - excluir equipos de baja
        $totalEquipos = Equipo::where('estado_equipo_id', '!=', $estadoBajaId)->count();

        $enMantencion = Equipo::whereHas('estadoEquipo', function ($q) {
            $q->where('nombre', 'Mantención');
        })->where('estado_equipo_id', '!=', $estadoBajaId)->count();

        $disponibles = Equipo::whereHas('estadoEquipo', function ($q) {
            $q->where('nombre', 'Disponible');
        })->where('estado_equipo_id', '!=', $estadoBajaId)->count();

        $dadosDeBaja = Equipo::whereHas('estadoEquipo', function ($q) {
            $q->where('nombre', 'Baja');
        })->count();

        $equiposAsignados = Equipo::has('asignaciones')
            ->where('estado_equipo_id', '!=', $estadoBajaId)
            ->count();

        // Distribución por tipo - excluir equipos de baja
        $distribucion = TipoEquipo::withCount(['equipos' => function($query) use ($estadoBajaId) {
            $query->where('estado_equipo_id', '!=', $estadoBajaId);
        }])->get();

        // Equipos recientemente agregados (últimos 5) - excluir equipos de baja
        $equiposRecientes = Equipo::with(['tipoEquipo', 'estadoEquipo', 'proveedor'])
            ->where('estado_equipo_id', '!=', $estadoBajaId)
            ->orderBy('fecha_registro', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalEquipos',
            'enMantencion',
            'disponibles',
            'dadosDeBaja',
            'equiposAsignados',
            'distribucion',
            'equiposRecientes'
        ));
    }
    
    // =========================================================================
    // FUNCIÓN DE EXPORTACIÓN (USANDO LIBRERÍA EXCEL)
    // =========================================================================

    /**
     * Maneja la solicitud de exportación de reportes desde el modal.
     */
    public function exportar(Request $request)
    {
        $tipoReporte = $request->query('tipo');
        $formato = $request->query('formato', 'pdf'); 

        if (empty($tipoReporte)) {
             return redirect()->route('dashboard')->with('error', 'Tipo de reporte no válido o no seleccionado.');
        }

        $request->merge([
            'tipo_reporte' => $tipoReporte,
            'formato' => $formato,
        ]);

        $datos = $this->obtenerDatosFiltrados($request);

        $filename = "reporte_{$tipoReporte}_" . now()->format('Ymd_His');

        if ($formato === 'pdf') {
            // LÓGICA DE PDF (sin cambios)
            $vista = 'reportes.inventario_pdf'; 
            
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
            } elseif ($tipoReporte === 'general') {
                $vista = 'reportes.inventario_pdf';
            } elseif ($tipoReporte === 'mantenciones') {
                 $vista = 'reportes.mantenciones_pdf';
            } else {
                 return redirect()->route('dashboard')->with('error', 'Tipo de reporte PDF desconocido: ' . $tipoReporte);
            }

            try {
                $pdf = Pdf::loadView($vista, $datos);
                return $pdf->download($filename . '.pdf');
            } catch (\Exception $e) {
                Log::error('Error generando PDF: ' . $e->getMessage());
                return redirect()->route('dashboard')->with('error', 'Error al generar el reporte PDF. Verifique la vista Blade (' . $vista . ').');
            }

        } elseif ($formato === 'csv' || $formato === 'excel') {
            // LÓGICA DE EXCEL/CSV USANDO LARAVEL-EXCEL (SOLUCIÓN ESTABLE)
            
            // Verificamos si hay datos relevantes antes de exportar
            if ($datos['equipos']->isEmpty() && $datos['insumos']->isEmpty()) {
                return redirect()->route('dashboard')->with('error', 'No hay datos para el tipo de reporte seleccionado y los filtros aplicados.');
            }
            
            // Llama a la librería de Excel para descargar el CSV/XLSX
            // La clase DashboardExport maneja la codificación, delimitador y formato de los datos.
            return Excel::download(new DashboardExport($datos), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);

        } else {
            return redirect()->route('dashboard')->with('error', 'Formato de exportación no soportado.');
        }
    }

    // =========================================================================
    // MÉTODOS AUXILIARES
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

        // Filtro específico del modal de exportación (Usamos input() para tomar el valor del merge)
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
            case 'mantenciones': // Soporte para el nuevo tipo de reporte
                // Para reportes que necesitan ambos conjuntos de datos
                $equipos = $equiposQuery->orderBy('fecha_registro', 'desc')->get();
                $insumos = $insumosQuery->orderBy('fecha_registro', 'desc')->get();
                break;
            case 'equipos':
                // Solo equipos (insumos está vacío)
                $equipos = $equiposQuery->orderBy('fecha_registro', 'desc')->get();
                break;
            case 'insumos':
                // Solo insumos (equipos está vacío)
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
    
    private function generarTituloReporte($tipo, $sucursal) {
        $titulo = [
            'general' => 'Inventario Completo',
            'equipos' => 'Reporte de Equipos',
            'insumos' => 'Reporte de Insumos',
            'asignaciones' => 'Reporte de Asignaciones por Usuario',
            'sucursales' => 'Inventario por Sucursal: ' . ($sucursal ?: 'Todas'),
            'estadisticas' => 'Estadísticas Generales de Inventario',
            'mantenciones' => 'Reporte de Equipos en Mantención', 
        ];
        return ($titulo[$tipo] ?? 'Reporte Desconocido') . ' - ' . now()->format('d/m/Y H:i');
    }

    // El método generarFilasCSV() ya no es necesario aquí porque su lógica fue movida a App\Exports\DashboardExport.php
}
