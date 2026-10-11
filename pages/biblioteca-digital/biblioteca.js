/* biblioteca.js — Lógica Principal */

   document.addEventListener('DOMContentLoaded', function () {
       'use strict';

       // Carrusel de destacados — botones prev/next
    var carousel  = document.getElementById('featuredCarousel');
    var prevBtn   = document.getElementById('carouselPrev');
    var nextBtn   = document.getElementById('carouselNext');
    var fadeLeft  = document.querySelector('.carousel-fade--left');
    var fadeRight = document.querySelector('.carousel-fade--right');

    if (carousel && prevBtn && nextBtn) {

        /**
         * Calcula la cantidad de píxeles a desplazar (ancho de 1 tarjeta + gap).
         */
        function getScrollStep() {
            var card = carousel.querySelector('.featured-card');
            if (!card) return 320;
            var gap = parseInt(getComputedStyle(carousel).gap) || 16;
            return card.offsetWidth + gap;
        }

        /**
         * Actualiza el estado habilitado/deshabilitado de los botones
         * y la visibilidad de los degradados laterales.
         */
        function syncControls() {
            var atStart = carousel.scrollLeft <= 2;
            var atEnd   = carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 2;

            prevBtn.disabled = atStart;
            nextBtn.disabled = atEnd;

            if (fadeLeft)  fadeLeft.style.opacity  = atStart ? '0' : '1';
            if (fadeRight) fadeRight.style.opacity = atEnd   ? '0' : '1';
        }

        prevBtn.addEventListener('click', function () {
            carousel.scrollBy({ left: -getScrollStep(), behavior: 'smooth' });
        });

        nextBtn.addEventListener('click', function () {
            carousel.scrollBy({ left: getScrollStep(), behavior: 'smooth' });
        });

        carousel.addEventListener('scroll', syncControls, { passive: true });
        window.addEventListener('resize', syncControls);
        syncControls(); // Estado inicial
    }


    const grid = document.getElementById('landing-book-grid');
    const catalogFilters = { materia: '', availability: '', sort: 'recommended' };

    function escaparHTML(value) {
        return String(value ?? '').replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[character]);
    }

    function rutaPortada(portada) {
        if (!portada) return '/Appjoteca/assets/images/books/default-cover.jpg';
        if (/^https?:\/\//i.test(portada)) return portada;
        return `/Appjoteca/${portada.replace(/^\/+/, '')}`;
    }

    function renderizarLibros(libros) {
        if (!grid) return;
        if (!libros.length) {
            grid.innerHTML = '<p class="catalog-empty-message">No hay libros que coincidan con estos filtros.</p>';
            return;
        }

        grid.innerHTML = libros.map((libro) => {
            const id = Number(libro.id);
            const detailUrl = `../biblioteca-catalogo/vista-libro/book-view.php?id=${id}`;
            const title = escaparHTML(libro.titulo);
            const disponibles = Number(libro.disponibles) || 0;
            const reservados = Number(libro.reservados) || 0;
            const prestados = Number(libro.prestados) || 0;
            const status = disponibles > 0 ? 'Disponible' : reservados > 0 ? 'Reservado' : prestados > 0 ? 'Prestado' : 'No disponible';
            return `
                <article class="book-card" aria-label="${title}">
                    <div class="book-cover-wrapper">
                        <a href="${detailUrl}">
                            <img src="${escaparHTML(rutaPortada(libro.portada))}"
                                 alt="${title}" class="book-cover" loading="lazy">
                        </a>
                        <span class="book-badge">${escaparHTML(libro.categoria || 'Literatura')}</span>
                        <div class="book-card-cta">
                            <a class="book-card-cta-btn" href="${detailUrl}">
                                <span class="material-symbols-outlined">auto_stories</span>
                                Ver Libro
                            </a>
                        </div>
                    </div>
                    <div class="book-info">
                        <h4 class="book-title">${title}</h4>
                        <p class="book-author">${escaparHTML(libro.autor || 'Sin autor')}</p>
                        <div class="landing-book-stock" aria-label="Estado y existencias">
                            <strong>${status}</strong>
                            <span>Disponible: ${disponibles}</span>
                            <span>Reservado: ${reservados}</span>
                            <span>Prestado: ${prestados}</span>
                            <span>Stock total: ${Number(libro.total) || 0}</span>
                        </div>
                    </div>
                </article>`;
        }).join('');
    }

    async function cargarLibros() {
        if (!grid) return;

        const params = new URLSearchParams({
            page: '1',
            limit: '8',
            materia: catalogFilters.materia,
            availability: catalogFilters.availability,
            sort: catalogFilters.sort
        });

        try {
            const response = await fetch(`../biblioteca-catalogo/catalogo/back.php?${params}`);
            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
            const data = await response.json();
            if (!data.exito) throw new Error(data.mensaje || 'No se pudo cargar el catálogo.');

            window.catalogFilters.setMaterias(data.materias || []);
            renderizarLibros(data.libros || []);
        } catch (error) {
            console.error('Error al filtrar el catálogo:', error);
            grid.innerHTML = '<p class="catalog-empty-message">No se pudo cargar el catálogo. Inténtalo de nuevo.</p>';
        }
    }

    document.addEventListener('catalog:applyFilters', (event) => {
        Object.assign(catalogFilters, event.detail);
        cargarLibros();
    });

    cargarLibros();


    // Botón Ver Todo
    var verTodoBtn = document.querySelector('.btn-ver-todo');
    if (verTodoBtn) {
        verTodoBtn.addEventListener('click', function () {
            window.location.href = '/Appjoteca/pages/biblioteca-catalogo/catalogo/catalogo.php';
        });
    }

    function verLibro(){
        window.location.href="../biblioteca-catalogo/vista-libro/book-view.php";
    }

});