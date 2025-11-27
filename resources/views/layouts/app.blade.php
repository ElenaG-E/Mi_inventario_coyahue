<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Sistema Inventario TI - Grupo Coyahue')</title>

    {{-- LIBRERÍAS CDN (Externas) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    {{-- ASSETS LOCALES (Compilados por VITE) --}}
    <link rel="icon" href="{{ asset('images/logo-coyahue.png') }}" type="image/png">
    
    {{-- FIX CLAVE: Se carga explícitamente app.css y base.css para asegurar que el layout se aplique --}}
    @vite(['resources/css/app.css', 'resources/css/base.css', 'resources/js/app.js'])

    {{-- Librería QR (CDN) --}}
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="min-vh-100">
    
    @auth
    <div class="container-fluid p-0">
        <div class="layout-wrapper d-flex min-vh-100">
            
            <nav id="sidebar" class="sidebar">
                <div class="position-sticky pt-4">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('registro_equipo.create') ? 'active' : '' }}" href="{{ route('registro_equipo.create') }}">
                                <i class="fas fa-plus-circle me-2"></i>Registros
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('inventario.index') ? 'active' : '' }}" href="{{ route('inventario.index') }}">
                                <i class="fas fa-laptop me-2"></i>Inventario
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gestion_proveedores') ? 'active' : '' }}" href="{{ route('gestion_proveedores') }}">
                                <i class="fas fa-truck me-2"></i>Proveedores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gestion_usuarios') ? 'active' : '' }}" href="{{ route('gestion_usuarios') }}">
                                <i class="fas fa-users me-2"></i>Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gestion_sucursales') ? 'active' : '' }}" href="{{ route('gestion_sucursales') }}">
                                <i class="fas fa-building me-2"></i>Sucursales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog me-2"></i>Ajustes
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main id="mainContent" class="main-content transition-all">
                
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary me-2">
                            <i id="sidebarIcon" class="fas fa-angle-double-left"></i>
                        </button>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-label="Toggle navigation">
                            <i class="fas fa-bars"></i>
                        </button>

                        <div class="navbar-brand d-flex align-items-center">
                            <img src="{{ asset('images/logo-coyahue.png') }}" alt="Grupo Coyahue" class="logo-header">
                        </div>

                        <div class="d-flex">
                            <div class="dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-bell"></i>
                                    <span class="badge bg-danger">3</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Nuevo equipo registrado</a>
                                    <a class="dropdown-item" href="#">Asignación pendiente</a>
                                    <a class="dropdown-item" href="#">Mantención requerida</a>
                                </div>
                            </div>
                            <div class="dropdown ms-3">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-user me-1"></i>
                                    {{ Auth::user()?->nombre ?? 'Usuario' }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Perfil</a>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <div class="container-fluid py-4">
                    @yield('contenido')
                </div>

            </main>
        </div>
    </div>
    @endauth
    
    @guest
        @yield('content')
    @endguest

{{-- Popup QR global (Se mantiene fuera del bloque @auth) --}}
<div id="popupQR" class="position-fixed top-0 start-0 w-100 h-100 d-none"
      style="background: rgba(0,0,0,0.6); z-index: 1050;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="card shadow mb-4" style="width: 90%; max-width: 500px;">
            <div class="card-header bg-orange text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Escanear Código QR</h5>
                <button id="cerrarQR" class="btn btn-sm text-white" style="font-size: 1.5rem; line-height: 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="card-body text-center">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-result" class="mt-3 text-muted text-center"></div>
            </div>
        </div>
    </div>
</div>

{{-- CARGA DE SCRIPTS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebarIcon = document.getElementById('sidebarIcon');

    const estadoGuardado = localStorage.getItem('sidebarEstado');
    if (estadoGuardado === 'collapsed') {
        sidebar?.classList.add('collapsed');
        main?.classList.add('expanded');
        sidebarIcon?.classList.remove('fa-angle-double-left');
        sidebarIcon?.classList.add('fa-angle-double-right');
    }

    toggleBtn?.addEventListener('click', () => {
        const estaColapsado = sidebar.classList.toggle('collapsed');
        if (estaColapsado) {
            main.classList.add('expanded');
            sidebarIcon.classList.remove('fa-angle-double-left');
            sidebarIcon.classList.add('fa-angle-double-right');
            localStorage.setItem('sidebarEstado', 'collapsed');
        } else {
            main.classList.remove('expanded');
            sidebarIcon.classList.remove('fa-angle-double-right');
            sidebarIcon.classList.add('fa-angle-double-left');
            localStorage.setItem('sidebarEstado', 'expanded');
        }
    });

    document.getElementById('cerrarQR')?.addEventListener('click', () => {
        document.getElementById('popupQR')?.classList.add('d-none');
    });
</script>

@stack('scripts')

</body>
</html>
