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
    <title>Usuarios · APPJOTECA</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="https://unpkg.com/tabulator-tables@6.3.0/dist/css/tabulator_midnight.min.css">
    <!-- 1. THEME GLOBAL -->
    <link rel="stylesheet" href="../../../../shared/css/theme.css">

    <!-- 2. Componentes Compartidos -->
    <link rel="stylesheet" href="../../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../../shared/css/components/footer.css">
    

    <!-- 3. Estilos Locales del Dashboard -->
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="usuarios.css">
    <link rel="stylesheet" href="../../css/typography.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="icon" type="image/png" href="../../../../shared/images/logo-appjoteca.png">
<base target="_self">
</head>
<body>

<div id="overlay" class="overlay" aria-hidden="true"></div>

<?php include '../../../../shared/layouts/notifications.php'; ?>
<?php include '../../../../shared/layouts/menu-off-canvas.php'; ?>

<header class="topbar" role="banner">
    <div class="topbar-inner">
        <a href="#" class="topbar-logo">
            <div class="logo" aria-hidden="true"></div>
            <span class="logo-text">AppJoteca</span>
        </a>
        <div class="topbar-search">
            <input type="text" id="search" class="topbar-search-input" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
            <span class="material-symbols-outlined topbar-search-icon">search</span>
        </div>
        <div class="topbar-actions">
            <button class="icon-btn search-toggle-btn" id="search-toggle-btn" aria-label="Buscar" aria-expanded="false">
                <span class="material-symbols-outlined">search</span>
            </button>
            <button class="notification-tray" aria-label="Notificaciones" aria-expanded="false">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge" aria-label="3 notificaciones sin leer"></span>
            </button>

            <!-- Botón de perfil inyectado por JS -->
            <div id="profile-button-topbar"></div>

            <button class="icon-btn menu-toggle-btn" id="menu-activar" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobileMenu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>
    <div class="topbar-search-mobile" id="topbar-search-mobile" aria-hidden="true">
        <input type="text" id="search-mobile-input" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
    </div>
</header>

    <!-- Nav SIDEBAR -->
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
                    <a href="usuarios.php" class="menu-item active">
                        <span class="material-symbols-outlined">group</span>
                        <span class="menu-texto">Usuarios</span>
                    </a>
                </li>


                <li>
                    <a href="../reportes/reportes.php" class="menu-item">
                        <span class="material-symbols-outlined">analytics</span>
                        <span class="menu-texto">Reportes</span>
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

        <section class="page-hero">
            <div class="page-hero-text">
                <p class="text-eyebrow">Gestión de miembros</p>
                <h1 class="headline-xl white-text">Directorio de Usuarios</h1>
                <p class="text-body text-muted">Gestionando estudiantes, docentes y bibliotecarios en la biblioteca</p>
            </div>
        </section>

        <section class="users-toolbar" aria-label="Resumen de usuarios">
            <div class="toolbar-stats">
                <div class="stat-item">
                    <span class="material-symbols-outlined stat-icon">group</span>
                    <div class="stat-info"><strong id="stat-total">0</strong><span>Usuarios</span></div>
                </div>
                <div class="stat-item available">
                    <span class="material-symbols-outlined stat-icon">check_circle</span>
                    <div class="stat-info"><strong id="stat-active">0</strong><span>Activos</span></div>
                </div>
                <div class="stat-item">
                    <span class="material-symbols-outlined stat-icon">school</span>
                    <div class="stat-info"><strong id="stat-students">0</strong><span>Estudiantes</span></div>
                </div>
                <div class="stat-item">
                    <span class="material-symbols-outlined stat-icon">co_present</span>
                    <div class="stat-info"><strong id="stat-teachers">0</strong><span>Docentes</span></div>
                </div>
            </div>
            <div class="toolbar-filters">
                <div class="filter-group">
                    <label for="filter-role" class="filter-label">Rol</label>
                    <select id="filter-role" class="filter-select">
                        <option value="">Todos</option>
                        <option value="Estudiante">Estudiantes</option>
                        <option value="Docente">Docentes</option>
                        <option value="Bibliotecario">Bibliotecarios</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-status" class="filter-label">Estado</label>
                    <select id="filter-status" class="filter-select">
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="users-section" aria-label="Listado de usuarios">
        <div class="tabulator-search-wrapper">
            <span class="material-symbols-outlined search-icon">search</span>
            <input type="text" id="users-search" class="tabulator-search-input" placeholder="Buscar usuario (nombre, correo...)">
        </div>
                <div id="users-table"></div>
            </div>
        </section>

          <!-- ══════════════════════════════════════════
       FOOTER
  ══════════════════════════════════════════════ -->
  <?php include '../../../../shared/layouts/footer.php'; ?>
    </main>

    <!-- Overlay detalle de usuario -->
    <div class="user-detail-overlay" id="user-detail-overlay" role="dialog" aria-modal="true" aria-labelledby="user-detail-title" hidden>
        <div class="detail-backdrop" id="user-detail-backdrop"></div>
        <div class="detail-panel">
            <header class="detail-header">
                <h2 id="user-detail-title" class="detail-title">Detalle del usuario</h2>
                <button type="button" class="detail-close-btn" id="user-detail-close" aria-label="Cerrar">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </header>

            <div class="detail-body">
                <div class="user-detail-header">
                    <img class="detail-avatar" id="detail-avatar" src="" alt="">
                    <div class="detail-header-info">
                        <h3 id="detail-name" class="detail-name"></h3>
                        <p id="detail-subtitle" class="detail-subtitle"></p>
                        <div class="detail-badges">
                            <span class="badge-status" id="detail-status-badge"></span>
                        </div>
                    </div>
                </div>

                <section class="detail-section">
                    <div class="section-label">
                        <span class="material-symbols-outlined">person</span>
                        Información personal
                    </div>
                    <div class="section-fields">
                        <p class="detail-bio" id="detail-bio"></p>
                        <div class="info-row">
                            <span class="info-label">Usuario</span>
                            <span class="info-value" id="detail-usuario"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Correo institucional</span>
                            <span class="info-value" id="detail-email"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Documento</span>
                            <span class="info-value" id="detail-documento"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Rol</span>
                            <span class="info-value" id="detail-rol"></span>
                        </div>
                    </div>
                </section>

                <section class="detail-section">
                    <div class="section-label">
                        <span class="material-symbols-outlined">badge</span>
                        Documento de identidad
                    </div>
                    <div class="section-fields">
                        <div class="id-preview" id="id-preview">
                            <img class="id-preview-img" id="detail-doc-img" src="" alt="Documento de identidad" hidden>
                            <div class="id-placeholder" id="id-placeholder">
                                <span class="material-symbols-outlined">badge</span>
                                <span>Sin documento cargado</span>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="delete-space">
                    <button type="button" class="delete" id="delete-account-btn">
                        <span class="material-symbols-outlined">delete_forever</span>
                        Eliminar Cuenta
                    </button>
                    <p>Esta acción es irreversible y eliminará toda la cuenta del usuario</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: confirmar eliminación -->
    <div class="confirm-modal" id="delete-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title" hidden>
        <div class="confirm-backdrop" id="delete-confirm-backdrop"></div>
        <div class="confirm-box">
            <div class="confirm-icon">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <h3 id="delete-confirm-title">Eliminar cuenta</h3>
            <p>¿Confirma que desea eliminar la cuenta de <strong id="delete-confirm-name"></strong>? Esta acción no se puede deshacer.</p>
            <div class="confirm-actions">
                <button type="button" class="btn btn-ghost" id="delete-confirm-cancel">Cancelar</button>
                <button type="button" class="btn btn-danger" id="delete-confirm-accept">
                    <span class="material-symbols-outlined">delete_forever</span>
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
  

    <script src="https://unpkg.com/tabulator-tables@6.3.0/dist/js/tabulator.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../../../shared/js/components/alert.js"></script>
    <script src="../../../../shared/js/global.js"></script>
    <script src="usuarios.js"></script>
</body>
</html>
