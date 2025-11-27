<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Individual - EQ{{ $equipo->id }}</title>
    {{-- Estilos CSS para el PDF (Dompdf requiere CSS inline o simple) --}}
    <style>
        body { font-family: sans-serif; font-size: 10pt; padding: 20px; }
        h1 { font-size: 18pt; color: #343a40; border-bottom: 3px solid #ff6b35; padding-bottom: 8px; margin-bottom: 20px; }
        h2 { font-size: 12pt; color: #0d6efd; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .data-table th, .data-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .data-table th { background-color: #f2f2f2; width: 30%; font-weight: bold; }
        .qr-section { text-align: center; width: 30%; float: right; margin-left: 20px; }
        .qr-section img { max-width: 150px; height: auto; }
    </style>
</head>
<body>
    <h1>Reporte de Equipo Individual: EQ{{ $equipo->id }}</h1>
    <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>

    {{-- Sección QR (Se asume que la URL del QR es generada por el controlador si usas Dompdf) --}}
    <div class="qr-section">
        <h2>Código QR</h2>
        <img src="{{ route('inventario.equipo', $equipo->id) }}" alt="Código QR de Equipo">
        <p style="font-size: 9pt; margin-top: 5px;">Escanea para ver el detalle online.</p>
    </div>

    <h2>Información General</h2>
    <table class="data-table">
        <tbody>
            <tr><th>Tipo</th><td>{{ $equipo->tipoEquipo->nombre ?? 'N/A' }}</td></tr>
            <tr><th>Marca y Modelo</th><td>{{ $equipo->marca }} {{ $equipo->modelo }}</td></tr>
            <tr><th>N° Serie</th><td>{{ $equipo->numero_serie }}</td></tr>
            <tr><th>Estado Actual</th><td>{{ $equipo->estadoEquipo->nombre ?? '-' }}</td></tr>
            <tr><th>Precio</th><td>{{ number_format($equipo->precio, 0, ',', '.') }} CLP</td></tr>
            <tr><th>Sucursal</th><td>{{ $equipo->sucursal->nombre ?? '-' }}</td></tr>
        </tbody>
    </table>

    @if($equipo->especificacionesTecnicas)
        <h2>Especificaciones Técnicas</h2>
        <table class="data-table">
            <tbody>
                @if($equipo->especificacionesTecnicas->procesador)
                    <tr><th>Procesador</th><td>{{ $equipo->especificacionesTecnicas->procesador }}</td></tr>
                    <tr><th>RAM</th><td>{{ $equipo->especificacionesTecnicas->ram_gb }} GB</td></tr>
                    <tr><th>Almacenamiento</th><td>{{ $equipo->especificacionesTecnicas->almacenamiento_gb }} GB ({{ $equipo->especificacionesTecnicas->tipo_almacenamiento }})</td></tr>
                @endif
                @if($equipo->especificacionesTecnicas->otros_datos)
                    <tr><th>Otros Detalles</th><td>{{ $equipo->especificacionesTecnicas->otros_datos }}</td></tr>
                @endif
            </tbody>
        </table>
    @endif

    @if($equipo->asignaciones->isNotEmpty())
        <h2>Asignación Actual</h2>
        @php
            $currentAssignment = $equipo->asignaciones->whereNull('fecha_fin')->first();
        @endphp
        @if($currentAssignment)
            <p>Este equipo está actualmente asignado a **{{ $currentAssignment->usuario->nombre ?? 'Usuario desconocido' }}** desde el {{ $currentAssignment->fecha_asignacion->format('d/m/Y') }}.</p>
            <p>Motivo: {{ $currentAssignment->motivo }}</p>
        @else
            <p>El equipo está actualmente disponible o la asignación más reciente ha finalizado.</p>
        @endif
    @endif

</body>
</html>
