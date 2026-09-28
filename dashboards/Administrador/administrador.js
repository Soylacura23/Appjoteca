document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ── Helpers ──
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

    function getToken() {
        return window.getCSRFToken
            ? window.getCSRFToken()
            : (document.querySelector('meta[name="csrf-token"]')?.content || '');
    }

    async function peticion(action, extra) {
        extra = extra || {};
        const formData = new FormData();
        formData.append('action', action);
        formData.append('csrf_token', getToken());

        for (const key in extra) {
            formData.append(key, extra[key]);
        }

        const respuesta = await fetch('../../backend/admin/api_bibliotecarios.php', {
            method: 'POST',
            body: formData
        });
        return respuesta.json();
    }

    // ════════════════════════════════════
    // 1. Tabla Bibliotecarios
    // ════════════════════════════════════
    const tablaBibliotecarios = new Tabulator('#tabla-bibliotecarios', {
        data: [],
        layout: 'fitColumns',
        responsiveLayout: 'collapse',
        placeholder: 'No se encontraron bibliotecarios registrados',
        columns: [
            { title: 'ID', field: 'id_usuario', width: 70, hozAlign: 'center' },
            { title: 'Documento', field: 'documento' },
            { title: 'Nombre y Apellido', field: 'nombre_apellido' },
            { title: 'Correo', field: 'correo_institucional', widthGrow: 2,
                tooltip: true },
            { title: 'Fecha de Alta', field: 'fecha_registro', hozAlign: 'center' },
            {
                title: 'Acciones',
                hozAlign: 'center',
                width: 140,
                minWidth: 120,
                formatter: function () {
                    return `<div class="acciones-btns">
                        <button class="btn-danger-sm action-delete-btn" type="button">
                            <span class="material-symbols-outlined" style="font-size:16px;">delete</span> Eliminar
                        </button>
                    </div>`;
                },
                cellClick: function (e, cell) {
                    if (e.target.closest('.action-delete-btn')) {
                        const row = cell.getRow().getData();
                        confirmarEliminacion(row.id_usuario, row.nombre_apellido);
                    }
                }
            }
        ]
    });

    // ════════════════════════════════════
    // 2. Tabla Cuentas Pendientes (signup)
    // ════════════════════════════════════
    const tablaPendientes = new Tabulator('#tabla-pendientes', {
        data: [],
        layout: 'fitColumns',
        responsiveLayout: 'collapse',
        placeholder: 'No hay usuarios pendientes por aprobar',
        columns: [
            { title: 'ID', field: 'id_usuario', width: 70, hozAlign: 'center' },
            { title: 'Documento', field: 'documento' },
            { title: 'Nombre', field: 'nombre_apellido' },
            { title: 'Usuario', field: 'nombre_usuario' },
            { title: 'Correo', field: 'correo_institucional' },
            { title: 'Rol', field: 'rol_nombre', hozAlign: 'center' },
            { title: 'Fecha', field: 'fecha_registro', hozAlign: 'center' },
            {
                title: 'Acciones',
                hozAlign: 'center',
                width: 250,
                minWidth: 240,
                formatter: function () {
                    return `<div class="acciones-btns">
                        <button class="btn-approve-sm action-approve-btn" type="button">
                            <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span> Aprobar
                        </button>
                        <button class="btn-danger-sm action-reject-btn" type="button">
                            <span class="material-symbols-outlined" style="font-size:16px;">cancel</span> Rechazar
                        </button>
                    </div>`;
                },
                cellClick: function (e, cell) {
                    const row = cell.getRow().getData();
                    if (e.target.closest('.action-approve-btn')) {
                        confirmarAprobacion(row.id_usuario, row.nombre_apellido);
                    }
                    if (e.target.closest('.action-reject-btn')) {
                        confirmarRechazo(row.id_usuario, row.nombre_apellido);
                    }
                }
            }
        ]
    });

    // ════════════════════════════════════
    // 3. Tabla Solicitudes de Cambio
    // ════════════════════════════════════
    const tablaSolicitudes = new Tabulator('#tabla-solicitudes', {
        data: [],
        layout: 'fitColumns',
        responsiveLayout: 'collapse',
        placeholder: 'No hay solicitudes de cambio pendientes',
        columns: [
            { title: 'ID', field: 'id_solicitud', width: 70, hozAlign: 'center' },
            { title: 'Usuario', field: 'nombre_usuario' },
            { title: 'Nombre', field: 'nombre_apellido' },
            {
                title: 'Tipo',
                field: 'tipo',
                hozAlign: 'center',
                width: 120,
                formatter: function (cell) {
                    const t = cell.getValue() || '';
                    return '<span class="tipo-badge ' + t + '">' + t + '</span>';
                }
            },
            { title: 'Valor actual', field: 'valor_actual' },
            { title: 'Valor nuevo', field: 'valor_nuevo' },
            { title: 'Fecha', field: 'fecha_solicitud', hozAlign: 'center' },
            {
                title: 'Acciones',
                hozAlign: 'center',
                width: 250,
                minWidth: 240,
                formatter: function () {
                    return `<div class="acciones-btns">
                        <button class="btn-approve-sm action-sol-aprobar" type="button">
                            <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span> Aprobar
                        </button>
                        <button class="btn-danger-sm action-sol-rechazar" type="button">
                            <span class="material-symbols-outlined" style="font-size:16px;">cancel</span> Rechazar
                        </button>
                    </div>`;
                },
                cellClick: function (e, cell) {
                    const row = cell.getRow().getData();
                    if (e.target.closest('.action-sol-aprobar')) {
                        confirmarSolicitud(row, 'aprobar');
                    }
                    if (e.target.closest('.action-sol-rechazar')) {
                        confirmarSolicitud(row, 'rechazar');
                    }
                }
            }
        ]
    });

    // ════════════════════════════════════
    // 4. Cargar datos
    // ════════════════════════════════════
    async function cargarBibliotecarios() {
        try {
            const data = await peticion('listar');
            tablaBibliotecarios.setData(Array.isArray(data) ? data : []);
        } catch (err) {
            console.error(err);
            alerta('error', 'Error', 'No se pudieron cargar los bibliotecarios.');
        }
    }

    async function cargarPendientes() {
        try {
            const data = await peticion('listar_pendientes');
            const lista = Array.isArray(data) ? data : [];
            tablaPendientes.setData(lista);
            actualizarBadge('badge-pendientes', lista.length);
        } catch (err) {
            console.error(err);
            alerta('error', 'Error', 'No se pudieron cargar las cuentas pendientes.');
        }
    }

    async function cargarSolicitudes() {
        try {
            const data = await peticion('listar_solicitudes');
            const lista = Array.isArray(data) ? data : [];
            tablaSolicitudes.setData(lista);
            actualizarBadge('badge-solicitudes', lista.length);
        } catch (err) {
            console.error(err);
            alerta('error', 'Error', 'No se pudieron cargar las solicitudes de cambio.');
        }
    }

    async function refrescarTodo() {
        await Promise.all([
            cargarBibliotecarios(),
            cargarPendientes(),
            cargarSolicitudes()
        ]);
    }

    function actualizarBadge(id, total) {
        const badge = document.getElementById(id);
        if (!badge) return;
        if (total > 0) {
            badge.textContent = total;
            badge.hidden = false;
        } else {
            badge.hidden = true;
        }
    }

    // ════════════════════════════════════
    // 5. Confirmar acciones (cuentas)
    // ════════════════════════════════════
    function confirmarAprobacion(id, nombre) {
        Swal.fire({
            title: '¿Aprobar usuario?',
            text: 'Se aprobará la cuenta de ' + nombre + '.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            background: '#121212',
            color: '#e2e2e2',
            confirmButtonColor: '#4caf50',
            cancelButtonColor: 'rgba(255,255,255,0.1)'
        }).then(async function (result) {
            if (!result.isConfirmed) return;
            try {
                const data = await peticion('aprobar', { id: id });
                if (data.status === 'success') {
                    alerta('success', 'Aprobado', data.message);
                    refrescarTodo();
                } else {
                    alerta('error', 'Error', data.message);
                }
            } catch (err) {
                console.error(err);
                alerta('error', 'Error de red', 'No se pudo aprobar el usuario.');
            }
        });
    }

    function confirmarRechazo(id, nombre) {
        Swal.fire({
            title: '¿Rechazar solicitud?',
            text: 'La cuenta de ' + nombre + ' será eliminada permanentemente.',
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
        }).then(async function (result) {
            if (!result.isConfirmed) return;
            try {
                const data = await peticion('rechazar', { id: id });
                if (data.status === 'success') {
                    alerta('success', 'Rechazado', data.message);
                    refrescarTodo();
                } else {
                    alerta('error', 'Error', data.message);
                }
            } catch (err) {
                console.error(err);
                alerta('error', 'Error de red', 'No se pudo rechazar el usuario.');
            }
        });
    }

    function confirmarEliminacion(id, nombre) {
        Swal.fire({
            title: '¿Eliminar bibliotecario?',
            text: 'Está a punto de remover el acceso a ' + nombre + '. Esta acción no se puede deshacer.',
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
        }).then(async function (result) {
            if (!result.isConfirmed) return;
            try {
                const data = await peticion('eliminar', { id: id });
                if (data.status === 'success') {
                    alerta('success', 'Eliminado', data.message);
                    cargarBibliotecarios();
                } else {
                    alerta('error', 'Error', data.message);
                }
            } catch (err) {
                console.error(err);
                alerta('error', 'Error de red', 'No se pudo eliminar.');
            }
        });
    }

    // ════════════════════════════════════
    // 6. Confirmar solicitudes de cambio
    // ════════════════════════════════════
    function confirmarSolicitud(row, accion) {
        const esAprobar = accion === 'aprobar';
        const titulo = esAprobar ? '¿Aprobar solicitud?' : '¿Rechazar solicitud?';
        const texto = esAprobar
            ? 'Se aplicará el cambio de "' + row.tipo + '" para ' + (row.nombre_apellido || row.nombre_usuario) + '.'
            : 'Se rechazará la solicitud de "' + row.tipo + '" sin aplicar cambios.';

        Swal.fire({
            title: titulo,
            text: texto,
            icon: esAprobar ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: esAprobar ? 'Sí, aprobar' : 'Sí, rechazar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            background: '#121212',
            color: '#e2e2e2',
            iconColor: esAprobar ? undefined : '#ffb4ab',
            confirmButtonColor: esAprobar ? '#4caf50' : '#ffb4ab',
            cancelButtonColor: 'rgba(255,255,255,0.1)',
            input: esAprobar ? undefined : 'text',
            inputPlaceholder: esAprobar ? undefined : 'Comentario opcional...',
            inputLabel: esAprobar ? undefined : 'Motivo del rechazo (opcional)'
        }).then(async function (result) {
            if (!result.isConfirmed) return;

            try {
                const extra = {
                    id_solicitud: row.id_solicitud,
                    decision: accion
                };
                if (!esAprobar && result.value) {
                    extra.comentario = result.value;
                }

                const data = await peticion('resolver_solicitud', extra);

                if (data.status === 'success') {
                    alerta('success', esAprobar ? 'Aprobada' : 'Rechazada', data.message);
                    cargarSolicitudes();
                } else {
                    alerta('error', 'Error', data.message || 'No se pudo procesar');
                }
            } catch (err) {
                console.error(err);
                alerta('error', 'Error de red', 'No se pudo procesar la solicitud.');
            }
        });
    }

    // ════════════════════════════════════
    // 7. Tabs
    // ════════════════════════════════════
    const tabs = document.querySelectorAll('.admin-tab');
    const panels = document.querySelectorAll('.admin-panel');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const target = this.dataset.tab;

            tabs.forEach(function (t) {
                t.classList.toggle('is-active', t === tab);
            });
            panels.forEach(function (p) {
                p.classList.toggle('is-active', p.dataset.panel === target);
            });

            setTimeout(function () {
                if (target === 'bibliotecarios') tablaBibliotecarios.redraw(true);
                if (target === 'pendientes') tablaPendientes.redraw(true);
                if (target === 'solicitudes') tablaSolicitudes.redraw(true);
            }, 60);
        });
    });

    // ════════════════════════════════════
    // 8. Modal crear usuario
    // ════════════════════════════════════
    const modal = document.getElementById('modalRegistro');
    const btnAbrir = document.getElementById('btnAbrirModal');
    const btnCerrar = document.getElementById('btnCerrarModal');
    const form = document.getElementById('formBibliotecario');

    function openModal() {
        if (!modal) return;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (btnAbrir) btnAbrir.addEventListener('click', openModal);
    if (btnCerrar) btnCerrar.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    }

    // Toggle mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon = this.querySelector('.material-symbols-outlined');
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        });
    });

    // Fortaleza de contraseña
    const passInput = document.getElementById('passInput');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const passConfirm = document.getElementById('passConfirmInput');
    const matchHint = document.getElementById('matchHint');

    if (passInput && strengthBar && strengthText) {
        passInput.addEventListener('input', function () {
            const v = this.value;
            let puntos = 0;
            if (v.length >= 8) puntos++;
            if (/[A-Z]/.test(v)) puntos++;
            if (/[0-9]/.test(v)) puntos++;
            if (/[^A-Za-z0-9]/.test(v)) puntos++;

            if (v.length === 0) {
                strengthBar.className = 'strength-bar';
                strengthText.innerHTML = 'Fortaleza: <em>Débil</em>';
                return;
            }

            let label = 'Débil';
            let color = '#ffb4ab';
            let cls = 'weak';

            if (puntos >= 4) {
                label = 'Fuerte';
                color = '#4ade80';
                cls = 'strong';
            } else if (puntos >= 2) {
                label = 'Media';
                color = '#f2ca50';
                cls = 'medium';
            }

            strengthBar.className = 'strength-bar ' + cls;
            strengthText.innerHTML = 'Fortaleza: <em style="color:' + color + '">' + label + '</em>';
        });
    }

    // Coincidencia de contraseñas
    if (passConfirm && matchHint) {
        passConfirm.addEventListener('input', function () {
            if (!passInput.value || !this.value) {
                matchHint.textContent = '';
                return;
            }
            if (this.value === passInput.value) {
                matchHint.textContent = 'Las contraseñas coinciden';
                matchHint.style.color = '#4ade80';
            } else {
                matchHint.textContent = 'Las contraseñas no coinciden';
                matchHint.style.color = '#ffb4ab';
            }
        });
    }

    // Preview foto documento
    const fotoDocInput = document.getElementById('fotoDocInput');
    const docPreview = document.getElementById('docPhotoPreview');

    if (fotoDocInput && docPreview) {
        fotoDocInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                docPreview.classList.remove('visible');
                docPreview.src = '';
                return;
            }
            docPreview.src = URL.createObjectURL(file);
            docPreview.classList.add('visible');
        });
    }

    // Submit crear usuario
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const pass = document.getElementById('passInput').value;
            const passConf = document.getElementById('passConfirmInput').value;

            if (pass !== passConf) {
                alerta('warning', 'No coinciden', 'Las contraseñas no coinciden.');
                return;
            }

            if (pass.length < 8) {
                alerta('warning', 'Muy corta', 'La contraseña debe tener al menos 8 caracteres.');
                return;
            }

            const formData = new FormData(form);
            formData.append('action', 'crear');
            formData.append('csrf_token', getToken());

            try {
                const respuesta = await fetch('../../backend/admin/api_bibliotecarios.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await respuesta.json();

                if (data.status === 'success') {
                    alerta('success', 'Registro exitoso', data.message);
                    form.reset();
                    if (docPreview) {
                        docPreview.classList.remove('visible');
                        docPreview.src = '';
                    }
                    if (strengthBar) strengthBar.className = 'strength-bar';
                    if (strengthText) strengthText.innerHTML = 'Fortaleza: <em>Débil</em>';
                    if (matchHint) matchHint.textContent = '';
                    closeModal();
                    refrescarTodo();
                } else {
                    alerta('error', 'Error de registro', data.message);
                }
            } catch (err) {
                console.error(err);
                alerta('error', 'Error del servidor', 'No se pudo guardar el registro.');
            }
        });
    }

    // ── Inicio ──
    refrescarTodo();
});