@extends('layouts.app')

@section('contenido') {{-- Usa 'contenido' para el layout con Sidebar --}}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Registro de Inventario</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('inventario') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i>Volver al Inventario
        </a>
    </div>
</div>

{{-- Mensaje de Éxito Global (si se redirige después de guardar) --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow">
            <div class="card-header text-white" style="background: #ff6b35;">
                <h5 class="card-title mb-0"><i class="fas fa-box-open me-2"></i>Formulario de Registro</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('registro_equipo.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Categoría -->
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" id="categoria" class="form-select" onchange="filtrarTipos()" required>
                            <option value="">Seleccionar...</option>
                            <option value="equipo" {{ old('categoria') == 'equipo' ? 'selected' : '' }}>Equipo</option>
                            <option value="insumo" {{ old('categoria') == 'insumo' ? 'selected' : '' }}>Insumo</option>
                        </select>
                        @error('categoria') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <!-- Tipo (solo para equipos) -->
                    <div class="mb-3" id="bloqueTipo" style="display:none;">
                        <label class="form-label">Tipo</label>
                        <select name="tipo_equipo_id" id="tipo_equipo_id" class="form-select" onchange="mostrarEspecificaciones()">
                            <option value="">Seleccionar...</option>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->id }}" data-categoria="{{ strtolower($tipo->categoria) }}" {{ old('tipo_equipo_id') == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_equipo_id') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <!-- Número de serie, marca, modelo -->
                    <div id="camposEquipo" style="display:none;">
                        <div class="row align-items-end">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Número de serie</label>
                                <input type="text" name="numero_serie" id="numero_serie" class="form-control" value="{{ old('numero_serie') }}">
                                @error('numero_serie') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3 text-end">
                                <label class="form-label d-block invisible">Buscar con IA</label>
                                <button type="button" id="buscarIAButton" class="btn btn-outline-dark w-100" disabled>
                                    <i class="fas fa-robot me-2"></i>Buscar con IA
                                </button>
                            </div>
                            
                            {{-- CONTENEDOR DE MENSAJES DE ESTADO DE LA IA --}}
                            <div class="col-12">
                                <div id="ia_status_message" class="mb-3 p-1 rounded text-sm"></div>
                            </div>
                            
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marca</label>
                                <input type="text" name="marca" class="form-control" value="{{ old('marca') }}">
                                @error('marca') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Modelo</label>
                                <input type="text" name="modelo" class="form-control" value="{{ old('modelo') }}">
                                @error('modelo') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Precio y sucursal -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio</label>
                                <input type="number" step="0.01" name="precio" class="form-control" value="{{ old('precio') }}">
                                @error('precio') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sucursal</label>
                                <select name="sucursal_id" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ old('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sucursal_id') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Especificaciones técnicas dinámicas -->
                    <div id="especificacionesTecnicas" style="display:none;">
                        
                        {{-- Campo oculto para el resumen generado por la IA (guarda en EspecificacionTecnica::resumen_ia) --}}
                        <input type="hidden" name="especificaciones_ia" id="especificaciones_ia" value="{{ old('especificaciones_ia') }}">

                        <!-- Notebook / PC / Servidor -->
                        <div id="bloqueNotebook" class="bloque-especificaciones" style="display:none;">
                            <h6 class="mt-3 mb-3 text-primary">Detalles de Computador/Servidor</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Procesador</label>
                                    <input type="text" name="procesador" class="form-control" value="{{ old('procesador') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">RAM (GB)</label>
                                    <input type="number" name="ram_gb" class="form-control" min="0" value="{{ old('ram_gb') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Almacenamiento (GB)</label>
                                    <input type="number" name="almacenamiento_gb" class="form-control" min="0" value="{{ old('almacenamiento_gb') }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tipo de almacenamiento</label>
                                    <input type="text" name="tipo_almacenamiento" class="form-control" value="{{ old('tipo_almacenamiento') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tarjeta gráfica</label>
                                    <input type="text" name="tarjeta_grafica" class="form-control" value="{{ old('tarjeta_grafica') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Sistema operativo</label>
                                    <input type="text" name="sistema_operativo" class="form-control" value="{{ old('sistema_operativo') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Smartphone / Tablet -->
                        <div id="bloqueSmartphone" class="bloque-especificaciones" style="display:none;">
                            <h6 class="mt-3 mb-3 text-primary">Detalles de Dispositivo Móvil</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Pantalla (pulgadas)</label>
                                    <input type="text" name="pantalla_pulgadas" class="form-control" value="{{ old('pantalla_pulgadas') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Resolución pantalla</label>
                                    <input type="text" name="resolucion_pantalla" class="form-control" value="{{ old('resolucion_pantalla') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tipo de panel</label>
                                    <input type="text" name="tipo_panel" class="form-control" value="{{ old('tipo_panel') }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Frecuencia (Hz)</label>
                                    <input type="number" name="frecuencia_hz" class="form-control" min="0" value="{{ old('frecuencia_hz') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Batería (mAh)</label>
                                    <input type="number" name="bateria_mah" class="form-control" min="0" value="{{ old('bateria_mah') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cámara frontal (MP)</label>
                                    <input type="number" name="camara_frontal_mp" class="form-control" min="0" value="{{ old('camara_frontal_mp') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Monitor -->
                        <div id="bloqueMonitor" class="bloque-especificaciones" style="display:none;">
                            <h6 class="mt-3 mb-3 text-primary">Detalles de Monitor</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Puertos monitor</label>
                                    <input type="text" name="puertos_monitor" class="form-control" value="{{ old('puertos_monitor') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Resolución pantalla</label>
                                    <input type="text" name="resolucion_pantalla" class="form-control" value="{{ old('resolucion_pantalla') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tipo de panel</label>
                                    <input type="text" name="tipo_panel" class="form-control" value="{{ old('tipo_panel') }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Frecuencia (Hz)</label>
                                    <input type="number" name="frecuencia_hz" class="form-control" min="0" value="{{ old('frecuencia_hz') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Proyector -->
                        <div id="bloqueProyector" class="bloque-especificaciones" style="display:none;">
                            <h6 class="mt-3 mb-3 text-primary">Detalles de Proyector</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Lúmenes</label>
                                    <input type="number" name="lumenes" class="form-control" min="0" value="{{ old('lumenes') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Resolución nativa</label>
                                    <input type="text" name="resolucion_nativa" class="form-control" value="{{ old('resolucion_nativa') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tecnología proyector</label>
                                    <input type="text" name="tecnologia_proyector" class="form-control" value="{{ old('tecnologia_proyector') }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Entradas de video</label>
                                    <input type="text" name="entradas_video" class="form-control" value="{{ old('entradas_video') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Impresora -->
                        <div id="bloqueImpresora" class="bloque-especificaciones" style="display:none;">
                            <h6 class="mt-3 mb-3 text-primary">Detalles de Impresora</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tecnología de impresión</label>
                                    <input type="text" name="tecnologia_impresion" class="form-control" value="{{ old('tecnologia_impresion') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Color de impresión</label>
                                    <input type="text" name="color_impresion" class="form-control" value="{{ old('color_impresion') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Resolución (DPI)</label>
                                    <input type="number" name="resolucion_dpi" class="form-control" min="0" value="{{ old('resolucion_dpi') }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de tinta</label>
                                    <input type="text" name="tipo_tinta" class="form-control" value="{{ old('tipo_tinta') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Conectividad</label>
                                    <input type="text" name="conectividad" class="form-control" value="{{ old('conectividad') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Router / Switch / Access Point -->
                        <div id="bloqueRed" class="bloque-especificaciones" style="display:none;">
                            <h6 class="mt-3 mb-3 text-primary">Detalles de Equipo de Red</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Puertos de red</label>
                                    <input type="text" name="puertos_red" class="form-control" value="{{ old('puertos_red') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bandas</label>
                                    <input type="text" name="bandas" class="form-control" value="{{ old('bandas') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tipo de switch</label>
                                    <input type="text" name="tipo_switch" class="form-control" value="{{ old('tipo_switch') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Cámara Web -->
                        <div id="bloqueCamara" class="bloque-especificaciones" style="display:none;">
                             <h6 class="mt-3 mb-3 text-primary">Detalles de Cámara Web</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cámara frontal (MP)</label>
                                    <input type="number" name="camara_frontal_mp" class="form-control" min="0" value="{{ old('camara_frontal_mp') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Almacenamiento expansible</label>
                                    <input type="text" name="almacenamiento_expansible" class="form-control" value="{{ old('almacenamiento_expansible') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Otros siempre visible -->
                        <div id="bloqueOtros" class="bloque-especificaciones" style="display:none;">
                             <h6 class="mt-3 mb-3 text-primary">Otros Datos</h6>
                            <div class="mb-3">
                                <label class="form-label">Otros datos</label>
                                <textarea name="otros_datos" class="form-control" rows="2">{{ old('otros_datos') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Campos insumo -->
                    <div id="camposInsumo" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" id="nombreInsumo" name="nombre_insumo" class="form-control"
                                    placeholder="Escribe o selecciona un insumo" value="{{ old('nombre_insumo') }}">
                                @error('nombre_insumo') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" min="1" value="{{ old('cantidad') }}">
                                @error('cantidad') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio</label>
                                <input type="number" name="precio" class="form-control" min="0" step="0.01" value="{{ old('precio') }}">
                                @error('precio') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sucursal</label>
                                <select name="sucursal_id" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ old('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sucursal_id') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Documentos -->
                    <h5 class="mt-4 mb-3">Subir documentos</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white fw-bold">Subir facturas</div>
                                <div class="card-body">
                                    <input type="file" name="documentos_factura[]" class="form-control file-input" multiple>
                                    @error('documentos_factura') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success mb-4">
                                <div class="card-header bg-success text-white fw-bold">Subir garantías</div>
                                <div class="card-body">
                                    <input type="file" name="documentos_garantia[]" class="form-control file-input" multiple>
                                    @error('documentos_garantia') <div class="text-danger">{{ $message }}</div> @enderror
                                    <div class="mt-3">
                                        <label class="form-label">Duración de la garantía (meses)</label>
                                        <input type="number" name="tiempo_garantia_meses" class="form-control" min="0" value="{{ old('tiempo_garantia_meses') }}">
                                        @error('tiempo_garantia_meses') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Asociar documentos existentes -->
                    <div class="card border-secondary mb-4">
                        <div class="card-header bg-light fw-bold">Asociar documentos existentes</div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="toggleFacturas">
                                <label class="form-check-label" for="toggleFacturas">Mostrar facturas disponibles</label>
                            </div>
                            <div id="listaFacturas" class="mb-3" style="display:none;">
                                <select name="factura_ids[]" class="form-select" multiple style="height: 140px;">
                                    @foreach ($facturas as $factura)
                                        <option value="{{ $factura->id }}">{{ $factura->nombre_archivo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="toggleGarantias">
                                <label class="form-check-label" for="toggleGarantias">Mostrar garantías disponibles</label>
                            </div>
                            <div id="listaGarantias" class="mb-3" style="display:none;">
                                <select name="garantia_ids[]" class="form-select" multiple style="height: 140px;">
                                    @foreach ($garantias as $garantia)
                                        <option value="{{ $garantia->id }}">
                                            {{ $garantia->nombre_archivo }} ({{ $garantia->tiempo_garantia_meses ?? 'N/A' }} meses)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Estado, fecha y proveedor -->
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado_equipo_id" class="form-select">
                                <option value="">Seleccionar...</option>
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado->id }}" {{ old('estado_equipo_id') == $estado->id ? 'selected' : '' }}>
                                        {{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('estado_equipo_id') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha de compra</label>
                            <input type="date" name="fecha_compra" class="form-control" value="{{ old('fecha_compra') }}">
                            @error('fecha_compra') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Proveedor</label>
                            <select name="proveedor_id" class="form-select">
                                <option value="">Seleccionar...</option>
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                        {{ $proveedor->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proveedor_id') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <input type="hidden" name="fecha_registro" value="{{ now()->format('Y-m-d') }}">

                    <!-- Botones -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <button type="reset" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-eraser me-2"></i>Limpiar
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-save me-2"></i>Registrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Popup QR - Mantenemos la estructura global -->
<div id="popupQR" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.6); z-index: 1050;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="card shadow" style="width: 90%; max-width: 400px;">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Código QR generado</h5>
                <button onclick="cerrarPopupQR()" class="btn btn-sm text-white" style="font-size: 1.5rem;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('images/qr-placeholder.png') }}" alt="QR generado" class="img-fluid mb-3" style="max-width: 200px;">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Imprimir QR
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('qr_generado'))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById('popupQR').classList.remove('d-none');
        });
    </script>
@endif

@push('scripts')
<script>
    // --- LÓGICA GENERAL (FUNCIONES GLOBALES) ---
    // Funciones que deben estar disponibles para onclick o llamadas asíncronas
    
    function filtrarTipos() {
        const categoria = document.getElementById('categoria').value;
        const tipoSelect = document.getElementById('tipo_equipo_id');
        const bloqueTipo = document.getElementById('bloqueTipo');

        bloqueTipo.style.display = (categoria === 'equipo') ? 'block' : 'none';
        document.getElementById('camposEquipo').style.display = (categoria === 'equipo') ? 'block' : 'none';
        document.getElementById('camposInsumo').style.display = (categoria === 'insumo') ? 'block' : 'none';
        document.getElementById('especificacionesTecnicas').style.display = 'none';

        Array.from(tipoSelect.options).forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (opt.dataset.categoria === categoria) ? 'block' : 'none';
        });

        tipoSelect.value = '';
    }

    function mostrarEspecificaciones() {
        const tipoSelect = document.getElementById('tipo_equipo_id');
        const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
        const tipoNombre = selectedOption?.text.trim() || ''; // Usar trim para limpiar espacios
        document.querySelectorAll('.bloque-especificaciones').forEach(div => div.style.display = 'none');

        document.getElementById('especificacionesTecnicas').style.display = 'block';

        if (['Notebook','PC Escritorio','Servidor'].includes(tipoNombre)) {
            document.getElementById('bloqueNotebook').style.display = 'block';
        } else if (['Smartphone','Tablet'].includes(tipoNombre)) {
            document.getElementById('bloqueSmartphone').style.display = 'block';
        } else if (tipoNombre === 'Monitor') {
            document.getElementById('bloqueMonitor').style.display = 'block';
        } else if (tipoNombre === 'Proyector') {
            document.getElementById('bloqueProyector').style.display = 'block';
        } else if (tipoNombre === 'Impresora') {
            document.getElementById('bloqueImpresora').style.display = 'block';
        } else if (['Router','Switch','Access Point'].includes(tipoNombre)) {
            document.getElementById('bloqueRed').style.display = 'block';
        } else if (tipoNombre === 'Cámara Web') {
            document.getElementById('bloqueCamara').style.display = 'block';
        }
        
        document.getElementById('bloqueOtros').style.display = 'block';
    }

    function cerrarPopupQR() {
        document.getElementById('popupQR').classList.add('d-none');
    }

    // Función auxiliar para actualizar el estado del mensaje (debe ser global si se usa fuera de DOMContentLoaded)
    const updateStatus = (message, className) => {
        const statusMessage = document.querySelector('#ia_status_message');
        if (statusMessage) {
            statusMessage.textContent = message;
            statusMessage.className = `mt-2 p-2 rounded text-sm ${className} animate__animated animate__fadeIn`;
        }
    };
    
    
    // --- LÓGICA DE INICIALIZACIÓN (IA, JQUERY, etc.) ---
    document.addEventListener('DOMContentLoaded', () => {
        // --- LÓGICA DE BÚSQUEDA IA (JS NATIVO) ---
        const iaButton = document.querySelector('#buscarIAButton');
        const numeroSerieInput = document.querySelector('#numero_serie');
        const marcaInput = document.querySelector('input[name="marca"]');
        const modeloInput = document.querySelector('input[name="modelo"]');
        const tipoEquipoSelect = document.querySelector('select[name="tipo_equipo_id"]');
        const precioInput = document.querySelector('input[name="precio"]');
        
        // Elemento que existe en el Blade (ya no creamos input:hidden dinámicamente)
        const especificacionesIaInput = document.querySelector('#especificaciones_ia'); 

        if (iaButton && numeroSerieInput) {
            iaButton.disabled = false;

            iaButton.addEventListener('click', (e) => {
                e.preventDefault();

                const numeroSerie = numeroSerieInput.value.trim();
                if (!numeroSerie) {
                    updateStatus('⚠️ Por favor, introduce un número de serie para buscar con IA.', 'bg-warning text-dark');
                    return;
                }

                // Iniciar estado de carga
                iaButton.disabled = true;
                iaButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Buscando...';
                updateStatus('Consultando bases de datos de hardware...', 'bg-info text-white');

                // Realizar la solicitud Fetch
                fetch(`/registro-equipo/ia-buscar?numero_serie=${encodeURIComponent(numeroSerie)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        iaButton.disabled = false;
                        iaButton.innerHTML = '<i class="fas fa-robot me-2"></i>Buscar con IA';
                        
                        // 1. Manejar Errores del Backend
                        if (data.error || data.success === false) {
                            updateStatus(`❌ Error de la IA: ${data.error || 'Fallo desconocido.'}`, 'bg-danger text-white');
                            console.error('IA Error:', data.raw_response || data.error);
                            return;
                        }

                        // 2. Llenar campos
                        if (marcaInput) marcaInput.value = data.marca || 'Desconocido';
                        if (modeloInput) modeloInput.value = data.modelo || 'Desconocido';
                        if (precioInput) precioInput.value = data.precio ? parseFloat(data.precio).toFixed(2) : '0.00';
                        
                        // 3. Llenar campo de especificaciones oculto
                        if (especificacionesIaInput) especificacionesIaInput.value = data.especificaciones_clave || '';
                        
                        // 4. Manejo del Tipo de Equipo (Selección por ID)
                        if (tipoEquipoSelect) {
                            if (data.tipo_equipo_id) {
                                tipoEquipoSelect.value = data.tipo_equipo_id;
                                mostrarEspecificaciones(); // Dispara el display de bloques
                                updateStatus(`✅ Datos encontrados y clasificados como: ${data.tipo_equipo_sugerido}.`, 'bg-success text-white');
                            } else {
                                updateStatus(`⚠️ Tipo sugerido (${data.tipo_equipo_sugerido}) no encontrado localmente. Seleccione manualmente.`, 'bg-warning text-dark');
                            }
                        }

                        if (precioInput) precioInput.focus();

                    })
                    .catch(error => {
                        iaButton.disabled = false;
                        iaButton.innerHTML = '<i class="fas fa-robot me-2"></i>Buscar con IA';
                        updateStatus(`❌ Error de red o servidor al consultar la IA. Revise la consola.`, 'bg-danger text-white');
                        console.error('Error al buscar con IA:', error);
                    });
            });
        }
        
        // --- LÓGICA JQUERY ---
        // Autocompletado para insumos
        const insumos = @json($insumosExistentes->pluck('nombre'));
        $('#nombreInsumo').autocomplete({
            source: insumos,
            minLength: 1
        });

        // Mostrar/ocultar listas de documentos existentes
        $('#toggleFacturas').on('change', function () {
            $('#listaFacturas').toggle(this.checked);
        });

        $('#toggleGarantias').on('change', function () {
            $('#listaGarantias').toggle(this.checked);
        });
        
        // Lógica para mantener los bloques de especificación si hay errores de validación
        if (document.getElementById('tipo_equipo_id')?.value) {
            filtrarTipos();
            mostrarEspecificaciones();
        }
    });
</script>
@endpush
@endsection
