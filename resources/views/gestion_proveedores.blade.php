@extends('layouts.base')

@section('contenido')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Proveedores</h1>
    <div class="btn-toolbar">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalProveedor">
            <i class="fas fa-plus me-1"></i>Agregar Proveedor
        </button>
    </div>
</div>

<!-- Buscador -->
<div class="mb-3">
    <form method="GET" action="{{ route('gestion_proveedores') }}" class="d-flex">
        <input id="buscador" type="text" name="search" class="form-control me-2"
               placeholder="Buscar por RUT, nombre, teléfono o dirección..."
               value="{{ request('search') }}">
        <button class="btn btn-outline-primary">Buscar</button>
    </form>
</div>

<div class="card shadow">
    <div class="card-header bg-orange text-white">
        <h5 class="card-title mb-0">Lista de Proveedores</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proveedores as $proveedor)
                    <tr>
                        <td>{{ $proveedor->rut }}</td>
                        <td>{{ $proveedor->nombre }}</td>
                        <td>{{ $proveedor->telefono }}</td>
                        <td>{{ $proveedor->correo }}</td>
                        <td>{{ $proveedor->direccion }}</td>
                        <td>
                            <!-- Botón Editar -->
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar{{ $proveedor->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <!-- Botón Eliminar -->
                            <form action="{{ route('proveedores.destroy', $proveedor) }}"
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $proveedores->links() }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {
    $("#buscador").autocomplete({
        source: "{{ route('proveedores.autocomplete') }}",
        minLength: 2
    });
});
</script>
@endpush

<!-- Inclusión de modales desde parciales -->
@include('partials.modal-agregar-proveedor')
@include('partials.modal-editar-proveedor')
