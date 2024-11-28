@extends('layout')
@section('title','Administrador de usuarios')
@section('content')

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
