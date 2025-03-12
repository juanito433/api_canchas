<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservación de Canchas</title>

    <!-- Estilos Bootstrap (puedes usar CDN o locales) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos personalizados -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- FontAwesome (para íconos) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Otros estilos o fuentes -->
    @stack('styles') <!-- Para agregar estilos personalizados por vista -->
</head>
<body>
    <div class="container-fluid">
        <!-- Navbar o encabezado -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="#">Club Haciendas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('home') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reservation.index') }}">Reservaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile') }}">Perfil</a>
                    </li>
                    <!-- Agrega más enlaces según sea necesario -->
                </ul>
            </div>
        </nav>

        <!-- Contenido principal (por ejemplo, la vista del formulario de reservación) -->
        @yield('content')

    </div> <!-- container-fluid -->

    <!-- Scripts de Bootstrap y JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts personalizados -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts') <!-- Para agregar scripts personalizados por vista -->
</body>
</html>
