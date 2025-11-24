<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Inventario Coyahue</title>

    <!-- Bootstrap y FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Estilos de la aplicación -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Estilos personalizados para lograr la estética de la captura */
        body {
            /* Fondo naranja principal de la imagen */
            background-color: #ff6b35 !important; 
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
        .logo-login {
            max-height: 60px;
            margin-bottom: 1.5rem;
        }
        /* Color del botón principal para que sea naranja */
        .btn-coyahue-orange {
            background-color: #ff6b35;
            border-color: #ff6b35;
            color: white;
            transition: background-color 0.2s;
        }
        .btn-coyahue-orange:hover {
            background-color: #e55c20; /* Tono más oscuro al pasar el mouse */
            border-color: #e55c20;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        {{-- Aquí se inyecta el contenido de auth/login.blade.php --}}
        @yield('content')
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
