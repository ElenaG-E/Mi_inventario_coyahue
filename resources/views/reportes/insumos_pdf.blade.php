<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    {{-- Usamos la metadata pasada por el controlador para el título --}}
    <title>{{ $metadata['titulo'] ?? 'Reporte de Insumos' }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        h1 { font-size: 16pt; color: #343a40; border-bottom: 2px solid #ff6b35; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>{{ $metadata['titulo'] ?? 'Reporte de Insumos' }}</h1>
    <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>

    @if ($insumos->isEmpty())
        <p>No se encontraron insumos para generar este reporte con los filtros aplicados.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Estado</th>
                    <th>Proveedor</th>
                    <th>Sucursal</th>
                    <th>{{ $fechaTipo == 'compra' ? 'Fecha Compra' : 'Fecha Registro' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($insumos as $insumo)
                    <tr>
                        <td>{{ $insumo->nombre }}</td>
                        <td>{{ $insumo->cantidad }}</td>
                        <td>{{ number_format($insumo->precio, 0, ',', '.') }} CLP</td>
                        <td>{{ $insumo->estadoEquipo->nombre ?? '-' }}</td>
                        <td>{{ $insumo->proveedor->nombre ?? 'N/A' }}</td>
                        <td>{{ $insumo->sucursal->nombre ?? 'N/A' }}</td>
                        <td>
                            @php
                                // Determina la fecha a mostrar
                                $fecha = $fechaTipo == 'compra' ? $insumo->fecha_compra : $insumo->fecha_registro;
                            @endphp
                            {{ $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <h3>Resumen de Insumos</h3>
        <p><strong>Total de Tipos de Insumos:</strong> {{ $insumos->pluck('nombre')->unique()->count() }}</p>
        <p><strong>Cantidad Total en Stock:</strong> {{ $insumos->sum('cantidad') }}</p>

    @endif
</body>
</html>
