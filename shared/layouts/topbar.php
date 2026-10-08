<?php
// Asegurar que la sesión esté iniciada para leer el rol
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = '/Appjoteca/'; 
$currentUri = $_SERVER['REQUEST_URI'];

$userRole = isset($_SESSION['rol']) ? (string)$_SESSION['rol'] : null;

$menusByRole = [
    'guest' => [ 
        ['text' => 'Catálogo', 'url' => 'pages/biblioteca-digital/index.php', 'nav' => 'catalogo'],
        
    ],
    '1' => [ // Estudiante
        ['text' => 'Catálogo',     'url' => 'pages/biblioteca-digital/index.php', 'nav' => 'catalogo'],
        ['text' => 'Mi historial', 'url' => 'pages/history/historial.php',           'nav' => 'historial'],
        ['text' => 'Panel',         'url' => 'dashboards/estudiante/index.php',            'nav' => 'panel']
    ],
    '2' => [ // Docente
        ['text' => 'Catálogo',     'url' => 'pages/biblioteca-digital/index.php', 'nav' => 'catalogo'],
        ['text' => 'Mi historial', 'url' => 'pages/history/historial.php',        'nav' => 'historial'],
        ['text' => 'Panel',         'url' => 'dashboards/docente/index.php',        'nav' => 'panel']
    ],
    '3' => [ // Bibliotecario
        ['text' => 'Gestión Libros', 'url' => 'pages/admin/libros.php',           'nav' => 'gestion-libros'],
        ['text' => 'Préstamos',      'url' => 'pages/admin/prestamos.php',        'nav' => 'prestamos'],
        ['text' => 'Panel Control',  'url' => 'pages/admin/dashboard.php',        'nav' => 'panel']
    ],
    '4' => [ // Administrador
        ['text' => 'Gestión Comentarios', 'url' => 'dashboards/Administrador/comentarios/comentarios.php', 'nav' => 'comentarios'],
        ['text' => 'Panel Control',  'url' => 'dashboards/Administrador/index.php',        'nav' => 'panel']
    ]
];

$rolesValidos = ['1', '2', '3', '4'];
if ($userRole !== null && !in_array($userRole, $rolesValidos, true)) {
    header('Location: /Appjoteca/backend/auth/logout.php');
    exit;
}

$navigationMenu = $menusByRole[$userRole] ?? $menusByRole['guest'];

function isNavActive($urlTarget, $currentUri) {
    $needle = ltrim($urlTarget, '/');
    return (strpos($currentUri, $needle) !== false) ? 'active' : '';
}
?>

<header class="topbar" role="banner">
    <div class="topbar-inner">
        
        <a href="<?php echo $baseUrl; ?>index.php" class="logo-link">
            <img src="<?php echo $baseUrl; ?>shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
        </a>
        
        <div class="topbar-search">
            <input type="text" class="topbar-search-input" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
            <span class="material-symbols-outlined topbar-search-icon">search</span>
        </div>
        
        <nav class="topbar-nav" aria-label="Navegación principal">
            <?php foreach ($navigationMenu as $item): ?>
                <a href="<?php echo $baseUrl . $item['url']; ?>" 
                    class="nav-link <?php echo isNavActive($item['url'], $currentUri); ?>" 
                   data-nav="<?php echo $item['nav']; ?>">
                    <?php echo $item['text']; ?>
                </a>
            <?php endforeach; ?>
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
