<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Equipos en Mantención</title>
    <style>
        /* Estilos básicos para el PDF */
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 20px; }
        h1 { text-align: center; color: #dc8428; font-size: 16px; border-bottom: 2px solid #dc8428; padding-bottom: 5px; }
        .metadata { margin-bottom: 15px; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total { text-align: right; margin-top: 15px; font-weight: bold; }
        .status-mantencion { background-color: #ffc10740; } /* Resaltar filas de mantención */
        .empty-state { text-align: center; color: #666; padding: 20px; }
    </style>
</head>
<body>

    <h1>{{ $metadata['titulo'] ?? 'Reporte de Equipos en Mantención' }}</h1>

    <div class="metadata">
        Generado el: {{ now()->format('d/m/Y H:i:s') }}
        @if ($equipos->isNotEmpty())
            <br>Total de equipos: {{ $equipos->count() }}
        @endif
    </div>

    @php
        // Filtramos la colección de equipos para mostrar solo los que están en Mantención
        $equiposEnMantencion = $equipos->filter(function($equipo) {
            // Asume que la relación estadoEquipo está cargada y el nombre es 'Mantención'
            return optional($equipo->estadoEquipo)->nombre === 'Mantención';
        });
    @endphp

    @if ($equiposEnMantencion->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Marca y Modelo</th>
                    <th>N° Serie</th>
                    <th>Precio</th>
                    <th>Usuario Asignado</th>
                    <th>Sucursal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equiposEnMantencion as $equipo)
                    <tr class="status-mantencion">
                        <td>{{ $equipo->id }}</td>
                        <td>{{ optional($equipo->tipoEquipo)->nombre ?? 'N/A' }}</td>
                        <td>{{ $equipo->marca }} {{ $equipo->modelo }}</td>
                        <td>{{ $equipo->numero_serie }}</td>
                        <td>${{ number_format($equipo->precio, 0, ',', '.') }}</td>
                        <td>{{ optional(optional($equipo->usuarioAsignado)->usuario)->nombre ?? 'Sin asignar' }}</td>
                        <td>{{ optional($equipo->sucursal)->nombre ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="total">
            Equipos en Mantención: {{ $equiposEnMantencion->count() }}
        </div>
    @else
        <div class="empty-state">No hay equipos actualmente registrados con estado "Mantención" para los filtros aplicados.</div>
    @endif

</body>
</html>
