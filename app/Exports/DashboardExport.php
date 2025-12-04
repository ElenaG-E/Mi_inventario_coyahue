<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class DashboardExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    protected $datos;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    /**
    * Define la configuración CSV/Excel para Latinoamérica.
    * Esto es CRUCIAL para que Excel use el punto y coma (;) como delimitador.
    */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'excel_compatibility' => false,
            'use_bom' => true, // Fuerza la codificación UTF-8 para caracteres especiales (Ñ, tildes)
        ];
    }

    /**
    * @return Collection
    */
    public function collection()
    {
        $equipos = $this->datos['equipos'];
        $insumos = $this->datos['insumos'];
        $tipoReporte = $this->datos['tipoReporte'];
        $rows = collect();

        // Lógica de datos (adaptada de tu antiguo generarFilasCSV)
        switch ($tipoReporte) {
            case 'general':
            case 'equipos':
            case 'insumos':
            case 'sucursales':
            case 'mantenciones':
                // Datos de Equipos
                if ($equipos->isNotEmpty() && in_array($tipoReporte, ['general', 'equipos', 'sucursales', 'mantenciones'])) {
                    $rows = $rows->merge($equipos->map(fn($e) => [
                        'Equipo',
                        $e->tipoEquipo->nombre ?? '-',
                        $e->marca,
                        $e->modelo,
                        $e->numero_serie,
                        // Formato de precio sin separadores de miles y coma decimal, CRUCIAL para Excel
                        str_replace('.', ',', number_format($e->precio, 0, '.', '')),
                        $e->estadoEquipo->nombre ?? '-',
                        $e->usuarioAsignado?->usuario->nombre ?? 'Sin asignar',
                        $e->sucursal->nombre ?? '-',
                    ]));
                }

                // Datos de Insumos
                if ($insumos->isNotEmpty() && in_array($tipoReporte, ['general', 'insumos', 'sucursales'])) {
                    $rows = $rows->merge($insumos->map(fn($i) => [
                        'Insumo',
                        $i->nombre,
                        'N/A', 
                        'N/A', 
                        $i->cantidad, 
                        str_replace('.', ',', number_format($i->precio, 0, '.', '')),
                        $i->estadoEquipo->nombre ?? '-',
                        $i->usuarioAsignado?->usuario->nombre ?? 'Sin asignar',
                        $i->sucursal->nombre ?? '-',
                    ]));
                }
                break;
            // ... (otras lógicas de asignaciones/estadísticas deberían ir aquí, si necesitas CSV detallado)
            default:
                // Fallback a general
                $rows = $rows->merge($equipos->map(fn($e) => [
                    'Equipo', $e->tipoEquipo->nombre ?? '-', $e->marca, $e->modelo, $e->numero_serie, str_replace('.', ',', number_format($e->precio, 0, '.', '')), $e->estadoEquipo->nombre ?? '-', $e->usuarioAsignado?->usuario->nombre ?? 'Sin asignar', $e->sucursal->nombre ?? '-',
                ]));
                $rows = $rows->merge($insumos->map(fn($i) => [
                    'Insumo', $i->nombre, 'N/A', 'N/A', $i->cantidad, str_replace('.', ',', number_format($i->precio, 0, '.', '')), $i->estadoEquipo->nombre ?? '-', $i->usuarioAsignado?->usuario->nombre ?? 'Sin asignar', $i->sucursal->nombre ?? '-',
                ]));
                break;
        }

        return $rows;
    }

    /**
    * Define los encabezados de la tabla.
    * @return array
    */
    public function headings(): array
    {
        $tipoReporte = $this->datos['tipoReporte'];
        
        switch ($tipoReporte) {
            case 'asignaciones':
                return ['Usuario', 'Item Asignado', 'Tipo de Item', 'Fecha Asignación', 'Fecha Fin', 'Motivo'];
            case 'estadisticas':
                return ['Estadística', 'Valor'];
            default:
                return ['Categoría', 'Tipo/Nombre', 'Marca', 'Modelo', 'N° Serie / Cantidad', 'Precio', 'Estado', 'Usuario Asignado', 'Sucursal'];
        }
    }
}
