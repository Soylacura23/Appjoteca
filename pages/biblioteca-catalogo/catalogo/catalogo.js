document.addEventListener('DOMContentLoaded', () => {
    // 1. Estado global del catálogo
    const estado = {
        paginaActual: 1,
        materiaId: 0,          // 0 = todas las materias
        materiaNombre: 'General',
        busqueda: '',
        debounceTimer: null
    };

    // 2. Referencias DOM
    const gridContainer = document.getElementById('bookshelf-grid') || document.querySelector('main section.grid, main .bookshelf-grid');
    const paginationContainer = document.querySelector('.pagination-container') || document.querySelector('footer');
    const searchInputs = document.querySelectorAll('.catalog-search-input, input[placeholder*="Buscar"]');
    const filterContainer = document.querySelector('.filter-categories') || document.querySelector('section.filter-categories');

    inicializar();

    function inicializar() {
        if (!gridContainer) {
            console.error('No se encontró el contenedor de la cuadrícula de libros (#bookshelf-grid).');
            return;
        }
        cargarLibros();
        registrarEventos();
    }

    // 3. Petición al backend
    async function cargarLibros() {
        mostrarCargando();

        try {
            const params = new URLSearchParams({
                page: estado.paginaActual,
                materia: estado.materiaId,
                query: estado.busqueda
            });

            const response = await fetch(`back.php?${params.toString()}`);

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const data = await response.json();

            if (data.exito) {
                if (data.materias && Array.isArray(data.materias)) {
                    renderizarMaterias(data.materias);
                }
                renderizarTarjetas(data.libros);
                renderizarPaginacion(data.totalPaginas, data.paginaActual);
            } else {
                mostrarMensajeVacio(data.mensaje || 'No se encontraron libros.');
            }
        } catch (error) {
            console.error('Error al cargar libros:', error);
            mostrarMensajeVacio('No se pudo conectar con el servidor. Inténtalo de nuevo más tarde.');
        }
    }

    // 4. Chips de materias (categorías) desde la BD
    function renderizarMaterias(materias) {
        if (!filterContainer) return;
        if (filterContainer.dataset.loaded === 'true') return;

        filterContainer.innerHTML = '';

        // Chip "General" (todas)
        const btnGeneral = crearChipMateria(0, 'General');
        filterContainer.appendChild(btnGeneral);

        materias.forEach(m => {
            const btn = crearChipMateria(m.id, m.nombre);
            filterContainer.appendChild(btn);
        });

        filterContainer.dataset.loaded = 'true';
    }

    function crearChipMateria(id, nombre) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'filter-chip whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-all';
        btn.dataset.id = id;
        btn.textContent = nombre;

        if (Number(id) === Number(estado.materiaId)) {
            btn.classList.add('active', 'bg-primary', 'text-on-primary');
        } else {
            btn.classList.add('bg-surface-container-high', 'text-on-surface-variant');
        }

        btn.addEventListener('click', () => {
            // Quitar activo de todos
            filterContainer.querySelectorAll('button').forEach(c => {
                c.classList.remove('active', 'bg-primary', 'text-on-primary');
                c.classList.add('bg-surface-container-high', 'text-on-surface-variant');
            });

            btn.classList.add('active', 'bg-primary', 'text-on-primary');
            btn.classList.remove('bg-surface-container-high', 'text-on-surface-variant');

            estado.materiaId = Number(id);
            estado.materiaNombre = nombre;
            estado.paginaActual = 1;
            cargarLibros();
        });

        return btn;
    }

    // 5. Tarjetas de libros
    function renderizarTarjetas(libros) {
        gridContainer.innerHTML = '';

        if (!libros || libros.length === 0) {
            mostrarMensajeVacio('No hay libros disponibles en esta categoría.');
            return;
        }

        const fragmento = document.createDocumentFragment();

        libros.forEach(libro => {
            const card = document.createElement('div');
            card.className = 'group cursor-pointer catalog-book-card flex flex-col';
            card.dataset.id = libro.id;

            // Ruta de portada (compatible con tu estructura de uploads)
            let rutaPortada = '../../../assets/images/default-cover.jpg';
            if (libro.portada && libro.portada.trim() !== '') {
                if (libro.portada.startsWith('http')) {
                    rutaPortada = libro.portada;
                } else if (libro.portada.startsWith('uploads/')) {
                    rutaPortada = '../../../' + libro.portada;
                } else if (libro.portada.startsWith('assets/')) {
                    rutaPortada = '../../../' + libro.portada;
                } else {
                    rutaPortada = '../../../' + libro.portada;
                }
            }

            card.innerHTML = `
                <div class="relative aspect-[2/3] mb-4 overflow-hidden rounded-lg book-glow book-cover-wrap">
                    <img 
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                        src="${escaparHTML(rutaPortada)}" 
                        alt="${escaparHTML(libro.titulo)}" 
                        loading="lazy"
                        onerror="this.onerror=null; this.src='../../assets/images/default-cover.jpg';"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4 book-hover-overlay">
                        <span class="text-primary font-bold text-xs tracking-widest uppercase overlay-action">Ver Detalles</span>
                    </div>
                </div>
                <h3 class="font-headline text-lg font-bold text-on-surface group-hover:text-primary transition-colors book-title line-clamp-2">
                    ${escaparHTML(libro.titulo)}
                </h3>
                <p class="font-body text-xs text-neutral-500 mt-1 uppercase tracking-tighter book-author">
                    ${escaparHTML(libro.autor)}
                </p>
            `;

            card.addEventListener('click', () => {
                window.location.href = `../vista-libro/book-view.php?id=${libro.id}`;
            });

            fragmento.appendChild(card);
        });

        gridContainer.appendChild(fragmento);
    }

    // 6. Paginación
    function renderizarPaginacion(totalPaginas, paginaActual) {
        if (!paginationContainer) return;

        if (totalPaginas <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = `
            <div class="flex items-center justify-center gap-2 mt-12 w-full">
                <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant/20 text-on-surface hover:bg-primary/10 hover:border-primary/50 transition-all ${paginaActual === 1 ? 'opacity-40 cursor-not-allowed' : ''}" id="btn-prev" ${paginaActual === 1 ? 'disabled' : ''}>
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <div class="flex gap-2 page-numbers">`;

        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || (i >= paginaActual - 1 && i <= paginaActual + 1)) {
                const esActiva = i === paginaActual;
                html += `
                    <button class="w-10 h-10 flex items-center justify-center rounded-lg ${esActiva ? 'bg-primary text-on-primary font-bold' : 'border border-outline-variant/20 text-on-surface hover:bg-surface-container'} transition-all" data-page="${i}">
                        ${i}
                    </button>`;
            } else if (i === paginaActual - 2 || i === paginaActual + 2) {
                html += `<span class="w-8 h-10 flex items-center justify-center text-neutral-500">...</span>`;
            }
        }

        html += `
                </div>
                <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant/20 text-on-surface hover:bg-primary/10 hover:border-primary/50 transition-all ${paginaActual === totalPaginas ? 'opacity-40 cursor-not-allowed' : ''}" id="btn-next" ${paginaActual === totalPaginas ? 'disabled' : ''}>
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>`;

        paginationContainer.innerHTML = html;

        paginationContainer.querySelectorAll('.page-numbers button[data-page]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                estado.paginaActual = parseInt(e.currentTarget.dataset.page);
                cargarLibros();
            });
        });

        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');

        if (btnPrev && !btnPrev.disabled) {
            btnPrev.addEventListener('click', () => {
                estado.paginaActual--;
                cargarLibros();
            });
        }

        if (btnNext && !btnNext.disabled) {
            btnNext.addEventListener('click', () => {
                estado.paginaActual++;
                cargarLibros();
            });
        }
    }

    // 7. Eventos de búsqueda
    function registrarEventos() {
        searchInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                clearTimeout(estado.debounceTimer);
                estado.debounceTimer = setTimeout(() => {
                    estado.busqueda = e.target.value.trim();
                    estado.paginaActual = 1;
                    cargarLibros();
                }, 300);
            });
        });
    }

    // 8. UI estados
    function mostrarCargando() {
        gridContainer.innerHTML = `
            <div class="col-span-full flex justify-center items-center py-20 text-primary">
                <span class="material-symbols-outlined animate-spin text-5xl">progress_activity</span>
            </div>`;
    }

    function mostrarMensajeVacio(mensaje) {
        gridContainer.innerHTML = `
            <div class="col-span-full text-center py-16 text-neutral-400">
                <span class="material-symbols-outlined text-4xl mb-2 opacity-60">menu_book</span>
                <p class="text-base font-medium">${escaparHTML(mensaje)}</p>
            </div>`;
    }

    // 9. Sanitización XSS
    function escaparHTML(cadena) {
        if (!cadena) return '';
        return String(cadena)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});