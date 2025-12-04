@extends('layouts.base')

@section('contenido')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-sync-alt me-1"></i>Actualizar
        </button>
    </div>
</div>

<div class="row mb-4">
    @php
        $kpis = [
            ['label' => 'Total Equipos', 'value' => $totalEquipos, 'icon' => 'fa-laptop', 'color' => 'primary'],
            ['label' => 'Disponibles', 'value' => $disponibles, 'icon' => 'fa-check-circle', 'color' => 'success'],
            ['label' => 'Equipos Asignados', 'value' => $equiposAsignados, 'icon' => 'fa-user-check', 'color' => 'info'],
            ['label' => 'En Mantención', 'value' => $enMantencion, 'icon' => 'fa-tools', 'color' => 'warning'],
        ];
    @endphp

    @foreach ($kpis as $kpi)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ $kpi['color'] }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-{{ $kpi['color'] }} text-uppercase mb-1">
                                {{ $kpi['label'] }}
                            </div>
                            <div class="h5 mb-0 fw-bold text-dark">{{ $kpi['value'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas {{ $kpi['icon'] }} fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header bg-orange text-white">
                <h6 class="m-0 fw-bold">Distribución por Tipo de Equipo</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center">
                    <div style="max-width: 240px; margin-right: 20px;">
                        <canvas id="graficoTipos"></canvas>
                    </div>
                    <div id="legendTipos" class="d-flex flex-wrap" style="max-width: 280px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header bg-orange text-white">
                <h6 class="m-0 fw-bold">Equipos Recientemente Agregados</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse ($equiposRecientes as $equipo)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $equipo->marca }} {{ $equipo->modelo }}</strong>
                                    <div class="text-muted small">
                                        {{ $equipo->tipo->nombre ?? 'Sin tipo' }} |
                                        {{ $equipo->estadoEquipo->nombre ?? 'Sin estado' }} |
                                        {{ $equipo->proveedor->nombre ?? 'Sin proveedor' }}
                                    </div>
                                </div>
                                <span class="badge bg-success">Nuevo</span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No hay equipos recientes.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-orange text-white">
                <h6 class="m-0 fw-bold">Accesos Rápidos</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('registro_equipo.create') }}" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-plus-circle me-2"></i>Agregar Equipo
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button id="abrirQR" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-qrcode me-2"></i>Escanear QR
                        </button>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modalReportes">
                            <i class="fas fa-file-export me-2"></i>Generar Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.modal-reportes')
@endsection

@push('scripts')
<script>
    // ==========================================================
    // LÓGICA DEL ESCÁNER QR
    // ==========================================================
    let qrScanner;

    const popup = document.getElementById('popupQR');
    const abrirBtn = document.getElementById('abrirQR');
    const cerrarBtn = document.getElementById('cerrarQR');

    abrirBtn.addEventListener('click', () => {
        popup.classList.remove('d-none');
        if (!qrScanner) {
            qrScanner = new Html5Qrcode("qr-reader");
            qrScanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                qrCodeMessage => {
                    document.getElementById("qr-result").innerText = `Código detectado: ${qrCodeMessage}`;
                    if (qrCodeMessage.startsWith("http")) {
                        window.location.href = qrCodeMessage;
                    } else {
                        window.location.href = `/equipos/${qrCodeMessage}`;
                    }
                    cerrarQR();
                },
                errorMessage => {}
            ).catch(err => {
                document.getElementById("qr-result").innerText = "Error al iniciar la cámara.";
            });
        }
    });

    cerrarBtn.addEventListener('click', cerrarQR);

    function cerrarQR() {
        popup.classList.add('d-none');
        if (qrScanner) {
            qrScanner.stop().then(() => {
                qrScanner.clear();
                qrScanner = null;
                document.getElementById("qr-result").innerText = "";
            });
        }
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ==========================================================
        // LÓGICA DEL GRÁFICO (existente)
        // ==========================================================
        const distribucion = @json($distribucion);
        const labels = distribucion.map(t => t.nombre);
        const data = distribucion.map(t => t.equipos_count);
        const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1', '#20c997', '#fd7e14'];

        const ctx = document.getElementById('graficoTipos').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: labels.map((_, i) => colors[i % colors.length]),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        const legendEl = document.getElementById('legendTipos');
        legendEl.innerHTML = labels.map((label, i) => `
            <div class="d-flex align-items-center mb-3" style="width: 50%;">
                <span style="width:14px;height:14px;background:${colors[i % colors.length]};border-radius:3px;margin-right:8px;"></span>
                ${label} <small class="text-muted">(${data[i]})</small>
            </div>
        `).join('');

        // ==========================================================
        // LÓGICA DEL BOTÓN DE REPORTE (CORREGIDA PARA CSV/PDF)
        // ==========================================================
        const btnGenerarReporte = document.getElementById('btnGenerarReporte');
        const reporteTipoSelect = document.getElementById('reporteTipo');
        const reporteFormatoSelect = document.getElementById('reporteFormato'); // Se espera que exista en el modal
        
        const modalReportes = new bootstrap.Modal(document.getElementById('modalReportes')); 

        if (btnGenerarReporte && reporteTipoSelect && reporteFormatoSelect) {
            btnGenerarReporte.addEventListener('click', function(e) {
                e.preventDefault();
                const tipo = reporteTipoSelect.value;
                const formato = reporteFormatoSelect.value; // ¡AHORA SÍ CAPTURAMOS EL FORMATO!
                const baseUrl = btnGenerarReporte.dataset.reporteUrl; 

                if (tipo) {
                    // CONSTRUIMOS LA URL CON AMBOS PARÁMETROS: tipo y formato
                    const url = `${baseUrl}?tipo=${tipo}&formato=${formato}`;
                    
                    modalReportes.hide();

                    window.open(url, '_blank'); 
                } else {
                    alert('Por favor, selecciona un tipo de reporte.');
                }
            });
        }
    });
</script>
@endpush
