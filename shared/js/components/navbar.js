  document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const href = link.getAttribute('href');

            if (href && href !== '#' && !href.startsWith('#')) {
                return; 
            }

            e.preventDefault();
            navLinks.forEach(function (l) { l.classList.remove('active'); });
            link.classList.add('active');

            // Sincronizar estado activo con el menú móvil
            const target = link.getAttribute('data-nav');
            if (target) {
                document.querySelectorAll('.mobile-nav-link').forEach(function (ml) {
                    ml.classList.toggle('active', ml.getAttribute('data-nav') === target);
                });
            }
        });
    });


    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');

    mobileNavLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const href = link.getAttribute('href');

            if (href && href !== '#' && !href.startsWith('#')) {
                closeMobileMenu();
                return;
            }

            e.preventDefault();
            mobileNavLinks.forEach(function (l) { l.classList.remove('active'); });
            link.classList.add('active');

            const target = link.getAttribute('data-nav');
            if (target) {
                navLinks.forEach(function (dl) {
                    dl.classList.toggle('active', dl.getAttribute('data-nav') === target);
                });
            }

            closeMobileMenu();
        });
    });


    const menuToggleBtn    = document.querySelector('.menu-toggle-btn');
    const mobileMenu       = document.querySelector('.mobile-menu');
    const mobileMenuClose  = document.querySelector('.mobile-menu-close');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');

    function openMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.add('open');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.add('open');
        document.body.classList.add('menu-open');
        if (menuToggleBtn) menuToggleBtn.setAttribute('aria-expanded', 'true');

        if (mobileMenuClose) {
            setTimeout(function () { mobileMenuClose.focus(); }, 80);
        }
        console.log('[AppJoteca] Menú móvil abierto');
    }

    function closeMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.remove('open');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.remove('open');
        document.body.classList.remove('menu-open');
        if (menuToggleBtn) menuToggleBtn.setAttribute('aria-expanded', 'false');
        // Devolver el foco al botón que abrió el menú
        if (menuToggleBtn) menuToggleBtn.focus();
        console.log('[AppJoteca] Menú móvil cerrado');
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

    // Cerrar con Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mobileMenu && mobileMenu.classList.contains('open')) {
            closeMobileMenu();
        }
    });


    const searchToggleBtn   = document.querySelector('.search-toggle-btn');
    const searchMobileArea  = document.querySelector('.topbar-search-mobile');
    const searchMobileInput = searchMobileArea
        ? searchMobileArea.querySelector('input')
        : null;

    if (searchToggleBtn && searchMobileArea) {
        searchToggleBtn.addEventListener('click', function () {
            const isOpen = searchMobileArea.classList.toggle('open');
            searchToggleBtn.setAttribute('aria-expanded', String(isOpen));
            if (isOpen && searchMobileInput) {
                searchMobileInput.focus();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && searchMobileArea.classList.contains('open')) {
                searchMobileArea.classList.remove('open');
                searchToggleBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }


    const topbarSearchInput = document.querySelector('.topbar-search-input');

    if (topbarSearchInput && searchMobileInput) {
        topbarSearchInput.addEventListener('input', function () {
            searchMobileInput.value = topbarSearchInput.value;
        });
        searchMobileInput.addEventListener('input', function () {
            topbarSearchInput.value = searchMobileInput.value;
        });
    }


   
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 900) {
            closeMobileMenu();
            if (searchMobileArea) {
                searchMobileArea.classList.remove('open');
                if (searchToggleBtn) searchToggleBtn.setAttribute('aria-expanded', 'false');
            }
        }
    });

    (function marcarActivoPorURL() {
        const path = window.location.pathname;

        document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(function (link) {
            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('#')) return;

            // Compara el último segmento de la URL con el href
            const hrefFile = href.split('/').pop();
            if (hrefFile && path.endsWith(hrefFile)) {
                document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(function (l) {
                    l.classList.remove('active');
                });
                link.classList.add('active');

                // Sincronizar con su contraparte (desktop ↔ móvil)
                const target = link.getAttribute('data-nav');
                if (target) {
                    document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(function (l) {
                        if (l.getAttribute('data-nav') === target) {
                            l.classList.add('active');
                        }
                    });
                }
            }
        });
    })();

});