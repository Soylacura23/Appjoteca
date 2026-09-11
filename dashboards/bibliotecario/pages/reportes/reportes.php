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
    <!-- 1. THEME GLOBAL -->
    <link rel="stylesheet" href="../../../../shared/css/theme.css">
    
    <!-- 2. Componentes Compartidos -->
    <link rel="stylesheet" href="../../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../../shared/css/components/footer.css">
    
    <!-- 3. Estilos Locales del Dashboard -->
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="reportes.css">
    <link rel="stylesheet" href="../../css/typography.css">
    
    <link rel="icon" type="image/png" href="../../../../shared/images/logo-appjoteca.png">
<base target="_self">
</head>
<body>

<div id="overlay" class="overlay" aria-hidden="true"></div>

<?php include '../../../../shared/layouts/notifications.php'; ?>
<?php include '../../../../shared/layouts/menu-off-canvas.php'; ?>
<?php include '../../../../shared/layouts/menu-movil.php'; ?>

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
        <div class="topbar-actions">
            <button class="icon-btn search-toggle-btn" aria-label="Buscar" aria-expanded="false">
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
    <div class="topbar-search-mobile" aria-hidden="true">
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
                    <a href="../ajustes/ajustes.php" class="menu-item">
                        <span class="material-symbols-outlined">settings</span>
                        <span class="menu-texto">Ajustes</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main id="main-content" class="main-content">

        <!-- Header -->
        <section class="header">
            <div class="header-text">
                <p class="label-overline text-primary">Análisis Institucional</p>
                <h1 class="headline-xl white-text">Reportes del Bibliotecario</h1>
            </div>
            <div class="header-actions">
                <button class="btn-outline">Descargar PDF</button>
                <button class="btn-primary">Generar informe descargable</button>
            </div>
        </section>

        <!-- Métricas Principales -->
        <section class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon-bg">
                    <span class="material-symbols-outlined">show_chart</span>
                </div>
                <div class="metric-body">
                    <p class="metric-label">Tráfico Total</p>
                    <h2 class="metric-value">12,842</h2>
                    <p class="metric-trend up">
                        <span class="material-symbols-outlined">arrow_upward</span>
                        14% vs mes anterior
                    </p>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-icon-bg">
                    <span class="material-symbols-outlined">event_available</span>
                </div>
                <div class="metric-body">
                    <p class="metric-label">Reservas Diarias</p>
                    <h2 class="metric-value">30</h2>
                    <p class="metric-trend up">
                        <span class="material-symbols-outlined">trending_up</span>
                        Tendencia al alza
                    </p>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-icon-bg">
                    <span class="material-symbols-outlined">group</span>
                </div>
                <div class="metric-body">
                    <p class="metric-label">Usuarios Activos</p>
                    <h2 class="metric-value">10</h2>
                    <p class="metric-trend neutral">Acceso Institucional</p>
                </div>
            </div>
        </section>

        <!-- Gráfico + Insights -->
        <section class="analytics-grid">
            <!-- Gráfico de Barras -->
            <div class="chart-panel">
                <div class="chart-header">
                    <div>
                        <h3 class="headline-sm white-text">Frecuencia de Reservas</h3>
                        <p class="text-outline text-body-sm">Volumen de adquisiciones del trimestre</p>
                    </div>
                    <span class="badge-live">En vivo</span>
                </div>
                <div class="chart-area">
                    <div class="chart-bars" id="chart-bars">
                        <!-- Barras generadas por JS -->
                    </div>
                    <div class="chart-gridlines">
                        <div></div><div></div><div></div><div></div>
                    </div>
                </div>
                <div class="chart-labels">
                    <span>Lunes</span><span>Martes</span><span>Miércoles</span>
                    <span>Jueves</span><span>Viernes</span><span>Sábado</span><span>Domingo</span>
                </div>
            </div>

            <!-- Panel Derecho: Peak Hours + Categoría -->
            <div class="insights-panel">
                <div class="peak-hours">
                    <h3 class="headline-sm white-text">Densidad Horaria</h3>
                    <div class="peak-item">
                        <div class="peak-info">
                            <span class="peak-time">10:00 AM — 12:00 PM</span>
                            <span class="peak-load">92% Carga</span>
                        </div>
                        <div class="peak-bar-bg">
                            <div class="peak-bar-fill" style="width:92%"></div>
                        </div>
                    </div>
                    <div class="peak-item">
                        <div class="peak-info">
                            <span class="peak-time">02:00 PM — 04:00 PM</span>
                            <span class="peak-load">78% Carga</span>
                        </div>
                        <div class="peak-bar-bg">
                            <div class="peak-bar-fill" style="width:78%"></div>
                        </div>
                    </div>
                    <div class="peak-item">
                        <div class="peak-info">
                            <span class="peak-time">04:00 PM — 06:00 PM</span>
                            <span class="peak-load">65% Carga</span>
                        </div>
                        <div class="peak-bar-bg">
                            <div class="peak-bar-fill" style="width:65%"></div>
                        </div>
                    </div>
                </div>
                <div class="popular-category">
                    <div class="category-header">
                        <span class="material-symbols-outlined">star</span>
                        <h3 class="headline-sm">Categoría Popular</h3>
                    </div>
                    <p class="category-name">El mundo de Sofía</p>
                    <p class="category-desc">Domina el 24% de los préstamos esta semana</p>
                </div>
            </div>
        </section>

        <!-- Alertas de Inventario -->
        <section class="alerts-section">
            <div class="alerts-header">
                <h3 class="headline-md white-text">Alertas Críticas de Inventario</h3>
                <div class="divider-line"></div>
                <span class="alert-count">
                    <span class="alert-dot"></span>
                    12 Ítems Marcados
                </span>
            </div>
            <div class="alerts-grid">
                <div class="alert-card">
                    <div class="alert-thumb">
                        <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=200&h=300&fit=crop" alt="El Secreto del Alquimista">
                    </div>
                    <div class="alert-body">
                        <h4 class="alert-title">El Secreto del Alquimista</h4>
                        <p class="alert-id">ID: ACQ-772910</p>
                        <span class="alert-badge error">En restauración</span>
                        <p class="alert-meta">Retorno estimado: 24 Oct, 2026</p>
                    </div>
                </div>
                <div class="alert-card">
                    <div class="alert-thumb">
                        <img src="https://images.unsplash.com/photo-1532012197267-da84d127e765?w=200&h=300&fit=crop" alt="Lógica Cuántica">
                    </div>
                    <div class="alert-body">
                        <h4 class="alert-title">Lógica Cuántica</h4>
                        <p class="alert-id">ID: PHY-110245</p>
                        <span class="alert-badge warning">Máx. Préstamos</span>
                        <p class="alert-meta">0 de 12 copias disponibles</p>
                    </div>
                </div>
                <div class="alert-card">
                    <div class="alert-thumb">
                        <img src="https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=200&h=300&fit=crop" alt="La Proporción Áurea">
                    </div>
                    <div class="alert-body">
                        <h4 class="alert-title">La Proporción Áurea</h4>
                        <p class="alert-id">ID: ART-900331</p>
                        <span class="alert-badge error">Copia Dañada</span>
                        <p class="alert-meta">En encuadernación</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Banner Predictivo -->
        <section class="forecast-banner">
            <div class="forecast-bg"></div>
            <div class="forecast-content">
                <p class="label-overline text-primary">Visión del Bibliotecario</p>
                <h3 class="headline-lg white-text">Pronóstico: Inventario Impulsado por IA</h3>
                <p class="text-body text-outline">Nuestros algoritmos de próxima generación predicen un aumento del 40% en la demanda de literatura clásica el próximo mes. Prepara las estanterías para nuevos préstamos.</p>
                <a href="#" class="forecast-link">
                    Explorar Tendencias
                    <span class="material-symbols-outlined">trending_flat</span>
                </a>
            </div>
        </section>

        <!-- Footer -->
        <?php include '../../../../shared/layouts/footer.php'; ?>

    </main>

    <script src="../../../../shared/js/global.js"></script>
    <script src="reportes.js"></script>
</body>
</html>
