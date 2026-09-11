<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';

// Solo permite el acceso al rol Administrador (ej. Rol 1)
requiereRol([1]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <a href="#" class="topbar-logo">
                <div class="logo" aria-hidden="true"></div>
                <span class="logo-text">AppJoteca</span>
            </a>
            <div class="topbar-search">
                <input type="text" class="topbar-search-input" placeholder="Buscar en la plataforma..." aria-label="Buscar">
                <span class="material-symbols-outlined topbar-search-icon">search</span>
            </div>
            <nav class="topbar-nav" aria-label="Navegación principal">
                <a href="#" class="nav-link active" data-nav="panel">Panel Admin</a>
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
                <h1 class="config-page-title">Gestión de <span>Bibliotecarios</span></h1>
                <p class="config-page-subtitle">Panel de administración global para añadir y remover personal bibliotecario.</p>
            </header>

            <div class="admin-actions-bar">
                <button id="btnAbrirModal" class="btn-gold" type="button">
                    <span class="material-symbols-outlined">person_add</span>
                    Añadir Bibliotecario
                </button>
            </div>

            <!-- TABLA TABULATOR -->
            <section class="config-section" style="margin-top: var(--sp-lg);">
                <div class="section-body">
                    <div id="tabla-bibliotecarios"></div>
                </div>
            </section>
        </div>
    </main>

    <!-- MODAL DE REGISTRO DE BIBLIOTECARIO -->
    <div class="modal-overlay" id="modalRegistro" aria-hidden="true">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Registrar Nuevo Bibliotecario</h3>
                <button type="button" class="modal-close" id="btnCerrarModal">&times;</button>
            </div>
            <form id="formBibliotecario" class="form-grid">
                <div class="form-group">
                    <label for="docInput">Documento de Identidad</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">badge</span>
                        <input type="text" id="docInput" name="documento" placeholder="Número de documento" required>
                    </div>
                </div>
                <div class="form-grid form-grid--2">
                    <div class="form-group">
                        <label for="nombreInput">Nombres</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">person</span>
                            <input type="text" id="nombreInput" name="nombres" placeholder="Nombres" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="apellidoInput">Apellidos</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">person</span>
                            <input type="text" id="apellidoInput" name="apellidos" placeholder="Apellidos" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="emailInput">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">alternate_email</span>
                        <input type="email" id="emailInput" name="correo" placeholder="correo@institucional.edu" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="passInput">Contraseña Acceso</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input type="password" id="passInput" name="password" placeholder="Contraseña de inicio de sesión" required>
                    </div>
                </div>
                <div class="form-actions" style="margin-top: 15px;">
                    <button type="submit" class="btn-gold" style="width: 100%; justify-content: center;">
                        <span class="material-symbols-outlined">save</span>
                        Guardar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
    <?php include '../../shared/layouts/footer.php'; ?>

    <!-- Scripts compartidos y librerías -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js"></script>
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="../../shared/js/global.js"></script>
    
    <!-- Script del Administrador -->
    <script src="administrador.js"></script>
</body>
</html>