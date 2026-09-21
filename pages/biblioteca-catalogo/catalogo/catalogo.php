<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';

requiereRol([1, 2]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appjoteca - Explorar Colección</title>

    <!-- Fuentes e Iconos de Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Estilos compartidos y de componentes -->
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="../../shared/css/components/book-card.css">

    <!-- Estilos específicos de la página -->
    <link rel="stylesheet" href="catalogo.css">
</head>
<body>

    <!-- OVERLAY GLOBAL -->
    <div id="overlay" class="overlay" aria-hidden="true"></div>

    <!-- NOTIFICACIONES Y MENÚS SHARED -->
    <?php include '../../shared/layouts/notifications.php'; ?>
    <?php include '../../shared/layouts/menu-off-canvas.php'; ?>

    <!-- BARRA DE NAVEGACIÓN PRINCIPAL -->
    <header class="topbar" role="banner">
        <div class="topbar-inner">
               <a href="../../index.php" class="logo-link">
    <img src="../../shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
</a>
            <div class="topbar-search">
                <input type="text" class="topbar-search-input catalog-search-input" placeholder="Buscar por título, autor o ISBN..." aria-label="Buscar en el catálogo">
                <span class="material-symbols-outlined topbar-search-icon">search</span>
            </div>
            <nav class="topbar-nav" aria-label="Navegación principal">
                <a href="#" class="nav-link active" data-nav="catalogo">Catálogo</a>
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
            <input type="text" class="catalog-search-input" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
        </div>
    </header>

    <!-- MENÚ MÓVIL -->
    <?php include '../../shared/layouts/menu-movil.php'; ?>

    <!-- CONTENIDO PRINCIPAL DEL CATÁLOGO -->
    <main class="catalog-main">
        <div class="catalog-container">
            
            <!-- Encabezado de la página -->
            <header class="catalog-header">
                <h1 class="catalog-title">Explorar Colección</h1>
                <p class="catalog-subtitle">
                    Sumérgete en un archivo curado de conocimiento y narrativa. Desde clásicos olvidados hasta visiones contemporáneas.
                </p>
            </header>

            <!-- Barra de búsqueda y filtros -->
            <section class="catalog-filter-bar">
                <div class="search-input-wrapper">
                    <span class="material-symbols-outlined search-icon">search</span>
                    <input type="text" class="catalog-search-input" placeholder="Buscar por título, autor o ISBN...">
                </div>
                <div class="filter-categories">
                    <button class="filter-chip active" type="button">General</button>
                    <button class="filter-chip" type="button">Filosofía</button>
                    <button class="filter-chip" type="button">Ciencias</button>
                    <button class="filter-chip" type="button">Artes</button>
                    <button class="filter-chip" type="button">Historia</button>
                </div>
            </section>

            <!-- Grid de Libros (Renderizado dinámicamente por catalogo.js) -->
            <section class="bookshelf-grid" id="bookshelf-grid"></section>

            <!-- Paginación (Renderizada dinámicamente por catalogo.js) -->
            <nav class="pagination-container" aria-label="Navegación de páginas"></nav>

        </div>
    </main>

    <!-- FOOTER -->
    <?php include '../../shared/layouts/footer.php'; ?>

    <!-- SCRIPTS JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="../../shared/js/global.js"></script>
    <script src="catalogo.js"></script>
</body>
</html>