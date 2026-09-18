<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';

// Control de acceso exclusivo para Docentes (Rol 2)
requiereRol([2]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docente | Dashboard - AppJoteca</title>

    <!-- Fuentes e Iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Estilos del Sistema y del Módulo -->
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="docente.css">
    
    <link rel="icon" type="image/png" href="../../shared/images/logo-appjoteca.png">
</head>
<body>

    <div id="overlay" class="overlay" aria-hidden="true"></div>

    <?php include '../../shared/layouts/notifications.php'; ?>
    <?php include '../../shared/layouts/menu-off-canvas.php'; ?>

    <!-- NAVBAR TOPBAR -->
    <header class="topbar" role="banner">
        <div class="topbar-inner">
            <a href="../../index.php" class="topbar-logo">
                <div class="logo" aria-hidden="true"></div>
                <span class="logo-text">AppJoteca</span>
            </a>

            <div class="topbar-search">
                <input type="text" class="topbar-search-input" placeholder="Buscar título, autor o materia..." aria-label="Buscar en el catálogo">
                <span class="material-symbols-outlined topbar-search-icon">search</span>
            </div>

            <nav class="topbar-nav" aria-label="Navegación principal">
                <a href="#" class="nav-link activo" data-nav="dashboard">Dashboard</a>
                <a href="../../pages/catalogo/index.php" class="nav-link" data-nav="catalogo">Catálogo</a>
                <a href="../../pages/prestamos/index.php" class="nav-link" data-nav="prestamos">Préstamos</a>
                <a href="../../pages/reservas/index.php" class="nav-link" data-nav="reservas">Reservas</a>
            </nav>

            <div class="topbar-actions">
                <button class="icon-btn search-toggle-btn" aria-label="Buscar">
                    <span class="material-symbols-outlined">search</span>
                </button>
                <button class="notification-tray" aria-label="Notificaciones">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notification-badge" aria-label="Notificaciones sin leer"></span>
                </button>
                <div id="profile-button-topbar"></div>
                <button class="icon-btn menu-toggle-btn" aria-label="Abrir menú" aria-controls="mobileMenu">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </div>
        <div class="topbar-search-mobile" aria-hidden="true">
            <input type="text" placeholder="Buscar título, autor o materia..." aria-label="Buscar en el catálogo">
        </div>
    </header>

    <?php include '../../shared/layouts/menu-movil.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="dashboard-main">
        <div class="dashboard-container">

            <!-- SECCIÓN DE BIENVENIDA -->
            <section class="welcome-section">
                <div class="welcome-text">
                    <h1 class="welcome-title"><span id="greetingTime"></span>, Prof. <?= htmlspecialchars($mi_nombre) ?></h1>
                    <p class="welcome-subtitle">Gestiona los libros y recursos pedagógicos para tus clases fácilmente</p>
                </div>
                <div class="welcome-meta">
                    <p class="welcome-info">Estado de cuenta: <strong>Al día</strong></p>
                    <p class="welcome-date" id="currentDate">Cargando fecha...</p>
                </div>
            </section>

            <!-- ACCIONES RÁPIDAS Y NAVEGACIÓN DE SESIÓN -->
            <section class="quick-actions">
                <a href="../../pages/catalogo/index.php" class="action-card">
                    <span class="material-symbols-outlined action-icon">search</span>
                    <div class="action-text">
                        <h3>Catálogo General</h3>
                        <p>Explorar acervo escolar</p>
                    </div>
                </a>
                <a href="../../pages/prestamos/index.php" class="action-card">
                    <span class="material-symbols-outlined action-icon">menu_book</span>
                    <div class="action-text">
                        <h3>Mis Préstamos</h3>
                        <p>Gestión de ejemplares</p>
                    </div>
                </a>
                <a href="../../pages/reservas/index.php" class="action-card">
                    <span class="material-symbols-outlined action-icon">bookmark</span>
                    <div class="action-text">
                        <h3>Reservas de Clase</h3>
                        <p>Solicitar colecciones</p>
                    </div>
                </a>
                <!-- Redirección directa al Login / Salir -->
                <a href="../../auth/login/index.php" class="action-card action-card-logout">
                    <span class="material-symbols-outlined action-icon icon-logout">logout</span>
                    <div class="action-text">
                        <h3>Ir al Login</h3>
                        <p>Cerrar o cambiar sesión</p>
                    </div>
                </a>
            </section>

            <!-- TARJETAS DE MÉTRICAS -->
            <section class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="material-symbols-outlined metric-icon">library_books</span>
                        <span class="metric-number">120</span>
                    </div>
                    <p class="metric-label">Libros disponibles</p>
                </div>

                <div class="metric-card">
                    <div class="metric-header">
                        <span class="material-symbols-outlined metric-icon">auto_stories</span>
                        <span class="metric-number">10</span>
                    </div>
                    <p class="metric-label">Préstamos activos</p>
                </div>

                <div class="metric-card warning">
                    <div class="metric-header">
                        <span class="material-symbols-outlined metric-icon">event_busy</span>
                        <span class="metric-number">8</span>
                    </div>
                    <p class="metric-label">Préstamos vencidos</p>
                </div>

                <div class="metric-card success">
                    <div class="metric-header">
                        <span class="material-symbols-outlined metric-icon">group</span>
                        <span class="metric-number">25</span>
                    </div>
                    <p class="metric-label">Alumnos consultaron material</p>
                </div>
            </section>

            <!-- ESTRUCTURA PRINCIPAL DE DOS COLUMNAS -->
            <div class="dashboard-layout">

                <!-- COLUMNA IZQUIERDA -->
                <div class="dashboard-content">

                    <!-- LIBROS / RECURSOS SUGERIDOS -->
                    <section class="dashboard-block">
                        <div class="block-header">
                            <h2 class="block-title">Recursos Recomendados para Clase</h2>
                        </div>
                        <div class="books-grid" id="booksGrid">
                            <!-- Renderizado dinámico vía JS -->
                        </div>
                    </section>

                    <!-- ACTIVIDAD RECIENTE -->
                    <section class="dashboard-block">
                        <div class="block-header">
                            <h2 class="block-title">Actividad Reciente</h2>
                        </div>
                        <div class="activity-card">
                            <ul class="activity-list">
                                <li class="activity-item">
                                    <span class="material-symbols-outlined activity-icon">book</span>
                                    <div class="activity-details">
                                        <p class="activity-text">Prestaste <strong>"El Principito"</strong></p>
                                        <span class="activity-time">Hace 2 horas</span>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <span class="material-symbols-outlined activity-icon">assignment_return</span>
                                    <div class="activity-details">
                                        <p class="activity-text">Devolviste <strong>"La Vanguardia"</strong></p>
                                        <span class="activity-time">Ayer a las 15:30</span>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <span class="material-symbols-outlined activity-icon">bookmark_add</span>
                                    <div class="activity-details">
                                        <p class="activity-text">Reservaste kit pedagógico <strong>"Guía Curricular 2024"</strong></p>
                                        <span class="activity-time">Hace 3 días</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </section>

                </div>

                <!-- COLUMNA DERECHA (SIDEBAR) -->
                <aside class="dashboard-sidebar">

                    <!-- RESUMEN PERFIL -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Perfil Docente</h3>
                        <div class="profile-summary-content">
                            <div class="profile-avatar-small">
                                <span class="material-symbols-outlined">school</span>
                            </div>
                            <div class="profile-details">
                                <h4>Prof. <?= htmlspecialchars($mi_nombre) ?></h4>
                                <span class="student-id">Doc: <?= htmlspecialchars($mi_documento); ?></span>
                            </div>
                        </div>
                        <div class="sidebar-actions-group">
                            <a href="../../pages/settings/configuracion.php" class="btn-outline-small">Ajustes de Perfil</a>
                            <a href="../../auth/login/index.php" class="btn-outline-small btn-logout-outline">Cerrar Sesión / Login</a>
                        </div>
                    </div>

                    <!-- PANEL: PRÉSTAMOS ACTIVOS -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Préstamos Activos</h3>
                        <ul class="panel-list">
                            <li>
                                <span class="material-symbols-outlined">menu_book</span>
                                <div>
                                    <strong>Pedagogía Ética</strong>
                                    <small>Devolución: 24 Sep</small>
                                </div>
                            </li>
                            <li>
                                <span class="material-symbols-outlined">menu_book</span>
                                <div>
                                    <strong>Física Aplicada</strong>
                                    <small>Devolución: 28 Sep</small>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- PANEL: NUEVO MATERIAL -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Nuevos Libros / Recursos</h3>
                        <ul class="panel-list">
                            <li>
                                <span class="material-symbols-outlined">new_releases</span>
                                <div>
                                    <strong>Guía Curricular 2024</strong>
                                    <small>Disponible en Hemeroteca</small>
                                </div>
                            </li>
                        </ul>
                    </div>

                </aside>
            </div>
        </div>
    </main>

    <?php include '../../shared/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="../../shared/js/global.js"></script>
    <script src="docente.js"></script>
</body>
</html>