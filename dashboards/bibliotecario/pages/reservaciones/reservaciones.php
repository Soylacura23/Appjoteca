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
    <title>Reservaciones · APPJOTECA</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <!-- Tabulator -->
    <link href="https://unpkg.com/tabulator-tables@6.3.0/dist/css/tabulator.min.css" rel="stylesheet">
    <!-- 1. THEME GLOBAL -->
    <link rel="stylesheet" href="../../../../shared/css/theme.css">
    
    <!-- 2. Componentes Compartidos -->
    <link rel="stylesheet" href="../../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../../shared/css/components/footer.css">
    
    <!-- 3. Estilos Locales del Dashboard -->
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="reservaciones.css">
    <link rel="stylesheet" href="../../css/typography.css">
    
    <link rel="icon" type="image/png" href="../../../../shared/images/logo-appjoteca.png">
    <base target="_self">
</head>
<body>

<div id="overlay" class="overlay" aria-hidden="true"></div>

<?php include '../../../../shared/layouts/notifications.php'; ?>
<?php include '../../../../shared/layouts/menu-off-canvas.php'; ?>

<header class="topbar" role="banner">
    <div class="topbar-inner">
                 <a href="../../index.php" class="logo-link">
    <img src="/Appjoteca/shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
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
                <a href="reservaciones.php" class="menu-item active">
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
                <a href="../reportes/reportes.php" class="menu-item">
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

<?php include '../../../../shared/layouts/menu-movil.php'; ?>

<!-- Main Content -->
<main id="main-content" class="main-content">

    <!-- Header -->
    <section class="header">
        <div class="header-text">
            <p class="label-overline text-primary">Gestión de Préstamos</p>
            <h1 class="headline-xl white-text">Reservaciones</h1>
            <p class="text-body text-outline">Administra las solicitudes de préstamo, supervisa las devoluciones y controla los volúmenes atrasados.</p>
        </div>
        <div class="header-stats">
            <div class="header-stat">
                <span class="header-stat-value" id="stat-nuevas">—</span>
                <span class="header-stat-label">Nuevas Solicitudes</span>
            </div>
            <div class="header-stat danger">
                <span class="header-stat-value" id="stat-atrasados">—</span>
                <span class="header-stat-label">Ítems Atrasados</span>
            </div>
        </div>
    </section>

    <!-- Solicitudes Nuevas -->
    <section class="requests-section" id="requests-section">
        <div class="section-header">
            <h2 class="headline-sm white-text">
                <span class="material-symbols-outlined">pending_actions</span>
                Solicitudes Nuevas
                <span id="count-solicitudes" style="font-size:0.75rem;color:rgba(255,255,255,0.35);font-weight:600;margin-left:8px;"></span>
            </h2>
            <a href="#" class="section-link" id="btn-ver-cola">
                Ver Cola
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>
        <div class="requests-grid" id="requests-grid">
            <!-- Se llena por JS -->
        </div>
    </section>

    <!-- Cola completa (oculta por defecto) -->
    <section class="queue-section" id="queue-section">
        <div class="section-header">
            <h2 class="headline-sm white-text">
                <span class="material-symbols-outlined">queue</span>
                Cola de Solicitudes
            </h2>
            <a href="#" class="section-link" id="btn-cerrar-cola">
                Cerrar
                <span class="material-symbols-outlined">close</span>
            </a>
        </div>
        <div class="queue-list" id="queue-list"></div>
        <div class="queue-pagination" id="queue-pagination"></div>
    </section>

    <!-- Estado en Vivo + Próximas Devoluciones -->
    <section class="status-grid">
        <div class="status-panel">
            <h2 class="headline-sm white-text">
                <span class="material-symbols-outlined">analytics</span>
                Estado en Vivo
            </h2>
            <div class="status-cards">
                <div class="status-card">
                    <div class="status-card-top">
                        <span class="status-card-label">Vencen Hoy</span>
                        <span class="material-symbols-outlined">schedule</span>
                    </div>
                    <span class="status-card-value" id="stat-vencen-hoy">—</span>
                    <span class="status-card-hint">Verificación antes de las 18:00</span>
                </div>
                <div class="status-card danger">
                    <div class="status-card-top">
                        <span class="status-card-label">Atrasados Críticos</span>
                        <span class="material-symbols-outlined">warning</span>
                    </div>
                    <span class="status-card-value" id="stat-criticos">—</span>
                    <span class="status-card-hint">Protocolos de notificación activados</span>
                </div>
            </div>
        </div>
        <div class="returns-panel">
            <div class="returns-header">
                <span class="returns-title">Próximas Devoluciones <span id="count-devoluciones"></span></span>
                <div class="live-badge">
                    <span class="live-dot"></span>
                    <span>En vivo</span>
                </div>
            </div>
            <div class="returns-list" id="returns-list"></div>
            <button class="returns-more-btn" id="returns-more-btn" style="display:none;">Cargar más</button>
        </div>
    </section>

    <!-- Historial de Movimientos -->
    <section class="history-section">
        <div class="section-header">
            <h2 class="headline-sm white-text">
                <span class="material-symbols-outlined">history</span>
                Historial de Movimientos
            </h2>
            <div class="history-actions">
                <button class="btn-ghost-sm" id="btn-export-pdf">Exportar Log</button>
            </div>
        </div>
        <div class="history-table-wrap">
            <div id="history-table"></div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include '../../../../shared/layouts/footer.php'; ?>

</main>

<!-- Overlay detalle de préstamo -->
<div class="loan-overlay" id="loan-overlay" aria-hidden="true">
    <div class="loan-overlay-backdrop" id="loan-overlay-backdrop"></div>
    <div class="loan-overlay-panel" role="dialog" aria-modal="true" aria-labelledby="loan-overlay-title">
        <button class="loan-overlay-close" id="loan-overlay-close" aria-label="Cerrar">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="loan-overlay-header">
            <img class="loan-overlay-cover" id="loan-cover" src="" alt="">
            <div class="loan-overlay-meta">
                <h3 id="loan-overlay-title">—</h3>
                <p class="author" id="loan-author">—</p>
            </div>
        </div>
        <div class="loan-overlay-grid">
            <div class="loan-overlay-field">
                <label>Usuario</label>
                <span id="loan-user">—</span>
            </div>
            <div class="loan-overlay-field">
                <label>Ejemplar</label>
                <span id="loan-ejemplar">—</span>
            </div>
            <div class="loan-overlay-field">
                <label>Fecha préstamo</label>
                <span id="loan-fecha-prestamo">—</span>
            </div>
            <div class="loan-overlay-field">
                <label>Devolución prevista</label>
                <span id="loan-fecha-prevista">—</span>
            </div>
            <div class="loan-overlay-field">
                <label>Estado</label>
                <span id="loan-estado">—</span>
            </div>
            <div class="loan-overlay-field">
                <label>Días restantes</label>
                <span id="loan-dias">—</span>
            </div>
        </div>
        <div class="loan-overlay-actions">
            <button class="btn-return" id="loan-btn-devolver">Marcar como Devuelto</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/tabulator-tables@6.3.0/dist/js/tabulator.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="../../../../shared/js/global.js"></script>
<script src="reservaciones.js"></script>
</body>
</html>