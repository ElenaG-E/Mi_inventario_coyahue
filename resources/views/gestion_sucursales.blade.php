@extends('layouts.base')

@section('contenido')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Sucursales</h1>
    <div class="btn-toolbar">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalSucursal">
            <i class="fas fa-plus me-1"></i>Agregar Sucursal
        </button>
    </div>
</div>

<!-- Buscador -->
<div class="mb-3">
    <form method="GET" action="{{ route('gestion_sucursales') }}" class="d-flex">
        <input id="buscadorSucursales" type="text" name="search" class="form-control me-2"
               placeholder="Buscar por nombre, dirección o teléfono..."
               value="{{ request('search') }}">
        <button class="btn btn-outline-primary">Buscar</button>
    </form>
</div>

<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Lista de Sucursales</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sucursales as $sucursal)
                    <tr>
                        <td>{{ $sucursal->nombre }}</td>
                        <td>{{ $sucursal->direccion }}</td>
                        <td>{{ $sucursal->telefono }}</td>
                        <td>
                            <!-- Botón Editar -->
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarSucursal{{ $sucursal->id }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- Botón Eliminar -->
                            <button class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmarEliminarSucursal{{ $sucursal->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $sucursales->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $("#buscadorSucursales").autocomplete({
        source: "{{ route('sucursales.autocomplete') }}",
        minLength: 2
    });
});
</script>
@endpush

@include('partials.modal-agregar-sucursal')
@include('partials.modal-editar-sucursal')
@include('partials.modal-confirmar-eliminar-sucursal')