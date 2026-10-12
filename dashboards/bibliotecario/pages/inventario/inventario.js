document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const API = '../../../../backend/controllers/procesar_libro.php';

    let paginaActual = 1;
    let totalPaginas = 1;
    let catalogos = { materias: [], editoriales: [], tipos: [], colecciones: [], idiomas: [] };
    let libroActual = null;
    let esNuevo = false;
    let coautores = [];
    let portadaUrlTemp = null;
    const parametrosBusqueda = new URLSearchParams(window.location.search);
    const idAutorSolicitado = Number(parametrosBusqueda.get('autor')) || 0;

    // HELPERS
    function toast(msg, tipo = 'info') {
        const iconos = { success: 'check_circle', error: 'error', info: 'info' };
        const div = document.createElement('div');
        div.className = 'toast ' + tipo;
        div.innerHTML = `
            <span class="material-symbols-outlined toast-icon">${iconos[tipo] || 'info'}</span>
            <span class="toast-msg">${esc(msg)}</span>`;
        document.getElementById('toast-container').appendChild(div);
        setTimeout(() => {
            div.style.opacity = '0';
            setTimeout(() => div.remove(), 300);
        }, 2800);
    }

    function esc(texto) {
        return String(texto ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function get(action, params = {}) {
        const qs = new URLSearchParams({ action, ...params });
        const res = await fetch(API + '?' + qs);
        return res.json();
    }

    async function post(action, formData) {
        formData.append('action', action);
        formData.append('csrf_token', window.getCSRFToken());
        const res = await fetch(API, { method: 'POST', body: formData });
        return res.json();
    }

    // CATÁLOGOS
    async function cargarCatalogos() {
        try {
            const data = await get('catalogos');
            if (data.status !== 'success') return;

            catalogos = data.data;

            llenarSelect('detail-category-input', catalogos.materias, 'Selecciona materia');
            llenarSelect('detail-material-type', catalogos.tipos, 'Tipo de material');
            llenarSelect('detail-location-input', catalogos.colecciones, 'Selecciona colección');
            llenarDatalist('publisher-options', catalogos.editoriales);
            llenarDatalist('language-options', catalogos.idiomas);

            const filtro = document.getElementById('filter-category');
            filtro.replaceChildren(new Option('Todas', ''));
            catalogos.materias.forEach(m => filtro.add(new Option(m.nombre, String(m.id))));
        } catch (e) {
            console.error(e);
            toast('No se pudieron cargar los catálogos', 'error');
        }
    }

    function llenarSelect(id, items, placeholder) {
        const sel = document.getElementById(id);
        if (!sel) return;
        sel.replaceChildren();
        if (placeholder) sel.add(new Option(placeholder, ''));
        items.forEach(it => sel.add(new Option(it.nombre, String(it.id))));
    }

    function llenarDatalist(id, items) {
        const lista = document.getElementById(id);
        if (!lista) return;
        lista.replaceChildren(...items.map(item => {
            const option = document.createElement('option');
            option.value = item.nombre;
            return option;
        }));
    }

    function idCatalogoPorNombre(items, nombre) {
        const encontrado = items.find(item => item.nombre.trim().toLocaleLowerCase() === nombre.trim().toLocaleLowerCase());
        return encontrado ? String(encontrado.id) : '';
    }

    // LISTADO
    async function cargarLibros(pagina = 1) {
        paginaActual = pagina;

        const estado  = document.getElementById('filter-status').value;
        const materia = document.getElementById('filter-category').value;
        const orden   = document.getElementById('sort-by').value;

        try {
            const data = await get('listar', {
                pagina: pagina,
                estado: estado,
                materia: materia,
                orden: orden,
                autor: idAutorSolicitado
            });

            if (data.status !== 'success') {
                toast(data.mensaje || 'Error al cargar libros', 'error');
                return;
            }

            const info = data.data;
            totalPaginas = info.total_paginas;

            renderStats(info.libros);
            renderGrid(info.libros);
            renderPaginacion();
        } catch (e) {
            console.error(e);
            toast('Error de conexión al cargar libros', 'error');
        }
    }

    function renderStats(libros) {
        document.getElementById('stat-total').textContent     = libros.length;
        document.getElementById('stat-available').textContent = libros.reduce((s, l) => s + (+l.disponibles || 0), 0);
        document.getElementById('stat-borrowed').textContent  = libros.reduce((s, l) => s + (+l.prestados || 0), 0);
        document.getElementById('stat-copies').textContent    = libros.reduce((s, l) => s + (+l.total_ejemplares || 0), 0);
    }

    function estadoDe(l) {
        const disp  = +l.disponibles || 0;
        const total = +l.total_ejemplares || 0;
        if (total === 0) return ['Sin ejemplares', 'status-processing'];
        if (disp > 0)   return ['Disponible', 'status-in-stock'];
        if (+l.prestados > 0) return ['Prestado', 'status-low-stock'];
        return ['Sin stock', 'status-out-stock'];
    }

    function renderGrid(libros) {
        const grid  = document.getElementById('books-grid');
        const empty = document.getElementById('empty-state');

        if (!libros.length) {
            grid.hidden = true;
            empty.hidden = false;
            return;
        }

        grid.hidden = false;
        empty.hidden = true;

        grid.innerHTML = libros.map(l => {
            const [txt, cls] = estadoDe(l);
            const portada = l.portada || 'assets/images/books/default-cover.jpg';
            return `
            <article class="book-card" data-id="${Number(l.id_libro) || 0}" role="listitem" tabindex="0">
                <div class="book-image-wrap">
                    <img src="/Appjoteca/${esc(portada)}" alt="Portada de ${esc(l.titulo)}" loading="lazy"
                         onerror="this.src='/Appjoteca/assets/images/books/default-cover.jpg'">
                </div>
                <div class="book-info">
                    <div class="book-top-row">
                        <span class="book-category">${esc(l.materia || 'Sin materia')}</span>
                        <button type="button" class="book-edit-btn" aria-label="Editar">
                            <span class="material-symbols-outlined">edit_square</span>
                        </button>
                    </div>
                    <h3 class="book-title">${esc(l.titulo)}</h3>
                    <p class="book-author">${esc(l.autores || 'Autor desconocido')}</p>
                    <div class="book-meta">
                        <span class="book-copies"><strong>${Number(l.disponibles) || 0}</strong> / ${Number(l.total_ejemplares) || 0}</span>
                        <span class="book-status-tag ${cls}">${txt}</span>
                    </div>
                </div>
            </article>`;
        }).join('');

        grid.querySelectorAll('.book-card').forEach(card => {
            card.addEventListener('click', e => {
                if (e.target.closest('.book-edit-btn')) return;
                abrirDetalle(+card.dataset.id);
            });
            const btnEdit = card.querySelector('.book-edit-btn');
            if (btnEdit) {
                btnEdit.addEventListener('click', e => {
                    e.stopPropagation();
                    abrirDetalle(+card.dataset.id);
                });
            }
        });
    }

    function renderPaginacion() {
        const nav   = document.getElementById('pagination');
        const pages = document.getElementById('pagination-pages');

        if (totalPaginas <= 1) {
            nav.hidden = true;
            return;
        }

        nav.hidden = false;
        pages.innerHTML = '';

        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.className = 'pag-btn' + (i === paginaActual ? ' active' : '');
            btn.textContent = i;
            btn.addEventListener('click', () => cargarLibros(i));
            pages.appendChild(btn);
        }

        document.getElementById('pagination-prev').onclick = () => {
            if (paginaActual > 1) cargarLibros(paginaActual - 1);
        };
        document.getElementById('pagination-next').onclick = () => {
            if (paginaActual < totalPaginas) cargarLibros(paginaActual + 1);
        };
    }

    // DETALLE
    async function abrirDetalle(id = null) {
        esNuevo = (id === null);
        coautores = [];
        portadaUrlTemp = null;
        renderCoautores();
        document.getElementById('detail-cover-file').value = '';

        if (esNuevo) {
            libroActual = null;
            document.getElementById('detail-panel-title').textContent = 'Nuevo libro';
            document.getElementById('detail-delete-btn').hidden = true;
            document.getElementById('detail-save-text').textContent = 'Crear';
            document.getElementById('detail-status-text').textContent = 'Nuevo libro';
            document.getElementById('detail-status-dot').className = 'status-dot new';
            document.getElementById('auto-book-id').textContent = '—';
            document.getElementById('auto-created-date').textContent = '—';
            document.getElementById('detail-form').reset();
            resetCover();
            resetHealth();
            resetEjemplares();
            mostrarHistorial([], 'Guarda el libro para comenzar a registrar su actividad.');
        } else {
            try {
                const data = await get('obtener', { id: id });
                if (data.status !== 'success') {
                    toast(data.mensaje || 'No se pudo cargar el libro', 'error');
                    return;
                }
                libroActual = data.data;
                fillForm(libroActual);
            } catch (e) {
                toast('Error al cargar el libro', 'error');
                return;
            }
        }

        document.getElementById('book-detail-overlay').hidden = false;
        document.body.style.overflow = 'hidden';
        if (!esNuevo && libroActual) cargarHistorial(libroActual.id_libro);
    }

    function fillForm(l) {
        document.getElementById('detail-panel-title').textContent = l.titulo;
        document.getElementById('detail-delete-btn').hidden = false;
        document.getElementById('detail-save-text').textContent = 'Guardar';
        document.getElementById('detail-status-text').textContent = 'Editando';
        document.getElementById('detail-status-dot').className = 'status-dot editing';

        document.getElementById('auto-book-id').textContent = 'LB-' + String(l.id_libro).padStart(4, '0');
        document.getElementById('auto-created-date').textContent = l.fecha_registro_libro ? l.fecha_registro_libro.slice(0, 10) : '—';

        document.getElementById('detail-title-input').value    = l.titulo || '';
        document.getElementById('detail-author-input').value   = (l.autores_array && l.autores_array[0]) || '';
        document.getElementById('detail-isbn-input').value     = l.isbn || '';
        document.getElementById('detail-edition-input').value  = l.edicion || '';
        document.getElementById('detail-city-input').value     = l.ciudad || '';
        document.getElementById('detail-year-input').value     = l.publicacion_year || '';
        document.getElementById('detail-serie-input').value    = l.serie || '';
        document.getElementById('detail-volumen-input').value  = l.volumen || '';
        document.getElementById('detail-pages-input').value    = l.numero_paginas || '';
        document.getElementById('detail-synopsis-input').value = l.sinopsis || '';
        actualizarContadorSinopsis();

        document.getElementById('detail-category-input').value  = l.id_materia || '';
        const editorial = catalogos.editoriales.find(item => String(item.id) === String(l.id_editorial));
        document.getElementById('detail-publisher-input').value = editorial?.nombre || '';
        document.getElementById('detail-material-type').value   = l.id_tipo_material || '';
        const idioma = catalogos.idiomas.find(item => String(item.id) === String(l.idioma_id || l.idioma));
        document.getElementById('detail-language').value = idioma?.nombre || l.idioma || '';

        coautores = (l.autores_array || []).slice(1);
        renderCoautores();

        if (l.portada) {
            document.getElementById('detail-cover-img').src = '/Appjoteca/' + l.portada;
            document.getElementById('detail-cover-img').hidden = false;
            document.getElementById('cover-placeholder').hidden = true;
        } else {
            resetCover();
        }

        fillHealth(l.ejemplares || []);
        fillEjemplares(l.ejemplares || []);
    }

    // GUARDAR
    async function guardarLibro() {
        const titulo      = document.getElementById('detail-title-input').value.trim();
        const autor       = document.getElementById('detail-author-input').value.trim();
        const isbn        = document.getElementById('detail-isbn-input').value.trim();
        const idMateria   = document.getElementById('detail-category-input').value;
        const idColeccion = document.getElementById('detail-location-input').value;

        if (!titulo)      return toast('El título es obligatorio', 'error');
        if (!autor)       return toast('El autor principal es obligatorio', 'error');
        if (!isbn)        return toast('El ISBN es obligatorio', 'error');
        if (!idMateria)   return toast('La materia es obligatoria', 'error');
        if (esNuevo && !idColeccion) return toast('La colección es obligatoria', 'error');

        const fd = new FormData();
        fd.append('titulo', titulo);
        fd.append('isbn', isbn);
        fd.append('id_materia', idMateria);
        fd.append('id_tipo_material', document.getElementById('detail-material-type').value);
        fd.append('edicion', document.getElementById('detail-edition-input').value.trim());
        fd.append('ciudad', document.getElementById('detail-city-input').value.trim());
        fd.append('publicacion_year', document.getElementById('detail-year-input').value.trim());
        fd.append('serie', document.getElementById('detail-serie-input').value.trim());
        fd.append('volumen', document.getElementById('detail-volumen-input').value.trim());
        fd.append('numero_paginas', document.getElementById('detail-pages-input').value.trim());
        fd.append('sinopsis', document.getElementById('detail-synopsis-input').value.trim());
        fd.append('id_coleccion', idColeccion);

        const editorial = document.getElementById('detail-publisher-input').value.trim();
        const idEditorial = idCatalogoPorNombre(catalogos.editoriales, editorial);
        if (idEditorial) {
            fd.append('id_editorial', idEditorial);
        } else if (editorial) {
            fd.append('editorial_texto', editorial);
        }

        const idioma = document.getElementById('detail-language').value.trim();
        const idIdioma = idCatalogoPorNombre(catalogos.idiomas, idioma);
        if (idIdioma) {
            fd.append('idioma_id', idIdioma);
        } else if (idioma) {
            fd.append('idioma_texto', idioma);
        }

        [autor, ...coautores].forEach(a => fd.append('autores[]', a));

        const archivo = document.getElementById('detail-cover-file').files[0];
        if (archivo) {
            fd.append('portada', archivo);
        } else if (portadaUrlTemp) {
            fd.append('portada_url', portadaUrlTemp);
        }

        try {
            let data;
            if (esNuevo) {
                fd.append('cantidad_ejemplares', document.getElementById('detail-total-copies').value || 1);
                data = await post('guardar', fd);
            } else {
                fd.append('id', libroActual.id_libro);
                data = await post('actualizar', fd);
            }

            if (data.status === 'success') {
                toast(data.mensaje, 'success');
                cerrarDetalle();
                cargarLibros(paginaActual);
            } else {
                toast(data.mensaje || 'Error al guardar', 'error');
            }
        } catch (e) {
            console.error(e);
            toast('Error de red al guardar', 'error');
        }
    }

    // ELIMINAR
    async function eliminarLibro() {
        if (!libroActual) return;

        const fd = new FormData();
        fd.append('id', libroActual.id_libro);

        try {
            const data = await post('eliminar', fd);
            if (data.status === 'success') {
                toast(data.mensaje, 'success');
                cerrarDetalle();
                cargarLibros(1);
            } else {
                toast(data.mensaje || 'No se pudo eliminar', 'error');
            }
        } catch (e) {
            toast('Error de red', 'error');
        }
    }

    function cerrarDetalle() {
        document.getElementById('book-detail-overlay').hidden = true;
        document.getElementById('confirm-modal').hidden = true;
        const coverModal = document.getElementById('cover-select-modal');
        if (coverModal) coverModal.hidden = true;
        document.body.style.overflow = '';
        libroActual = null;
    }

    function mostrarHistorial(movimientos, vacio = 'No hay actividad registrada para este libro.') {
        const timeline = document.getElementById('history-timeline');
        if (!movimientos.length) {
            timeline.innerHTML = `<div class="history-empty"><span class="material-symbols-outlined">history</span><p>${esc(vacio)}</p></div>`;
            return;
        }

        const etiquetas = {
            reservado: ['Reservado', 'event-reserved'],
            reserva_rechazada: ['Reserva rechazada', 'event-cancelled'],
            reserva_cancelada: ['Reserva cancelada', 'event-cancelled'],
            prestado: ['Prestado', 'event-loaned'],
            vencido: ['Préstamo vencido', 'event-cancelled'],
            devuelto: ['Devuelto', 'event-returned']
        };
        timeline.innerHTML = movimientos.map(movimiento => {
            const [etiqueta, clase] = etiquetas[movimiento.actividad] || ['Actividad', ''];
            const fecha = movimiento.fecha ? new Date(movimiento.fecha.replace(' ', 'T')).toLocaleDateString() : 'Fecha desconocida';
            return `
                <article class="history-event ${clase}">
                    <div class="history-event-heading">
                        <span class="history-event-label">${esc(etiqueta)}</span>
                        <time>${esc(fecha)}</time>
                    </div>
                    <div class="history-event-detail">${esc(movimiento.usuario || 'Usuario')} · ${esc(movimiento.detalle || '')} · ${Number(movimiento.cantidad) || 1} ${Number(movimiento.cantidad) === 1 ? 'ejemplar' : 'ejemplares'}</div>
                    ${movimiento.observacion ? `<div class="history-event-note">${esc(movimiento.observacion)}</div>` : ''}
                </article>`;
        }).join('');
    }

    async function cargarHistorial(idLibro) {
        const timeline = document.getElementById('history-timeline');
        timeline.innerHTML = '<div class="history-loading"><span class="skeleton-line"></span><span class="skeleton-line"></span></div>';
        try {
            const data = await get('historial', { id: idLibro });
            if (data.status !== 'success' || !Array.isArray(data.data)) {
                throw new Error(data.mensaje || 'No se pudo cargar el historial del libro');
            }
            if (libroActual && String(libroActual.id_libro) === String(idLibro)) {
                mostrarHistorial(data.data);
            }
        } catch (error) {
            console.error(error);
            if (libroActual && String(libroActual.id_libro) === String(idLibro)) {
                mostrarHistorial([], 'No se pudo cargar el historial. Intenta abrir el libro nuevamente.');
            }
        }
    }

    // PORTADA
    function resetCover() {
        document.getElementById('detail-cover-img').hidden = true;
        document.getElementById('detail-cover-img').src = '';
        document.getElementById('cover-placeholder').hidden = false;
        portadaUrlTemp = null;
    }

    function mostrarSelectorPortadas(portadas) {
        const modal = document.getElementById('cover-select-modal');
        const grid  = document.getElementById('cover-select-grid');
        if (!modal || !grid) {
            // Fallback simple si no hay modal
            portadaUrlTemp = portadas[0].url;
            document.getElementById('detail-cover-img').src = portadas[0].url;
            document.getElementById('detail-cover-img').hidden = false;
            document.getElementById('cover-placeholder').hidden = true;
            toast('Portada aplicada (primera encontrada)', 'success');
            return;
        }

        grid.innerHTML = portadas.map((p, i) => `
            <button type="button" class="cover-option" data-url="${esc(p.url)}">
                <img src="${esc(p.url)}" alt="${esc(p.titulo)}" loading="lazy">
                <span>${esc(p.titulo)}${p.anio ? ' (' + esc(p.anio) + ')' : ''}</span>
            </button>
        `).join('');

        grid.querySelectorAll('.cover-option').forEach(btn => {
            btn.addEventListener('click', () => {
                portadaUrlTemp = btn.dataset.url;
                document.getElementById('detail-cover-img').src = btn.dataset.url;
                document.getElementById('detail-cover-img').hidden = false;
                document.getElementById('cover-placeholder').hidden = true;
                modal.hidden = true;
                toast('Portada seleccionada', 'success');
            });
        });

        modal.hidden = false;
    }

    function mostrarCargaPortadas() {
        const modal = document.getElementById('cover-select-modal');
        const grid = document.getElementById('cover-select-grid');
        grid.innerHTML = Array.from({ length: 6 }, () => `
            <div class="cover-skeleton" aria-hidden="true">
                <span class="cover-skeleton-image"></span>
                <span class="cover-skeleton-line"></span>
            </div>
        `).join('');
        modal.hidden = false;
    }

    // COAUTORES
    function renderCoautores() {
        const lista = document.getElementById('coauthors-list');
        lista.innerHTML = coautores.map((c, i) => `
            <span class="tag-chip">${esc(c)}
                <button type="button" class="tag-chip-remove" data-i="${i}">&times;</button>
            </span>`).join('');

        lista.querySelectorAll('.tag-chip-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                coautores.splice(+btn.dataset.i, 1);
                renderCoautores();
            });
        });
    }

    // SALUD / EJEMPLARES
    function resetHealth() {
        ['health-total', 'health-available', 'health-borrowed', 'health-out'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = '0';
        });
        const rate = document.getElementById('health-rate');
        if (rate) rate.textContent = '0%';
    }

    function fillHealth(ejemplares) {
        const total       = ejemplares.length;
        const disponibles = ejemplares.filter(e => e.estado === 'Disponible').length;
        const prestados   = ejemplares.filter(e => e.estado === 'Prestado').length;
        const sinStock    = total - disponibles - prestados;

        document.getElementById('health-total').textContent     = total;
        document.getElementById('health-available').textContent = disponibles;
        document.getElementById('health-borrowed').textContent  = prestados;
        document.getElementById('health-out').textContent       = sinStock;
        document.getElementById('health-rate').textContent      = total ? Math.round((prestados / total) * 100) + '%' : '0%';
    }

    function resetEjemplares() {
        document.getElementById('exemplars-count').textContent = '0 ejemplares';
        document.getElementById('exemplars-table-wrap').hidden = true;
        document.getElementById('exemplars-hint').hidden = false;
        document.getElementById('exemplars-tbody').innerHTML = '';
    }

    function fillEjemplares(ejemplares) {
        document.getElementById('exemplars-count').textContent = ejemplares.length + ' ejemplares';
        document.getElementById('exemplars-table-wrap').hidden = ejemplares.length === 0;
        document.getElementById('exemplars-hint').hidden = ejemplares.length > 0;

        document.getElementById('exemplars-tbody').innerHTML = ejemplares.map(e => `
            <tr>
                <td class="exemplar-id">EJ-${String(Number(e.id_ejemplar) || 0).padStart(4, '0')}</td>
                <td>${esc(e.estado)}</td>
            </tr>`).join('');
    }

    function actualizarContadorSinopsis() {
        const cont = document.getElementById('synopsis-count');
        if (cont) cont.textContent = document.getElementById('detail-synopsis-input').value.length;
    }

    // EVENTOS

    document.getElementById('add-book-btn').addEventListener('click', () => abrirDetalle());
    document.getElementById('empty-add-btn').addEventListener('click', () => abrirDetalle());
    document.getElementById('detail-save-btn').addEventListener('click', guardarLibro);
    document.getElementById('detail-close-btn').addEventListener('click', cerrarDetalle);
    document.getElementById('detail-cancel-btn').addEventListener('click', cerrarDetalle);
    document.getElementById('detail-backdrop').addEventListener('click', cerrarDetalle);

    document.getElementById('detail-delete-btn').addEventListener('click', () => {
        document.getElementById('confirm-book-title').textContent = '«' + (libroActual?.titulo || '') + '»';
        document.getElementById('confirm-modal').hidden = false;
    });
    document.getElementById('confirm-cancel-btn').addEventListener('click', () => {
        document.getElementById('confirm-modal').hidden = true;
    });
    document.getElementById('confirm-backdrop').addEventListener('click', () => {
        document.getElementById('confirm-modal').hidden = true;
    });
    document.getElementById('confirm-delete-btn').addEventListener('click', eliminarLibro);

    document.getElementById('copies-minus').addEventListener('click', () => {
        const v = parseInt(document.getElementById('detail-total-copies').value) || 1;
        document.getElementById('detail-total-copies').value = Math.max(1, v - 1);
    });
    document.getElementById('copies-plus').addEventListener('click', () => {
        const v = parseInt(document.getElementById('detail-total-copies').value) || 1;
        document.getElementById('detail-total-copies').value = Math.min(999, v + 1);
    });

    document.getElementById('filter-status').addEventListener('change', () => cargarLibros(1));
    document.getElementById('filter-category').addEventListener('change', () => cargarLibros(1));
    document.getElementById('sort-by').addEventListener('change', () => cargarLibros(1));

    // Portada manual
    document.getElementById('cover-preview').addEventListener('click', () => {
        document.getElementById('detail-cover-file').click();
    });
    document.getElementById('detail-cover-change')?.addEventListener('click', e => {
        e.stopPropagation();
        document.getElementById('detail-cover-file').click();
    });
    document.getElementById('detail-cover-file').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('detail-cover-img').src = e.target.result;
            document.getElementById('detail-cover-img').hidden = false;
            document.getElementById('cover-placeholder').hidden = true;
            portadaUrlTemp = null;
        };
        reader.readAsDataURL(file);
    });

    // Buscar varias portadas
    document.getElementById('btn-buscar-portada')?.addEventListener('click', async () => {
        const titulo = document.getElementById('detail-title-input').value.trim();
        const isbn = document.getElementById('detail-isbn-input').value.trim();
        const autor = document.getElementById('detail-author-input').value.trim();
        if (!titulo && !isbn) return toast('Escribe el título o el ISBN primero', 'error');

        const boton = document.getElementById('btn-buscar-portada');
        boton.disabled = true;
        mostrarCargaPortadas();
        try {
            const data = await get('buscar_portada', { titulo, isbn, autor });
            if (data.status === 'success' && data.data?.portadas?.length) {
                mostrarSelectorPortadas(data.data.portadas);
            } else {
                document.getElementById('cover-select-modal').hidden = true;
                toast(data.mensaje || 'No se encontraron portadas', 'error');
            }
        } catch (e) {
            document.getElementById('cover-select-modal').hidden = true;
            toast('Error al buscar portadas', 'error');
        } finally {
            boton.disabled = false;
        }
    });

    // Generar sinopsis
    document.getElementById('btn-generar-sinopsis')?.addEventListener('click', async () => {
        const titulo = document.getElementById('detail-title-input').value.trim();
        const isbn = document.getElementById('detail-isbn-input').value.trim();
        const autor = document.getElementById('detail-author-input').value.trim();
        if (!titulo && !isbn) return toast('Escribe el título o el ISBN primero', 'error');

        let lang = 'es';
        const nombreIdioma = document.getElementById('detail-language').value.toLocaleLowerCase();
        if (nombreIdioma.includes('inglés') || nombreIdioma.includes('english')) lang = 'en';
        else if (nombreIdioma.includes('francés') || nombreIdioma.includes('français')) lang = 'fr';
        else if (nombreIdioma.includes('portugués') || nombreIdioma.includes('português')) lang = 'pt';
        else if (nombreIdioma.includes('alemán') || nombreIdioma.includes('deutsch')) lang = 'de';

        const boton = document.getElementById('btn-generar-sinopsis');
        boton.disabled = true;
        toast('Generando sinopsis...', 'info');
        try {
            const data = await get('generar_sinopsis', { titulo, isbn, autor, idioma: lang });
            if (data.status === 'success' && data.data?.sinopsis) {
                document.getElementById('detail-synopsis-input').value = data.data.sinopsis;
                actualizarContadorSinopsis();
                toast('Sinopsis generada', 'success');
            } else {
                toast(data.mensaje || 'No se pudo generar', 'error');
            }
        } catch (e) {
            toast('Error al generar sinopsis', 'error');
        } finally {
            boton.disabled = false;
        }
    });

    // Coautores
    document.getElementById('detail-coauthors-input').addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const val = e.target.value.trim().replace(/,$/, '');
            if (val && !coautores.includes(val)) {
                coautores.push(val);
                renderCoautores();
            }
            e.target.value = '';
        }
    });

    document.getElementById('detail-synopsis-input').addEventListener('input', actualizarContadorSinopsis);

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('book-detail-overlay').hidden) {
            cerrarDetalle();
        }
    });

    // Cerrar modal de portadas
    document.getElementById('cover-select-close')?.addEventListener('click', () => {
        document.getElementById('cover-select-modal').hidden = true;
    });

    // INICIO
    const idLibroSolicitado = Number(parametrosBusqueda.get('open_book'));
    if (Number.isInteger(idLibroSolicitado) && idLibroSolicitado > 0) {
        cargarCatalogos().then(() => abrirDetalle(idLibroSolicitado));
        window.history.replaceState({}, '', window.location.pathname);
    } else {
        cargarCatalogos();
    }
    cargarLibros(1);
});