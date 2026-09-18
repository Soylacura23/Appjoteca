document.addEventListener('DOMContentLoaded', () => {
    const estado = {
        paginaActual: 1,
        categoriaActual: 'General',
        busqueda: '',
        debounceTimer: null
    };

    const gridContainer = document.getElementById('bookshelf-grid');
    const paginationContainer = document.querySelector('.pagination-container');
    const searchInputs = document.querySelectorAll('.catalog-search-input');
    const filterChips = document.querySelectorAll('.filter-chip');

    inicializar();

    function inicializar() {
        cargarLibros();
        registrarEventos();
    }

    async function cargarLibros() {
        mostrarCargando();

        try {
            const params = new URLSearchParams({
                page: estado.paginaActual,
                categoria: estado.categoriaActual,
                query: estado.busqueda
            });

            const response = await fetch(`back.php?${params.toString()}`);
            const data = await response.json();

            if (data.exito) {
                renderizarTarjetas(data.libros);
                renderizarPaginacion(data.totalPaginas, data.paginaActual);
            } else {
                mostrarError(data.mensaje || 'Error al obtener los datos.');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarError('No se pudo conectar con el servidor.');
        }
    }

    function renderizarTarjetas(libros) {
        gridContainer.innerHTML = '';

        if (!libros || libros.length === 0) {
            gridContainer.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem; color: #888;">
                    <p style="font-size: 1.125rem;">No se encontraron libros disponibles.</p>
                </div>`;
            return;
        }

        const fragmento = document.createDocumentFragment();

        libros.forEach(libro => {
            const card = document.createElement('article');
            card.className = 'catalog-book-card';
            card.dataset.id = libro.id;

            const rutaPortada = libro.portada && libro.portada.trim() !== ''
                ? `../../${libro.portada}`
                : '../../assets/images/default-cover.jpg';

            card.innerHTML = `
                <div class="book-cover-wrap">
                    <img src="${escapar(rutaPortada)}" alt="${escapar(libro.titulo)}" loading="lazy">
                    <div class="book-hover-overlay">
                        <span class="overlay-action">Ver Detalles</span>
                    </div>
                </div>
                <h3 class="book-title">${escapar(libro.titulo)}</h3>
                <p class="book-author">${escapar(libro.autor)}</p>
            `;

            card.addEventListener('click', () => {
                window.location.href = `../detalle-libro/index.php?id=${libro.id}`;
            });

            fragmento.appendChild(card);
        });

        gridContainer.appendChild(fragmento);
    }

    function renderizarPaginacion(totalPaginas, paginaActual) {
        if (!paginationContainer || totalPaginas <= 1) {
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        let html = `
            <button class="page-btn page-nav" id="btn-prev" ${paginaActual === 1 ? 'disabled' : ''}>
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <div class="page-numbers">`;

        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || (i >= paginaActual - 1 && i <= paginaActual + 1)) {
                html += `<button class="page-btn ${i === paginaActual ? 'active' : ''}" data-page="${i}">${i}</button>`;
            } else if (i === paginaActual - 2 || i === paginaActual + 2) {
                html += `<span class="page-ellipsis">...</span>`;
            }
        }

        html += `
            </div>
            <button class="page-btn page-nav" id="btn-next" ${paginaActual === totalPaginas ? 'disabled' : ''}>
                <span class="material-symbols-outlined">chevron_right</span>
            </button>`;

        paginationContainer.innerHTML = html;

        paginationContainer.querySelectorAll('.page-numbers .page-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                estado.paginaActual = parseInt(e.target.dataset.page);
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

        filterChips.forEach(chip => {
            chip.addEventListener('click', (e) => {
                filterChips.forEach(c => c.classList.remove('active'));
                e.target.classList.add('active');

                estado.categoriaActual = e.target.textContent.trim();
                estado.paginaActual = 1;
                cargarLibros();
            });
        });
    }

    function mostrarCargando() {
        gridContainer.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 0;">
                <span class="material-symbols-outlined" style="font-size: 2.5rem; color: var(--primary);">progress_activity</span>
            </div>`;
    }

    function mostrarError(msg) {
        gridContainer.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #ff6b6b;">
                <p>${escapar(msg)}</p>
            </div>`;
    }

    function escapar(cadena) {
        if (!cadena) return '';
        return String(cadena)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
