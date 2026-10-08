/* ================================================================
   biblioteca.js — Lógica Principal
   AppJoteca v2.0
   ================================================================ */

   document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ════════════════════════════════════════════
    // Tabs de catálogo
    // ════════════════════════════════════════════
    const tabButtons = document.querySelectorAll('.tab-btn');

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            tabButtons.forEach(function (btn) { btn.classList.remove('active'); });
            button.classList.add('active');

            const filterName = button.textContent.trim();
            console.log('[AppJoteca] Filtro activo:', filterName);
            // TODO: filtrar el grid de libros según la pestaña
        });
    });


    // ════════════════════════════════════════════
    // Carrusel de destacados — botones prev/next
    // ════════════════════════════════════════════
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


    // ════════════════════════════════════════════
    // Botón de filtros
    // ════════════════════════════════════════════
    var filterBtn = document.querySelector('.btn-filter');
    if (filterBtn) {
        filterBtn.addEventListener('click', function () {
            var category     = document.querySelector('#filterCategory')?.value || '';
            var availability = document.querySelector('#filterAvailability')?.value || '';
            console.log('[AppJoteca] Filtrar por:', { category, availability });
            // TODO: lógica de filtrado del grid
        });
    }


    // ════════════════════════════════════════════
    // Botón Ver Todo
    // ════════════════════════════════════════════
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