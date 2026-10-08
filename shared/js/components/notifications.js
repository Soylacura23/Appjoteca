(() => {
    'use strict';


    const API = '/Appjoteca/backend/controllers/procesar_notificaciones.php';
    const LIMITE_INICIAL = 5;
    const LIMITE_MAS = 10;
    const SWIPE_MIN = 80;

    const panel     = document.getElementById('notification-container');
    const listaEl   = document.getElementById('notification-list');
    const pillEl    = document.getElementById('notif-count-pill');
    const btnTodas  = document.getElementById('mark-all-read');
    const btnMas    = document.getElementById('notif-see-all');

    if (!panel || !listaEl) return;

    let offset = 0;
    let cargando = false;

    /* ── Helpers ─────────────────────────────────── */

    function escaparHTML(texto) {
        return String(texto)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function tiempoRelativo(fechaStr) {
        const fecha = new Date(fechaStr.replace(' ', 'T'));
        const seg = Math.floor((Date.now() - fecha.getTime()) / 1000);
        if (seg < 60) return 'ahora';
        if (seg < 3600) return Math.floor(seg / 60) + 'm';
        if (seg < 86400) return Math.floor(seg / 3600) + 'h';
        return Math.floor(seg / 86400) + 'd';
    }

    function actualizarBadge(noLeidas) {
        document.querySelectorAll('.notification-badge').forEach((b) => {
            b.classList.toggle('hidden', noLeidas <= 0);
        });
        if (pillEl) {
            if (noLeidas > 0) {
                pillEl.textContent = String(noLeidas);
                pillEl.hidden = false;
            } else {
                pillEl.hidden = true;
            }
        }
    }

    function itemHTML(item) {
        const unread = item.leida ? '' : ' unread';
        const mensaje = item.mensaje || '';
        const titulo = mensaje.length > 60
            ? mensaje.slice(0, 60) + '…'
            : mensaje;
        const urlAttr = item.url ? ` data-url="${escaparHTML(item.url)}"` : '';
        const fullAttr = escaparHTML(mensaje);

        return `
            <div class="notification-item${unread}" data-id="${item.id}"${urlAttr} title="${fullAttr}">
                <div class="notification-item-content">
                    <span class="notification-dot"></span>
                    <div class="notification-icon">
                        <span class="material-symbols-outlined">notifications</span>
                    </div>
                    <div class="notification-body">
                        <p class="notification-title">${escaparHTML(titulo)}</p>
                        <p class="notification-desc">${escaparHTML(mensaje)}</p>
                    </div>
                    <span class="notification-time">${tiempoRelativo(item.fecha)}</span>
                    <button type="button" class="notification-delete" aria-label="Eliminar notificación" title="Eliminar">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>`;
    }

    function estadoVacio() {
        listaEl.innerHTML = `
            <div class="notification-empty">
                <span class="material-symbols-outlined">notifications_off</span>
                <p>No tienes notificaciones</p>
            </div>`;
        if (btnMas) btnMas.style.display = 'none';
    }

    /* ── API ─────────────────────────────────────── */

    async function pedirListado(limite, desde, reemplazar) {
        if (cargando) return;
        cargando = true;

        try {
            const res = await fetch(
                `${API}?accion=listar&limite=${limite}&offset=${desde}`,
                { credentials: 'same-origin' }
            );
            const data = await res.json();
            if (!data.ok) return;

            actualizarBadge(data.no_leidas);

            if (reemplazar) {
                listaEl.innerHTML = '';
            }

            if (!data.items.length && desde === 0) {
                estadoVacio();
                return;
            }

            data.items.forEach((item) => {
                listaEl.insertAdjacentHTML('beforeend', itemHTML(item));
            });

            offset = desde + data.items.length;

            if (btnMas) {
                btnMas.style.display = data.hay_mas ? '' : 'none';
                btnMas.textContent = data.hay_mas ? 'Ver más' : 'No hay más';
            }
        } catch (err) {
            console.error('Notificaciones:', err);
        } finally {
            cargando = false;
        }
    }

    async function marcarUna(id) {
        const body = new FormData();
        body.append('accion', 'marcar_una');
        body.append('id_notificacion', id);

        const res = await fetch(API, { method: 'POST', body, credentials: 'same-origin' });
        const data = await res.json();
        if (data.ok) actualizarBadge(data.no_leidas);
    }

    async function marcarTodas() {
        const body = new FormData();
        body.append('accion', 'marcar_todas');

        const res = await fetch(API, { method: 'POST', body, credentials: 'same-origin' });
        const data = await res.json();
        if (!data.ok) return;

        listaEl.querySelectorAll('.notification-item.unread').forEach((el) => {
            el.classList.remove('unread');
        });
        actualizarBadge(0);
    }

    async function eliminarNotificacion(itemEl) {
        const id = itemEl.getAttribute('data-id');
        if (!id) return;

        const body = new FormData();
        body.append('accion', 'eliminar');
        body.append('id_notificacion', id);

        try {
            const res = await fetch(API, { method: 'POST', body, credentials: 'same-origin' });
            const data = await res.json();
            if (!data.ok) return;

            itemEl.classList.add('removing');
            setTimeout(() => {
                itemEl.remove();
                if (!listaEl.querySelector('.notification-item')) {
                    estadoVacio();
                }
            }, 220);

            actualizarBadge(data.no_leidas);
        } catch (err) {
            console.error('Eliminar notificación:', err);
        }
    }

    /* ── Eventos de la bandeja ───────────────────── */

    const observer = new MutationObserver(() => {
        if (panel.classList.contains('open') && listaEl.children.length === 0) {
            offset = 0;
            pedirListado(LIMITE_INICIAL, 0, true);
        }
    });
    observer.observe(panel, { attributes: true, attributeFilter: ['class'] });

    fetch(`${API}?accion=contar`, { credentials: 'same-origin' })
        .then((r) => r.json())
        .then((data) => {
            if (data.ok) actualizarBadge(data.no_leidas);
        })
        .catch(() => {});

    if (btnTodas) {
        btnTodas.addEventListener('click', (e) => {
            e.stopPropagation();
            marcarTodas();
        });
    }

    if (btnMas) {
        btnMas.addEventListener('click', (e) => {
            e.stopPropagation();
            pedirListado(LIMITE_MAS, offset, false);
        });
    }

    // Clic: eliminar | expandir | ir a url
    listaEl.addEventListener('click', (e) => {
        const btnDelete = e.target.closest('.notification-delete');
        if (btnDelete) {
            e.stopPropagation();
            const item = btnDelete.closest('.notification-item');
            if (item) eliminarNotificacion(item);
            return;
        }

        const item = e.target.closest('.notification-item');
        if (!item) return;

        const id = item.getAttribute('data-id');
        if (item.classList.contains('unread') && id) {
            item.classList.remove('unread');
            marcarUna(id);
        }

        if (!item.classList.contains('expanded')) {
            item.classList.add('expanded');
            return;
        }

        const url = item.getAttribute('data-url');
        if (url) {
            window.location.href = url;
        }
    });

    /* ── Swipe para eliminar (móvil) ─────────────── */

    let touchStartX = 0;
    let touchStartY = 0;
    let swipingEl = null;

    listaEl.addEventListener('touchstart', (e) => {
        const item = e.target.closest('.notification-item');
        if (!item) return;
        touchStartX = e.changedTouches[0].clientX;
        touchStartY = e.changedTouches[0].clientY;
        swipingEl = item;
        item.classList.add('swiping');
    }, { passive: true });

    listaEl.addEventListener('touchmove', (e) => {
        if (!swipingEl) return;
        const dx = e.changedTouches[0].clientX - touchStartX;
        const dy = e.changedTouches[0].clientY - touchStartY;

        if (Math.abs(dy) > Math.abs(dx)) return;

        if (dx < 0) {
            const content = swipingEl.querySelector('.notification-item-content');
            if (content) {
                content.style.transform = `translateX(${Math.max(dx, -120)}px)`;
            }
        }
    }, { passive: true });

    listaEl.addEventListener('touchend', (e) => {
        if (!swipingEl) return;
        const dx = e.changedTouches[0].clientX - touchStartX;
        const content = swipingEl.querySelector('.notification-item-content');
        const item = swipingEl;

        swipingEl.classList.remove('swiping');
        swipingEl = null;

        if (dx < -SWIPE_MIN) {
            eliminarNotificacion(item);
        } else if (content) {
            content.style.transform = '';
        }
    }, { passive: true });
})();