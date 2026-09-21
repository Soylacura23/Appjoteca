<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';
require_once __DIR__ . '/../../backend/Database/conexion.php';
require_once __DIR__ . '/../../backend/models/historial.php';
requiereRol([3]);

// -------- Estadísticas --------
$queryStats = "SELECT 
    (SELECT COUNT(*) FROM libros) as total_libros,
    (SELECT COUNT(*) FROM reservas WHERE estado = 'activa') as reservas_pendientes,
    (SELECT COUNT(*) FROM prestamos WHERE estado = 'activo') as prestamos_activos,
    (SELECT COUNT(*) FROM prestamos WHERE estado = 'activo' AND fecha_devolucion_prevista < CURDATE()) as prestamos_vencidos";
$stats = $connection->query($queryStats)->fetch_assoc();

// -------- Reservación destacada --------
$queryFeatured = "SELECT l.titulo, l.portada, COUNT(r.id_reserva) as total_reservas 
    FROM libros l 
    LEFT JOIN reservas r ON l.id_libro = r.fk_id_libro_reserva 
    GROUP BY l.id_libro 
    ORDER BY total_reservas DESC 
    LIMIT 1";
$featured = $connection->query($queryFeatured)->fetch_assoc();

// -------- Historial reciente (usando modelo) --------
$modeloHistorial = new Historial($connection);
$historial = $modeloHistorial->obtenerRecientes(5);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Bibliotecario</title>
    
    <!-- Fuentes e Íconos -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <!-- CSS de Tabulator -->
    <link href="https://unpkg.com/tabulator-tables@6.2.1/dist/css/tabulator.min.css" rel="stylesheet">

    <!-- CSS del proyecto -->
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/typography.css">
    
    <link rel="icon" type="image/png" href="../../shared/images/logo-appjoteca.png">
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

<?php include '../../shared/layouts/menu-movil.php'; ?>

<aside id="sidebar" class="sidebar">
    <nav class="sidebar-navigator">
        <ul class="menu-items">
            <li>
                <a href="index.php" class="menu-item active">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="menu-texto">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="pages/inventario/inventario.php" class="menu-item">
                    <span class="material-symbols-outlined">menu_book</span>
                    <span class="menu-texto">Inventario</span>
                </a>
            </li>
            <li>
                <a href="pages/reservaciones/reservaciones.php" class="menu-item">
                    <span class="material-symbols-outlined">event_available</span>
                    <span class="menu-texto">Reservaciones</span>
                </a>
            </li>
            <li>
                <a href="pages/usuarios/usuarios.php" class="menu-item">
                    <span class="material-symbols-outlined">group</span>
                    <span class="menu-texto">Usuarios</span>
                </a>
            </li>
            <li>
                <a href="pages/reportes/reportes.php" class="menu-item">
                    <span class="material-symbols-outlined">analytics</span>
                    <span class="menu-texto">Reportes</span>
                </a>
            </li>
            <li>
                <a href="pages/configuracion-catalogo/configuracion-catalogo.php" class="menu-item">
                    <span class="material-symbols-outlined">auto_stories</span>
                    <span class="menu-texto">Catálogo</span>
                </a>
            </li>
            <li>
                <a href="pages/ajustes/ajustes.php" class="menu-item">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="menu-texto">Ajustes</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>



<main id="main-content" class="main-content">

<section class="estadisticas">
    <div class="stats-grid">
        <div class="stat-card">
            <p class="label text-sm">LIBROS TOTALES</p>
            <h3 class="stat-number white-text headline-md"><?= $stats['total_libros'] ?? 0 ?></h3>
        </div>
        <div class="stat-card warning">
            <p class="label text-sm">APROBACIONES PENDIENTES</p>
            <h3 class="stat-number white-text error headline-md"><?= $stats['reservas_pendientes'] ?? 0 ?></h3>
        </div>
        <div class="stat-card">
            <p class="label text-sm">PRÉSTAMOS ACTIVOS</p>
            <h3 class="stat-number white-text headline-md"><?= $stats['prestamos_activos'] ?? 0 ?></h3>
        </div>
        <div class="stat-card">
            <p class="label text-sm">PRÉSTAMOS VENCIDOS</p>
            <h3 class="stat-number white-text headline-md"><?= $stats['prestamos_vencidos'] ?? 0 ?></h3>
        </div>
    </div>
</section>

<div class="two-col-grid">
    <section class="pending">
        <div class="reservation-table-container">
            <div class="table-header flex justify-between align-center mb-3">
                <h3 class="headline-md white-text">Fila de reservación</h3>
                <button class="btn-outline">VER TODOS</button>
            </div>
            <div id="reservas-table" class="custom-tabulator"></div>
        </div>
    </section>

    <section class="actions">
        <div class="one-column-grid">
            <div class="featured-card">
                <img src="<?= $featured['portada'] ? htmlspecialchars($featured['portada']) : 'images/libro.png' ?>" class="featured-image" alt="Libro Destacado">
                <div class="featured-overlay">
                    <p class="text-xs text-primary">RESERVACIÓN DESTACADA</p>
                    <h5 class="headline-md white-text"><?= htmlspecialchars($featured['titulo'] ?? 'Ninguno') ?></h5>
                    <button class="btn-text text-body" id="btn-featured" type="button">VER DETALLES</button>
                </div>
            </div>

            <div class="history">
                <p class="text-outline text text-body">HISTORIAL</p>
                <?php foreach ($historial as $item): ?>
                    <div class="trail">
                        <div class="<?= $item['tipo'] === 'reserva' ? 'active_icon' : 'unactive_icon' ?>"></div>
                        <div class="trail-content">
                            <span class="text-sm text-secondary">
                                <?= htmlspecialchars($item['accion']) ?>
                                <?php if (!empty($item['usuario'])): ?>
                                    — <?= htmlspecialchars($item['usuario']) ?>
                                <?php endif; ?>
                            </span>
                            <span class="text-sm text-outline">
                                <?= htmlspecialchars($item['detalle']) ?>
                                <?php if (!empty($item['referencia'])): ?>
                                    · <?= htmlspecialchars($item['referencia']) ?>
                                <?php endif; ?>
                                — <?= date('d M, Y', strtotime($item['fecha'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($historial)): ?>
                    <span class="text-sm text-outline">No hay actividad reciente.</span>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php include '../../shared/layouts/footer.php'; ?>
</main>

<script src="https://unpkg.com/tabulator-tables@6.2.1/dist/js/tabulator.min.js"></script>
<script src="../../shared/js/global.js"></script>
<script src="js/dashboard.js"></script>
</body>
</html>