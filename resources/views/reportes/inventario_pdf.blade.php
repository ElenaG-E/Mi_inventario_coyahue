<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario - {{ now()->format('Y-m-d') }}</title>
    {{-- Incluir estilos CSS para Dompdf --}}
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .insumo-header { background-color: #e6f7ff; } /* Color para diferenciar la tabla de insumos */
    </style>
</head>
<body>
    <h1>Reporte de Inventario Coyahue</h1>
    <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>

    {{-- ======================= SECCIÓN EQUIPOS ======================= --}}
    <h2>Equipos</h2>
    @if ($equipos->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Usuario Asig.</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipos as $equipo)
                    <tr>
                        <td>{{ $equipo->tipoEquipo->nombre ?? '-' }}</td>
                        <td>{{ $equipo->marca }}</td>
                        <td>{{ $equipo->modelo }}</td>
                        <td>{{ number_format($equipo->precio, 0, ',', '.') }} CLP</td>
                        <td>{{ $equipo->estadoEquipo->nombre ?? '-' }}</td>
                        <td>{{ $equipo->usuarioAsignado?->usuario->nombre ?? 'Sin asignar' }}</td>
                        <td>{{ \Carbon\Carbon::parse($equipo->fecha_registro)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No se encontraron equipos en el inventario con los filtros aplicados.</p>
    @endif

    {{-- ======================= SECCIÓN INSUMOS AGREGADA ======================= --}}
    <h2 style="margin-top: 30px;">Insumos</h2>
    @if ($insumos->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th class="insumo-header">Nombre</th>
                    <th class="insumo-header">Cantidad</th>
                    <th class="insumo-header">Precio Unitario</th>
                    <th class="insumo-header">Estado</th>
                    <th class="insumo-header">Sucursal</th>
                    <th class="insumo-header">Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($insumos as $insumo)
                    <tr>
                        <td>{{ $insumo->nombre }}</td>
                        <td>{{ $insumo->cantidad }}</td>
                        <td>{{ number_format($insumo->precio, 0, ',', '.') }} CLP</td>
                        <td>{{ $insumo->estadoEquipo->nombre ?? '-' }}</td>
                        <td>{{ $insumo->sucursal->nombre ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($insumo->fecha_registro)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No se encontraron insumos en el inventario con los filtros aplicados.</p>
    @endif

</body>
</html>
