@extends('layouts.base')

@section('contenido')
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Perfil de Usuario - US{{ $usuario->id }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('gestion_usuarios') }}" class="btn btn-sm btn-outline-success me-2">
                <i class="fas fa-arrow-left me-1"></i>Volver a Gestión de Usuarios
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header text-white bg-primary">
                    <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $usuario->nombre }}</p>
                            <p><strong>Email:</strong> {{ $usuario->email }}</p>
                            <p><strong>Teléfono:</strong> {{ $usuario->telefono ?? 'No registrado' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Rol:</strong> <span
                                    class="badge bg-info">{{ $usuario->rol->nombre ?? 'Sin rol' }}</span></p>
                            <p><strong>Estado:</strong>
                                <span class="badge {{ $usuario->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($usuario->estado) }}
                            </span>
                            </p>
                            <p><strong>Email verificado:</strong>
                                {{ $usuario->email_verified_at ? $usuario->email_verified_at->format('d/m/Y H:i') : 'Pendiente' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header text-white bg-primary">
                    <h5 class="card-title mb-0"><i class="fas fa-laptop-house me-2"></i>Equipos actualmente asignados
                    </h5>
                </div>
                <div class="card-body">
                    @if($equiposAsignados->isEmpty())
                        <p class="text-muted mb-0">No hay equipos asignados actualmente.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Equipo</th>
                                    <th>Estado</th>
                                    <th>Sucursal</th>
                                    <th>Asignado desde</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($equiposAsignados as $asignacion)
                                    <tr>
                                        <td>
                                            <a href="{{ route('inventario.equipo', $asignacion->equipo->id) }}"
                                               class="text-decoration-none">
                                                EQ{{ $asignacion->equipo->id }}
                                            </a>
                                        </td>
                                        <td>{{ $asignacion->equipo->marca }} {{ $asignacion->equipo->modelo }}</td>
                                        <td>
                                            <span class="badge bg-{{ match($asignacion->equipo->estadoEquipo->nombre) {
                                                'Disponible' => 'success',
                                                'Asignado' => 'primary',
                                                'Mantención' => 'warning',
                                                'Baja' => 'danger',
                                                'En tránsito' => 'secondary',
                                                default => 'info',
                                            } }}">
                                                {{ $asignacion->equipo->estadoEquipo->nombre }}
                                            </span>
                                        </td>
                                        <td>{{ $asignacion->equipo->sucursal->nombre ?? '-' }}</td>
                                        <td>{{ $asignacion->fecha_asignacion?->format('d/m/Y') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header text-white bg-secondary">
                    <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Historial de asignaciones</h5>
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    @if($historialAsignaciones->isEmpty())
                        <p class="text-muted mb-0">No hay historial registrado.</p>
                    @else
                        <ul class="list-group">
                            @foreach($historialAsignaciones as $asignacion)
                                <li class="list-group-item">
                                    <strong>{{ $asignacion->equipo->marca }} {{ $asignacion->equipo->modelo }}
                                        (EQ{{ $asignacion->equipo->id }})</strong><br>
                                    <small class="text-muted">
                                        Desde {{ $asignacion->fecha_asignacion?->format('d/m/Y') ?? 'N/A' }}
                                        @if($asignacion->fecha_fin)
                                            hasta {{ $asignacion->fecha_fin->format('d/m/Y') }}
                                        @else
                                            (vigente)
                                        @endif
                                    </small>
                                    @if($asignacion->motivo)
                                        <p class="mb-0 mt-1"><em>{{ $asignacion->motivo }}</em></p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header text-white bg-primary">
                    <h5 class="card-title mb-0"><i class="fas fa-tools me-2"></i>Accesos rápidos</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('gestion_usuarios') }}#usuario-{{ $usuario->id }}"
                           class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Editar usuario
                        </a>
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="fas fa-paper-plane me-1"></i>Enviar credenciales
                        </button>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header text-white bg-primary">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Notas</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        Aquí podrás ver un resumen del usuario y los equipos que tiene asignados. Cualquier cambio debe
                        realizarse desde la sección de Gestión de Usuarios o mediante los formularios de asignación de
                        equipos.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

