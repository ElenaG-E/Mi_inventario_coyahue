@extends('layouts.base')

@section('contenido')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Usuarios</h1>
    <div class="btn-toolbar">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            <i class="fas fa-plus me-1"></i>Agregar Usuario
        </button>
    </div>
</div>

<!-- Buscador -->
<div class="mb-3">
    <form method="GET" action="{{ route('gestion_usuarios') }}" class="d-flex">
        <input id="buscadorUsuarios" type="text" name="search" class="form-control me-2"
               placeholder="Buscar por nombre, email, teléfono, rol o estado..."
               value="{{ request('search') }}">
        <button class="btn btn-outline-primary">Buscar</button>
    </form>
</div>

<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Lista de Usuarios</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->nombre }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->telefono }}</td>
                        <td><span class="badge bg-info">{{ $usuario->rol->nombre ?? 'Sin rol' }}</span></td>
                        <td>
                            <span class="badge {{ $usuario->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($usuario->estado) }}
                            </span>
                        </td>
                        <td>
                            <!-- Botón Editar -->
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarUsuario{{ $usuario->id }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- Botón Eliminar (abre modal en partials) -->
                            <button class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmarEliminar{{ $usuario->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $usuarios->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $("#buscadorUsuarios").autocomplete({
        source: "{{ route('usuarios.autocomplete') }}",
        minLength: 2
    });
});
</script>
@endpush

@include('partials.modal-agregar-usuario')
@include('partials.modal-editar-usuario')
@include('partials.modal-confirmar-eliminar') 