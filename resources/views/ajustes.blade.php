@extends('layouts.app')

@section('titulo', 'Ajustes del Sistema')

@section('contenido')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-cog me-2"></i>Ajustes del Sistema
                    </h1>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Preferencias de Visualización -->
        <div class="row">
            <div class="col-lg-6 col-md-8 col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-orange text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-palette me-2"></i>Preferencias de Visualización
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-moon me-2"></i>Modo de Tema
                            </label>
                            <div class="d-flex gap-3">
                                <!-- Botón Modo Claro -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="themeMode" id="lightMode"
                                           value="light" checked>
                                    <label class="form-check-label" for="lightMode">
                                        <i class="fas fa-sun me-1"></i>Modo Claro
                                    </label>
                                </div>

                                <!-- Botón Modo Oscuro -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="themeMode" id="darkMode"
                                           value="dark">
                                    <label class="form-check-label" for="darkMode">
                                        <i class="fas fa-moon me-1"></i>Modo Oscuro
                                    </label>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Tu preferencia se guardará automáticamente en tu navegador.
                            </small>
                        </div>

                        <!-- Vista previa del tema -->
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Vista previa:</strong> El cambio de tema se aplicará inmediatamente en toda la
                            aplicación.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Información -->
            <div class="col-lg-6 col-md-4 col-12 mt-4 mt-md-0">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold">Sistema de Inventario TI</h6>
                        <p class="text-muted mb-2">Grupo Coyahue</p>

                        <hr>

                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-code me-2"></i>Versión: 1.0.0
                            </small>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-user me-2"></i>Usuario: {{ Auth::user()->nombre }}
                            </small>
                        </div>
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-2"></i>Rol: {{ Auth::user()->rol->nombre ?? 'N/A' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Gestión del modo oscuro
            const lightModeRadio = document.getElementById('lightMode');
            const darkModeRadio = document.getElementById('darkMode');

            // Cargar preferencia guardada al cargar la página
            function loadThemePreference() {
                const savedTheme = localStorage.getItem('themeMode') || 'light';
                if (savedTheme === 'dark') {
                    darkModeRadio.checked = true;
                    document.body.classList.add('dark-mode');
                } else {
                    lightModeRadio.checked = true;
                    document.body.classList.remove('dark-mode');
                }
            }

            // Guardar y aplicar preferencia de tema
            function setTheme(theme) {
                localStorage.setItem('themeMode', theme);
                if (theme === 'dark') {
                    document.body.classList.add('dark-mode');
                } else {
                    document.body.classList.remove('dark-mode');
                }
            }

            // Event listeners para los radio buttons
            lightModeRadio.addEventListener('change', function () {
                if (this.checked) {
                    setTheme('light');
                }
            });

            darkModeRadio.addEventListener('change', function () {
                if (this.checked) {
                    setTheme('dark');
                }
            });

            // Inicializar tema al cargar la página
            loadThemePreference();
        </script>
    @endpush

@endsection

