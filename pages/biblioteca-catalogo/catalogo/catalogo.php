

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../backend/config/auth.php';
require_once __DIR__ . '/../../../backend/config/user_context.php';

// Control de acceso según los roles del sistema

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
    <link rel="stylesheet" href="../../../shared/css/theme.css">
    <link rel="stylesheet" href="../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../shared/css/components/footer.css">
    <link rel="stylesheet" href="../../../shared/css/components/book-card.css">

    <!-- Estilos específicos de la página -->
    <link rel="stylesheet" href="catalogo.css">
</head>
<body>

    <!-- OVERLAY GLOBAL -->
    <div id="overlay" class="overlay" aria-hidden="true"></div>

    <!-- NOTIFICACIONES Y MENÚS SHARED -->
    <?php include '../../../shared/layouts/notifications.php'; ?>
    <?php include '../../../shared/layouts/menu-off-canvas.php'; ?>

    <!-- BARRA DE NAVEGACIÓN PRINCIPAL -->
    <header class="topbar" role="banner">
        <div class="topbar-inner">
            <a href="../../index.php" class="topbar-logo">
                <div class="logo" aria-hidden="true"></div>
                <span class="logo-text">AppJoteca</span>
            </a>
            <div class="topbar-search">
                <input type="text" class="topbar-search-input" placeholder="Buscar por título, autor o ISBN..." aria-label="Buscar en el catálogo">
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
            <input type="text" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
        </div>
    </header>

    <!-- MENÚ MÓVIL -->
    <?php include '../../../shared/layouts/menu-movil.php'; ?>

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

            <!-- Grid de Libros -->
            <section class="bookshelf-grid">
                <!-- Tarjeta 1 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/la_metamorfosis.jpg" alt="La Estética del Silencio" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">La Estética del Silencio</h3>
                    <p class="book-author">Sontag, Susan</p>
                </article>

                <!-- Tarjeta 2 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/etica-a-nicomaco.jpg" alt="Geometría del Alma" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">Geometría del Alma</h3>
                    <p class="book-author">Descartes, René</p>
                </article>

                <!-- Tarjeta 3 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/civilizaciones-perdidas.jpg" alt="Arquitectura Neoclásica" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">Arquitectura Neoclásica</h3>
                    <p class="book-author">Palladio, Andrea</p>
                </article>

                <!-- Tarjeta 4 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/acheron.jpg" alt="Sombras del Crepúsculo" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">Sombras del Crepúsculo</h3>
                    <p class="book-author">Rilke, Rainer Maria</p>
                </article>

                <!-- Tarjeta 5 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/cien-años-de-soledad.jpg" alt="Crónica de una Era" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">Crónica de una Era</h3>
                    <p class="book-author">Hobsbawm, Eric</p>
                </article>

                <!-- Tarjeta 6 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/cosmos.jpg" alt="Trópico de Cáncer" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">Trópico de Cáncer</h3>
                    <p class="book-author">Miller, Henry</p>
                </article>

                <!-- Tarjeta 7 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/constelaciones-doradas.png" alt="Atlas de lo Invisible" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">Atlas de lo Invisible</h3>
                    <p class="book-author">Cheshire, James</p>
                </article>

                <!-- Tarjeta 8 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/la_metamorfosis.jpg" alt="Memorias de Adriano" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">Memorias de Adriano</h3>
                    <p class="book-author">Yourcenar, Marguerite</p>
                </article>

                <!-- Tarjeta 9 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/etica-a-nicomaco.jpg" alt="Ensayo sobre la Ceguera" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">Ensayo sobre la Ceguera</h3>
                    <p class="book-author">Saramago, José</p>
                </article>

                <!-- Tarjeta 10 -->
                <article class="catalog-book-card">
                    <div class="book-cover-wrap">
                        <img src="../../assets/images/books/la_metamorfosis.jpg" alt="La Metamorfosis" loading="lazy">
                        <div class="book-hover-overlay">
                            <span class="overlay-action">Ver Detalles</span>
                        </div>
                    </div>
                    <h3 class="book-title">La Metamorfosis</h3>
                    <p class="book-author">Kafka, Franz</p>
                </article>
            </section>

            <!-- Paginación -->
            <nav class="pagination-container" aria-label="Navegación de páginas">
                <button class="page-btn page-nav" aria-label="Página anterior" type="button">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <div class="page-numbers">
                    <button class="page-btn active" type="button">1</button>
                    <button class="page-btn" type="button">2</button>
                    <button class="page-btn" type="button">3</button>
                    <span class="page-ellipsis">...</span>
                    <button class="page-btn" type="button">12</button>
                </div>
                <button class="page-btn page-nav" aria-label="Siguiente página" type="button">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </nav>

        </div>
    </main>

    <!-- FOOTER -->
    <?php include '../../../shared/layouts/footer.php'; ?>

    <!-- SCRIPTS JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../../shared/js/components/navbar.js"></script>
    <script src="../../../shared/js/global.js"></script>
    <script src="catalogo.js"></script>
</body>
</html>

