<div class="content">
    <header>
        <img src="{{ asset('img/Logo_Gobierno.png') }}" alt="logo">
        <h1>{{ $headerWord ?? 'Administración de Usuarios' }}</h1>


        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <img id="logout-image" src="{{ asset('img/logout.png') }}" alt="Cerrar Sesión"
                 style="cursor: pointer; width: 25px; height: auto; margin-left: 440px;">
        </form>

        <script>
            document.getElementById('logout-image').addEventListener('click', function () {
                if (confirm("¿Está seguro de que desea cerrar sesión?")) {
                    document.getElementById('logout-form').submit();
                }
            });
        </script>


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
            <a href="{{ route('userlog') }}">Registro de Actividades</a>
        </div>
    </div>
</aside>
