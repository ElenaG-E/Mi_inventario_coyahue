@extends('layouts.app')

@section('contenido')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalle del Equipo - EQ{{ $equipo->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        {{-- FIX: Se usa 'inventario.index' para la ruta principal --}}
        <a href="{{ route('inventario.index') }}" class="btn btn-sm btn-outline-success me-2">
            <i class="fas fa-arrow-left me-1"></i>Volver al Inventario
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">

        <div class="card shadow mb-4">
            <div class="card-header text-white bg-warning">
                <h5 class="card-title mb-0">
                    <i class="fas fa-desktop me-2"></i>{{ $equipo->marca }} {{ $equipo->modelo }}
                </h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tipo:</strong> {{ $equipo->tipoEquipo->nombre ?? 'N/A' }}</p>
                        <p><strong>Marca:</strong> {{ $equipo->marca }}</p>
                        <p><strong>Modelo:</strong> {{ $equipo->modelo }}</p>
                        <p><strong>N° Serie:</strong> {{ $equipo->numero_serie }}</p>
                        <p><strong>Precio:</strong> {{ number_format($equipo->precio, 0, ',', '.') }} CLP</p>
                        <p><strong>Fecha de Compra:</strong> {{ \Carbon\Carbon::parse($equipo->fecha_compra)->format('d/m/Y') }}</p>
                    </div>

                    <div class="col-md-6">
                        <p><strong>Estado:</strong>
                            <span class="badge bg-{{ match($equipo->estadoEquipo->nombre) {
                                'Disponible' => 'success',
                                'Asignado' => 'primary',
                                'Mantención' => 'warning',
                                'Baja' => 'danger',
                                'En tránsito' => 'secondary',
                                default => 'info',
                            } }}">{{ $equipo->estadoEquipo->nombre }}</span>
                        </p>

                        <p><strong>Proveedor:</strong> {{ $equipo->proveedor->nombre ?? 'N/A' }}</p>

                        <p><strong>Usuario Asignado:</strong>
                            {{ $equipo->usuarioAsignado?->usuario?->nombre ?? 'Sin asignar' }}
                        </p>

                        <p><strong>Sucursal:</strong> {{ $equipo->sucursal->nombre ?? '-' }}</p>

                        <p><strong>Fecha de Registro:</strong>
                            {{ \Carbon\Carbon::parse($equipo->fecha_registro)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if($equipo->especificacionesTecnicas)
        <div class="card shadow mb-4">
            <div class="card-header text-white bg-dark">
                <h5 class="card-title mb-0"><i class="fas fa-cogs me-2"></i>Especificaciones Técnicas</h5>
            </div>

            <div class="card-body small">
                {{-- Resumen IA --}}
                @if($equipo->especificacionesTecnicas->resumen_ia)
                    <p class="mb-2">
                        <strong>Resumen IA/Clave:</strong> 
                        <em class="text-info">{{ $equipo->especificacionesTecnicas->resumen_ia }}</em>
                    </p>
                    <hr>
                @endif

                {{-- Campos --}}
                <div class="row">
                    @if($equipo->especificacionesTecnicas->procesador)
                        <div class="col-md-4"><strong>Procesador:</strong> {{ $equipo->especificacionesTecnicas->procesador }}</div>
                        <div class="col-md-4"><strong>RAM:</strong> {{ $equipo->especificacionesTecnicas->ram_gb }} GB</div>
                        <div class="col-md-4"><strong>Almacenamiento:</strong> {{ $equipo->especificacionesTecnicas->almacenamiento_gb }} GB ({{ $equipo->especificacionesTecnicas->tipo_almacenamiento }})</div>
                    @endif

                    @if($equipo->especificacionesTecnicas->pantalla_pulgadas)
                        <div class="col-md-4 mt-2"><strong>Pantalla:</strong> {{ $equipo->especificacionesTecnicas->pantalla_pulgadas }}"</div>
                        <div class="col-md-4 mt-2"><strong>Resolución:</strong> {{ $equipo->especificacionesTecnicas->resolucion_pantalla }}</div>
                        <div class="col-md-4 mt-2"><strong>Panel:</strong> {{ $equipo->especificacionesTecnicas->tipo_panel }}</div>
                    @endif

                    @if($equipo->especificacionesTecnicas->otros_datos)
                        <div class="col-12 mt-3">
                            <strong>Otros Detalles:</strong> {{ $equipo->especificacionesTecnicas->otros_datos }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header text-white bg-secondary">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-clock me-2"></i>Historial de Usuarios Asignados
                </h5>
            </div>

            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <ul class="list-group">
                    @forelse($equipo->asignaciones->sortByDesc('fecha_asignacion') as $asignacion)
                        <li class="list-group-item">
                            <strong>{{ $asignacion->usuario->nombre ?? 'Usuario desconocido' }}</strong><br>
                            <small class="text-muted">
                                Desde {{ $asignacion->fecha_asignacion->format('d/m/Y') }}
                                @if($asignacion->fecha_fin)
                                    hasta {{ $asignacion->fecha_fin->format('d/m/Y') }}
                                @else
                                    (actual)
                                @endif
                            </small>
                            @if($asignacion->motivo)
                                <p class="mb-0 mt-1"><em>{{ $asignacion->motivo }}</em></p>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No hay asignaciones registradas.</li>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>

    <div class="col-lg-4">

        {{-- ================= QR ================= --}}
        <div class="card shadow">
            <div class="card-header text-white bg-warning">
                <h5 class="card-title mb-0"><i class="fas fa-qrcode me-2"></i>Código QR</h5>
            </div>

            <div class="card-body text-center">

                @php
                    $qrUrl = route('inventario.equipo', $equipo->id);

                    try {
                        $qrCode = QrCode::format('png')
                            ->size(250)
                            ->margin(2)
                            ->errorCorrection('H')
                            ->generate($qrUrl); 
                        
                        $qrBase64 = 'data:image/png;base64,' . base64_encode($qrCode);
                    } catch (\Exception $e) {
                        $qrBase64 = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrUrl); 
                    }

                    $modeloSlug = \Illuminate\Support\Str::slug($equipo->modelo);
                @endphp

                <p class="text-muted small">Escanea para ver la información del equipo</p>

                <img id="qrImageDetail"
                    src="{{ $qrBase64 }}"
                    alt="QR {{ $equipo->modelo }}"
                    class="img-fluid mx-auto d-block mb-3"
                    style="max-width: 250px;">

                <div class="d-grid gap-2">
                    <button onclick="imprimirQR()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Imprimir QR
                    </button>
                    <button onclick="descargarQR('QR_EQ{{ $equipo->id }}_{{ $modeloSlug }}.png')" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> Descargar QR
                    </button>
                </div>
            </div>
        </div>

        {{-- ================= ACCIONES ================= --}}
        <div class="card shadow mt-4">
            <div class="card-header text-white bg-warning">
                <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
            </div>

            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalGestionEquipo">
                        <i class="fas fa-edit me-2"></i>Editar / Asignar
                    </button>

                    {{-- Conectar el botón a la ruta de reporte individual --}}
                    <a href="{{ route('equipo.reporte', $equipo->id) }}" target="_blank" class="btn btn-outline-info">
                        <i class="fas fa-file-pdf me-2"></i>Generar Reporte
                    </a>

                    {{-- DAR DE BAJA --}}
                    <form method="POST"
                        action="{{ route('equipo.update', $equipo->id) }}"
                        onsubmit="return confirm('¿Dar de baja este equipo? Esto cambiará su estado a "Baja".');">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="categoria" value="equipo">
                        <input type="hidden" name="estado_equipo_id" value="4"> 
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Dar de Baja
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL --}}
@include('partials.modal-gestion-equipo', [
    'equipo' => $equipo,
    'estados' => $estados,
    'usuarios' => $usuarios,
    'sucursales' => $sucursales,
    'proveedores' => $proveedores
])
@endsection

@push('scripts')
<script>
// Variables PHP escapadas de forma segura para JavaScript (FIX SyntaxError)
const EQUIPO_TITULO = {{ json_encode($equipo->marca . ' ' . $equipo->modelo) }};
const EQUIPO_SERIE = {{ json_encode($equipo->numero_serie) }};
const EQUIPO_ID = {{ json_encode($equipo->id) }};

function imprimirQR() {
    const qrImage = document.getElementById('qrImageDetail');
    if (!qrImage) return;
    
    // Abrir una nueva ventana temporal
    const ventana = window.open('', '', 'width=400,height=500');
    
    // FIX: Se utiliza slice(1, -1) para quitar las comillas dobles que json_encode() añade.
    // ESTE ES EL FORMATO FINAL JSON-SAFE + CONCATENACIÓN
    ventana.document.write(
        '<html>' +
        '<head>' +
            '<title>Imprimir QR - EQ' + EQUIPO_ID.slice(1, -1) + '</title>' +
            '<style>' +
                'body { text-align: center; font-family: Arial; padding: 20px; }' +
                'img { max-width: 100%; margin: 20px 0; }' +
            '</style>' +
        '</head>' +
        '<body>' +
            '<h3>' + EQUIPO_TITULO.slice(1, -1) + '</h3>' +
            '<p>N° Serie: ' + EQUIPO_SERIE.slice(1, -1) + '</p>' +
            '<img src="' + qrImage.src + '">' +
            '<p>ID: EQ' + EQUIPO_ID.slice(1, -1) + '</p>' +
            '<script>window.onload = function(){ window.print(); setTimeout(()=>window.close(),500); }<\/script>' +
        '</body>' +
        '</html>'
    );
    ventana.document.close();
}

function descargarQR(filename) {
    const qrImage = document.getElementById('qrImageDetail');
    if (!qrImage) return;
    const link = document.createElement('a');
    link.download = filename;
    
    // Lógica para manejar la descarga de imágenes Base64/URL
    if (qrImage.src.startsWith('data:image')) {
        fetch(qrImage.src)
            .then(res => res.blob())
            .then(blob => {
                const objectURL = URL.createObjectURL(blob);
                link.href = objectURL;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(objectURL);
            });
    } else {
        // Manejar URL externa (fallback)
        link.href = qrImage.src;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
</script>
@endpush
