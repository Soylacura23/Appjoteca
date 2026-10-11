/* book-cards.js — Lógica de Tarjetas de Libros */

   (function () {
    'use strict';


    function initBookCards() {
        const bookCards = document.querySelectorAll('.book-card');

        bookCards.forEach(function (card) {
            // Accesibilidad: hacer la tarjeta enfocable
            if (!card.getAttribute('tabindex')) {
                card.setAttribute('tabindex', '0');
            }
            if (!card.getAttribute('role')) {
                card.setAttribute('role', 'button');
            }

            const ctaBtn = card.querySelector('.book-card-cta-btn');
            const title = card.querySelector('.book-title');

            // Clic en el botón CTA
            if (ctaBtn) {
                ctaBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const bookTitle = title ? title.textContent.trim() : 'Libro';
                    onBookOpen(bookTitle, card);
                });
            }

            // Clic en la tarjeta completa (fuera del CTA)
            card.addEventListener('click', function (e) {
                // Solo activar si no se hizo clic en el botón CTA
                if (!e.target.closest('.book-card-cta-btn')) {
                    const bookTitle = title ? title.textContent.trim() : 'Libro';
                    onBookOpen(bookTitle, card);
                }
            });

            // Teclado: Enter o Espacio activan la tarjeta
            card.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    if (ctaBtn) {
                        ctaBtn.click();
                    } else {
                        card.click();
                    }
                }
            });
        });
    }

    /**
     * Callback cuando se abre un libro.
     * @param {string} title - Título del libro
     * @param {HTMLElement} card - Elemento tarjeta
     */
    function onBookOpen(title, card) {
        console.log('[AppJoteca] Abriendo libro:', title);
        // TODO: Navegar a la vista de detalle del libro
        // Ejemplo: window.location.href = '/libro/' + encodeURIComponent(title);
    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBookCards);
    } else {
        initBookCards();
    }

})();