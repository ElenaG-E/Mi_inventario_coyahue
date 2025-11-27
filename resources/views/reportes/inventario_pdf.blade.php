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
    </style>
</head>
<body>
    <h1>Reporte de Inventario Coyahue</h1>
    <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>

    <h2>Equipos</h2>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Precio</th>
                <th>Estado</th>
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
                </tr>
            @endforeach
        </tbody>
    </table>
    
    {{-- Agrega la sección de insumos si es necesario --}}

</body>
</html>
