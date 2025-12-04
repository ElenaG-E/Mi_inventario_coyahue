<div class="modal fade" id="modalReportes" tabindex="-1" aria-labelledby="modalReportesLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-orange text-white">
                <h5 class="modal-title" id="modalReportesLabel">
                    <i class="fas fa-file-export me-2"></i>Generar Reporte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                
                {{-- Selector de TIPO de Reporte --}}
                <div class="mb-3">
                    <label for="reporteTipo" class="form-label">Tipo de Reporte:</label>
                    <select class="form-select" id="reporteTipo">
                        <option value="general">Reporte General (Inventario Completo)</option> 
                        <option value="equipos">Reporte Detallado de Equipos</option>
                        <option value="insumos">Reporte Detallado de Insumos</option>
                        <option value="asignaciones">Reporte de Asignaciones</option>
                        <option value="mantenciones">Reporte de Equipos en Mantención</option>
                        <option value="sucursales">Reporte por Sucursal</option>
                        <option value="estadisticas">Reporte de Estadísticas</option>
                    </select>
                </div>

                {{-- Selector de FORMATO de Exportación (NUEVO) --}}
                <div class="mb-4">
                    <label for="reporteFormato" class="form-label">Formato de Exportación:</label>
                    <select class="form-select" id="reporteFormato">
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV (Excel)</option>
                    </select>
                </div>
                
                {{-- La ruta 'inventario.exportar' debe apuntar al DashboardController --}}
                <button id="btnGenerarReporte" class="btn btn-primary w-100" data-reporte-url="{{ route('inventario.exportar') }}">
                    <i class="fas fa-download me-2"></i>Generar
                </button>
            </div>
        </div>
    </div>
</div>
