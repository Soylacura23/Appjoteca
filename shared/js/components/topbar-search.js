(function () {
    'use strict';

    const API = '/Appjoteca/backend/controllers/buscar.php';
    let timer = null;
    let caja = null;

    function inputs() {
        return [
            document.querySelector('.topbar-search-input'),
            document.querySelector('.topbar-search-mobile input')
        ].filter(Boolean);
    }

    function asegurarCaja(input) {
        if (caja && document.body.contains(caja)) return caja;

        caja = document.createElement('div');
        caja.className = 'topbar-search-results';
        caja.setAttribute('role', 'listbox');
        caja.hidden = true;

        const padre = input.closest('.topbar-search')
            || input.closest('.topbar-search-mobile')
            || input.parentElement;

        if (getComputedStyle(padre).position === 'static') {
            padre.style.position = 'relative';
        }
        padre.appendChild(caja);
        return caja;
    }

    function ocultar() {
        if (!caja) return;
        caja.hidden = true;
        caja.innerHTML = '';
    }

    function iconoTipo(tipo) {
        const iconos = {
            libro: 'menu_book',
            usuario: 'person',
            reserva: 'bookmark',
            prestamo: 'local_library',
            comentario: 'chat'
        };
        return iconos[tipo] || 'search';
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Ordena con Fuse si está cargado; si no, deja la lista igual
    function ordenarConFuse(items, texto) {
        if (!window.Fuse || !items.length) return items;

        const fuse = new window.Fuse(items, {
            keys: ['titulo', 'subtitulo'],
            threshold: 0.4,
            ignoreLocation: true
        });

        return fuse.search(texto).map((r) => r.item);
    }

    function pintar(items, input) {
        const el = asegurarCaja(input);
        el.innerHTML = '';

        if (!items.length) {
            el.innerHTML = '<div class="topbar-search-empty">Sin resultados</div>';
            el.hidden = false;
            return;
        }

        items.forEach((item) => {
            const a = document.createElement('a');
            a.className = 'topbar-search-item';
            a.href = item.url || '#';
            a.setAttribute('role', 'option');
            a.innerHTML =
                '<span class="material-symbols-outlined">' + iconoTipo(item.tipo) + '</span>' +
                '<span class="topbar-search-text">' +
                    '<strong>' + escapeHtml(item.titulo || '') + '</strong>' +
                    '<small>' + escapeHtml(item.subtitulo || '') + '</small>' +
                '</span>';
            el.appendChild(a);
        });

        el.hidden = false;
    }

    function buscar(texto, input) {
        if (texto.length < 2) {
            ocultar();
            return;
        }

        fetch(API + '?q=' + encodeURIComponent(texto) + '&limit=12', {
            credentials: 'same-origin'
        })
            .then((r) => r.json())
            .then((data) => {
                if (!data || !data.ok) {
                    ocultar();
                    return;
                }
                const lista = ordenarConFuse(data.resultados || [], texto);
                pintar(lista, input);
            })
            .catch(() => {
                ocultar();
            });
    }

    function onInput(e) {
        const input = e.target;
        const texto = (input.value || '').trim();

        inputs().forEach((otro) => {
            if (otro !== input) otro.value = input.value;
        });

        clearTimeout(timer);
        timer = setTimeout(() => {
            buscar(texto, input);
        }, 250);
    }

    document.addEventListener('DOMContentLoaded', () => {
        inputs().forEach((input) => {
            input.addEventListener('input', onInput);
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') ocultar();
            });
        });

        document.addEventListener('click', (e) => {
            if (!caja) return;
            const enBuscador = e.target.closest('.topbar-search')
                || e.target.closest('.topbar-search-mobile');
            if (!caja.contains(e.target) && !enBuscador) {
                ocultar();
            }
        });
    });
})();