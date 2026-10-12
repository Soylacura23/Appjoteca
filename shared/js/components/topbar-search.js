(function () {
    'use strict';

    const API = '/Appjoteca/backend/controllers/buscar.php';
    const cajas = new Map();
    let timer = null;
    let solicitudActual = null;

    function inputs() {
        return [
            ...document.querySelectorAll('.topbar-search-input, .topbar-search-mobile input')
        ];
    }

    function areaBusqueda(input) {
        return input.closest('.topbar-search')
            || input.closest('.topbar-search-mobile')
            || input.parentElement;
    }

    function asegurarCaja(input) {
        const padre = areaBusqueda(input);
        let caja = cajas.get(padre);
        if (caja && document.body.contains(caja)) return caja;

        caja = document.createElement('div');
        caja.className = 'topbar-search-results';
        caja.setAttribute('role', 'listbox');
        caja.hidden = true;
        if (getComputedStyle(padre).position === 'static') {
            padre.style.position = 'relative';
        }
        padre.appendChild(caja);
        cajas.set(padre, caja);
        return caja;
    }

    function ocultarTodas() {
        cajas.forEach((caja) => {
            caja.hidden = true;
            caja.innerHTML = '';
        });
    }

    function iconoTipo(tipo) {
        const iconos = {
            libro: 'menu_book',
            usuario: 'person',
            pendiente: 'person_add',
            solicitud: 'assignment',
            reserva: 'bookmark',
            prestamo: 'local_library',
            comentario: 'chat'
        };
        return iconos[tipo] || 'search';
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function ordenarConFuse(items, texto) {
        if (!window.Fuse || !items.length) return items;
        const fuse = new window.Fuse(items, {
            keys: ['titulo', 'subtitulo'],
            threshold: 0.4,
            ignoreLocation: true
        });
        const ordenados = fuse.search(texto).map((resultado) => resultado.item);
        return ordenados.length ? ordenados : items;
    }

    function urlInterna(url) {
        try {
            const destino = new URL(url || '', window.location.origin);
            if (destino.origin === window.location.origin
                && destino.pathname.startsWith('/Appjoteca/')) {
                return destino.href;
            }
        } catch {
            return '';
        }
        return '';
    }

    function pintar(items, input, mensajeVacio = 'Sin resultados') {
        const caja = asegurarCaja(input);
        caja.replaceChildren();

        if (!items.length) {
            caja.innerHTML = '<div class="topbar-search-empty">' + escapeHtml(mensajeVacio) + '</div>';
            caja.hidden = false;
            return;
        }

        items.forEach((item) => {
            const enlace = document.createElement('a');
            enlace.className = 'topbar-search-item';
            enlace.href = urlInterna(item.url) || '#';
            enlace.setAttribute('role', 'option');
            enlace.innerHTML =
                '<span class="material-symbols-outlined">' + iconoTipo(item.tipo) + '</span>' +
                '<span class="topbar-search-text">' +
                    '<strong>' + escapeHtml(item.titulo || '') + '</strong>' +
                    '<small>' + escapeHtml(item.subtitulo || '') + '</small>' +
                '</span>';
            caja.appendChild(enlace);
        });

        caja.hidden = false;
    }

    async function buscar(texto, input, seleccionarPrimero = false) {
        if (solicitudActual) solicitudActual.abort();
        solicitudActual = null;

        if (texto.length < 2) {
            ocultarTodas();
            return;
        }

        const controlador = new AbortController();
        solicitudActual = controlador;
        const caja = asegurarCaja(input);
        if (!seleccionarPrimero) {
            caja.innerHTML = '<div class="topbar-search-empty">Buscando…</div>';
            caja.hidden = false;
        }

        try {
            const respuesta = await fetch(API + '?q=' + encodeURIComponent(texto) + '&limit=12', {
                credentials: 'same-origin',
                signal: controlador.signal
            });
            if (!respuesta.ok) throw new Error('El servidor no pudo completar la búsqueda.');
            const data = await respuesta.json();
            if (!data || !data.ok || !Array.isArray(data.resultados)) {
                throw new Error(data?.error || 'La búsqueda no está disponible.');
            }
            if (input.value.trim() !== texto || solicitudActual !== controlador) return;

            const resultados = ordenarConFuse(data.resultados, texto);
            if (seleccionarPrimero) {
                const destino = urlInterna(resultados[0]?.url);
                if (!destino) {
                    pintar([], input);
                    return;
                }
                window.location.assign(destino);
                return;
            }
            pintar(resultados, input);
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error('Búsqueda global:', error);
            pintar([], input, 'No se pudo completar la búsqueda. Intenta de nuevo.');
        } finally {
            if (solicitudActual === controlador) solicitudActual = null;
        }
    }

    function sincronizar(input) {
        inputs().forEach((otro) => {
            if (otro !== input) otro.value = input.value;
        });
    }

    function ejecutarBusqueda(input, seleccionarPrimero = false) {
        const texto = (input.value || '').trim();
        sincronizar(input);
        window.clearTimeout(timer);
        if (solicitudActual) solicitudActual.abort();

        if (seleccionarPrimero) {
            buscar(texto, input, true);
            return;
        }
        timer = window.setTimeout(() => buscar(texto, input), 250);
    }

    function configurarInput(input) {
        const area = areaBusqueda(input);
        input.setAttribute('enterkeyhint', 'search');
        area.classList.add('has-search-submit');
        if (!area.querySelector('.topbar-search-submit')) {
            const boton = document.createElement('button');
            boton.className = 'topbar-search-submit';
            boton.type = 'button';
            boton.setAttribute('aria-label', 'Buscar y abrir el primer resultado');
            boton.title = 'Buscar y abrir el primer resultado';
            boton.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">search</span>';
            boton.addEventListener('click', () => ejecutarBusqueda(input, true));
            area.appendChild(boton);
        }

        input.addEventListener('input', () => ejecutarBusqueda(input));
        input.addEventListener('keydown', (evento) => {
            if (evento.key === 'Escape') {
                ocultarTodas();
                return;
            }
            if (evento.key === 'Enter') {
                evento.preventDefault();
                ejecutarBusqueda(input, true);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        inputs().forEach(configurarInput);

        const toggle = document.querySelector('.search-toggle-btn');
        const areaMobile = document.querySelector('.topbar-search-mobile');
        const inputMobile = areaMobile?.querySelector('input');
        if (toggle && areaMobile) {
            toggle.addEventListener('click', () => {
                const isOpen = areaMobile.classList.toggle('open');
                toggle.setAttribute('aria-expanded', String(isOpen));
                areaMobile.setAttribute('aria-hidden', String(!isOpen));
                if (isOpen) inputMobile?.focus();
                else ocultarTodas();
            });
        }

        document.addEventListener('click', (evento) => {
            if (evento.target.closest('.topbar-search, .topbar-search-mobile')) return;
            ocultarTodas();
        });

        document.addEventListener('keydown', (evento) => {
            if (evento.key === 'Escape' && areaMobile?.classList.contains('open')) {
                areaMobile.classList.remove('open');
                areaMobile.setAttribute('aria-hidden', 'true');
                toggle?.setAttribute('aria-expanded', 'false');
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth < 900 || !areaMobile) return;
            areaMobile.classList.remove('open');
            areaMobile.setAttribute('aria-hidden', 'true');
            toggle?.setAttribute('aria-expanded', 'false');
        });
    });
})();
