document.addEventListener("DOMContentLoaded", function () {
    // Botón "Nuevo Usuario"
    document.querySelector('.add-user-btn').addEventListener('click', function () {
        Swal.fire({
            title: 'Agregar Usuario',
            html: `
                <label>Nombre:</label>
                <input type="text" id="user-name" class="swal2-input" placeholder="Ingresa el nombre">
                <label>Email:</label>
                <input type="email" id="user-email" class="swal2-input" placeholder="Ingresa el email">
                <label>Rol:</label>
                <select id="user-role" class="swal2-select">
                    <option value="Administrador">Administrador</option>
                    <option value="Editor">Editor</option>
                </select>
            `,
            confirmButtonText: 'Agregar',
            cancelButtonText: 'Cancelar',
            showCancelButton: true,
            confirmButtonColor: '#003366', // Color del botón de confirmación
            cancelButtonColor: '#003366',  // Color del botón de cancelar
            preConfirm: () => {
                const name = document.getElementById('user-name').value;
                const email = document.getElementById('user-email').value;
                const role = document.getElementById('user-role').value;

                if (!name || !email) {
                    Swal.showValidationMessage('Todos los campos son obligatorios');
                }
                return { name, email, role };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { name, email, role } = result.value;
                // Agregar el usuario a la tabla
                const tbody = document.querySelector('.users-table tbody');
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>${name}</td>
                    <td>${email}</td>
                    <td>${role}</td>
                    <td>Activo</td>
                    <td>
                        <button class="action-btn edit-btn">Editar</button>
                        <button class="action-btn delete-btn">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(newRow);
                Swal.fire({
                    title: '¡Usuario agregado!',
                    text: `El usuario ${name} ha sido añadido.`,
                    icon: 'success',
                    confirmButtonColor: '#003366' // Color del botón en el mensaje de éxito
                });
            }
        });
    });

    // Delegación para botones de "Eliminar" y "Editar"
    document.querySelector('.users-table').addEventListener('click', function (e) {
        const button = e.target;

        // Eliminar usuario
        if (button.classList.contains('delete-btn')) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#003366', // Color del botón de confirmación
                cancelButtonColor: '#003366'  // Color del botón de cancelar
            }).then((result) => {
                if (result.isConfirmed) {
                    const row = button.closest('tr');
                    row.remove();
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El usuario ha sido eliminado.',
                        icon: 'success',
                        confirmButtonColor: '#003366' // Color del botón en el mensaje de éxito
                    });
                }
            });
        }

        // Editar usuario
        if (button.classList.contains('edit-btn')) {
            const row = button.closest('tr');
            const currentName = row.querySelector('td:nth-child(1)').textContent;
            const currentEmail = row.querySelector('td:nth-child(2)').textContent;

            Swal.fire({
                title: 'Editar Usuario',
                html: `
                    <label>Nombre:</label>
                    <input type="text" id="edit-name" class="swal2-input" value="${currentName}">
                    <label>Email:</label>
                    <input type="email" id="edit-email" class="swal2-input" value="${currentEmail}">
                `,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                showCancelButton: true,
                confirmButtonColor: '#003366', // Color del botón de confirmación
                cancelButtonColor: '#003366',  // Color del botón de cancelar
                preConfirm: () => {
                    const name = document.getElementById('edit-name').value;
                    const email = document.getElementById('edit-email').value;

                    if (!name || !email) {
                        Swal.showValidationMessage('Todos los campos son obligatorios');
                    }
                    return { name, email };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const { name, email } = result.value;
                    row.querySelector('td:nth-child(1)').textContent = name;
                    row.querySelector('td:nth-child(2)').textContent = email;
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: 'El usuario ha sido actualizado.',
                        icon: 'success',
                        confirmButtonColor: '#003366' // Color del botón en el mensaje de éxito
                    });
                }
            });
        }
    });
});
