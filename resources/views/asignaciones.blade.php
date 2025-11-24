@extends('layouts.base')

@section('titulo', 'Asignaciones - Sistema Inventario TI')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
    <h1 class="h2">Asignaciones de Equipos e Insumos</h1>
</div>

<!-- Filtros -->
<div class="card shadow mb-4">
    <div class="card-header bg-orange text-white">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('asignaciones.index') }}" id="formFiltros" class="row g-3">

            <!-- Categoría -->
            <div class="col-md-2">
                <label class="form-label">Categoría</label>
                <select name="categoria" id="categoria" class="form-select">
                    <option value="">Todas</option>
                    <option value="equipo" {{ request('categoria') === 'equipo' ? 'selected' : '' }}>Equipos</option>
                    <option value="insumo" {{ request('categoria') === 'insumo' ? 'selected' : '' }}>Insumos</option>
                </select>
            </div>

            <!-- Sucursal -->
            <div class="col-md-2">
                <label class="form-label">Sucursal</label>
                <select name="sucursal_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Estado -->
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>
                            {{ $estado->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tipo con autocompletado -->
            <div class="col-md-3" id="grupoTipo">
                <label class="form-label">Tipo de equipo</label>
                <input
                    type="text"
                    class="form-control"
                    id="tipo_autocomplete"
                    name="tipo_nombre"
                    value="{{ request('tipo_nombre', '') }}"
                    placeholder="Escribe para buscar tipo (ej: Notebook, Desktop)">
                <input type="hidden" id="tipo_id" name="tipo_id" value="{{ request('tipo_id') }}">
            </div>

            <!-- Proveedor -->
            <div class="col-md-3">
                <label class="form-label">Proveedor</label>
                <select name="proveedor_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}" {{ request('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                            {{ $proveedor->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Fechas -->
            <div class="col-md-2">
                <label class="form-label">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>

            <!-- Usuario asignado -->
            <div class="col-md-3">
                <label class="form-label">Usuario asignado</label>
                <select name="usuario_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                            {{ $usuario->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de equipos e insumos -->
<form method="POST" action="{{ route('asignaciones.store') }}">
    @csrf
    <div class="card shadow">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-laptop me-2"></i>Listado de Equipos e Insumos</h5>
            <div class="d-flex">
                <select id="tipoAsignacion" name="tipo_asignacion" class="form-select me-2 w-auto">
                    <option value="usuario">Asignar a Usuario</option>
                    <option value="sucursal">Asignar a Sucursal</option>
                </select>
                <select id="selectorAsignacion" name="destino_id" class="form-select w-auto"></select>
                <button type="submit" class="btn btn-success ms-2">
                    <i class="fas fa-check me-2"></i>Asignar Seleccionados
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th>Categoría</th>
                        <th>Tipo</th>
                        <th>Marca/Nombre</th>
                        <th>Modelo</th>
                        <th>Estado</th>
                        <th>Sucursal</th>
                        <th>Usuario Asignado</th>
                        <th>Fecha Registro</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        @php
                            $esEquipo = $item instanceof App\Models\Equipo;
                            $categoriaLabel = $esEquipo ? 'Equipo' : 'Insumo';
                            $tipoNombre = $esEquipo ? ($item->tipoEquipo->nombre ?? '-') : '-';
                        @endphp
                        <tr>
                            <td><input type="checkbox" name="items[]" value="{{ $item->id }}"></td>
                            <td>{{ $item->id }}</td>
                            <td>{{ $categoriaLabel }}</td>
                            <td>{{ $tipoNombre }}</td>
                            <td>{{ $item->marca ?? $item->nombre }}</td>
                            <td>{{ $item->modelo ?? '-' }}</td>
                            <td>{{ $item->estadoEquipo->nombre ?? '-' }}</td>
                            <td>{{ $item->sucursal->nombre ?? '-' }}</td>
                            <td>{{ $item->usuarioAsignado?->usuario?->nombre ?? 'Sin asignar' }}</td>
                            <td>{{ $item->fecha_registro ? \Carbon\Carbon::parse($item->fecha_registro)->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
    // ✅ Envío automático de filtros
    document.querySelectorAll('#formFiltros select, #formFiltros input').forEach(el => {
        el.addEventListener('change', () => {
            document.getElementById('formFiltros').submit();
        });
    });

    // ✅ Autocompletado de tipo
    const $input = $('#tipo_autocomplete');
    if ($input.length && typeof $.ui !== 'undefined') {
        $input.autocomplete({
            minLength: 2,
            delay: 150,
            source: function (request, response) {
                $.ajax({
                    url: "{{ route('asignaciones.autocomplete') }}",
                    data: { q: request.term },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            select: function (event, ui) {
                $input.val(ui.item.label);
                $('#tipo_id').val(ui.item.value);
                $('#formFiltros').submit();
            }
        });
    }

    // ✅ Mostrar/ocultar campo Tipo según categoría
    const categoriaSelect = document.getElementById('categoria');
    const grupoTipo = document.getElementById('grupoTipo');
    function toggleTipo() {
        if (categoriaSelect.value === 'equipo') {
            grupoTipo.style.display = 'block';
        } else {
            grupoTipo.style.display = 'none';
            $input.val('');
            $('#tipo_id').val('');
        }
    }
    toggleTipo();
    categoriaSelect.addEventListener('change', toggleTipo);

    // ✅ Selector dinámico de asignación
    const tipoAsignacion = document.getElementById('tipoAsignacion');
    const selectorAsignacion = document.getElementById('selectorAsignacion');
    const usuarios = @json($usuarios);
    const sucursales = @json($sucursales);

    function cargarOpciones(tipo) {
        selectorAsignacion.innerHTML = '';
        let opciones = tipo === 'usuario' ? usuarios : sucursales;
        opciones.forEach(op => {
            let opt = document.createElement('option');
            opt.value = op.id;
            opt.textContent = op.nombre;
            selectorAsignacion.appendChild(opt);
        });
    }

    cargarOpciones('usuario');
    tipoAsignacion.addEventListener('change', e => {
        cargarOpciones(e.target.value);
    });

    // ✅ SelectAll
    document.getElementById('selectAll')?.addEventListener('change', function(e) {
        document.querySelectorAll('input[name="items[]"]').forEach(cb => cb.checked = e.target.checked);
    });
</script>
@endpush

@endsection