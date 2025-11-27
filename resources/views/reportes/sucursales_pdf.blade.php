<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    {{-- Usamos la metadata pasada por el controlador para el título --}}
    <title>{{ $metadata['titulo'] ?? 'Reporte por Sucursales' }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        h1 { font-size: 16pt; color: #343a40; border-bottom: 2px solid #ff6b35; padding-bottom: 5px; }
        h2 { font-size: 14pt; color: #0d6efd; margin-top: 25px; margin-bottom: 10px; }
        h3 { font-size: 11pt; color: #555; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $metadata['titulo'] ?? 'Inventario por Sucursal' }}</h1>
    <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>

    @php
        // Combinar equipos e insumos en una sola colección para agrupar
        $inventory = $equipos->map(fn($e) => (object)[
            'category' => 'Equipo',
            'name' => $e->marca . ' ' . $e->modelo,
            'details' => 'S/N: ' . $e->numero_serie,
            'price' => $e->precio,
            'estado' => $e->estadoEquipo->nombre ?? '-',
            'user' => $e->usuarioAsignado?->usuario->nombre ?? 'Sin asignar',
            'sucursal_name' => $e->sucursal->nombre ?? 'Sin Sucursal Asignada',
            'date' => $e->fecha_registro,
        ])->merge($insumos->map(fn($i) => (object)[
            'category' => 'Insumo',
            'name' => $i->nombre,
            'details' => 'Cantidad: ' . $i->cantidad,
            'price' => $i->precio,
            'estado' => $i->estadoEquipo->nombre ?? '-',
            'user' => $i->usuarioAsignado?->usuario->nombre ?? 'Sin asignar',
            'sucursal_name' => $i->sucursal->nombre ?? 'Sin Sucursal Asignada',
            'date' => $i->fecha_registro,
        ]));

        // Agrupar la colección por el nombre de la sucursal
        $groupedBySucursal = $inventory->groupBy('sucursal_name');
    @endphp

    @if ($inventory->isEmpty())
        <p>No se encontraron equipos o insumos para generar este reporte con los filtros aplicados.</p>
    @else
        @foreach ($groupedBySucursal as $sucursalName => $items)
            <h2>{{ $sucursalName }}</h2>
            <h3>Total de Items en esta sucursal: {{ $items->count() }}</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Item</th>
                        <th>Detalles / Cant.</th>
                        <th>Estado</th>
                        <th>Usuario Asig.</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->category }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->details }}</td>
                            <td>{{ $item->estado }}</td>
                            <td>{{ $item->user }}</td>
                            <td>{{ number_format($item->price, 0, ',', '.') }} CLP</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
</body>
</html>
