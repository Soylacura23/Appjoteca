<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../../backend/config/auth.php';
require_once __DIR__ . '/../../../../backend/config/user_context.php';
requiereRol([3]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes | Appjoteca</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <!-- Theme y componentes compartidos -->
    <link rel="stylesheet" href="../../../../shared/css/theme.css">
    <link rel="stylesheet" href="../../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../../shared/css/components/footer.css">
    <!-- Estilos del dashboard -->
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="reportes.css">
    <link rel="stylesheet" href="../../css/typography.css">
    <link rel="stylesheet" href="../../css/layout.css">

    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
    
    <?php require __DIR__ . '/../../../../shared/layouts/favicon.php'; ?>
    <base target="_self">
</head>
<body>

<div id="overlay" class="overlay" aria-hidden="true"></div>

<?php include '../../../../shared/layouts/notifications.php'; ?>
<?php include '../../../../shared/layouts/menu-off-canvas.php'; ?>
<?php include '../../../../shared/layouts/menu-movil.php'; ?>

<header class="topbar" role="banner">
    <div class="topbar-inner">
     
         <a href="../../index.php" class="logo-link">
    <img src="../../../../shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
</a>

        <div class="topbar-search">
            <input type="text" class="topbar-search-input" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
            <span class="material-symbols-outlined topbar-search-icon">search</span>
        </div>
        <div class="topbar-actions">
            <button class="icon-btn search-toggle-btn" id="search-toggle-btn" aria-label="Buscar" aria-expanded="false">
                <span class="material-symbols-outlined">search</span>
            </button>
            <button class="notification-tray" id="notification-tray-btn" aria-label="Notificaciones" aria-expanded="false">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge" aria-label="notificaciones sin leer"></span>
            </button>
            <div id="profile-button-topbar"></div>
            <button class="icon-btn menu-toggle-btn" id="menu-activar" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobileMenu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>
    <div class="topbar-search-mobile" id="topbar-search-mobile" aria-hidden="true">
        <input type="text" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
    </div>
</header>

<!-- Sidebar -->
<div class="dashboard-layout">
<aside id="sidebar" class="sidebar">
    <nav class="sidebar-navigator">
        <ul class="menu-items">
            <li>
                <a href="../../index.php" class="menu-item">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="menu-texto">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="../inventario/inventario.php" class="menu-item">
                    <span class="material-symbols-outlined">menu_book</span>
                    <span class="menu-texto">Inventario</span>
                </a>
            </li>
            <li>
                <a href="../reservaciones/reservaciones.php" class="menu-item">
                    <span class="material-symbols-outlined">event_available</span>
                    <span class="menu-texto">Reservaciones</span>
                </a>
            </li>
            <li>
                <a href="../usuarios/usuarios.php" class="menu-item">
                    <span class="material-symbols-outlined">group</span>
                    <span class="menu-texto">Usuarios</span>
                </a>
            </li>
            <li>
                <a href="reportes.php" class="menu-item active">
                    <span class="material-symbols-outlined">analytics</span>
                    <span class="menu-texto">Reportes</span>
                </a>
            </li>
            <li>
                <a href="../configuracion-catalogo/configuracion-catalogo.php" class="menu-item">
                    <span class="material-symbols-outlined">auto_stories</span>
                    <span class="menu-texto">Catálogo</span>
                </a>
            </li>
            <li>
                <a href="../ajustes/ajustes.php" class="menu-item">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="menu-texto">Ajustes</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<main id="main-content" class="main-content">

    <!-- Header -->
    <section class="header">
        <div class="header-text">
            <p class="label-overline text-primary">Análisis Institucional</p>
            <h1 class="headline-xl white-text">Reportes del Bibliotecario</h1>
        </div>
        <div class="header-actions">
            <button type="button" class="btn-outline" id="btn-refrescar">Actualizar datos</button>
        </div>
    </section>

    <!-- Estado de carga -->
    <div id="reportes-loading" class="reportes-loading">
        <span class="material-symbols-outlined spin">progress_activity</span>
        <p>Cargando reportes...</p>
    </div>

    <!-- Contenido (se llena con JS) -->
    <div id="reportes-contenido" class="reportes-contenido" hidden>

        <!-- Métricas -->
        <section class="metrics-grid" id="metrics-grid">
            <!-- Se generan por JS -->
        </section>

        <!-- Gráfico + Insights -->
        <section class="analytics-grid">
            <div class="chart-panel">
                <div class="chart-header">
                    <div>
                        <h3 class="headline-sm white-text">Frecuencia de Reservas</h3>
                        <p class="text-outline text-body-sm" id="chart-subtitulo">Últimos 30 días por día de la semana</p>
                    </div>
                    <div class="chart-periodos" id="chart-periodos" role="group" aria-label="Filtrar período">
                        <button type="button" class="periodo-btn" data-periodo="7">7 días</button>
                        <button type="button" class="periodo-btn active" data-periodo="30">30 días</button>
                        <button type="button" class="periodo-btn" data-periodo="90">90 días</button>
                        <button type="button" class="periodo-btn" data-periodo="365">1 año</button>
                        <button type="button" class="periodo-btn" data-periodo="todo">Todo</button>
                    </div>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chart-reservas" height="220"></canvas>
                </div>
            </div>

            <div class="insights-panel">
                <!-- Categoría popular -->
                <div class="popular-category" id="categoria-popular">
                    <div class="category-header">
                        <span class="material-symbols-outlined">category</span>
                        <h3 class="headline-sm">Categoría Popular</h3>
                    </div>
                    <p class="category-name" id="cat-nombre">—</p>
                    <p class="category-desc" id="cat-desc">Sin datos aún</p>
                </div>

                <!-- Libro popular -->
                <div class="popular-category libro-popular" id="libro-popular">
                    <div class="category-header">
                        <span class="material-symbols-outlined">star</span>
                        <h3 class="headline-sm">Libro más reservado</h3>
                    </div>
                    <p class="category-name" id="libro-nombre">—</p>
                    <p class="category-desc" id="libro-desc">Sin datos aún</p>
                </div>

                <!-- Editoriales -->
                <div class="peak-hours" id="editoriales-box">
                    <h3 class="headline-sm white-text">Editoriales frecuentes</h3>
                    <div id="editoriales-lista">
                        <p class="text-outline text-body-sm">Sin datos</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Alertas de inventario -->
        <section class="alerts-section">
            <div class="alerts-header">
                <h3 class="headline-md white-text">Alertas de Inventario</h3>
                <div class="divider-line"></div>
                <span class="alert-count" id="alert-count">
                    <span class="alert-dot"></span>
                    <span id="alert-count-text">0 ítems</span>
                </span>
            </div>
            <div class="alerts-grid" id="alerts-grid">
                <!-- Se llenan por JS -->
            </div>
            <p id="alerts-empty" class="text-outline text-body-sm" hidden style="margin-top:12px;">
                No hay alertas. Todo el inventario tiene ejemplares disponibles.
            </p>
        </section>

        <!-- Banner frase de la semana -->
        <section class="forecast-banner">
            <div class="forecast-bg"></div>
            <div class="forecast-content">
                <p class="label-overline text-primary">Frase de la semana</p>
                <h3 class="headline-lg white-text" id="frase-texto">Cargando frase...</h3>
                <p class="text-body text-outline">Se actualiza cada semana automáticamente.</p>
            </div>
        </section>

    </div>

    <!-- Error -->
    <div id="reportes-error" class="reportes-error" hidden>
        <span class="material-symbols-outlined">error</span>
        <p id="reportes-error-msg">No se pudieron cargar los reportes.</p>
        <button type="button" class="btn-outline" id="btn-reintentar">Reintentar</button>
    </div>

</main>
</div>
<?php include '../../../../shared/layouts/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="/Appjoteca/shared/js/components/notifications.js"></script>
<script src="../../../../shared/js/global.js"></script>
<script src="reportes.js"></script>
</body>
</html>