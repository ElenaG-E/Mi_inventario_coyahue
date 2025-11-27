<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $metadata['titulo'] ?? 'Reporte de Estadísticas' }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        h1 { font-size: 16pt; color: #343a40; border-bottom: 2px solid #ff6b35; padding-bottom: 5px; }
        h2 { font-size: 12pt; color: #0d6efd; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary-box { border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>{{ $metadata['titulo'] ?? 'Reporte de Estadísticas Generales' }}</h1>
    <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>

    <div class="summary-box">
        <h3>Resumen Total</h3>
        <table>
            <tbody>
                <tr>
                    <td><strong>Total de Equipos Registrados:</strong></td>
                    <td>{{ $equipos->count() }}</td>
                </tr>
                <tr>
                    <td><strong>Total de Insumos Registrados:</strong></td>
                    <td>{{ $insumos->count() }}</td>
                </tr>
                <tr>
                    <td><strong>Valor Total de Equipos:</strong></td>
                    <td>{{ number_format($equipos->sum('precio'), 0, ',', '.') }} CLP</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ================================================================= --}}
    <h2>Estadísticas de Equipos por Estado</h2>

    @php
        $estadoCounts = $equipos->groupBy('estadoEquipo.nombre')->map->count();
    @endphp
    
    @if ($estadoCounts->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estadoCounts as $estadoNombre => $count)
                    @php
                        $porcentaje = ($count / $equipos->count()) * 100;
                    @endphp
                    <tr>
                        <td>{{ $estadoNombre }}</td>
                        <td>{{ $count }}</td>
                        <td>{{ number_format($porcentaje, 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No se encontraron equipos para el análisis estadístico.</p>
    @endif
    
    {{-- ================================================================= --}}
    <h2>Equipos por Sucursal (Top 10)</h2>

    @php
        $sucursalCounts = $equipos->groupBy('sucursal.nombre')->map->count()->sortDesc()->take(10);
    @endphp
    
    @if ($sucursalCounts->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Sucursal</th>
                    <th>Cantidad de Equipos</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sucursalCounts as $sucursalNombre => $count)
                    <tr>
                        <td>{{ $sucursalNombre ?: 'Sin Sucursal Asignada' }}</td>
                        <td>{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No se encontraron datos de sucursales.</p>
    @endif

</body>
</html>
