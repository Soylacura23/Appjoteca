document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const menuToggleBtn     = document.querySelector('.menu-toggle-btn');
    const mobileMenu        = document.querySelector('.mobile-menu');
    const mobileMenuClose   = document.querySelector('.mobile-menu-close');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');


    /* Menú móvil */

    function openMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.add('open');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.add('open');
        document.body.classList.add('menu-open');
        if (menuToggleBtn) menuToggleBtn.setAttribute('aria-expanded', 'true');
        if (mobileMenuClose) {
            setTimeout(function () { mobileMenuClose.focus(); }, 80);
        }
    }

    function closeMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.remove('open');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.remove('open');
        document.body.classList.remove('menu-open');
        if (menuToggleBtn) {
            menuToggleBtn.setAttribute('aria-expanded', 'false');
            menuToggleBtn.focus();
        }
    }

    if (menuToggleBtn && mobileMenu) {
        menuToggleBtn.addEventListener('click', openMobileMenu);
    }
    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', closeMobileMenu);
    }
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);
    }

    // Al hacer clic en un enlace real del menú móvil, cerrar el panel
    document.querySelectorAll('.mobile-nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            const href = link.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('#')) {
                closeMobileMenu();
            }
        });
    });

    /* Escape y resize */

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;

        if (mobileMenu && mobileMenu.classList.contains('open')) {
            closeMobileMenu();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth < 900) return;
        closeMobileMenu();
    });
});