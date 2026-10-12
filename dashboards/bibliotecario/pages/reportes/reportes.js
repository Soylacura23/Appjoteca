/* reportes.js — Página de Reportes · Appjoteca */

   document.addEventListener('DOMContentLoaded', () => {

    // 1. Referencias del DOM
    const skeletonEl  = document.getElementById('reportes-skeleton');
    const contenidoEl = document.getElementById('reportes-contenido');
    const errorEl     = document.getElementById('reportes-error');
    const errorMsg    = document.getElementById('reportes-error-msg');
    const metricsGrid = document.getElementById('metrics-grid');
    const alertsGrid  = document.getElementById('alerts-grid');
    const alertsEmpty = document.getElementById('alerts-empty');
    const alertCountText = document.getElementById('alert-count-text');
    const btnRefrescar  = document.getElementById('btn-refrescar');
    const btnReintentar = document.getElementById('btn-reintentar');

    const API_URL = '../../api/get_reportes.php';
    let chartInstance = null;
    let periodoActual = '30';

    // Portada por defecto si no hay imagen
    const PORTADA_DEFAULT = '../../../../shared/images/logo-appjoteca.svg';

    // Etiquetas legibles por período
    const etiquetasPeriodo = {
        '7':   'Últimos 7 días por día de la semana',
        '30':  'Últimos 30 días por día de la semana',
        '90':  'Últimos 90 días por día de la semana',
        '365': 'Último año por día de la semana',
        'todo':'Todo el historial por día de la semana'
    };

    // 2. Funciones de UI

    function mostrarCarga() {
        skeletonEl.hidden = false;
        contenidoEl.hidden = true;
        errorEl.hidden = true;
    }

    function mostrarContenido() {
        skeletonEl.hidden = true;
        contenidoEl.hidden = false;
        errorEl.hidden = true;
    }

    function mostrarError(mensaje) {
        skeletonEl.hidden = true;
        contenidoEl.hidden = true;
        errorEl.hidden = false;
        if (errorMsg) errorMsg.textContent = mensaje || 'No se pudieron cargar los reportes.';
    }

    // 3. Renderizar métricas

    function renderMetricas(m) {
        const cards = [
            { icon: 'show_chart',       label: 'Tráfico Total',          value: m.trafico_total,        extra: 'Reservas + préstamos' },
            { icon: 'event_available',  label: 'Reservas de Hoy',        value: m.reservas_diarias,     extra: 'Hoy' },
            { icon: 'group',            label: 'Usuarios Activos',        value: m.usuarios_activos,     extra: 'Estado activo' },
            { icon: 'person_off',       label: 'Usuarios Inactivos',      value: m.usuarios_inactivos,   extra: 'Sin acceso' },
            { icon: 'bookmark',         label: 'Con Reserva Activa',     value: m.usuarios_con_reserva, extra: 'Usuarios' },
            { icon: 'menu_book',        label: 'Préstamos Activos',      value: m.prestamos_activos,    extra: 'Incluye vencidos' },
            { icon: 'assignment_return',label: 'Devoluciones',           value: m.devoluciones,         extra: 'Completadas' }
        ];

        metricsGrid.innerHTML = '';

        cards.forEach(function (c) {
            const div = document.createElement('div');
            div.className = 'metric-card';
            div.innerHTML =
                '<div class="metric-icon-bg">' +
                    '<span class="material-symbols-outlined">' + c.icon + '</span>' +
                '</div>' +
                '<div class="metric-body">' +
                    '<p class="metric-label">' + c.label + '</p>' +
                    '<h2 class="metric-value">' + formatearNumero(c.value) + '</h2>' +
                    '<p class="metric-trend neutral">' + c.extra + '</p>' +
                '</div>';
            metricsGrid.appendChild(div);
        });
    }

    function formatearNumero(n) {
        if (n === null || n === undefined) return '0';
        return Number(n).toLocaleString('es-CO');
    }

    // 4. Gráfica con Chart.js

    function actualizarSubtitulo(etiqueta, periodo) {
        const el = document.getElementById('chart-subtitulo');
        if (!el) return;
        el.textContent = etiqueta || etiquetasPeriodo[periodo] || etiquetasPeriodo['30'];
    }

    function marcarPeriodoActivo(periodo) {
        document.querySelectorAll('.periodo-btn').forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-periodo') === periodo);
        });
    }

    function renderGrafica(datos, etiqueta) {
        const labels = datos.map(function (d) { return d.dia; });
        const valores = datos.map(function (d) { return d.total; });

        const canvas = document.getElementById('chart-reservas');
        if (!canvas) return;

        if (chartInstance) {
            chartInstance.destroy();
        }

        actualizarSubtitulo(etiqueta, periodoActual);

        chartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Reservas',
                    data: valores,
                    backgroundColor: 'rgba(201, 168, 76, 0.35)',
                    borderColor: 'rgba(201, 168, 76, 0.8)',
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleColor: '#e2e2e2',
                        bodyColor: '#c9a84c',
                        borderColor: '#333',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        ticks: { color: 'rgba(255,255,255,0.35)', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'rgba(255,255,255,0.35)',
                            font: { size: 11 },
                            precision: 0
                        },
                        grid: { color: 'rgba(255,255,255,0.06)' }
                    }
                }
            }
        });
    }

    // Solo recarga la gráfica (sin tocar métricas ni alertas)
    function cargarGrafica(periodo) {
        periodoActual = periodo;
        marcarPeriodoActivo(periodo);

        const url = API_URL + '?tipo=grafica&periodo=' + encodeURIComponent(periodo);

        fetch(url)
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (!data.ok) return;
                renderGrafica(data.grafica_reservas || [], data.etiqueta);
            })
            .catch(function (err) {
                console.error('Gráfica:', err);
            });
    }

    // 5. Insights (categoría, libro, editoriales)

    function renderInsights(data) {
        // Categoría
        const catNombre = document.getElementById('cat-nombre');
        const catDesc   = document.getElementById('cat-desc');
        if (data.categoria_popular) {
            catNombre.textContent = data.categoria_popular.nombre;
            catDesc.textContent = 'Representa el ' + data.categoria_popular.porcentaje +
                '% de las reservas (' + data.categoria_popular.total + ')';
        } else {
            catNombre.textContent = 'Sin datos';
            catDesc.textContent = 'Aún no hay reservas con materia asignada';
        }

        // Libro popular
        const libroNombre = document.getElementById('libro-nombre');
        const libroDesc   = document.getElementById('libro-desc');
        if (data.libro_popular) {
            libroNombre.textContent = data.libro_popular.titulo;
            libroDesc.textContent = data.libro_popular.total + ' reserva(s)';
        } else {
            libroNombre.textContent = 'Sin datos';
            libroDesc.textContent = 'Aún no hay reservas registradas';
        }

        // Editoriales
        const lista = document.getElementById('editoriales-lista');
        if (!data.editoriales || data.editoriales.length === 0) {
            lista.innerHTML = '<p class="text-outline text-body-sm">Sin editoriales registradas</p>';
            return;
        }

        lista.innerHTML = '';
        data.editoriales.forEach(function (ed) {
            const item = document.createElement('div');
            item.className = 'peak-item';
            item.innerHTML =
                '<div class="peak-info">' +
                    '<span class="peak-time">' + escapeHtml(ed.nombre) + '</span>' +
                    '<span class="peak-load">' + ed.total + ' libro(s)</span>' +
                '</div>';
            lista.appendChild(item);
        });
    }

    // 6. Alertas

    function renderAlertas(alertas) {
        alertsGrid.innerHTML = '';

        if (!alertas || alertas.length === 0) {
            alertsEmpty.hidden = false;
            alertCountText.textContent = '0 ítems';
            return;
        }

        alertsEmpty.hidden = true;
        alertCountText.textContent = alertas.length + (alertas.length === 1 ? ' ítem' : ' ítems');

        alertas.forEach(function (a) {
            const portada = a.portada
                ? '../../../../' + a.portada
                : PORTADA_DEFAULT;

            const badgeClass = a.tipo === 'warning' ? 'warning' : 'error';

            const card = document.createElement('div');
            card.className = 'alert-card';
            card.innerHTML =
                '<div class="alert-thumb">' +
                    '<img src="' + escapeHtml(portada) + '" alt="' + escapeHtml(a.titulo) + '" onerror="this.src=\'' + PORTADA_DEFAULT + '\'">' +
                '</div>' +
                '<div class="alert-body">' +
                    '<h4 class="alert-title">' + escapeHtml(a.titulo) + '</h4>' +
                    '<p class="alert-id">ID: ' + Number(a.id_libro) + '</p>' +
                    '<span class="alert-badge ' + badgeClass + '">' + escapeHtml(a.motivo) + '</span>' +
                    '<p class="alert-meta">' + escapeHtml(a.detalle) + '</p>' +
                '</div>';
            alertsGrid.appendChild(card);
        });
    }

    // 7. Frase de la semana

    function renderFrase(texto) {
        const el = document.getElementById('frase-texto');
        if (el) el.textContent = texto || 'La lectura abre puertas.';
    }

    function cargarFrase() {
        fetch(API_URL + '?tipo=frase')
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (data.ok) renderFrase(data.frase);
            })
            .catch(function (err) {
                console.error('Frase de la semana:', err);
            });
    }

    // 8. Escape HTML básico

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // 9. Cargar datos desde la API

    function cargarReportes() {
        mostrarCarga();

        const url = API_URL + '?periodo=' + encodeURIComponent(periodoActual);

        fetch(url)
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (!data.ok) {
                    mostrarError(data.error || 'Error al obtener los datos');
                    return;
                }

                if (data.periodo) {
                    periodoActual = data.periodo;
                    marcarPeriodoActivo(periodoActual);
                }

                renderMetricas(data.metricas || {});
                renderGrafica(data.grafica_reservas || [], data.etiqueta);
                renderInsights(data);
                renderAlertas(data.alertas || []);
                renderFrase(data.frase);

                mostrarContenido();
                cargarFrase();
            })
            .catch(function (err) {
                console.error('Reportes:', err);
                mostrarError('No se pudo conectar con el servidor.');
            });
    }

    // 10. Eventos

    if (btnRefrescar) {
        btnRefrescar.addEventListener('click', function () {
            cargarReportes();
        });
    }

    if (btnReintentar) {
        btnReintentar.addEventListener('click', function () {
            cargarReportes();
        });
    }

    // Filtro de períodos de la gráfica
    document.querySelectorAll('.periodo-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const p = btn.getAttribute('data-periodo');
            if (!p || p === periodoActual) return;
            cargarGrafica(p);
        });
    });

    // Notificaciones (toggle simple, si existen en el DOM)
    const notifBtn = document.getElementById('notification-tray-btn');
    const notifContainer = document.getElementById('notification-container');
    if (notifBtn && notifContainer) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            notifContainer.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (notifContainer.classList.contains('open') &&
                !notifContainer.contains(e.target) &&
                !notifBtn.contains(e.target)) {
                notifContainer.classList.remove('open');
            }
        });
    }

    // 11. Inicio
    cargarReportes();

});