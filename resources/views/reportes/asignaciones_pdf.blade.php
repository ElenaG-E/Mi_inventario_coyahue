<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    {{-- CORRECCIÓN 1: Usar $metadata['titulo'] --}}
    <title>{{ $metadata['titulo'] ?? 'Reporte de Asignaciones' }}</title> 
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        h1 { font-size: 16pt; color: #343a40; border-bottom: 2px solid #ff6b35; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .badge-activo { color: green; font-weight: bold; }
    </style>
</head>
<body>
    {{-- CORRECCIÓN 2: Usar $metadata['titulo'] --}}
    <h1>{{ $metadata['titulo'] ?? 'Reporte de Asignaciones' }}</h1>
    <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>

    {{-- CORRECCIÓN 3: Usar $equipos e $insumos --}}
    @if ($equipos->isEmpty() && $insumos->isEmpty())
        <p>No se encontraron asignaciones activas con los filtros aplicados.</p>
    @else
        <h2>Historial de Asignaciones (Equipos e Insumos)</h2>

        <table>
            <thead>
                <tr>
                    <th>Usuario Asignado</th>
                    <th>Item</th>
                    <th>Tipo</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                {{-- Procesar Asignaciones de Equipos --}}
                @foreach ($equipos as $equipo)
                    @foreach ($equipo->asignaciones as $asignacion)
                    <tr>
                        <td>{{ $asignacion->usuario->nombre ?? 'USUARIO NO ENCONTRADO' }}</td>
                        <td>{{ $equipo->marca }} {{ $equipo->modelo }} (S/N: {{ $equipo->numero_serie }})</td>
                        <td>Equipo</td>
                        <td>{{ $asignacion->fecha_asignacion->format('d/m/Y H:i') }}</td>
                        <td class="{{ $asignacion->fecha_fin ? '' : 'badge-activo' }}">
                            {{ $asignacion->fecha_fin ? $asignacion->fecha_fin->format('d/m/Y H:i') : 'ACTIVA' }}
                        </td>
                        <td>{{ $asignacion->motivo }}</td>
                    </tr>
                    @endforeach
                @endforeach
                
                {{-- Procesar Asignaciones de Insumos --}}
                @foreach ($insumos as $insumo)
                     @foreach ($insumo->asignaciones as $asignacion)
                    <tr>
                        <td>{{ $asignacion->usuario->nombre ?? 'USUARIO NO ENCONTRADO' }}</td>
                        <td>{{ $insumo->nombre }} (Cant: {{ $asignacion->cantidad }})</td>
                        <td>Insumo</td>
                        <td>{{ $asignacion->fecha_asignacion->format('d/m/Y H:i') }}</td>
                        <td class="{{ $asignacion->fecha_fin ? '' : 'badge-activo' }}">
                            {{ $asignacion->fecha_fin ? $asignacion->fecha_fin->format('d/m/Y H:i') : 'ACTIVA' }}
                        </td>
                        <td>{{ $asignacion->motivo }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
