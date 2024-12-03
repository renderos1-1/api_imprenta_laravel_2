<div class="content">
    <header>
        <img src="{{ asset('img/Logo_Gobierno.png') }}" alt="logo">
        <h1>{{ $headerWord ?? 'Administracion de usuarios' }}</h1>
    </header>
</div>
<aside class="sidebar">
    <h2>Menú</h2>
    <a href="{{ route('dash') }}">Inicio</a>
    <a href="{{ route('transacciones') }}">Transacciones</a>
    <a href="{{ route('estadisticas') }}">Estadísticas</a>

    <!-- Configuración con submenú -->
    <div class="submenu">
        <a href="#" class="submenu-toggle">Configuraciones</a>
        <div class="submenu-content">
            <a href="{{ route('adminuser') }}">Administración de Usuarios</a>
        </div>
    </div>
</aside>
