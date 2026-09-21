document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    function alerta(icon, title, text) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            background: '#121212',
            color: '#e2e2e2',
            confirmButtonColor: '#f2ca50'
        });
    }

    
    const table = new Tabulator("#tabla-bibliotecarios", {
        data: [], 
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No se encontraron bibliotecarios registrados",
        columns: [
            { title: "ID", field: "id_usuario", width: 10, hozAlign: "center" },
            { title: "Documento", field: "documento" },
            { title: "Nombre y Apellido", field: "nombre_apellido" },
            { title: "Correo Electrónico", field: "correo_institucional" },
            { title: "Fecha de Alta", field: "fecha_registro", hozAlign: "center" },
            { 
                title: "Acciones", 
                hozAlign: "center",
                formatter: function() {
                    return `<button class="btn-danger-sm action-delete-btn" type="button">
                                <span class="material-symbols-outlined" style="font-size:16px;">delete</span> Eliminar
                            </button>`;
                },
                cellClick: function(e, cell) {
                    if (e.target.closest('.action-delete-btn')) {
                        const rowData = cell.getRow().getData();
                        confirmarEliminacion(rowData.id_usuario, rowData.nombre_apellido);
                    }
                }
            }
        ]
    });

    function cargarBibliotecarios() {

        const formData = new FormData();
        formData.append('action', 'listar');
        formData.append('csrf_token', window.getCSRFToken());

        fetch('../../backend/admin/api_bibliotecarios.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                
                table.setData(data);
            })
            .catch(error => {
                console.error('Error al cargar la lista:', error);
                alerta('error', 'Error de Conexión', 'No se pudieron cargar los bibliotecarios.');
            });
    }

    
    cargarBibliotecarios();

    // --- 2. Controles de la Modal ---
    const modal = document.getElementById('modalRegistro');
    const btnAbrir = document.getElementById('btnAbrirModal');
    const btnCerrar = document.getElementById('btnCerrarModal');

    btnAbrir.addEventListener('click', () => {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    btnCerrar.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    // --- 3. Registrar Bibliotecario ---
    const form = document.getElementById('formBibliotecario');
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const pass = document.getElementById('passInput').value;
        const passConfirm = document.getElementById('passConfirmInput').value;

        if (pass !== passConfirm) {
            alerta('warning', 'Las contraseñas no coinciden', 'Por favor, asegúrate de escribir la misma contraseña en ambos campos.');
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'crear');
        formData.append('csrf_token', getCSRFToken());

        fetch('../../backend/api_bibliotecarios.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alerta('success', 'Registro Exitoso', data.message);
                form.reset();
                closeModal();
                cargarBibliotecarios(); // Actualizamos la tabla manualmente
            } else {
                alerta('error', 'Error de Registro', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alerta('error', 'Error del Servidor', 'No se pudo guardar el registro.');
        });
    });

    // --- 4. Eliminar Bibliotecario (FETCH) ---
    function confirmarEliminacion(id, nombre_apellido) {
        Swal.fire({
            title: '¿Eliminar bibliotecario?',
            text: `Está a punto de remover el acceso a ${nombre_apellido}. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            background: '#121212',
            color: '#e2e2e2',
            iconColor: '#ffb4ab',
            confirmButtonColor: '#ffb4ab',
            cancelButtonColor: 'rgba(255,255,255,0.1)'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'eliminar');
                formData.append('id', id);
                formData.append('csrf_token', getCSRFToken());

                fetch('../../backend/api_bibliotecarios.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alerta('success', 'Eliminado', data.message);
                        cargarBibliotecarios(); // Actualizamos la tabla manualmente
                    } else {
                        alerta('error', 'Error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alerta('error', 'Error de Red', 'No se pudo eliminar el registro.');
                });
            }
        });
    }
});