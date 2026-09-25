 (() => {
    'use strict';

    /* ════════════════════════════════════════════
       Config
    ════════════════════════════════════════════ */
    window.getCSRFToken = function() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };
    const PROFILE_IMAGE = window.AppUser.foto;
    const PROFILE_NAME  = window.AppUser.nombre;
    const PROFILE_ROLE  = window.AppUser.rol;

    /* ════════════════════════════════════════════
       Elementos Globales
    ════════════════════════════════════════════ */
    const overlay = document.getElementById('overlay');
    const menuOffCanvas = document.querySelector('.menu-off-canva');
    const notifContainer = document.getElementById('notification-container');
    
    // Elementos exclusivos del Dashboard (Bibliotecario)
    const sidebar = document.querySelector('.sidebar');
    const topbar = document.querySelector('.topbar');
    const hamburguer = document.querySelector('#menu-activar');

    /* ════════════════════════════════════════════
       Controladores de Paneles y Overlay
    ════════════════════════════════════════════ */
    function showOverlay() {
        if (!overlay) return;
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function hideOverlay() {
        if (!overlay) return;
        // Solo quitar el overlay si NINGÚN panel está abierto
        const profileOpen = menuOffCanvas && menuOffCanvas.classList.contains('open');
        const notifOpen   = notifContainer && notifContainer.classList.contains('open');
        const sidebarOpen = sidebar && sidebar.classList.contains('open');

        if (!profileOpen && !notifOpen && !sidebarOpen) {
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // Funciones de cierre individuales
    function closeProfileMenu() {
        if (!menuOffCanvas) return;
        menuOffCanvas.classList.remove('open');
        hideOverlay();
    }

    function closeNotifications(skipOverlay = false) {
        if (!notifContainer) return;
        notifContainer.classList.remove('open');
        if (!skipOverlay && window.innerWidth < 900) hideOverlay();
        
        document.querySelectorAll('.notification-tray').forEach(btn => {
            btn.classList.remove('active');
            btn.setAttribute('aria-expanded', 'false');
        });
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (topbar) topbar.classList.remove('above');
        hideOverlay();
    }

    // Cerrar todo al hacer clic en el overlay
    if (overlay) {
        overlay.addEventListener('click', () => {
            closeProfileMenu();
            closeNotifications();
            closeSidebar();
        });
    }

    /* ════════════════════════════════════════════
       Sidebar (Exclusivo Bibliotecario)
    ════════════════════════════════════════════ */
    if (hamburguer) {
        hamburguer.addEventListener('click', () => {
            
            closeProfileMenu();
            closeNotifications(true);

            overlay.classList.toggle('show');
            sidebar.classList.toggle('open');
            if (topbar) topbar.classList.toggle('above');

            if (sidebar.classList.contains('open') && overlay.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
    }

    /* ════════════════════════════════════════════
       Botón de perfil — creación dinámica
    ════════════════════════════════════════════ */
    function createProfileButton(containerId, sizeClass = 'btn-md') {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = `
            <button class="profile-button ${sizeClass}" aria-label="Abrir menú de perfil" type="button">
              <img src="${PROFILE_IMAGE}" alt="Foto de perfil" class="img-profile">
            </button>
        `;
    }

    createProfileButton('profile-button-topbar', 'btn-md');
    createProfileButton('profile-button-menu',   'btn-lg');

    /* ════════════════════════════════════════════
       Menú off-canvas de perfil
    ════════════════════════════════════════════ */
    const menuProfileContainer = document.getElementById('profile-button-menu');

    if (menuProfileContainer) {
        const nameEl = document.createElement('p');
        nameEl.className = 'menu-profile-name';
        nameEl.textContent = PROFILE_NAME;

        const roleEl = document.createElement('p');
        roleEl.className = 'menu-profile-role';
        roleEl.textContent = PROFILE_ROLE;

        menuProfileContainer.appendChild(nameEl);
        menuProfileContainer.appendChild(roleEl);
    }

    function openProfileMenu() {
        if (!menuOffCanvas) return;
        closeNotifications(true); 
        closeSidebar(); // Si el sidebar está abierto, lo cerramos
        menuOffCanvas.classList.add('open');
        showOverlay();
    }

    document.addEventListener('click', (e) => {
        if (e.target.closest('#profile-button-topbar .profile-button') || 
            e.target.closest('#profile-button-menu-footer .profile-button')) {
            openProfileMenu();
            return;
        }
    });

    const goBackBtn = document.querySelector('.menu-off-canva .arrow-back');
    if (goBackBtn) goBackBtn.addEventListener('click', closeProfileMenu);

    const routesPerRole = {
        "Estudiante": '/Appjoteca/pages/settings/configuracion.php',
        "Docente": 'Appjoteca/pages/settings/configuracion.php',
        "Bibliotecario": '/Appjoteca/dashboards/bibliotecario/pages/ajustes/ajustes.php'
    };

    const configBtn = document.querySelector('.menu-off-canva .config');
    if (configBtn) {
        configBtn.addEventListener('click', () => {

            const destination = routesPerRole[PROFILE_ROLE];

            if (destination) {
                window.location.href = destination;
            }
            
        });
    }
     //boton cerrar sesion
    const signOutBtn = document.querySelector('.menu-off-canva .signout');
    if (signOutBtn) {
        signOutBtn.addEventListener('click', () => {
            window.location.href = '/Appjoteca/backend/auth/logout.php';
        });
    }

    /* ════════════════════════════════════════════
       Bandeja de notificaciones
    ════════════════════════════════════════════ */
    function openNotifications() {
        if (!notifContainer) return;
        closeProfileMenu(); 
        closeSidebar();
        
        notifContainer.classList.add('open');
        if (window.innerWidth < 900) showOverlay();
        
        document.querySelectorAll('.notification-tray').forEach(btn => {
            btn.classList.add('active');
            btn.setAttribute('aria-expanded', 'true');
        });
    }

    document.addEventListener('click', (e) => {
        if (e.target.closest('.notification-tray')) {
            if (notifContainer && notifContainer.classList.contains('open')) {
                closeNotifications();
            } else {
                openNotifications();
            }
            return;
        }

        if (
            notifContainer &&
            notifContainer.classList.contains('open') &&
            !e.target.closest('#notification-container') &&
            !e.target.closest('.notification-tray') &&
            window.innerWidth >= 900
        ) {
            closeNotifications();
        }
    });

    const markAllBtn = document.getElementById('mark-all-read');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            document.querySelectorAll('.notification-badge').forEach(b => b.classList.add('hidden'));
            const pill = document.querySelector('.count-pill');
            if (pill) pill.style.display = 'none';
        });
    }

    if (notifContainer) {
        notifContainer.addEventListener('click', (e) => {
            const item = e.target.closest('.notification-item');
            if (item) {
                item.classList.remove('unread');
                if (notifContainer.querySelectorAll('.notification-item.unread').length === 0) {
                    document.querySelectorAll('.notification-badge').forEach(b => b.classList.add('hidden'));
                    const pill = notifContainer.querySelector('.count-pill');
                    if (pill) pill.style.display = 'none';
                }
            }
        });
    }

    /* ════════════════════════════════════════════
       Búsqueda (formulario)
    ════════════════════════════════════════════ */
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const q = searchForm.querySelector('[name="query"]');
            console.log('[AppJoteca] Búsqueda:', q ? q.value : '');
        });
    }

    /* ════════════════════════════════════════════
       Teclado y Resize
    ════════════════════════════════════════════ */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeProfileMenu();
            closeNotifications();
            closeSidebar();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 900 && overlay && overlay.classList.contains('show')) {
            const profileOpen = menuOffCanvas && menuOffCanvas.classList.contains('open');
            const sidebarOpen = sidebar && sidebar.classList.contains('open');
            if (!profileOpen && !sidebarOpen) {
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        }
    });

})();