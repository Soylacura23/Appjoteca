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

    async function peticion(action, extra = {}) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('csrf_token', window.getCSRFToken());
        for (const key in extra) {
            formData.append(key, extra[key]);
        }

        const respuesta = await fetch('../../backend/admin/api_bibliotecarios.php', {
            method: 'POST',
            body: formData
        });
        return respuesta.json();
    }

    const tablaBibliotecarios = new Tabulator("#tabla-bibliotecarios", {
        data: [],
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No se encontraron bibliotecarios registrados",
        columns: [
            { title: "ID", field: "id_usuario", width: 80, hozAlign: "center" },
            { title: "Documento", field: "documento" },
            { title: "Nombre y Apellido", field: "nombre_apellido" },
            { title: "Correo Electrónico", field: "correo_institucional" },
            { title: "Fecha de Alta", field: "fecha_registro", hozAlign: "center" },
            {
                title: "Acciones",
                hozAlign: "center",
                formatter: function () {
                    return `<button class="btn-danger-sm action-delete-btn" type="button">
                                <span class="material-symbols-outlined" style="font-size:16px;">delete</span> Eliminar
                            </button>`;
                },
                cellClick: function (e, cell) {
                    if (e.target.closest('.action-delete-btn')) {
                        const rowData = cell.getRow().getData();
                        confirmarEliminacion(rowData.id_usuario, rowData.nombre_apellido);
                    }
                }
            }
        ]
    });

    const tablaPendientes = new Tabulator("#tabla-pendientes", {
        data: [],
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No hay usuarios pendientes por aprobar",
        columns: [
            { title: "ID", field: "id_usuario", width: 70, hozAlign: "center" },
            { title: "Documento", field: "documento" },
            { title: "Nombre y Apellido", field: "nombre_apellido" },
            { title: "Usuario", field: "nombre_usuario" },
            { title: "Correo", field: "correo_institucional" },
            { title: "Rol solicitado", field: "rol_nombre", hozAlign: "center" },
            { title: "Fecha", field: "fecha_registro", hozAlign: "center" },
            {
                title: "Acciones",
                hozAlign: "center",
                width: 220,
                formatter: function () {
                    return `
                        <button class="btn-approve-sm action-approve-btn" type="button">
                            <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span> Aprobar
                        </button>
                        <button class="btn-danger-sm action-reject-btn" type="button">
                            <span class="material-symbols-outlined" style="font-size:16px;">cancel</span> Rechazar
                        </button>`;
                },
                cellClick: function (e, cell) {
                    const rowData = cell.getRow().getData();
                    if (e.target.closest('.action-approve-btn')) {
                        confirmarAprobacion(rowData.id_usuario, rowData.nombre_apellido);
                    }
                    if (e.target.closest('.action-reject-btn')) {
                        confirmarRechazo(rowData.id_usuario, rowData.nombre_apellido);
                    }
                }
            }
        ]
    });

    async function cargarBibliotecarios() {
        try {
            const data = await peticion('listar');
            tablaBibliotecarios.setData(data);
        } catch (error) {
            console.error('Error al cargar bibliotecarios:', error);
            alerta('error', 'Error de Conexión', 'No se pudieron cargar los bibliotecarios.');
        }
    }

    async function cargarPendientes() {
        try {
            const data = await peticion('listar_pendientes');
            tablaPendientes.setData(data);
            actualizarBadgePendientes(data.length);
        } catch (error) {
            console.error('Error al cargar pendientes:', error);
            alerta('error', 'Error de Conexión', 'No se pudieron cargar los usuarios pendientes.');
        }
    }

    async function refrescarTodo() {
        await Promise.all([cargarBibliotecarios(), cargarPendientes()]);
    }

    function actualizarBadgePendientes(total) {
        const badge = document.getElementById('badge-pendientes');
        if (!badge) return;
        if (total > 0) {
            badge.textContent = total;
            badge.hidden = false;
        } else {
            badge.hidden = true;
        }
    }

    function confirmarAprobacion(id, nombre) {
        Swal.fire({
            title: '¿Aprobar usuario?',
            text: `Se aprobará la cuenta de ${nombre}.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            background: '#121212',
            color: '#e2e2e2',
            confirmButtonColor: '#4caf50',
            cancelButtonColor: 'rgba(255,255,255,0.1)'
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            try {
                const data = await peticion('aprobar', { id: id });
                if (data.status === 'success') {
                    alerta('success', 'Aprobado', data.message);
                    refrescarTodo();
                } else {
                    alerta('error', 'Error', data.message);
                }
            } catch (error) {
                console.error(error);
                alerta('error', 'Error de Red', 'No se pudo aprobar el usuario.');
            }
        });
    }

    function confirmarRechazo(id, nombre) {
        Swal.fire({
            title: '¿Rechazar solicitud?',
            text: `La cuenta de ${nombre} será eliminada permanentemente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, rechazar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            background: '#121212',
            color: '#e2e2e2',
            iconColor: '#ffb4ab',
            confirmButtonColor: '#ffb4ab',
            cancelButtonColor: 'rgba(255,255,255,0.1)'
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            try {
                const data = await peticion('rechazar', { id: id });
                if (data.status === 'success') {
                    alerta('success', 'Rechazado', data.message);
                    refrescarTodo();
                } else {
                    alerta('error', 'Error', data.message);
                }
            } catch (error) {
                console.error(error);
                alerta('error', 'Error de Red', 'No se pudo rechazar el usuario.');
            }
        });
    }

    function confirmarEliminacion(id, nombre) {
        Swal.fire({
            title: '¿Eliminar bibliotecario?',
            text: `Está a punto de remover el acceso a ${nombre}. Esta acción no se puede deshacer.`,
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
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            try {
                const data = await peticion('eliminar', { id: id });
                if (data.status === 'success') {
                    alerta('success', 'Eliminado', data.message);
                    cargarBibliotecarios();
                } else {
                    alerta('error', 'Error', data.message);
                }
            } catch (error) {
                console.error(error);
                alerta('error', 'Error de Red', 'No se pudo eliminar el registro.');
            }
        });
    }

    const tabs = document.querySelectorAll('.admin-tab');
    const panels = document.querySelectorAll('.admin-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;

            tabs.forEach(t => t.classList.toggle('is-active', t === tab));
            panels.forEach(p => p.classList.toggle('is-active', p.dataset.panel === target));

            setTimeout(() => {
                if (target === 'bibliotecarios') tablaBibliotecarios.redraw(true);
                if (target === 'pendientes') tablaPendientes.redraw(true);
            }, 60);
        });
    });

    const modal = document.getElementById('modalRegistro');
    const btnAbrir = document.getElementById('btnAbrirModal');
    const btnCerrar = document.getElementById('btnCerrarModal');

    if (btnAbrir) btnAbrir.addEventListener('click', () => {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (btnCerrar) btnCerrar.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    
    const form = document.getElementById('formBibliotecario');
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const pass = document.getElementById('passInput').value;
        const passConfirm = document.getElementById('passConfirmInput').value;
        const rol = document.getElementById('rolInput').value;

        if (pass !== passConfirm) {
            alerta('warning', 'Las contraseñas no coinciden', 'Verifica que ambas contraseñas sean iguales.');
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'crear');
        formData.append('csrf_token', window.getCSRFToken());
        formData.append('rol', rol);

        try {
            const respuesta = await fetch('../../backend/admin/api_bibliotecarios.php', {
                method: 'POST',
                body: formData
            });
            const data = await respuesta.json();

            if (data.status === 'success') {
                alerta('success', 'Registro Exitoso', data.message);
                form.reset();
                closeModal();
                refrescarTodo();
            } else {
                alerta('error', 'Error de Registro', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alerta('error', 'Error del Servidor', 'No se pudo guardar el registro.');
        }
    });

    refrescarTodo();
});