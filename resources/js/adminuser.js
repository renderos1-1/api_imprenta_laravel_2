document.addEventListener("DOMContentLoaded", function () {
    // Botón "Nuevo Usuario"
    document.querySelector('.add-user-btn').addEventListener('click', function () {
        Swal.fire({
            title: 'Agregar Usuario',
            html: `
                <label>Nombre:</label>
                <input type="text" id="user-name" class="swal2-input" placeholder="Ingresa el nombre (máx. 4 palabras)">
                <br>
                <label>DUI:</label>
                <input type="text" id="user-dui" class="swal2-input" placeholder="Formato: 00000000-0">
                <br>
                <label>Rol:</label>
                <select id="user-role" class="swal2-select">
                    <option value="Administrador">Administrador</option>
                    <option value="Editor">Editor</option>
                </select>
            `,
            confirmButtonText: 'Agregar',
            cancelButtonText: 'Cancelar',
            showCancelButton: true,
            confirmButtonColor: '#003366',
            cancelButtonColor: '#003366',
            preConfirm: () => {
                const name = document.getElementById('user-name').value.trim();
                const dui = document.getElementById('user-dui').value.trim();
                const role = document.getElementById('user-role').value;

                // Validar que el nombre tenga máximo 4 palabras y solo letras
                const nameWords = name.split(/\s+/);
                const nameRegex = /^[A-Za-zÀ-ÿ\u00f1\u00d1\s]+$/; // Letras y espacios
                if (nameWords.length > 4) {
                    Swal.showValidationMessage('El nombre debe tener como máximo 4 palabras');
                    return;
                }
                if (!nameRegex.test(name)) {
                    Swal.showValidationMessage('El nombre solo puede contener letras y espacios');
                    return;
                }

                // Validar el formato del DUI
                const duiRegex = /^\d{8}-\d$/; // Ocho dígitos, guion, un dígito
                if (!duiRegex.test(dui)) {
                    Swal.showValidationMessage('El DUI debe estar en formato 00000000-0');
                    return;
                }

                return { name, dui, role };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { name, dui, role } = result.value;
                const tbody = document.querySelector('.users-table tbody');
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>${name}</td>
                    <td>${dui}</td>
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
                    confirmButtonColor: '#003366'
                });
            }
        });
    });

    // Delegación de eventos para "Editar" y "Eliminar"
    document.querySelector('.users-table tbody').addEventListener('click', function (e) {
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
                confirmButtonColor: '#003366',
                cancelButtonColor: '#003366'
            }).then((result) => {
                if (result.isConfirmed) {
                    const row = button.closest('tr');
                    row.remove();
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El usuario ha sido eliminado.',
                        icon: 'success',
                        confirmButtonColor: '#003366'
                    });
                }
            });
        }

        // Editar usuario
        if (button.classList.contains('edit-btn')) {
            const row = button.closest('tr');
            const currentName = row.querySelector('td:nth-child(1)').textContent;
            const currentDUI = row.querySelector('td:nth-child(2)').textContent;

            Swal.fire({
                title: 'Editar Usuario',
                html: `
                    <label>Nombre:</label>
                    <input type="text" id="edit-name" class="swal2-input" value="${currentName}">
                    <label>DUI:</label>
                    <input type="text" id="edit-dui" class="swal2-input" value="${currentDUI}">
                `,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                showCancelButton: true,
                confirmButtonColor: '#003366',
                cancelButtonColor: '#003366',
                preConfirm: () => {
                    const name = document.getElementById('edit-name').value.trim();
                    const dui = document.getElementById('edit-dui').value.trim();

                    // Validar que el nombre tenga máximo 4 palabras y solo letras
                    const nameWords = name.split(/\s+/);
                    const nameRegex = /^[A-Za-zÀ-ÿ\u00f1\u00d1\s]+$/; // Letras y espacios
                    if (nameWords.length > 4) {
                        Swal.showValidationMessage('El nombre debe tener como máximo 4 palabras');
                        return;
                    }
                    if (!nameRegex.test(name)) {
                        Swal.showValidationMessage('El nombre solo puede contener letras y espacios');
                        return;
                    }

                    // Validar el formato del DUI
                    const duiRegex = /^\d{8}-\d$/;
                    if (!duiRegex.test(dui)) {
                        Swal.showValidationMessage('El DUI debe estar en formato 00000000-0');
                        return;
                    }

                    return { name, dui };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const { name, dui } = result.value;
                    row.querySelector('td:nth-child(1)').textContent = name;
                    row.querySelector('td:nth-child(2)').textContent = dui;
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: 'El usuario ha sido actualizado.',
                        icon: 'success',
                        confirmButtonColor: '#003366'
                    });
                }
            });
        }
    });

    // Función de búsqueda
    const searchInput = document.querySelector('.search-bar');
    searchInput.addEventListener('input', function () {
        const filter = searchInput.value.toLowerCase(); // Convertir el texto a minúsculas
        const rows = document.querySelectorAll('.users-table tbody tr');

        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            const dui = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

            // Mostrar fila si coincide con el filtro en nombre o DUI
            if (name.includes(filter) || dui.includes(filter)) {
                row.style.display = ''; // Mostrar fila
            } else {
                row.style.display = 'none'; // Ocultar fila
            }
        });
    });
});
