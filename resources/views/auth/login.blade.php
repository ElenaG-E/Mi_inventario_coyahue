@extends('layouts.guest') {{-- Usa el layout minimalista con fondo naranja --}}

@section('content')
<div class="card shadow p-4 p-md-5 login-card border-0 rounded-4">
    <div class="text-center">
        {{-- Muestra el logo centrado --}}
        <img src="{{ asset('images/logo-coyahue.png') }}" alt="Logo Coyahue" class="logo-login">
        <h1 class="h4 mb-4" style="color: #ff6b35; font-weight: 700;">Gestión de Inventario TI</h1>
    </div>

    {{-- Muestra errores de sesión o autenticación --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show small" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Campo de Correo Electrónico --}}
        <div class="mb-3">
            <label for="email" class="form-label fw-bold">Correo Electrónico</label>
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-secondary"></i></span>
                <input id="email" type="email" class="form-control border-start-0" name="email" value="{{ old('email') }}" required autofocus placeholder="ej. admin@coyahue.com">
            </div>
            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Campo de Contraseña --}}
        <div class="mb-4">
            <label for="password" class="form-label fw-bold">Contraseña</label>
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-secondary"></i></span>
                <input id="password" type="password" class="form-control border-start-0" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>
            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        
        {{-- Opción de Recordar Sesión y Olvidé Contraseña --}}
        <div class="d-flex justify-content-end align-items-center mb-5">
            {{-- Enlace de Olvidé Contraseña --}}
            <a class="text-decoration-none small" style="color: #ff6b35; font-weight: 500;" href="{{ route('password.request') }}">
                ¿Olvidaste tu contraseña?
            </a>
        </div>

        {{-- Botón de Acceso --}}
        <div class="d-grid gap-2">
            {{-- Usar la clase personalizada de Bootstrap para el color naranja --}}
            <button type="submit" class="btn btn-lg fw-bold border-0 btn-coyahue-orange">
                <i class="fas fa-sign-in-alt me-2"></i>Ingresar al Sistema
            </button>
        </div>
    </form>
</div>
@endsection
