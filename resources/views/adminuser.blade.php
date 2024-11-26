@extends('layout')
@section('title','Administrador de usuarios')
@section('content')
    <div class="content">
        <header>
            <img src="Puro_money/img/Logo_Gobierno.png" alt="logo">
            <h1>Administración de usuarios</h1>
        </header>
    </div>

    <div class="sidebar">
        <h2>Menú</h2>
        <a href="pagina_inicio.html">Inicio</a>
        <a href="graficos.html">Gráficos</a>
        <a href="transacciones.html">Transacciones</a>
        <a href="estadisticas.html">Estadísticas</a>

        <!-- Configuración con submenú -->
        <div class="submenu">
            <a href="#" class="submenu-toggle">Configuraciones</a>
            <div class="submenu-content">
                <a href="administracion_usuarios.html">Administración de Usuarios</a>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="user-management">
            <div class="actions-bar">
                <input type="text" placeholder="Buscar usuarios..." class="search-bar">
                <button class="add-user-btn">+ Nuevo Usuario</button>
            </div>

            <table class="users-table">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Admin User</td>
                    <td>admin@example.com</td>
                    <td>Administrador</td>
                    <td>Activo</td>
                    <td>
                        <button class="action-btn edit-btn">Editar</button>
                        <button class="action-btn delete-btn">Eliminar</button>
                    </td>
                </tr>
                <tr>
                    <td>Editor User</td>
                    <td>editor@example.com</td>
                    <td>Editor</td>
                    <td>Activo</td>
                    <td>
                        <button class="action-btn edit-btn">Editar</button>
                        <button class="action-btn delete-btn">Eliminar</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


@endsection
