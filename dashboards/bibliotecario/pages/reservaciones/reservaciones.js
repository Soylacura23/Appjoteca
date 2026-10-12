   document.addEventListener('DOMContentLoaded', () => {

    // Rutas API
    const API_GET  = '../../api/get_reservaciones.php';
    const API_POST = '../../../../backend/controllers/procesar_reservas.php';
    const DEFAULT_COVER = '../../../../assets/images/books/default-cover.jpg';

    // Estado local
    let returnsPage = 1;
    let returnsTotalPages = 1;
    let queuePage = 1;
    let historyTable = null;
    let currentLoanId = null;
    let currentLoanMaxReturns = 1;

    // Helpers
    function coverUrl(path) {
        if (!path) return DEFAULT_COVER;
        if (path.startsWith('http')) return path;
        return '../../../../' + path.replace(/^\//, '');
    }

    function avatarUrl(path) {
        if (!path) return '../../../../assets/images/default-avatar.png';
        if (path.startsWith('http') || path.startsWith('/')) return path;
        return '../../../../' + path.replace(/^\//, '');
    }

    function formatDate(str) {
        if (!str) return '—';
        const d = new Date(str + 'T00:00:00');
        return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function diasLabel(dias) {
        if (dias === null || dias === undefined) return '—';
        if (dias > 1)  return `${dias} días`;
        if (dias === 1) return '1 día';
        if (dias === 0) return 'Hoy';
        return `${Math.abs(dias)} día(s) atrasado`;
    }

    async function getJSON(url) {
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Error de red');
        return res.json();
    }

    async function postForm(data) {
        const body = new FormData();
        Object.entries(data).forEach(([k, v]) => body.append(k, v));
        body.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
        const res = await fetch(API_POST, {
            method: 'POST',
            body,
            credentials: 'same-origin'
        });
        if (!res.ok) throw new Error('Error de red');
        return res.json();
    }

    // Stats
    async function cargarStats() {
        try {
            const json = await getJSON(`${API_GET}?tipo=stats`);
            if (!json.ok) return;
            const d = json.data;
            document.getElementById('stat-nuevas').textContent     = d.nuevas_solicitudes ?? 0;
            document.getElementById('stat-atrasados').textContent = d.items_atrasados ?? 0;
            document.getElementById('stat-vencen-hoy').textContent = String(d.vencen_hoy ?? 0).padStart(2, '0');
            document.getElementById('stat-criticos').textContent  = String(d.items_atrasados ?? 0).padStart(2, '0');
            document.getElementById('count-solicitudes').textContent =
                d.nuevas_solicitudes ? `(${d.nuevas_solicitudes})` : '';
            document.getElementById('count-devoluciones').textContent =
                d.prestamos_activos ? `(${d.prestamos_activos})` : '';
        } catch (e) {
            console.error('Stats:', e);
        }
    }

    // Solicitudes Nuevas (preview, max 6)
    async function cargarSolicitudes() {
        const grid = document.getElementById('requests-grid');
        grid.innerHTML = '<div class="empty-state"><span class="loading-spinner"></span></div>';

        try {
            const json = await getJSON(`${API_GET}?tipo=solicitudes&limit=6&page=1`);
            if (!json.ok) throw new Error(json.error);

            if (!json.data.length) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <span class="material-symbols-outlined">inbox</span>
                        No hay solicitudes nuevas
                    </div>`;
                return;
            }

            grid.innerHTML = '';
            json.data.forEach((req, i) => {
                const card = document.createElement('div');
                card.className = 'request-card';
                card.style.animationDelay = `${i * 80}ms`;
                card.dataset.id = req.id_reserva;

                const badge = escapeHtml([req.coleccion, req.edicion].filter(Boolean).join(' · ') || 'Reserva');
                card.innerHTML = `
                    <div class="request-thumb">
                        <img src="${escapeHtml(coverUrl(req.portada))}" alt="Portada de ${escapeHtml(req.libro)}" loading="lazy"
                             onerror="this.src='${DEFAULT_COVER}'">
                    </div>
                    <div class="request-body">
                        <div class="request-meta">
                            <span class="request-badge">${badge}</span>
                            <h3 class="request-title">${escapeHtml(req.libro)}</h3>
                            <p class="request-author">${escapeHtml(req.autor)}</p>
                            <div class="request-user">
                                <img class="request-user-avatar" src="${escapeHtml(avatarUrl(req.foto_perfil))}" alt="" loading="lazy"
                                     onerror="this.src='../../../../assets/images/default-avatar.png'">
                                <a href="../usuarios/usuarios.php?id=${Number(req.id_usuario)}">${escapeHtml(req.usuario)}</a>
                            </div>
                            <div class="request-extra">
                                <span>📅 ${formatDate(req.fecha_reserva)}</span>
                                <span>📦 Solicita ${Number(req.cantidad)} ${Number(req.cantidad) === 1 ? 'ejemplar' : 'ejemplares'}</span>
                                <span class="request-stock">📚 Disponibles ahora: ${Number(req.stock_disponible) || 0}</span>
                            </div>
                            ${req.observacion ? `<details class="request-reason"><summary>Ver motivo de la solicitud</summary><p>${escapeHtml(req.observacion)}</p></details>` : ''}
                        </div>
                        <div class="request-actions">
                            <button class="btn-approve" data-action="aceptar" data-id="${req.id_reserva}">Aprobar</button>
                            <button class="btn-decline" data-action="rechazar" data-id="${req.id_reserva}">Rechazar</button>
                        </div>
                    </div>`;
                grid.appendChild(card);
            });

            grid.querySelectorAll('.btn-approve, .btn-decline').forEach(btn => {
                btn.addEventListener('click', handleReservaAction);
            });

        } catch (e) {
            console.error('Solicitudes:', e);
            grid.innerHTML = '<div class="empty-state">Error al cargar solicitudes</div>';
        }
    }

    async function abrirSolicitudDesdeUrl() {
        const params = new URLSearchParams(window.location.search);
        const id = Number(params.get('reserva'));
        if (!Number.isInteger(id) || id <= 0 || !window.Swal) return;

        try {
            const json = await getJSON(`${API_GET}?tipo=solicitud&id=${id}`);
            if (!json.ok) throw new Error(json.error || 'No se encontró la solicitud.');
            const req = json.data;
            const result = await Swal.fire({
                icon: 'info',
                title: 'Solicitud pendiente',
                html: `<div class="reservation-preview">
                    <p><strong>${escapeHtml(req.libro)}</strong></p>
                    <p>Solicitante: ${escapeHtml(req.usuario)}</p>
                    <p>Cantidad: ${Number(req.cantidad)} ejemplar${Number(req.cantidad) === 1 ? '' : 'es'}</p>
                    <p>Fecha: ${formatDate(req.fecha_reserva)}</p>
                    ${req.observacion ? `<p>Motivo: ${escapeHtml(req.observacion)}</p>` : ''}
                </div>`,
                showCancelButton: true,
                confirmButtonText: 'Aprobar solicitud',
                cancelButtonText: 'Cerrar',
                confirmButtonColor: '#22c55e',
                background: '#121212',
                color: '#e2e2e2'
            });
            if (result.isConfirmed) {
                const response = await postForm({ accion: 'aceptar', id_reserva: id });
                if (!response.ok) throw new Error(response.error || 'No se pudo aprobar la solicitud.');
                window.alerta('success', 'Solicitud aprobada', 'El préstamo ya está registrado.');
            }
        } catch (error) {
            window.alerta('error', 'No se pudo abrir la solicitud', error.message || 'Inténtalo de nuevo.');
        }
    }

    // Acción aprobar / rechazar
    async function handleReservaAction(e) {
        const btn = e.currentTarget;
        const id  = btn.dataset.id;
        const accion = btn.dataset.action;
        const card = btn.closest('.request-card') || btn.closest('.queue-item');

        btn.disabled = true;
        btn.textContent = '...';

        try {
            const json = await postForm({ accion, id_reserva: id });
            if (!json.ok) throw new Error(json.error || 'Error');

            if (accion === 'aceptar') {
                btn.textContent = 'Aprobado';
                btn.style.background = '#22c55e';
                btn.style.color = '#fff';
            } else {
                btn.textContent = 'Rechazado';
                btn.style.background = '#ef4444';
                btn.style.color = '#fff';
            }

            const siblings = (card || document).querySelectorAll('[data-action]');
            siblings.forEach(b => {
                b.disabled = true;
                b.style.opacity = '0.5';
            });

            if (card) {
                setTimeout(() => {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(40px) scale(0.95)';
                    setTimeout(() => {
                        card.remove();
                        cargarStats();
                        if (historyTable) historyTable.setData();
                    }, 400);
                }, 600);
            } else {
                cargarStats();
                cargarCola(queuePage);
            }

        } catch (err) {
            console.error(err);
            btn.disabled = false;
            btn.textContent = accion === 'aceptar' ? 'Aprobar' : 'Rechazar';
            window.alerta('error', 'No se pudo procesar', err.message || 'Intenta nuevamente.');
        }
    }

    // Cola completa (paginada)
    async function cargarCola(page = 1) {
        const list = document.getElementById('queue-list');
        const pag  = document.getElementById('queue-pagination');
        list.innerHTML = '<div class="empty-state"><span class="loading-spinner"></span></div>';

        try {
            const json = await getJSON(`${API_GET}?tipo=solicitudes&page=${page}&limit=30`);
            if (!json.ok) throw new Error(json.error);

            queuePage = json.page;

            if (!json.data.length) {
                list.innerHTML = '<div class="empty-state">No hay solicitudes en cola</div>';
                pag.innerHTML = '';
                return;
            }

            list.innerHTML = '';
            json.data.forEach(req => {
                const item = document.createElement('div');
                item.className = 'queue-item';
                item.dataset.id = req.id_reserva;
                item.innerHTML = `
                    <div class="queue-item-thumb">
                        <img src="${escapeHtml(coverUrl(req.portada))}" alt="" loading="lazy"
                             onerror="this.src='${DEFAULT_COVER}'">
                    </div>
                    <div class="queue-item-info">
                        <div class="queue-item-title">${escapeHtml(req.libro)}</div>
                        <div class="queue-item-meta">
                            ${escapeHtml(req.autor)} · ${escapeHtml(req.usuario)} · ${formatDate(req.fecha_reserva)} · ${Number(req.cantidad)} ej.
                        </div>
                    </div>
                    <div class="queue-item-actions">
                        <button class="btn-approve" data-action="aceptar" data-id="${req.id_reserva}">Aprobar</button>
                        <button class="btn-decline" data-action="rechazar" data-id="${req.id_reserva}">Rechazar</button>
                    </div>`;
                list.appendChild(item);
            });

            list.querySelectorAll('.btn-approve, .btn-decline').forEach(btn => {
                btn.addEventListener('click', handleReservaAction);
            });

            pag.innerHTML = `
                <button class="queue-page-btn" id="queue-prev" ${page <= 1 ? 'disabled' : ''}>← Anterior</button>
                <span class="queue-page-info">Página ${json.page} de ${json.pages} (${json.total} total)</span>
                <button class="queue-page-btn" id="queue-next" ${page >= json.pages ? 'disabled' : ''}>Siguiente →</button>`;

            document.getElementById('queue-prev')?.addEventListener('click', () => cargarCola(page - 1));
            document.getElementById('queue-next')?.addEventListener('click', () => cargarCola(page + 1));

        } catch (e) {
            console.error('Cola:', e);
            list.innerHTML = '<div class="empty-state">Error al cargar la cola</div>';
        }
    }

    // Toggle cola
    document.getElementById('btn-ver-cola').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('requests-section').style.display = 'none';
        document.getElementById('queue-section').classList.add('visible');
        cargarCola(1);
    });
    document.getElementById('btn-cerrar-cola').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('queue-section').classList.remove('visible');
        document.getElementById('requests-section').style.display = '';
        cargarSolicitudes();
    });

    // Próximas Devoluciones
    async function cargarDevoluciones(page = 1, append = false) {
        const list = document.getElementById('returns-list');
        const moreBtn = document.getElementById('returns-more-btn');

        if (!append) {
            list.innerHTML = '<div class="empty-state"><span class="loading-spinner"></span></div>';
        }

        try {
            const json = await getJSON(`${API_GET}?tipo=devoluciones&page=${page}&limit=5`);
            if (!json.ok) throw new Error(json.error);

            returnsPage = json.page;
            returnsTotalPages = json.pages;

            if (!append && !json.data.length) {
                list.innerHTML = `
                    <div class="empty-state">
                        <span class="material-symbols-outlined">event_available</span>
                        Sin devoluciones próximas
                    </div>`;
                moreBtn.style.display = 'none';
                return;
            }

            if (!append) list.innerHTML = '';

            json.data.forEach(ret => {
                const item = document.createElement('div');
                item.className = 'return-item';
                item.dataset.id = ret.id_prestamo;

                const dias = parseInt(ret.dias_restantes, 10);
                let timeMain, timeLabel;
                if (dias < 0) {
                    timeMain = `${Math.abs(dias)}d atr.`;
                    timeLabel = 'Atrasado';
                } else if (dias === 0) {
                    timeMain = 'Hoy';
                    timeLabel = 'Devolución Esperada';
                } else {
                    timeMain = formatDate(ret.fecha_devolucion_prevista);
                    timeLabel = diasLabel(dias);
                }

                item.innerHTML = `
                    <div class="return-book">
                        <div class="return-thumb">
                            <img src="${escapeHtml(coverUrl(ret.portada))}" alt="" loading="lazy"
                                 onerror="this.src='${DEFAULT_COVER}'">
                        </div>
                        <div class="return-info">
                            <span class="return-title">${escapeHtml(ret.libro)}</span>
                            <span class="return-user">Prestado a: ${escapeHtml(ret.usuario)}</span>
                            <span class="return-copies">${Number(ret.cantidad_devolver) || 1} ejemplar${Number(ret.cantidad_devolver) === 1 ? '' : 'es'}</span>
                        </div>
                    </div>
                    <div class="return-time">
                        <span class="return-time-main">${timeMain}</span>
                        <span class="return-time-label">${timeLabel}</span>
                    </div>
                    <button class="btn-return-sm" data-id="${ret.id_prestamo}" title="Registrar devolución">Devolver</button>`;

                item.addEventListener('click', (ev) => {
                    if (ev.target.closest('.btn-return-sm')) return;
                    abrirOverlayPrestamo(ret.id_prestamo);
                });

                item.querySelector('.btn-return-sm').addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    marcarDevuelto(ret.id_prestamo, item, Number(ret.cantidad_devolver) || 1);
                });

                list.appendChild(item);
            });

            moreBtn.style.display = (page < json.pages) ? 'block' : 'none';

        } catch (e) {
            console.error('Devoluciones:', e);
            if (!append) list.innerHTML = '<div class="empty-state">Error al cargar</div>';
        }
    }

    document.getElementById('returns-more-btn').addEventListener('click', () => {
        cargarDevoluciones(returnsPage + 1, true);
    });

    // Marcar devuelto (desde lista o overlay)
    async function marcarDevuelto(idPrestamo, elementToRemove, maxCantidad = 1) {
        maxCantidad = Math.max(1, Number(maxCantidad) || 1);
        const confirmacion = await Swal.fire({
            icon: 'question',
            title: 'Registrar devolución',
            text: maxCantidad > 1
                ? `Este usuario tiene ${maxCantidad} ejemplares activos o vencidos de este libro.`
                : 'Confirma la devolución de este ejemplar.',
            input: 'number',
            inputLabel: 'Cantidad de ejemplares a devolver',
            inputValue: 1,
            inputAttributes: { min: '1', max: String(maxCantidad), step: '1' },
            showCancelButton: true,
            confirmButtonText: 'Devolver',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#22c55e',
            background: '#121212',
            color: '#e2e2e2',
            customClass: { popup: 'reservations-swal' },
            inputValidator: value => {
                const cantidad = Number(value);
                if (!Number.isInteger(cantidad) || cantidad < 1 || cantidad > maxCantidad) {
                    return `Indica una cantidad entre 1 y ${maxCantidad}.`;
                }
            }
        });
        if (!confirmacion.isConfirmed) return;
        const cantidad = Number(confirmacion.value);

        try {
            const json = await postForm({ accion: 'devolver', id_prestamo: idPrestamo, cantidad });
            if (!json.ok) throw new Error(json.error);

            if (elementToRemove) {
                elementToRemove.style.transition = 'all 0.3s ease';
                elementToRemove.style.opacity = '0';
                setTimeout(() => elementToRemove.remove(), 300);
            }
            cerrarOverlay();
            cargarStats();
            cargarDevoluciones(1);
            if (historyTable) historyTable.setData();
            const readingMessages = [];
            if (json.metas_completadas?.length) {
                readingMessages.push(`¡Felicitaciones! Completaste: ${json.metas_completadas.join(', ')}.`);
            }
            if (json.insignias_nuevas?.length) {
                readingMessages.push(`Insignias nuevas: ${json.insignias_nuevas.join(', ')}.`);
            }
            if (json.progreso_lectura === false) {
                readingMessages.push('El progreso de lectura no se pudo actualizar. Inténtalo más tarde.');
            }
            window.alerta(
                'success',
                'Devolución registrada',
                [
                    `Se ${cantidad === 1 ? 'devolvió 1 ejemplar' : `devolvieron ${cantidad} ejemplares`} correctamente.`,
                    ...readingMessages
                ].join('\n\n')
            );
        } catch (e) {
            window.alerta('error', 'No se pudo registrar la devolución', e.message || 'Intenta nuevamente.');
        }
    }

    // Overlay de préstamo
    async function abrirOverlayPrestamo(id) {
        currentLoanId = id;
        const overlay = document.getElementById('loan-overlay');

        try {
            const json = await getJSON(`${API_GET}?tipo=detalle&id=${id}`);
            if (!json.ok) throw new Error(json.error);
            const d = json.data;

            document.getElementById('loan-cover').src = coverUrl(d.portada);
            document.getElementById('loan-overlay-title').textContent = d.libro;
            document.getElementById('loan-author').textContent = d.autor;
            document.getElementById('loan-user').textContent = d.usuario;
            document.getElementById('loan-ejemplar').textContent =
                `${Number(d.cantidad_devolver) || 1} ejemplar${Number(d.cantidad_devolver) === 1 ? '' : 'es'}`;
            document.getElementById('loan-fecha-prestamo').textContent = formatDate(d.fecha_prestamo);
            document.getElementById('loan-fecha-prevista').textContent = formatDate(d.fecha_devolucion_prevista);
            document.getElementById('loan-estado').textContent = d.estado;
            document.getElementById('loan-dias').textContent = diasLabel(d.dias_restantes);

            const btnDev = document.getElementById('loan-btn-devolver');
            if (d.estado === 'devuelto') {
                btnDev.style.display = 'none';
            } else {
                currentLoanMaxReturns = Number(d.cantidad_devolver) || 1;
                btnDev.style.display = '';
                btnDev.disabled = false;
                btnDev.textContent = 'Devolver';
            }

            overlay.classList.add('open');
            overlay.setAttribute('aria-hidden', 'false');
        } catch (e) {
            console.error(e);
            window.alerta('error', 'No se pudo cargar el detalle', 'Intenta abrir el préstamo nuevamente.');
        }
    }

    function cerrarOverlay() {
        const overlay = document.getElementById('loan-overlay');
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        currentLoanId = null;
    }

    document.getElementById('loan-overlay-close').addEventListener('click', cerrarOverlay);
    document.getElementById('loan-overlay-backdrop').addEventListener('click', cerrarOverlay);
    document.getElementById('loan-btn-devolver').addEventListener('click', () => {
        if (currentLoanId) marcarDevuelto(currentLoanId, null, currentLoanMaxReturns);
    });

    // Historial con Tabulator
    function initHistorial() {
        historyTable = new Tabulator('#history-table', {
            ajaxURL: `${API_GET}?tipo=historial`,
            ajaxParams: { limit: 20 },
            ajaxResponse: (url, params, response) => {
                if (!response.ok) return [];
                return {
                    data: response.data,
                    last_page: response.pages || 1
                };
            },
            pagination: true,
            paginationMode: 'remote',
            paginationSize: 20,
            paginationSizeSelector: [10, 20, 50],
            layout: 'fitColumns',
            responsiveLayout: false,
            placeholder: 'No hay movimientos registrados',
            columns: [
                {
                    title: 'Volumen / Ítem',
                    field: 'libro',
                    minWidth: 180,
                    formatter: (cell) => {
                        const d = cell.getRow().getData();
                        return `<div class="history-item-book">
                            <div class="history-item-icon"><span class="material-symbols-outlined">book_4</span></div>
                            <div>
                                <div class="history-item-title">${escapeHtml(d.libro)}</div>
                                <div class="history-item-id">ID: ${Number(d.id_libro)} · Reserva #${Number(d.id_reserva)} · ${Number(d.cantidad) || 1} ${(Number(d.cantidad) || 1) === 1 ? 'ejemplar' : 'ejemplares'}</div>
                            </div>
                        </div>`;
                    },
                    formatterParams: { html: true }
                },
                {
                    title: 'Usuario',
                    field: 'usuario',
                    minWidth: 120,
                    formatter: (cell) => {
                        const d = cell.getRow().getData();
                        return `<div class="history-item-user">${escapeHtml(d.usuario)}</div>`;
                    }
                },
                {
                    title: 'Tipo de Movimiento',
                    field: 'tipo',
                    minWidth: 140,
                    formatter: (cell) => {
                        const d = cell.getRow().getData();
                        const cls = {
                            return: 'badge-return',
                            loan: 'badge-loan',
                            overdue: 'badge-overdue',
                            extension: 'badge-extension'
                        }[d.tipo] || 'badge-loan';
                        return `<span class="history-badge ${cls}">${escapeHtml(d.tipo_label)}</span>`;
                    }
                },
                {
                    title: 'Fecha',
                    field: 'fecha_prestamo',
                    minWidth: 110,
                    hozAlign: 'right',
                    formatter: (cell) => {
                        const d = cell.getRow().getData();
                        const fecha = d.fecha_devolucion_real || d.fecha_prestamo;
                        return `<span class="history-item-date">${formatDate(fecha)}</span>
                                <span class="history-item-time">${escapeHtml(d.estado)}</span>`;
                    }
                }
            ],
            rowFormatter: (row) => {
                row.getElement().querySelectorAll('.tabulator-cell').forEach((cell, i) => {
                    const labels = ['Volumen', 'Usuario', 'Movimiento', 'Fecha'];
                    if (labels[i]) cell.setAttribute('data-label', labels[i]);
                });
            }
        });
    }

    // Exportar PDF
    document.getElementById('btn-export-pdf').addEventListener('click', async () => {
        try {
            const json = await getJSON(`${API_GET}?tipo=historial&limit=200&page=1`);
            if (!json.ok || !json.data.length) {
                window.alerta('info', 'Sin datos', 'No hay datos para exportar.');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

            doc.setFillColor(20, 20, 20);
            doc.rect(0, 0, 297, 22, 'F');
            doc.setTextColor(201, 168, 76);
            doc.setFontSize(14);
            doc.setFont('helvetica', 'bold');
            doc.text('APPJOTECA — Historial de Movimientos', 14, 14);
            doc.setTextColor(180, 180, 180);
            doc.setFontSize(9);
            doc.text(`Generado: ${new Date().toLocaleString('es-CO')}`, 200, 14);

            const rows = json.data.map(d => [
                d.libro,
                d.usuario,
                d.tipo_label,
                Number(d.cantidad) || 1,
                formatDate(d.fecha_prestamo),
                d.fecha_devolucion_real ? formatDate(d.fecha_devolucion_real) : '—',
                d.estado
            ]);

            doc.autoTable({
                startY: 28,
                head: [['Libro', 'Usuario', 'Movimiento', 'Ejemplares', 'Préstamo', 'Devolución', 'Estado']],
                body: rows,
                theme: 'grid',
                headStyles: {
                    fillColor: [40, 40, 40],
                    textColor: [201, 168, 76],
                    fontStyle: 'bold',
                    fontSize: 8
                },
                bodyStyles: {
                    fillColor: [30, 30, 30],
                    textColor: [220, 220, 220],
                    fontSize: 8
                },
                alternateRowStyles: { fillColor: [36, 36, 36] },
                margin: { left: 14, right: 14 }
            });

            doc.save(`historial-movimientos-${Date.now()}.pdf`);
        } catch (e) {
            console.error(e);
            window.alerta('error', 'Error al generar el PDF', 'No se pudo generar el documento.');
        }
    });

    // Notificaciones / búsqueda móvil (legado)
    const notifBtn = document.getElementById('notification-tray-btn');
    const notifContainer = document.getElementById('notification-container');
    if (notifBtn && notifContainer) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifContainer.classList.toggle('open');
            notifBtn.setAttribute('aria-expanded', notifContainer.classList.contains('open'));
        });
        document.addEventListener('click', (e) => {
            if (notifContainer.classList.contains('open') &&
                !notifContainer.contains(e.target) &&
                !notifBtn.contains(e.target)) {
                notifContainer.classList.remove('open');
                notifBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Init
    cargarStats();
    cargarSolicitudes();
    cargarDevoluciones(1);
    initHistorial();
    abrirSolicitudDesdeUrl();
    const loanIdFromUrl = Number(new URLSearchParams(window.location.search).get('prestamo'));
    if (Number.isInteger(loanIdFromUrl) && loanIdFromUrl > 0) {
        abrirOverlayPrestamo(loanIdFromUrl);
    }
});