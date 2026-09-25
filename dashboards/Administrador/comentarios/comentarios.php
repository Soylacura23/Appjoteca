<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';

requiereRol([4]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Panel de Administrador - AppJoteca</title>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- SweetAlert2 & Tabulator CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://unpkg.com/tabulator-tables@5.5.0/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">

    <!-- Estilos compartidos -->
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">

    <!-- Estilos específicos del administrador -->
    <link rel="stylesheet" href="administrador.css">
</head>
<body>

    <!-- OVERLAY GLOBAL -->
    <div id="overlay" class="overlay" aria-hidden="true"></div>

    <!-- BANDEJA DE NOTIFICACIONES -->
    <?php include '../../shared/layouts/notifications.php'; ?>

    <!-- MENÚ OFF-CANVAS DE PERFIL -->
    <?php include '../../shared/layouts/menu-off-canvas.php'; ?>

    <!-- BARRA DE NAVEGACIÓN -->
    <header class="topbar" role="banner">
        <div class="topbar-inner">
            <a href="../../index.php" class="logo-link">
                <img src="../../shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
            </a>
            <div class="topbar-search">
                <input type="text" class="topbar-search-input" placeholder="Buscar en la plataforma..." aria-label="Buscar">
                <span class="material-symbols-outlined topbar-search-icon">search</span>
            </div>
            <nav class="topbar-nav" aria-label="Navegación principal">
                <a href="../index.php" class="nav-link" data-nav="panel">Panel Admin</a>
            </nav>
            <nav class="topbar-nav" aria-label="Navegación Comentarios">
                <a href="#" class="nav-link active" data-nav="panel">Comentarios</a>
            </nav>
            <div class="topbar-actions">
                <button class="notification-tray" aria-label="Notificaciones" aria-expanded="false">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notification-badge" aria-label="Notificaciones sin leer"></span>
                </button>
                <div id="profile-button-topbar"></div>
                <button class="icon-btn menu-toggle-btn" aria-label="Abrir menú" aria-expanded="false">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </div>
    </header>

    <!-- MENÚ MÓVIL -->
    <?php include '../../shared/layouts/menu-movil.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="config-main">
        <div class="config-container">
            <header class="config-page-header">
                <h1 class="config-page-title">Gestión de <span>Usuarios</span></h1>
                <p class="config-page-subtitle">Administra bibliotecarios, aprueba solicitudes y crea nuevas cuentas.</p>
            </header>


            <!-- PANEL: COMENTARIOS -->
            <section class="admin-panel" data-panel="comentarios">
                <div class="comentarios-panel">

                    <header class="comentarios-header">
                        <div class="comentarios-header__info">
                            <h2 class="comentarios-titulo">Bandeja de comentarios</h2>
                            <p class="comentarios-subtitulo">
                                <span id="contador-comentarios">0</span> comentario(s) recibido(s)
                            </p>
                        </div>
                    </header>

                    <!-- Loading -->
                    <div id="comentarios-loading" class="comentarios-loading" hidden>
                        <span class="material-symbols-outlined">progress_activity</span>
                        <span>Cargando comentarios…</span>
                    </div>

                    <!-- Lista de tarjetas -->
                    <div id="lista-comentarios" class="comentarios-lista"></div>

                    <!-- Estado vacío -->
                    <div id="comentarios-vacio" class="comentarios-vacio" hidden>
                        <span class="material-symbols-outlined">inbox</span>
                        <p>No hay comentarios por el momento.</p>
                    </div>

                    <!-- Botón cargar más -->
                    <div class="comentarios-footer">
                        <button id="btn-cargar-mas" class="btn-cargar-mas" type="button" hidden>
                            <span class="material-symbols-outlined">expand_more</span>
                            Cargar más comentarios
                        </button>
                    </div>

                </div>
            </section>
        </div>
    </main>

    

    <!-- FOOTER -->
    <?php include '../../shared/layouts/footer.php'; ?>

    <!-- Scripts compartidos y librerías -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js"></script>
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="../../shared/js/global.js"></script>

    <!-- Scripts del Administrador -->
    <script src="administrador.js"></script>
    <script src="comentarios.js"></script>
</body>
</html>