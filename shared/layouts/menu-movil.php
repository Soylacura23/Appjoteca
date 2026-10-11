<?php

if (!isset($baseUrl)) {
    include_once __DIR__ . '/../../backend/helpers/routes.php';
}

$librosNovedades = [];
$cuentasPendientes = [];
$esAdmin = (string) ($_SESSION['rol'] ?? '') === '4';

if (!isset($connection)) {
    $connPath = __DIR__ . '/../../backend/Database/conexion.php';
    if (file_exists($connPath)) {
        require_once $connPath;
    }
}

if (isset($connection) && $connection) {
    if ($esAdmin) {
        try {
            $queryPendientes = $connection->prepare(
                "SELECT u.id_usuario, u.nombre_apellido, u.nombre_usuario, r.nombre AS rol_nombre
                 FROM usuarios u
                 LEFT JOIN roles r ON r.id_rol = u.id_rol
                 WHERE u.estado = 'pendiente'
                 ORDER BY u.fecha_registro DESC, u.id_usuario DESC
                 LIMIT 3"
            );
            $queryPendientes->execute();
            $resultadoPendientes = $queryPendientes->get_result();
            while ($filaPendiente = $resultadoPendientes->fetch_assoc()) {
                $cuentasPendientes[] = $filaPendiente;
            }
            $queryPendientes->close();
        } catch (Throwable $e) {
            error_log('[menu-movil] Error al cargar cuentas pendientes: ' . $e->getMessage());
            $cuentasPendientes = [];
        }
    } else {
        if (!class_exists('libro')) {
        $modelPath = __DIR__ . '/../../backend/models/libro.php';
        if (file_exists($modelPath)) {
            require_once $modelPath;
        }
        }

        if (class_exists('libro')) {
            try {
                $modeloLibroMenu = new libro($connection);
                $todos = $modeloLibroMenu->listarLibros(true);
                $librosNovedades = array_slice($todos, 0, 3);
            } catch (Throwable $e) {
                error_log('[menu-movil] Error al cargar novedades: ' . $e->getMessage());
                $librosNovedades = [];
            }
        }
    }
}

if (!function_exists('menuMovilPortadaUrl')) {
    function menuMovilPortadaUrl($portada, $baseUrl) {
        if (function_exists('urlPortada')) {
            return urlPortada($portada ?? '', $baseUrl);
        }
        $portada = trim((string) $portada);
        $default = $baseUrl . 'assets/images/books/default-cover.jpg';
        if ($portada === '') return $default;
        if (preg_match('#^https?://#i', $portada)) return $portada;
        return $baseUrl . ltrim($portada, '/');
    }
}

if (!function_exists('menuMovilBookUrl')) {
    function menuMovilBookUrl($idLibro, $baseUrl) {
        return $baseUrl . 'pages/biblioteca-catalogo/vista-libro/book-view.php?id=' . (int) $idLibro;
    }
}
?>

<div class="mobile-menu-overlay" aria-hidden="true"></div>

<div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Menú de navegación">

    <?php
    $logoUrl = null;
    foreach ($navigationMenu as $item) {
        if (($item['nav'] ?? '') === 'panel') {
            $logoUrl = $baseUrl . ltrim($item['url'], '/');
            break;
        }
    }
    if ($logoUrl === null) {
        $logoUrl = !empty($navigationMenu)
            ? $baseUrl . ltrim($navigationMenu[0]['url'], '/')
            : $baseUrl . 'index.php';
    }
    ?>

<div class="mobile-menu-header">
    <a href="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>"
       class="mobile-menu-logo topbar-logo"
       aria-label="Ir al panel">
        <div class="logo" aria-hidden="true"></div>
        <img src="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>shared/images/logo-appjoteca.svg"
             alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
    </a>
    <button class="mobile-menu-close" aria-label="Cerrar menú">
        <span class="material-symbols-outlined">close</span>
    </button>
</div>

    <nav class="mobile-menu-nav" aria-label="Navegación principal">
        <?php foreach ($navigationMenu as $item):
            $itemUrl  = $baseUrl . ltrim($item['url'], '/');
            $itemNav  = $item['nav']  ?? '';
            $itemText = $item['text'] ?? '';
            $active   = function_exists('isNavActive')
                        ? isNavActive($item['url'], $currentUri)
                        : '';
        ?>
            <a href="<?php echo htmlspecialchars($itemUrl, ENT_QUOTES, 'UTF-8'); ?>"
               class="mobile-nav-link <?php echo htmlspecialchars($active, ENT_QUOTES, 'UTF-8'); ?>"
               data-nav="<?php echo htmlspecialchars($itemNav, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($itemText, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="mobile-menu-divider" aria-hidden="true"></div>

    <?php if ($esAdmin && !empty($cuentasPendientes)): ?>
        <div class="mobile-menu-section">
            <p class="mobile-menu-section-title">Cuentas pendientes</p>

            <div class="mobile-menu-items">
                <?php foreach ($cuentasPendientes as $cuenta):
                    $nombreCuenta = htmlspecialchars($cuenta['nombre_apellido'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
                    $usuarioCuenta = htmlspecialchars($cuenta['nombre_usuario'] ?? '', ENT_QUOTES, 'UTF-8');
                    $rolCuenta = htmlspecialchars($cuenta['rol_nombre'] ?? 'Cuenta nueva', ENT_QUOTES, 'UTF-8');
                    $urlPendientes = $baseUrl . 'dashboards/Administrador/index.php?tab=pendientes';
                ?>
                    <a href="<?php echo htmlspecialchars($urlPendientes, ENT_QUOTES, 'UTF-8'); ?>" class="mobile-menu-item">
                        <div class="mobile-menu-item-thumb">
                            <span class="material-symbols-outlined" aria-hidden="true" style="display:flex;height:100%;align-items:center;justify-content:center;color:var(--primary);">person</span>
                        </div>
                        <div class="mobile-menu-item-info">
                            <p class="mobile-menu-item-name"><?php echo $nombreCuenta; ?></p>
                            <p class="mobile-menu-item-sub"><?php echo $usuarioCuenta !== '' ? '@' . $usuarioCuenta . ' · ' : ''; ?><?php echo $rolCuenta; ?></p>
                        </div>
                        <span class="material-symbols-outlined mobile-menu-item-arrow">arrow_forward</span>
                    </a>
                <?php endforeach; ?>
            </div>

            <a href="<?php echo htmlspecialchars($baseUrl . 'dashboards/Administrador/index.php?tab=pendientes', ENT_QUOTES, 'UTF-8'); ?>"
               class="mobile-menu-all">
                <span>Ver todas las cuentas pendientes</span>
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        </div>
    <?php elseif (!$esAdmin && !empty($librosNovedades)): ?>
        <div class="mobile-menu-section">
            <p class="mobile-menu-section-title">Novedades</p>

            <div class="mobile-menu-items">
                <?php foreach ($librosNovedades as $novedad):
                    $nId      = (int) ($novedad['id_libro'] ?? 0);
                    $nTitulo  = htmlspecialchars($novedad['titulo'] ?? 'Sin título', ENT_QUOTES, 'UTF-8');
                    $nAutor   = htmlspecialchars($novedad['autores'] ?? 'Autor desconocido', ENT_QUOTES, 'UTF-8');
                    $nMateria = htmlspecialchars($novedad['materia'] ?? 'General', ENT_QUOTES, 'UTF-8');
                    $nImg     = htmlspecialchars(menuMovilPortadaUrl($novedad['portada'] ?? '', $baseUrl), ENT_QUOTES, 'UTF-8');
                    $nUrl     = menuMovilBookUrl($nId, $baseUrl);
                ?>
                    <a href="<?php echo htmlspecialchars($nUrl, ENT_QUOTES, 'UTF-8'); ?>" class="mobile-menu-item">
                        <div class="mobile-menu-item-thumb">
                            <img src="<?php echo $nImg; ?>"
                                 alt="<?php echo $nTitulo; ?>"
                                 loading="lazy">
                        </div>
                        <div class="mobile-menu-item-info">
                            <p class="mobile-menu-item-name"><?php echo $nTitulo; ?></p>
                            <p class="mobile-menu-item-sub"><?php echo $nAutor; ?> · <?php echo $nMateria; ?></p>
                        </div>
                        <span class="material-symbols-outlined mobile-menu-item-arrow">arrow_forward</span>
                    </a>
                <?php endforeach; ?>
            </div>

            <a href="<?php echo htmlspecialchars($baseUrl . 'pages/biblioteca-digital/index.php', ENT_QUOTES, 'UTF-8'); ?>"
               class="mobile-menu-all">
                <span>Ver todo el catálogo</span>
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        </div>
    <?php endif; ?>

    <!-- ── Footer ── -->
    <div class="mobile-menu-footer">
        <span class="mobile-menu-tagline">Sistema Institucional</span>
        <div class="mobile-menu-footer-actions">
            <button class="notification-tray icon-btn" aria-label="Notificaciones" aria-expanded="false">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge" aria-hidden="true"></span>
            </button>
            <div id="profile-button-menu-footer"></div>
        </div>
    </div>

</div>