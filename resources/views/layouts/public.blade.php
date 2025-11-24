<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Inventario TI</title>

    <!-- Bootstrap y FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Ícono -->
    <link rel="icon" href="{{ asset('images/logo-coyahue.png') }}" type="image/png">

    <!-- Estilos personalizados -->
    @vite([
        'resources/css/app.css',
        'resources/css/base.css',
        'resources/css/login.css',
        'resources/js/app.js'
    ])
</head>
<body>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>