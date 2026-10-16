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

const tablaUsuarios = new Tabulator('#tabla-usuarios', {
        index: 'id_usuario',
        data: [],
        layout: 'fitColumns',
        responsiveLayout: 'collapse',
        placeholder: 'No hay usuarios que coincidan con esos filtros',
        rowFormatter: rowFormatterWithLabels(['ID', 'Nombre', 'Usuario', 'Correo', 'Rol', 'Estado', 'Acciones']),
        columns: [
            { title: 'ID', field: 'id_usuario', width: 70, hozAlign: 'center', responsive: 0 },
            { title: 'Nombre', field: 'nombre_apellido', minWidth: 150, formatter: formatoNombreUsuario, cellClick: abrirPerfilDesdeCelda, responsive: 0 },
            { title: 'Usuario', field: 'nombre_usuario', minWidth: 110, responsive: 3 },
            { title: 'Correo', field: 'correo_institucional', minWidth: 170, widthGrow: 2, responsive: 2 },
            { title: 'Rol', field: 'rol', minWidth: 110, responsive: 1 },
            {
                title: 'Estado',
                field: 'estado',
                minWidth: 100,
                formatter: function (cell) {
                    const inactive = cell.getValue() === 'inactivo';
                    return '<span class="directory-status ' + (inactive ? 'is-inactive' : 'is-active') + '">' +
                        (inactive ? 'Inactivo' : 'Activo') + '</span>';
                }
            },
            {
                title: 'Acciones',
                field: 'acciones',
                width: 150,
                hozAlign: 'center',
                formatter: function (cell) {
                    const user = cell.getRow().getData();
                    const isSelf = Number(user.id_usuario) === Number(window.AppUser?.id);
                    const setInactive = user.estado === 'activo';
                    return isSelf ? '<span class="directory-self">Tu cuenta</span>' :
                        '<div class="acciones-btns">' +
                            '<button class="btn-state-sm" data-state="' + (setInactive ? 'inactivo' : '1') + '" type="button" title="' + (setInactive ? 'Desactivar' : 'Reactivar') + '">' +
                                '<span class="material-symbols-outlined">' + (setInactive ? 'pause_circle' : 'play_circle') + '</span>' +
                            '</button>' +
                            '<button class="btn-danger-sm action-user-delete" type="button" title="Eliminar">' +
                                '<span class="material-symbols-outlined">delete</span>' +
                            '</button>' +
                        '</div>';
                },
                cellClick: function (event, cell) {
                    const target = event.target.closest('button');
                    if (!target) return;
                    const user = cell.getRow().getData();
                    if (target.classList.contains('btn-state-sm')) {
                        confirmarCambioEstado(user, target.dataset.state);
                    } else if (target.classList.contains('action-user-delete')) {
                        confirmarEliminarUsuario(user);
                    }
                }
            }
        ]
    });

    let directorioPagina = 1;
    let directorioPaginas = 1;
    let directorioTimer = null;
    let solicitudDirectorio = 0;
    const overlayPerfil = document.getElementById('admin-profile-overlay');

    async function cargarDirectorio(pagina) {
        const numeroSolicitud = ++solicitudDirectorio;
        directorioPagina = Math.max(1, pagina);
        try {
            const data = await peticion('listar_usuarios', {
                pagina: directorioPagina,
                limite: 25,
                busqueda: document.getElementById('directory-search').value.trim(),
                rol: document.getElementById('directory-role').value,
                estado: document.getElementById('directory-status').value
            });
            if (numeroSolicitud !== solicitudDirectorio) return;
            if (data.status !== 'success') throw new Error(data.message || 'No se pudo cargar el directorio.');
            await tablaUsuarios.setData(data.usuarios || []);
            directorioPaginas = data.paginas || 1;
            document.getElementById('directory-page-label').textContent =
                'Página ' + data.pagina + ' de ' + directorioPaginas + ' · ' + data.total + ' usuarios';
            document.getElementById('directory-prev').disabled = directorioPagina <= 1;
            document.getElementById('directory-next').disabled = directorioPagina >= directorioPaginas;
        } catch (error) {
            if (numeroSolicitud !== solicitudDirectorio) return;
            console.error(error);
            alerta('error', 'Error', error.message || 'No se pudo cargar el directorio de usuarios.');
        }
    }

    async function abrirPerfilDeUsuario(id) {
        if (Number(id) === Number(window.AppUser?.id)) {
            window.location.assign('/Appjoteca/pages/settings/configuracion.php');
            return;
        }
        try {
            const data = await peticion('obtener_perfil', { id: id });
            if (data.status !== 'success') throw new Error(data.message || 'No se pudo cargar el perfil.');
            const user = data.perfil;
            overlayPerfil.dataset.userId = user.id_usuario;
            document.getElementById('admin-profile-avatar').src = user.foto_perfil;
            document.getElementById('admin-profile-avatar').alt = 'Foto de ' + (user.nombre_apellido || user.nombre_usuario);
            document.getElementById('admin-profile-name').textContent = user.nombre_apellido || 'Usuario';
            document.getElementById('admin-profile-username').textContent = '@' + (user.nombre_usuario || '');
            document.getElementById('admin-profile-role').textContent = user.rol || 'Usuario';
            document.getElementById('admin-profile-bio').textContent = user.biografia || 'Sin biografía registrada.';
            document.getElementById('admin-profile-email').textContent = user.correo_institucional || '—';
            document.getElementById('admin-profile-document').textContent = user.documento || '—';
            document.getElementById('admin-profile-status').textContent =
                user.estado === '1' ? 'Activo' :
                    (user.estado === '0' ? 'Inactivo' : user.estado.charAt(0).toUpperCase() + user.estado.slice(1));
            document.getElementById('admin-profile-date').textContent = user.fecha_registro || '—';
            document.getElementById('admin-profile-reservation').innerHTML = user.id_reserva_pendiente
                ? 'Solicitud #' + Number(user.id_reserva_pendiente)
                : 'Sin reservas pendientes';
            document.getElementById('admin-profile-loan').innerHTML = user.id_prestamo_activo
                ? 'Préstamo #' + Number(user.id_prestamo_activo)
                : 'Sin préstamos activos';

            const documentPreview = document.getElementById('admin-profile-document-preview');
            const documentImage = document.getElementById('admin-profile-document-image');
            const documentPdf = document.getElementById('admin-profile-document-pdf');
            const documentLink = document.getElementById('admin-profile-document-link');
            documentImage.hidden = true;
            documentPdf.hidden = true;
            documentLink.hidden = !user.foto_documento;
            documentPreview.hidden = !user.foto_documento;
            if (user.foto_documento) {
                documentLink.href = user.foto_documento;
                if (/\.pdf(?:$|[?#])/i.test(user.foto_documento)) {
                    documentPdf.src = user.foto_documento;
                    documentPdf.hidden = false;
                } else {
                    documentImage.src = user.foto_documento;
                    documentImage.hidden = false;
                }
            } else {
                documentImage.removeAttribute('src');
                documentPdf.removeAttribute('src');
            }
            const requestLink = document.getElementById('admin-profile-request');
            if (user.id_solicitud_pendiente) {
                requestLink.href = 'index.php?tab=solicitudes&focus_solicitud=' + encodeURIComponent(user.id_solicitud_pendiente);
                requestLink.textContent = 'Ver solicitud de cambio pendiente';
                requestLink.hidden = false;
            } else {
                requestLink.hidden = true;
            }

            const isActionable = ['activo', 'inactivo', '1'].includes(user.estado);
            const isInactive = user.estado === 'inactivo';
            const stateButton = document.getElementById('admin-profile-state');
            stateButton.hidden = !isActionable;
            stateButton.dataset.state = isInactive ? '1' : 'inactivo';
            stateButton.innerHTML = '<span class="material-symbols-outlined">' +
                (isInactive ? 'play_circle' : 'pause_circle') + '</span> ' +
                (isInactive ? 'Reactivar cuenta' : 'Desactivar cuenta');
            document.getElementById('admin-profile-delete').hidden = !isActionable;
            overlayPerfil.classList.add('is-open');
            overlayPerfil.hidden = false;
            document.body.style.overflow = 'hidden';
        } catch (error) {
            console.error(error);
            alerta('error', 'Error', error.message || 'No se pudo cargar el perfil del usuario.');
        }
    }

    function cerrarPerfil() {
        overlayPerfil.classList.remove('is-open');
        overlayPerfil.hidden = true;
        document.body.style.overflow = '';
    }

    async function confirmarCambioEstado(user, estado) {
        const reactiva = estado === '1';
        const result = await Swal.fire({
            title: reactiva ? '¿Reactivar cuenta?' : '¿Desactivar cuenta?',
            text: (reactiva ? 'Se habilitará el acceso de ' : 'Se bloqueará el acceso de ') + user.nombre_apellido + '.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: reactiva ? 'Reactivar' : 'Desactivar',
            cancelButtonText: 'Cancelar',
            background: '#121212',
            color: '#e2e2e2',
            confirmButtonColor: reactiva ? '#4caf50' : '#f2ca50'
        });
        if (!result.isConfirmed) return;
        try {
            const data = await peticion('cambiar_estado_usuario', { id: user.id_usuario, estado: estado });
            if (data.status !== 'success') throw new Error(data.message || 'No se pudo cambiar el estado.');
            await cargarDirectorio(directorioPagina);
            if (!overlayPerfil.hidden) await abrirPerfilDeUsuario(user.id_usuario);
        } catch (error) {
            alerta('error', 'No se pudo cambiar el estado', error.message);
        }
    }

    async function confirmarEliminarUsuario(user) {
        const result = await Swal.fire({
            title: '¿Eliminar esta cuenta?',
            text: 'La cuenta de ' + user.nombre_apellido + ' dejará de estar disponible. Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Eliminar cuenta',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            background: '#121212',
            color: '#e2e2e2',
            confirmButtonColor: '#b95757'
        });
        if (!result.isConfirmed) return;
        try {
            const data = await peticion('eliminar_usuario', { id: user.id_usuario });
            if (data.status !== 'success') throw new Error(data.message || 'No se pudo eliminar la cuenta.');
            cerrarPerfil();
            await cargarDirectorio(directorioPagina);
            alerta('success', 'Cuenta eliminada', data.message);
        } catch (error) {
            alerta('error', 'No se pudo eliminar', error.message);
        }
    }
    function getToken() {
        return window.getCSRFToken
            ? window.getCSRFToken()
            : (document.querySelector('meta[name="csrf-token"]')?.content || '');
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
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

    function rowFormatterWithLabels(labels) {
        const labelsByField = {
            id_usuario: 'ID',
            id_solicitud: 'ID',
            documento: 'Documento',
            nombre_apellido: 'Nombre',
            correo_institucional: 'Correo',
            fecha_registro: 'Fecha',
            nombre_usuario: 'Usuario',
            rol_nombre: 'Rol',
            tipo: 'Tipo',
            valor_actual: 'Valor actual',
            valor_nuevo: 'Valor nuevo',
            fecha_solicitud: 'Fecha',
            acciones: 'Acciones'
        };
        return function (row) {
            row.getCells().forEach(function (cell, index) {
                const element = cell.getElement();
                const field = cell.getColumn().getField();
                const label = labelsByField[field] || labels[index];
                if (label && !element.classList.contains('tabulator-responsive-collapse-toggle')) {
                    element.setAttribute('data-label', label);
                }
            });
        };
    }

    let mouseDownX = 0;
    let mouseDownY = 0;

    function abrirPerfilDesdeCelda(event, cell) {
        const dragDistance = Math.sqrt(
            Math.pow(event.clientX - mouseDownX, 2) +
            Math.pow(event.clientY - mouseDownY, 2)
        );

        if (dragDistance > 5) {
            return;
        }

        abrirPerfilDeUsuario(cell.getRow().getData().id_usuario);
    }

    function formatoNombreUsuario(cell) {
        return escapeHtml(cell.getValue() || 'Sin nombre');
    }

    document.addEventListener('mousedown', function(e) {
        mouseDownX = e.clientX;
        mouseDownY = e.clientY;
    });

    // 1. Tabla Bibliotecarios
    const tablaBibliotecarios = new Tabulator('#tabla-bibliotecarios', {
        index: 'id_usuario',
        data: [],
        layout: 'fitColumns',
        responsiveLayout: 'collapse',
        responsiveLayoutCollapseStartOpen: false,
        placeholder: 'No se encontraron bibliotecarios registrados',
        rowFormatter: rowFormatterWithLabels(['ID', 'Documento', 'Nombre', 'Correo', 'Alta', 'Acciones']),
        columns: [
            { title: 'ID', field: 'id_usuario', width: 70, minWidth: 60, hozAlign: 'center', responsive: 0 },
            { title: 'Documento', field: 'documento', minWidth: 110, responsive: 3 },
            { title: 'Nombre y Apellido', field: 'nombre_apellido', minWidth: 130, responsive: 0, formatter: formatoNombreUsuario, cellClick: abrirPerfilDesdeCelda },
            { title: 'Correo', field: 'correo_institucional', minWidth: 150, widthGrow: 2, responsive: 1,
                tooltip: true },
            { title: 'Fecha de Alta', field: 'fecha_registro', minWidth: 100, hozAlign: 'center', responsive: 2 },
            {
                title: 'Acciones',
                field: 'acciones',
                hozAlign: 'center',
                width: 110,
                minWidth: 90,
                responsive: 0,
                formatter: function () {
                    return `<div class="acciones-btns">
                        <button class="btn-danger-sm action-delete-btn" type="button">
                            <span class="material-symbols-outlined">delete</span>
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

    // 2. Tabla Cuentas Pendientes (signup)
    const tablaPendientes = new Tabulator('#tabla-pendientes', {
        index: 'id_usuario',
        data: [],
        layout: 'fitColumns',
        responsiveLayout: 'collapse',
        responsiveLayoutCollapseStartOpen: false,
        placeholder: 'No hay usuarios pendientes por aprobar',
        rowFormatter: rowFormatterWithLabels(['ID', 'Documento', 'Nombre', 'Usuario', 'Correo', 'Rol', 'Fecha', 'Acciones']),
        columns: [
            { title: 'ID', field: 'id_usuario', width: 70, minWidth: 60, hozAlign: 'center', responsive: 0 },
            { title: 'Documento', field: 'documento', minWidth: 110, responsive: 4 },
            { title: 'Nombre', field: 'nombre_apellido', minWidth: 130, responsive: 0, formatter: formatoNombreUsuario, cellClick: abrirPerfilDesdeCelda },
            { title: 'Usuario', field: 'nombre_usuario', minWidth: 110, responsive: 2 },
            { title: 'Correo', field: 'correo_institucional', minWidth: 155, responsive: 3 },
            { title: 'Rol', field: 'rol_nombre', minWidth: 95, hozAlign: 'center', responsive: 1 },
            { title: 'Fecha', field: 'fecha_registro', minWidth: 100, hozAlign: 'center', responsive: 5 },
            {
                title: 'Acciones',
                field: 'acciones',
                hozAlign: 'center',
                width: 155,
                minWidth: 120,
                responsive: 0,
                formatter: function () {
                    return `<div class="acciones-btns">
                        <button class="btn-approve-sm action-approve-btn" type="button" aria-label="Aprobar cuenta" title="Aprobar">
                            <span class="material-symbols-outlined">check_circle</span>
                        </button>
                        <button class="btn-danger-sm action-reject-btn" type="button" aria-label="Rechazar cuenta" title="Rechazar">
                            <span class="material-symbols-outlined">cancel</span>
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

    // 3. Tabla Solicitudes de Cambio
    const tablaSolicitudes = new Tabulator('#tabla-solicitudes', {
        index: 'id_solicitud',
        data: [],
        layout: 'fitColumns',
        responsiveLayout: 'collapse',
        responsiveLayoutCollapseStartOpen: false,
        placeholder: 'No hay solicitudes de cambio pendientes',
        rowFormatter: rowFormatterWithLabels(['ID', 'Usuario', 'Nombre', 'Tipo', 'Valor actual', 'Valor nuevo', 'Fecha', 'Acciones']),
        columns: [
            { title: 'ID', field: 'id_solicitud', width: 70, minWidth: 60, hozAlign: 'center', responsive: 0 },
            { title: 'Usuario', field: 'nombre_usuario', minWidth: 110, responsive: 3 },
            { title: 'Nombre', field: 'nombre_apellido', minWidth: 130, responsive: 1, formatter: formatoNombreUsuario, cellDblClick: abrirPerfilDesdeCelda },
            {
                title: 'Tipo',
                field: 'tipo',
                hozAlign: 'center',
                width: 110,
                minWidth: 90,
                responsive: 0,
                formatter: function (cell) {
                    const t = cell.getValue() || '';
                    return '<span class="tipo-badge ' + t + '">' + t + '</span>';
                }
            },
            { title: 'Valor actual', field: 'valor_actual', minWidth: 120, responsive: 4 },
            { title: 'Valor nuevo', field: 'valor_nuevo', minWidth: 130, responsive: 3 },
            { title: 'Fecha', field: 'fecha_solicitud', minWidth: 110, hozAlign: 'center', responsive: 5 },
            {
                title: 'Acciones',
                field: 'acciones',
                hozAlign: 'center',
                width: 115,
                minWidth: 95,
                responsive: 0,
                formatter: function () {
                    return `<div class="acciones-btns">
                        <button class="btn-review-sm action-sol-revisar" type="button" aria-label="Revisar solicitud" title="Revisar">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>`;
                },
                cellClick: function (e, cell) {
                    const row = cell.getRow().getData();
                    if (e.target.closest('.action-sol-revisar')) {
                        revisarSolicitud(row);
                    }
                }
            }
        ]
    });

    // 4. Cargar datos
    async function cargarBibliotecarios() {
        try {
            const data = await peticion('listar');
            await tablaBibliotecarios.setData(Array.isArray(data) ? data : []);
        } catch (err) {
            console.error(err);
            alerta('error', 'Error', 'No se pudieron cargar los bibliotecarios.');
        }
    }

    async function cargarPendientes() {
        try {
            const data = await peticion('listar_pendientes');
            const lista = Array.isArray(data) ? data : [];
            await tablaPendientes.setData(lista);
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
            await tablaSolicitudes.setData(lista);
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
            cargarSolicitudes(),
            cargarDirectorio(1)
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

    // 5. Confirmar acciones (cuentas)
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

    // 6. Revisar solicitudes de cambio
    async function revisarSolicitud(row) {
        const archivo = row.archivo_nuevo ? '/Appjoteca/' + String(row.archivo_nuevo).replace(/^\/+/, '') : '';
        let vistaArchivo = '<p class="request-file-empty">No se adjuntó un documento.</p>';
        if (archivo && row.archivo_mime === 'application/pdf') {
            vistaArchivo = `<iframe class="request-document-preview" src="${escapeHtml(archivo)}" title="Documento adjunto en PDF"></iframe>`;
        } else if (archivo && String(row.archivo_mime || '').startsWith('image/')) {
            vistaArchivo = `<img class="request-document-image" src="${escapeHtml(archivo)}" alt="Documento adjunto">`;
        }

        const result = await Swal.fire({
            title: 'Revisar solicitud',
            html: `<div class="request-review">
                <p><strong>${escapeHtml(row.nombre_apellido || row.nombre_usuario)}</strong> · ${escapeHtml(row.tipo)}</p>
                <dl>
                    <div><dt>Valor actual</dt><dd>${escapeHtml(row.valor_actual || '—')}</dd></div>
                    <div><dt>Valor solicitado</dt><dd>${escapeHtml(row.valor_nuevo || '—')}</dd></div>
                    <div><dt>Fecha</dt><dd>${escapeHtml(row.fecha_solicitud || '—')}</dd></div>
                </dl>
                ${vistaArchivo}
            </div>`,
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Aprobar cambio',
            denyButtonText: 'Rechazar',
            cancelButtonText: 'Cerrar',
            reverseButtons: true,
            background: '#121212',
            color: '#e2e2e2',
            confirmButtonColor: '#42a17d',
            denyButtonColor: '#b95757',
            cancelButtonColor: 'rgba(255,255,255,0.1)'
        });

        if (!result.isConfirmed && !result.isDenied) return;
        let decision = result.isConfirmed ? 'aprobar' : 'rechazar';
        let comentario = '';
        {
            const reason = await Swal.fire({
                title: decision === 'aprobar' ? 'Aprobar solicitud' : 'Rechazar solicitud',
                input: 'text',
                inputLabel: 'Comentario para el usuario (opcional)',
                inputPlaceholder: 'Explica el resultado de la solicitud',
                showCancelButton: true,
                confirmButtonText: decision === 'aprobar' ? 'Confirmar aprobación' : 'Confirmar rechazo',
                cancelButtonText: 'Volver',
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#b95757',
                cancelButtonColor: 'rgba(255,255,255,0.1)'
            });
            if (!reason.isConfirmed) return;
            comentario = reason.value || '';
        }

        try {
            const data = await peticion('resolver_solicitud', {
                id_solicitud: row.id_solicitud,
                decision: decision,
                comentario: comentario
            });
            if (data.status === 'success') {
                alerta('success', decision === 'aprobar' ? 'Aprobada' : 'Rechazada', data.message);
                cargarSolicitudes();
            } else {
                alerta('error', 'Error', data.message || 'No se pudo procesar');
            }
        } catch (err) {
            console.error(err);
            alerta('error', 'Error de red', 'No se pudo procesar la solicitud.');
        }
    }

    // 7. Tabs
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
                if (target === 'usuarios') tablaUsuarios.redraw(true);
            }, 60);
        });
    });

    document.getElementById('directory-search').addEventListener('input', function () {
        window.clearTimeout(directorioTimer);
        directorioTimer = window.setTimeout(function () { cargarDirectorio(1); }, 300);
    });
    document.getElementById('directory-role').addEventListener('change', function () { cargarDirectorio(1); });
    document.getElementById('directory-status').addEventListener('change', function () { cargarDirectorio(1); });
    document.getElementById('directory-prev').addEventListener('click', function () { cargarDirectorio(directorioPagina - 1); });
    document.getElementById('directory-next').addEventListener('click', function () { cargarDirectorio(directorioPagina + 1); });

    document.getElementById('admin-profile-close').addEventListener('click', cerrarPerfil);
    document.getElementById('admin-profile-backdrop').addEventListener('click', cerrarPerfil);
    document.getElementById('admin-profile-state').addEventListener('click', function () {
        const id = Number(overlayPerfil.dataset.userId);
        const state = this.dataset.state;
        const row = { id_usuario: id, nombre_apellido: document.getElementById('admin-profile-name').textContent };
        confirmarCambioEstado(row, state);
    });
    document.getElementById('admin-profile-delete').addEventListener('click', function () {
        const id = Number(overlayPerfil.dataset.userId);
        confirmarEliminarUsuario({
            id_usuario: id,
            nombre_apellido: document.getElementById('admin-profile-name').textContent
        });
    });

    const parametrosIniciales = new URLSearchParams(window.location.search);
    const tabInicial = parametrosIniciales.get('tab')
        || (parametrosIniciales.has('focus_solicitud') ? 'solicitudes' : null)
        || (parametrosIniciales.has('focus_usuario') ? 'pendientes' : null)
        || (parametrosIniciales.has('focus_user') ? 'usuarios' : null)
        || (parametrosIniciales.has('focus_bibliotecario') ? 'bibliotecarios' : null);
    if (tabInicial) {
        const tabPendiente = Array.from(tabs).find(function (tab) {
            return tab.dataset.tab === tabInicial;
        });
        if (tabPendiente) tabPendiente.click();
    }

    function enfocarFila(table, id) {
        const row = table.getRow(id);
        if (!row || !row.getElement()) return null;
        const element = row.getElement();
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        element.classList.add('notification-focus');
        window.setTimeout(function () {
            element.classList.remove('notification-focus');
        }, 4500);
        return row.getData();
    }

    async function enfocarDestinoNotificacion() {
        const params = new URLSearchParams(window.location.search);
        const idBibliotecario = Number(params.get('focus_bibliotecario'));
        const idPendiente = Number(params.get('focus_usuario'));
        const idSolicitud = Number(params.get('focus_solicitud'));
        if (Number.isInteger(idBibliotecario) && idBibliotecario > 0) {
            enfocarFila(tablaBibliotecarios, idBibliotecario);
        }
        if (Number.isInteger(idPendiente) && idPendiente > 0) {
            enfocarFila(tablaPendientes, idPendiente);
        }
        if (Number.isInteger(idSolicitud) && idSolicitud > 0) {
            const row = enfocarFila(tablaSolicitudes, idSolicitud);
            if (row) await revisarSolicitud(row);
        }
        const idPerfil = Number(params.get('focus_user'));
        if (Number.isInteger(idPerfil) && idPerfil > 0) {
            await abrirPerfilDeUsuario(idPerfil);
        }
    }

    // 8. Modal crear usuario
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
    refrescarTodo().then(enfocarDestinoNotificacion);
});