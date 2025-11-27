<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    {{-- Usamos la metadata pasada por el controlador para el título --}}
    <title>{{ $metadata['titulo'] ?? 'Reporte de Equipos' }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        h1 { font-size: 16pt; color: #343a40; border-bottom: 2px solid #ff6b35; padding-bottom: 5px; }
        h3 { font-size: 12pt; color: #0d6efd; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>{{ $metadata['titulo'] ?? 'Reporte de Equipos' }}</h1>
    <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>

    @if ($equipos->isEmpty())
        <p>No se encontraron equipos para generar este reporte con los filtros aplicados.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>N° Serie</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Usuario Asignado</th>
                    <th>{{ $fechaTipo == 'compra' ? 'Fecha Compra' : 'Fecha Registro' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipos as $equipo)
                    <tr>
                        <td>{{ $equipo->tipoEquipo->nombre ?? '-' }}</td>
                        <td>{{ $equipo->marca }}</td>
                        <td>{{ $equipo->modelo }}</td>
                        <td>{{ $equipo->numero_serie }}</td>
                        <td>{{ number_format($equipo->precio, 0, ',', '.') }} CLP</td>
                        <td>{{ $equipo->estadoEquipo->nombre ?? '-' }}</td>
                        <td>{{ $equipo->usuarioAsignado?->usuario->nombre ?? 'Sin asignar' }}</td>
                        <td>
                            @php
                                $fecha = $fechaTipo == 'compra' ? $equipo->fecha_compra : $equipo->fecha_registro;
                            @endphp
                            {{ $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
