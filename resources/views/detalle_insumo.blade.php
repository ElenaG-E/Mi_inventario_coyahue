@extends('layouts.base')

@section('contenido')
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Detalle del Insumo - IN{{ $insumo->id }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('inventario.index') }}" class="btn btn-sm btn-outline-success me-2">
                <i class="fas fa-arrow-left me-1"></i>Volver al Inventario
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Información del Insumo -->
            <div class="card shadow mb-4">
                <div class="card-header text-white bg-success">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-box me-2"></i>{{ $insumo->nombre }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $insumo->nombre }}</p>
                            <p><strong>Cantidad:</strong> {{ $insumo->cantidad }}</p>
                            <p><strong>Precio Unitario:</strong> {{ number_format($insumo->precio, 0, ',', '.') }} CLP
                            </p>
                            <p><strong>Fecha de
                                    Compra:</strong> {{ \Carbon\Carbon::parse($insumo->fecha_compra)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estado:</strong>
                                <span class="badge bg-{{ match($insumo->estadoEquipo->nombre) {
                                'Disponible' => 'success',
                                'Asignado' => 'primary',
                                'Mantención' => 'warning',
                                'Baja' => 'danger',
                                'En tránsito' => 'secondary',
                                default => 'info',
                            } }}">
                                {{ $insumo->estadoEquipo->nombre }}
                            </span>
                            </p>
                            <p><strong>Proveedor:</strong> {{ $insumo->proveedor->nombre ?? 'N/A' }}</p>
                            <!-- ✅ Usuario asignado actual -->
                            <p><strong>Usuario
                                    Asignado:</strong> {{ $insumo->usuarioAsignado?->usuario?->nombre ?? 'Sin asignar' }}
                            </p>
                            <p><strong>Sucursal:</strong> {{ $insumo->sucursal->nombre ?? '-' }}</p>
                            <p><strong>Fecha de
                                    Registro:</strong> {{ \Carbon\Carbon::parse($insumo->fecha_registro)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Garantía -->
            @php
                $garantia = $insumo->documentos->where('tipo', 'garantia')->sortByDesc('tiempo_garantia_meses')->first();
            @endphp

            @if($garantia)
                <div class="card shadow mb-4">
                    <div class="card-header text-white bg-success">
                        <h5 class="card-title mb-0"><i class="fas fa-shield-alt me-2"></i>Garantía</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Duración:</strong> {{ $garantia->tiempo_garantia_meses }} meses</p>
                        <p><strong>Vence:</strong>
                            {{ formatearDuracionGarantia(\Carbon\Carbon::parse($insumo->fecha_compra), $garantia->tiempo_garantia_meses) }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Documentos Asociados -->
            @if($insumo->documentos->count())
                <div class="card shadow mb-4">
                    <div class="card-header text-white bg-dark">
                        <h5 class="card-title mb-0"><i class="fas fa-file-alt me-2"></i>Documentos Asociados</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($insumo->documentos as $doc)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-file me-2"></i>{{ $doc->nombre_archivo }}
                                        <small class="text-muted">({{ ucfirst($doc->tipo) }})</small>
                                    </div>
                                    <span
                                        class="badge bg-secondary">{{ \Carbon\Carbon::parse($doc->fecha_subida)->format('d/m/Y') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Historial de Movimientos -->
            <div class="card shadow mb-4">
                <div class="card-header text-white bg-success">
                    <h5 class="card-title mb-0"><i class="fas fa-route me-2"></i>Historial de Movimientos</h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <div class="timeline">
                        @forelse($insumo->movimientos as $mov)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="timeline-marker bg-primary rounded-circle me-3"
                                         style="width: 12px; height: 12px; margin-top: 5px;"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ $mov->tipo_movimiento }}</h6>
                                        <p class="text-muted mb-1">
                                            {{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y - h:i A') }}
                                        </p>
                                        @if($mov->comentario)
                                            <p class="mb-0"><em>{{ $mov->comentario }}</em></p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No hay movimientos registrados.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Historial de Usuarios Asignados -->
            <div class="card shadow mb-4">
                <div class="card-header text-white bg-secondary">
                    <h5 class="card-title mb-0"><i class="fas fa-user-clock me-2"></i>Historial de Usuarios Asignados
                    </h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <ul class="list-group">
                        @forelse($insumo->asignaciones->sortByDesc('fecha_asignacion') as $asignacion)
                            <li class="list-group-item">
                                <strong>{{ $asignacion->usuario->nombre ?? 'Usuario desconocido' }}</strong><br>
                                <small class="text-muted">
                                    Desde {{ \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y') }}
                                    @if($asignacion->fecha_fin)
                                        hasta {{ \Carbon\Carbon::parse($asignacion->fecha_fin)->format('d/m/Y') }}
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

        <!-- Acciones -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header text-white bg-success">
                    <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-success" data-bs-toggle="modal"
                                data-bs-target="#modalGestionInsumo">
                            <i class="fas fa-edit me-2"></i>Editar / Asignar
                        </button>
                        <button class="btn btn-outline-info">
                            <i class="fas fa-file-pdf me-2"></i>Generar Reporte
                        </button>
                        <form method="POST" action="{{ route('insumo.destroy', $insumo->id) }}"
                              onsubmit="return confirm('¿Estás seguro de que deseas eliminar este insumo?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-2"></i>Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('partials.modal-gestion-insumo', [
    'insumo' => $insumo,
    'estados' => $estados,
    'usuarios' => $usuarios,
    'sucursales' => $sucursales,
    'proveedores' => $proveedores
])
