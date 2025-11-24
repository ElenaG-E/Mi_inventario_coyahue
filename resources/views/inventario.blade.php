@extends('layouts.base')

@section('contenido')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Inventario y Asignaciones</h1>
<div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('registro_equipo.create') }}" class="btn btn-sm btn-primary me-2">
            <i class="fas fa-plus me-1"></i>Agregar nuevo
        </a>
        <button class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalReportes">
            <i class="fas fa-file-export me-1"></i>Generar Reporte
        </button>
        <button class="btn btn-sm btn-outline-dark" type="button" onclick="abrirScannerQR()">
            <i class="fas fa-qrcode me-1"></i>Escanear QR
        </button>
    </div>
</div>

<!-- Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrScannerModalLabel">Escáner QR</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="qr-reader" style="width: 100%;"></div>
        <div id="qr-reader-results" class="mt-3"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="detenerScanner()">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Barra de filtros -->
<div class="card shadow mb-4">
    <div class="card-header text-white bg-orange d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filtros de Inventario
        </h6>
        <a href="/inventario?categoria=" class="btn btn-sm btn-outline-light">
            <i class="fas fa-times me-1"></i>Limpiar filtros
        </a>
    </div>

    <div id="filtrosInventario">
        <div class="card-body">
            <form method="GET" action="/inventario" id="formFiltros">
                <div class="row g-3 align-items-end">
                    <!-- Buscar -->
                    <div class="col-md-2">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="buscar" id="buscarInventario"
                               class="form-control form-control-sm"
                               placeholder="Buscar..."
                               value="{{ request('buscar') }}">
                    </div>

                    <!-- Categoría -->
                    <div class="col-md-2">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="" {{ $categoria == '' ? 'selected' : '' }}>Todos</option>
                            <option value="Equipo" {{ $categoria == 'Equipo' ? 'selected' : '' }}>Equipo</option>
                            <option value="Insumo" {{ $categoria == 'Insumo' ? 'selected' : '' }}>Insumo</option>
                        </select>
                    </div>

                    <!-- Tipo -->
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @if($categoria == 'Insumo')
                                @foreach($nombresInsumos as $nombre)
                                    <option value="{{ $nombre }}" {{ request('tipo') == $nombre ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            @else
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                                        {{ $tipo }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->nombre }}" {{ request('estado') == $estado->nombre ? 'selected' : '' }}>
                                    {{ $estado->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Usuario asignado -->
                    <div class="col-md-2">
                        <label class="form-label">Usuario asignado</label>
                        <select name="usuario" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="Sin asignar" {{ request('usuario') == 'Sin asignar' ? 'selected' : '' }}>Sin asignar</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->nombre }}" {{ request('usuario') == $usuario->nombre ? 'selected' : '' }}>
                                    {{ $usuario->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Proveedor -->
                    <div class="col-md-2">
                        <label class="form-label">Proveedor</label>
                        <select name="proveedor" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->nombre }}" {{ request('proveedor') == $prov->nombre ? 'selected' : '' }}>
                                    {{ $prov->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sucursal -->
                    <div class="col-md-2">
                        <label class="form-label">Sucursal</label>
                        <select name="sucursal" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->nombre }}" {{ request('sucursal') == $sucursal->nombre ? 'selected' : '' }}>
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Precio mínimo -->
                    <div class="col-md-2">
                        <label class="form-label">Precio mínimo</label>
                        <input type="number" name="precio_min" class="form-control form-control-sm"
                               value="{{ request('precio_min') }}" onchange="this.form.submit()">
                    </div>

                    <!-- Precio máximo -->
                    <div class="col-md-2">
                        <label class="form-label">Precio máximo</label>
                        <input type="number" name="precio_max" class="form-control form-control-sm"
                               value="{{ request('precio_max') }}" onchange="this.form.submit()">
                    </div>

                    <!-- Filtrar por -->
                    <div class="col-md-2">
                        <label class="form-label">Filtrar por</label>
                        <select name="fecha_tipo" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="registro" {{ request('fecha_tipo') == 'registro' ? 'selected' : '' }}>Fecha de Registro</option>
                            <option value="compra" {{ request('fecha_tipo') == 'compra' ? 'selected' : '' }}>Fecha de Compra</option>
                        </select>
                    </div>

                    <!-- Desde -->
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm"
                               value="{{ request('fecha_desde') }}" onchange="this.form.submit()">
                    </div>

                    <!-- Hasta -->
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                               value="{{ request('fecha_hasta') }}" onchange="this.form.submit()">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabla de Inventario con asignaciones -->
<form method="POST" action="{{ route('inventario.asignaciones') }}">
    @csrf
    <div class="card shadow">
        <div class="card-header text-white bg-orange d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Inventario</h5>
            <div class="d-flex">
                <select id="tipoAsignacion" name="tipo_asignacion" class="form-select me-2 w-auto">
                    <option value="usuario">Asignar a Usuario</option>
                    <option value="sucursal">Asignar a Sucursal</option>
                    <option value="ninguno">Quitar asignación</option>
                </select>
                <select id="selectorAsignacion" name="destino_id" class="form-select w-auto"></select>
                <button type="submit" class="btn btn-success ms-2">
                    <i class="fas fa-check me-2"></i>Aplicar a seleccionados
                </button>
            </div>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Categoría</th>
                        <th>Tipo / Nombre</th>

                        @if($categoria == 'Equipo' || $categoria == '')
                            <th>Marca</th>
                            <th>Modelo</th>
                        @endif

                        @if($categoria == 'Insumo' || $categoria == '')
                            <th>Cantidad</th>
                        @endif

                        <th>Precio</th>
                        <th>Sucursal</th>
                        <th>Estado</th>
                        <th>Usuario asignado</th>
                        <th>Proveedor</th>
                        <th>{{ request('fecha_tipo') == 'compra' ? 'Fecha de Compra' : 'Fecha de Registro' }}</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Equipos --}}
                    @if($categoria == 'Equipo' || $categoria == '')
                        @foreach($equipos as $equipo)
                            @if(request('estado') == 'Baja' || ($equipo->estadoEquipo->nombre != 'Baja'))
                                <tr onclick="window.location='{{ route('inventario.equipo', $equipo->id) }}'" style="cursor:pointer;">
                                    <td>
                                        <input type="checkbox" name="items[]" value="{{ $equipo->id }}"
                                               onclick="event.stopPropagation();">
                                    </td>
                                    <td><span class="badge bg-primary">Equipo</span></td>
                                    <td>{{ $equipo->tipoEquipo->nombre ?? '-' }}</td>

                                    @if($categoria == 'Equipo' || $categoria == '')
                                        <td>{{ $equipo->marca }}</td>
                                        <td>{{ $equipo->modelo }}</td>
                                    @endif

                                    @if($categoria == 'Insumo' || $categoria == '')
                                        <td>-</td>
                                    @endif

                                    <td>{{ number_format($equipo->precio, 0, ',', '.') }} CLP</td>
                                    <td>{{ $equipo->sucursal->nombre ?? '-' }}</td>
                                    <td>
                                        @php
                                            $estadoNombre = $equipo->estadoEquipo->nombre ?? 'Sin estado';
                                            $color = match($estadoNombre) {
                                                'Disponible' => 'bg-success',
                                                'Asignado'   => 'bg-primary',
                                                'Mantención' => 'bg-warning',
                                                'Baja'       => 'bg-danger',
                                                'En tránsito'=> 'bg-secondary',
                                                default      => 'bg-info',
                                            };
                                        @endphp
                                        <span class="badge {{ $color }}">{{ $estadoNombre }}</span>
                                    </td>
                                    <td>{{ $equipo->usuarioAsignado?->usuario?->nombre ?? '-' }}</td>
                                    <td>{{ $equipo->proveedor->nombre ?? 'N/A' }}</td>
                                    <td>{{ request('fecha_tipo') == 'compra'
                                        ? ($equipo->fecha_compra ? \Carbon\Carbon::parse($equipo->fecha_compra)->format('d-m-Y') : '-')
                                        : \Carbon\Carbon::parse($equipo->fecha_registro)->format('d-m-Y') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif

                    {{-- Insumos --}}
                    @if($categoria == 'Insumo' || $categoria == '')
                        @foreach($insumos as $insumo)
                            @if(request('estado') == 'Baja' || ($insumo->estadoEquipo->nombre != 'Baja'))
                                <tr onclick="window.location='{{ route('inventario.insumo', $insumo->id) }}'" style="cursor:pointer;">
                                    <td>
                                        <input type="checkbox" name="items[]" value="{{ $insumo->id }}"
                                               onclick="event.stopPropagation();">
                                    </td>
                                    <td><span class="badge bg-success">Insumo</span></td>
                                    <td>{{ $insumo->nombre }}</td>

                                    @if($categoria == 'Equipo' || $categoria == '')
                                        <td>-</td>
                                        <td>-</td>
                                    @endif

                                    @if($categoria == 'Insumo' || $categoria == '')
                                        <td>{{ $insumo->cantidad }}</td>
                                    @endif

                                    <td>{{ number_format($insumo->precio, 0, ',', '.') }} CLP</td>
                                    <td>{{ $insumo->sucursal->nombre ?? '-' }}</td>
                                    <td>
                                        @php
                                            $estadoNombre = $insumo->estadoEquipo->nombre ?? 'Sin estado';
                                            $color = match($estadoNombre) {
                                                'Disponible' => 'bg-success',
                                                'Asignado'   => 'bg-primary',
                                                'Mantención' => 'bg-warning',
                                                'Baja'       => 'bg-danger',
                                                'En tránsito'=> 'bg-secondary',
                                                default      => 'bg-info',
                                            };
                                        @endphp
                                        <span class="badge {{ $color }}">{{ $estadoNombre }}</span>
                                    </td>
                                    <td>{{ $insumo->usuarioAsignado?->usuario?->nombre ?? '-' }}</td>
                                    <td>{{ $insumo->proveedor->nombre ?? 'N/A' }}</td>
                                    <td>{{ request('fecha_tipo') == 'compra'
                                        ? ($insumo->fecha_compra ? \Carbon\Carbon::parse($insumo->fecha_compra)->format('d-m-Y') : '-')
                                        : \Carbon\Carbon::parse($insumo->fecha_registro)->format('d-m-Y') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Autocomplete del buscador
    const $input = $('#buscarInventario');
    if ($input.length && typeof $.ui !== 'undefined') {
        $input.autocomplete({
            minLength: 2,
            delay: 300,
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('inventario.autocomplete') }}",
                    method: "GET",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        response(data);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en autocomplete:", error);
                        response([]);
                    }
                });
            },
            select: function(event, ui) {
                const campo = ui.item.campo;
                const valor = ui.item.value;
                
                if (campo === 'buscar') {
                    $input.val(valor);
                    $('#formFiltros').submit();
                } else {
                    $input.val('');
                    const $select = $(`select[name="${campo}"]`);
                    if ($select.length) {
                        $select.val(valor).trigger('change');
                    } else {
                        $('#formFiltros').submit();
                    }
                }
                
                return false;
            },
            focus: function(event, ui) {
                event.preventDefault();
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            const badgeColors = {
                'marca': 'bg-info',
                'modelo': 'bg-info',
                'insumo': 'bg-success', 
                'tipo': 'bg-primary',
                'estado': 'bg-warning text-dark',
                'proveedor': 'bg-secondary',
                'sucursal': 'bg-dark',
                'usuario': 'bg-purple'
            };
            
            const badgeColor = badgeColors[item.tipo] || 'bg-light text-dark';
            const iconos = {
                'marca': 'fa-tag',
                'modelo': 'fa-tag',
                'insumo': 'fa-box',
                'tipo': 'fa-laptop',
                'estado': 'fa-circle',
                'proveedor': 'fa-truck',
                'sucursal': 'fa-building',
                'usuario': 'fa-user'
            };
            
            const icono = iconos[item.tipo] || 'fa-search';
            
            return $("<li>")
                .append(`
                    <div class="d-flex align-items-center py-2 px-2">
                        <i class="fas ${icono} text-muted me-2" style="width: 16px;"></i>
                        <div class="flex-grow-1">
                            <div>${item.label}</div>
                        </div>
                        <span class="badge ${badgeColor} ms-2">${item.tipo}</span>
                    </div>
                `)
                .appendTo(ul);
        };
    }

// Selector dinámico de asignación
const tipoAsignacion = document.getElementById('tipoAsignacion');
const selectorAsignacion = document.getElementById('selectorAsignacion');

const usuarios = @json($usuarios->map(function($user) { return ['id' => $user->id, 'nombre' => $user->nombre]; }));
const sucursales = @json($sucursales->map(function($suc) { return ['id' => $suc->id, 'nombre' => $suc->nombre]; }));

function cargarOpciones(tipo) {
    selectorAsignacion.innerHTML = '';
    
    if (tipo === 'ninguno') {
        // Para quitar asignación, no necesita selección adicional
        let opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Confirmar desasignación';
        selectorAsignacion.appendChild(opt);
        return;
    }
    
    let opciones = tipo === 'usuario' ? usuarios : sucursales;
    
    let defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = tipo === 'usuario' ? 'Seleccionar usuario' : 'Seleccionar sucursal';
    selectorAsignacion.appendChild(defaultOpt);
    
    opciones.forEach(op => {
        let opt = document.createElement('option');
        opt.value = op.id;
        opt.textContent = op.nombre;
        selectorAsignacion.appendChild(opt);
    });
}

if (tipoAsignacion && selectorAsignacion) {
    cargarOpciones(tipoAsignacion.value);
    tipoAsignacion.addEventListener('change', e => {
        cargarOpciones(e.target.value);
    });
}

// Validación antes de enviar el formulario
document.querySelector('form[action="{{ route('inventario.asignaciones') }}"]')?.addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[name="items[]"]:checked');
    const tipo = document.getElementById('tipoAsignacion').value;
    const destino = document.getElementById('selectorAsignacion').value;
    
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Por favor, selecciona al menos un elemento del inventario.');
        return;
    }
    
    if (tipo !== 'ninguno' && !destino) {
        e.preventDefault();
        alert('Por favor, selecciona un destino para la asignación.');
        return;
    }
    
    // Confirmación antes de proceder
    const accion = tipo === 'usuario' ? 'asignar usuario' : 
                   tipo === 'sucursal' ? 'cambiar sucursal' : 'quitar asignación';
    
    if (!confirm(`¿Estás seguro de que deseas ${accion} para ${checkboxes.length} elementos seleccionados?`)) {
        e.preventDefault();
    }
    });

    // SelectAll
    document.getElementById('selectAll')?.addEventListener('change', function(e) {
        document.querySelectorAll('input[name="items[]"]').forEach(cb => {
            cb.checked = e.target.checked;
        });
    });
});
</script>

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
.ui-autocomplete {
    max-height: 300px;
    overflow-y: auto;
    overflow-x: hidden;
}
</style>
@endpush

@endsection