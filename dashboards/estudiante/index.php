<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';

requiereRol([1]);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Estudiante - AppJoteca</title>

    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">

    <link rel="stylesheet" href="estudiante.css">
</head>
<body>

    <div id="overlay" class="overlay" aria-hidden="true"></div>

    <?php include '../../shared/layouts/notifications.php'; ?>

    <?php include '../../shared/layouts/menu-off-canvas.php'; ?>

    <header class="topbar" role="banner">
        <div class="topbar-inner">
                <a href="../../index.php" class="logo-link">
    <img src="../../shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
</a>
            <div class="topbar-search">
                <input type="text" class="topbar-search-input" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
                <span class="material-symbols-outlined topbar-search-icon">search</span>
            </div>
            <nav class="topbar-nav" aria-label="Navegación principal">
                <a href="../../pages/biblioteca-digital/index.php" class="nav-link" data-nav="catalogo">Catálogo</a>
                <a href="#" class="nav-link" data-nav="biblioteca">Mi Biblioteca</a>
                <a href="#" class="nav-link activo" data-nav="panel">Panel</a>
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

    <?php include '../../shared/layouts/menu-movil.php'; ?>

    <main class="dashboard-main">
        <div class="dashboard-container">

            <!-- BIENVENIDA -->
            <section class="welcome-section">
                <div class="welcome-text">
                    <h1 class="welcome-title"><span id="greetingTime"></span>, <?= $mi_nombre ?> </h1>
                    <p class="welcome-subtitle">¿Qué quieres descubrir hoy?</p>
                </div>
                <div class="welcome-meta">
                    <p class="welcome-info">Tienes <strong><?= $_SESSION['resumen_libros']['prestados']; ?> libros prestados</strong> y <strong><?= $_SESSION['resumen_libros']['reservados']; ?> reserva activa</strong>.</p>
                    <p class="welcome-date" id="currentDate">Cargando fecha...</p>
                </div>
            </section>

            <!-- ACCIONES RÁPIDAS -->
            <section class="quick-actions">
                <a href="#" class="action-card">
                    <span class="material-symbols-outlined action-icon">search</span>
                    <div class="action-text">
                        <h3>Buscar Libro</h3>
                        <p>Explora el catálogo</p>
                    </div>
                </a>
                <a href="#" class="action-card">
                    <span class="material-symbols-outlined action-icon">menu_book</span>
                    <div class="action-text">
                        <h3>Mis Préstamos</h3>
                        <p>Consulta tus préstamos</p>
                    </div>
                </a>
                <a href="#" class="action-card">
                    <span class="material-symbols-outlined action-icon">bookmark</span>
                    <div class="action-text">
                        <h3>Mis Reservas</h3>
                        <p>Consulta tus reservas</p>
                    </div>
                </a>
                <a href="#" class="action-card">
                    <span class="material-symbols-outlined action-icon">history</span>
                    <div class="action-text">
                        <h3>Historial</h3>
                        <p>Revisa tu actividad</p>
                    </div>
                </a>
            </section>

            <div class="dashboard-layout">

                <!-- CONTENIDO PRINCIPAL -->
                <div class="dashboard-content">

                    <!-- LIBROS SUGERIDOS -->
                    <section class="dashboard-block">
                        <div class="block-header">
                            <h2 class="block-title">Colecciones Sugeridas</h2>
                        </div>
                        <div class="books-grid">
                            <article class="book-card">
                                <div class="book-cover">
                                    <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=800" alt="El Mundo de Sofía" loading="lazy">
                                </div>
                                <div class="book-info">
                                    <h3 class="book-title">El mundo de Sofía</h3>
                                    <p class="book-author">Jostein Gaarder</p>
                                </div>
                            </article>
                            <article class="book-card">
                                <div class="book-cover">
                                    <img src="https://images.unsplash.com/photo-1589829085413-56de8ae18c73?q=80&w=800" alt="Física Conceptual" loading="lazy">
                                </div>
                                <div class="book-info">
                                    <h3 class="book-title">Física Conceptual</h3>
                                    <p class="book-author">Paul G. Hewitt</p>
                                </div>
                            </article>
                            <article class="book-card">
                                <div class="book-cover">
                                    <img src="https://images.unsplash.com/photo-1516979187457-637abb4f9353?q=80&w=800" alt="Lógica de Programación" loading="lazy">
                                </div>
                                <div class="book-info">
                                    <h3 class="book-title">Lógica de Programación</h3>
                                    <p class="book-author">Omar Trejos</p>
                                </div>
                            </article>
                            <article class="book-card">
                                <div class="book-cover">
                                    <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=800" alt="El arte de la lógica" loading="lazy">
                                </div>
                                <div class="book-info">
                                    <h3 class="book-title">El arte de la lógica</h3>
                                    <p class="book-author">Eugenia Cheng</p>
                                </div>
                            </article>
                            <article class="book-card">
                                <div class="book-cover">
                                    <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=800" alt="Teatro Moderno" loading="lazy">
                                </div>
                                <div class="book-info">
                                    <h3 class="book-title">Teatro Moderno</h3>
                                    <p class="book-author">Varios autores</p>
                                </div>
                            </article>
                        </div>
                    </section>

                    <!-- CATEGORÍAS -->
                    <section class="dashboard-block">
                        <div class="block-header">
                            <h2 class="block-title">Explora por Categoría</h2>
                        </div>
                        <div class="categories-flex">
                            <a href="#" class="category-chip">Literatura</a>
                            <a href="#" class="category-chip">Historia</a>
                            <a href="#" class="category-chip">Filosofía</a>
                            <a href="#" class="category-chip">Ciencias</a>
                            <a href="#" class="category-chip">Tecnología</a>
                            <a href="#" class="category-chip">Astronomía</a>
                            <a href="#" class="category-link">Explorar catálogo &rarr;</a>
                        </div>
                    </section>
                </div>

                <!-- SIDEBAR -->
                <aside class="dashboard-sidebar">

                    <!-- PERFIL -->
                    <div class="sidebar-card profile-summary">
                        <h3 class="sidebar-title">Mi Perfil</h3>
                        <div class="profile-summary-content">
                            <div class="profile-avatar-small">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <div class="profile-details">
                                <h4><?= $mi_nombre ?></h4>
                                <span class="student-id">ID: <?= $mi_documento; ?></span>
                            </div>
                        </div>
                        <a href="../../pages/settings//configuracion.php" class="btn-outline-small">Ver perfil</a>
                    </div>

                    <!-- ESTADO DE CUENTA -->
                    <div class="sidebar-card account-status">
                        <h3 class="sidebar-title">Estado de mi cuenta</h3>
                        <ul class="status-list">
                            <li><span>Préstamos activos</span> <strong>2</strong></li>
                            <li><span>Reservas activas</span> <strong>1</strong></li>
                            <li><span>Devoluciones pendientes</span> <strong>0</strong></li>
                        </ul>
                        <div class="status-indicator good-status">
                            <span class="dot"></span> En buen estado
                        </div>
                    </div>

                    <!-- RESERVAS ACTIVAS -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Reservas Activas</h3>
                        <div class="history-list">
                            <div class="history-item">
                                <div class="history-img">
                                    <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=200" alt="Libro" loading="lazy">
                                </div>
                                <div class="history-data">
                                    <h4>El arte de la lógica</h4>
                                    <p>Disponible en 2 días</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NUEVOS INGRESOS -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-title">Nuevos Ingresos</h3>
                        <div class="history-list">
                            <div class="history-item">
                                <div class="history-img">
                                    <img src="https://images.unsplash.com/photo-1555662137-f4e9185a5398?q=80&w=200" alt="Libro" loading="lazy">
                                </div>
                                <div class="history-data">
                                    <h4>Ingeniería de Software</h4>
                                    <p>Añadido hoy</p>
                                </div>
                            </div>
                            <div class="history-item">
                                <div class="history-img">
                                    <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=200" alt="Libro" loading="lazy">
                                </div>
                                <div class="history-data">
                                    <h4>Teatro Moderno</h4>
                                    <p>Añadido ayer</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </aside>
            </div>
        </div>
    </main>

    <?php include '../../shared/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="../../shared/js/global.js"></script>
    <script src="estudiante.js"></script>
</body>
</html>