@extends('layout')
@section('title','Administrador de usuarios')
@section('content')
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <main class="main-content">
        <div class="user-management">
            <div class="actions-bar">
                <input type="text" id="searchInput" placeholder="Buscar usuarios..." class="search-bar">
                <button class="add-user-btn" onclick="openUserModal()">+ Nuevo Usuario</button>
            </div>

            <table class="users-table">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>DUI</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->full_name }}</td>
                        <td>{{ $user->dui }}</td>
                        <td>{{ $user->role->name }}</td>
                        <td>{{ $user->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td>
                            <button class="action-btn edit-btn" onclick="openUserModal({{ json_encode($user) }})">Editar</button>
                            <button class="action-btn delete-btn" onclick="deleteUser('{{ $user->id }}')">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </main>

    <!-- User Modal -->
    <div class="modal" id="userModal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeUserModal()">&times;</span>
            <h2 id="modalTitle">Nuevo Usuario</h2>
            <br>
            <form id="userForm" onsubmit="saveUser(event)">
                @csrf
                <input type="hidden" id="userId">

                <div class="form-group">
                    <label for="full_name">Nombre Completo</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>

                <div class="form-group">
                    <label for="dui">DUI</label>
                    <input type="text" id="dui" name="dui"  placeholder="00000000-0"  maxlength="10" autofocus pattern="[0-9]{8}-[0-9]" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password">
                    <small id="passwordHelp" class="form-text">Dejar en blanco para mantener la contraseña actual (en caso de edición)</small>
                </div>

                <div class="form-group">
                    <label for="role_id">Rol</label>
                    <select id="role_id" name="role_id" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        Usuario Activo
                        <input type="checkbox" id="is_active" name="is_active" checked>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            border-radius: 8px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const searchText = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.users-table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Modal functions
        function openUserModal(user = null) {
            const modal = document.getElementById('userModal');
            const form = document.getElementById('userForm');
            const modalTitle = document.getElementById('modalTitle');
            const passwordField = document.getElementById('password');
            const passwordHelp = document.getElementById('passwordHelp');

            form.reset();

            if (user) {
                modalTitle.textContent = 'Editar Usuario';
                document.getElementById('userId').value = user.id;
                document.getElementById('full_name').value = user.full_name;
                document.getElementById('dui').value = user.dui;
                document.getElementById('role_id').value = user.role_id;
                document.getElementById('is_active').checked = user.is_active;

                passwordField.required = false;
                passwordHelp.style.display = 'block';
            } else {
                modalTitle.textContent = 'Nuevo Usuario';
                document.getElementById('userId').value = '';
                passwordField.required = true;
                passwordHelp.style.display = 'none';
            }

            modal.style.display = 'block';
        }

        function closeUserModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        // CRUD Operations
        async function saveUser(event) {
            event.preventDefault();

            const userId = document.getElementById('userId').value;
            const isEdit = userId !== '';
            const url = isEdit ? `/users/${userId}` : '/users';
            const method = isEdit ? 'PUT' : 'POST';

            const formData = {
                full_name: document.getElementById('full_name').value,
                dui: document.getElementById('dui').value,
                password: document.getElementById('password').value,
                role_id: document.getElementById('role_id').value,
                is_active: document.getElementById('is_active').checked
            };

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Error al procesar la solicitud');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error',
                    text: error.message,
                    icon: 'error'
                });
            }
        }

        function deleteUser(userId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede revertir",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`/users/${userId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: data.message,
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Error al eliminar el usuario');
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error',
                            text: error.message,
                            icon: 'error'
                        });
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const duiInput = document.getElementById('dui');

            duiInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove non-digits

                if (value.length >= 8) {
                    value = value.substring(0, 8) + '-' + value.substring(8, 9);
                }

                e.target.value = value;
            });
        });
    </script>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
