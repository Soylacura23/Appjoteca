<?php
// Asegurar que la sesión esté iniciada para leer el rol
include_once __DIR__ . '/../../backend/helpers/routes.php';

?>

<header class="topbar" role="banner">
    <div class="topbar-inner">
        
        <a href="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>index.php" class="logo-link">
            <img src="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
        </a>
        
        <div class="topbar-search">
            <input type="text" class="topbar-search-input" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
            <span class="material-symbols-outlined topbar-search-icon">search</span>
        </div>
        
        <nav class="topbar-nav" aria-label="Navegación principal">
            <?php foreach ($navigationMenu as $item): ?>
                <a href="<?php echo htmlspecialchars($baseUrl . $item['url'], ENT_QUOTES, 'UTF-8'); ?>"
                    class="nav-link <?php echo htmlspecialchars(isNavActive($item['url'], $currentUri), ENT_QUOTES, 'UTF-8'); ?>"
                   data-nav="<?php echo htmlspecialchars($item['nav'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?>
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
