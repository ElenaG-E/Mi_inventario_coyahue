<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\TipoEquipo;
use App\Models\EstadoEquipo;

class DashboardController extends Controller
{
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
}