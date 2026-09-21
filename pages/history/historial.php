<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';
requiereRol([1, 2, 3]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Actividad — Appjoteca</title>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Estilos compartidos -->
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">

    <!-- Estilos específicos -->
    <link rel="stylesheet" href="historial.css">
</head>
<body>

    <!-- OVERLAY GLOBAL -->
    <div id="overlay" class="overlay" aria-hidden="true"></div>

    <!-- ══════════════════════════════════════════
         BANDEJA DE NOTIFICACIONES
    ══════════════════════════════════════════════ -->
    <?php include '../../shared/layouts/notifications.php'; ?>

    <!-- MENÚ OFF-CANVAS DE PERFIL -->
    <?php include '../../shared/layouts/menu-off-canvas.php'; ?>

    <!-- BARRA DE NAVEGACIÓN -->
    <header class="topbar" role="banner">
        <div class="topbar-inner">
            <a href="#" class="topbar-logo">
                <div class="logo" aria-hidden="true"></div>
                <span class="logo-text">AppJoteca</span>
            </a>
            <div class="topbar-search">
                <input type="text" class="topbar-search-input" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
                <span class="material-symbols-outlined topbar-search-icon">search</span>
            </div>
            <nav class="topbar-nav" aria-label="Navegación principal">
                <a href="#" class="nav-link" data-nav="catalogo">Catálogo</a>
                <a href="#" class="nav-link" data-nav="biblioteca">Mi Biblioteca</a>
                <a href="#" class="nav-link" data-nav="panel">Panel</a>
            </nav>
            <div class="topbar-actions">
                <button class="icon-btn search-toggle-btn" aria-label="Buscar" aria-expanded="false">
                    <span class="material-symbols-outlined">search</span>
                </button>
                <button class="notification-tray" aria-label="Notificaciones" aria-expanded="false">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notification-badge" aria-label="3 notificaciones sin leer"></span>
                </button>
                <div id="profile-button-topbar"></div>
                <button class="icon-btn menu-toggle-btn" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobileMenu">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </div>
        <div class="topbar-search-mobile" aria-hidden="true">
            <input type="text" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
        </div>
    </header>

    <!-- MENÚ MÓVIL -->
    <?php include '../../shared/layouts/menu-movil.php'; ?>

    <!-- ══════════════════════════════════════════
         CONTENIDO PRINCIPAL: HISTORIAL
    ══════════════════════════════════════════════ -->
    <div class="historial-layout">

        <!-- SIDEBAR EXPANDIBLE -->
        <aside class="historial-sidebar" id="historialSidebar" aria-label="Navegación de historial">
            <div class="sidebar-header">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Expandir o colapsar menú">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>

            <nav class="sidebar-nav" role="tablist">
                <div class="sidebar-item active" data-section="prestamos" role="tab" aria-selected="true" tabindex="0">
                    <span class="material-symbols-outlined">auto_stories</span>
                    <span class="sidebar-label">Préstamos</span>
                </div>
                <div class="sidebar-item" data-section="reservas" role="tab" aria-selected="false" tabindex="0">
                    <span class="material-symbols-outlined">bookmark_added</span>
                    <span class="sidebar-label">Reservas</span>
                </div>
                <div class="sidebar-item" data-section="devoluciones" role="tab" aria-selected="false" tabindex="0">
                    <span class="material-symbols-outlined">assignment_return</span>
                    <span class="sidebar-label">Devoluciones</span>
                </div>
                <div class="sidebar-item" data-section="incidencias" role="tab" aria-selected="false" tabindex="0">
                    <span class="material-symbols-outlined">warning</span>
                    <span class="sidebar-label">Incidencias</span>
                </div>
            </nav>

            <div class="sidebar-footer">
                <span class="sidebar-footer-text">AppJoteca</span>
            </div>
        </aside>

        <!-- BOTÓN SIDEBAR MOBILE -->
        <button class="mobile-sidebar-toggle" id="mobileSidebarToggle" aria-label="Abrir menú de historial">
            <span class="material-symbols-outlined">menu_open</span>
        </button>

        <!-- CONTENIDO -->
        <main class="historial-main">

            <!-- HEADER HERO -->
            <section class="historial-hero">
                <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1600&q=80" alt="Interior de biblioteca" loading="eager">
                <div class="historial-hero-overlay">
                    <div class="hero-content">
                        <h1>Historial <span>de Actividad</span></h1>
                        <p>Tu traza dentro de la biblioteca: préstamos, reservas y cada momento que dejaste entre las páginas.</p>
                    </div>
                </div>
            </section>

            <!-- SECCIONES -->
            <div class="historial-sections">

                <!-- ═══ PRÉSTAMOS ═══ -->
                <section class="historial-section active" id="prestamos" role="tabpanel" aria-labelledby="tab-prestamos">
                    <div class="section-header">
                        <span class="material-symbols-outlined">auto_stories</span>
                        <div>
                            <h2 class="section-title">Préstamos</h2>
                            <p class="section-subtitle">Libros que has llevado a casa</p>
                        </div>
                    </div>

                    <div class="timeline-list">
                        <article class="timeline-card">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <p class="timeline-date">28 ago. 2026</p>
                                <h3 class="timeline-title">
                                    Préstamo realizado — <span class="book-name">Cien años de soledad</span>
                                    <span class="ejemplar">· Ejemplar EJ-0045</span>
                                </h3>
                                <p class="timeline-meta">
                                    Devuelto el <strong>1 sep. 2026</strong> · Estado: <strong>Devuelto</strong>
                                </p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- ═══ RESERVAS ═══ -->
                <section class="historial-section" id="reservas" role="tabpanel" aria-labelledby="tab-reservas">
                    <div class="section-header">
                        <span class="material-symbols-outlined">bookmark_added</span>
                        <div>
                            <h2 class="section-title">Reservas</h2>
                            <p class="section-subtitle">Libros que apartaste para ti</p>
                        </div>
                    </div>

                    <div class="timeline-list">
                        <article class="timeline-card">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <p class="timeline-date">25 ago. 2026</p>
                                <h3 class="timeline-title">
                                    Reserva realizada — <span class="book-name">El principito</span>
                                </h3>
                                <p class="timeline-meta">
                                    Convertida en préstamo el <strong>26 ago. 2026</strong>
                                </p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- ═══ DEVOLUCIONES ═══ -->
                <section class="historial-section" id="devoluciones" role="tabpanel" aria-labelledby="tab-devoluciones">
                    <div class="section-header">
                        <span class="material-symbols-outlined">assignment_return</span>
                        <div>
                            <h2 class="section-title">Devoluciones</h2>
                            <p class="section-subtitle">Momentos en que regresaste un libro</p>
                        </div>
                    </div>

                    <div class="timeline-list">
                        <article class="timeline-card">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <p class="timeline-date">30 ago. 2026</p>
                                <h3 class="timeline-title">
                                    Libro devuelto — <span class="book-name">El principito</span>
                                    <span class="ejemplar">· Ejemplar EJ-0012</span>
                                </h3>
                                <p class="timeline-meta">
                                    Devuelto en <strong>Buzón Central</strong> · Recepción confirmada
                                </p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- ═══ INCIDENCIAS ═══ -->
                <section class="historial-section" id="incidencias" role="tabpanel" aria-labelledby="tab-incidencias">
                    <div class="section-header">
                        <span class="material-symbols-outlined">warning</span>
                        <div>
                            <h2 class="section-title">Cambios e Incidencias</h2>
                            <p class="section-subtitle">Alertas y modificaciones en tus préstamos</p>
                        </div>
                    </div>

                    <div class="timeline-list">
                        <article class="timeline-card">
                            <div class="timeline-dot inactive"></div>
                            <div class="timeline-content">
                                <p class="timeline-date">15 ago. 2026</p>
                                <h3 class="timeline-title">
                                    Reserva cancelada — <span class="book-name">Crónica de una muerte anunciada</span>
                                </h3>
                                <p class="timeline-meta">
                                    Cancelada por <strong>expiración</strong> · No se reclamó en 48 horas
                                </p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- ═══ FRASE ═══ -->
                <section class="quote-section">
                    <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1200&q=80" alt="Pasillo de biblioteca" loading="lazy">
                    <div class="quote-overlay">
                        <span class="material-symbols-outlined quote-icon">format_quote</span>
                        <p class="quote-text">"The library is a growing organism; your history is its lifeblood."</p>
                        <p class="quote-author">— S.R. Ranganathan</p>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <!-- FOOTER -->
    <?php include '../../shared/layouts/footer.php'; ?>

    <!-- FAB -->
    <div class="fab-container">
        <button class="fab" aria-label="Explorar el archivo">
            <span class="material-symbols-outlined">auto_stories</span>
            <span class="fab-text">Explorar</span>
        </button>
    </div>

    <!-- Scripts compartidos -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="../../shared/js/global.js"></script>
    <script src="historial.js"></script>

</body>
</html>
