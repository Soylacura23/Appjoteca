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
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Inventario · APPJOTECA</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../shared/css/theme.css">
    <link rel="stylesheet" href="../../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../../shared/css/components/footer.css">
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="inventario.css">
    <link rel="stylesheet" href="../../css/typography.css">
    <link rel="stylesheet" href="../../css/layout.css">

    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
    
    <?php require __DIR__ . '/../../../../shared/layouts/favicon.php'; ?>
    <base target="_self">
</head>
<body>
<div id="overlay" class="overlay" aria-hidden="true"></div>

<?php include '../../../../shared/layouts/notifications.php'; ?>
<?php include '../../../../shared/layouts/menu-off-canvas.php'; ?>
<?php include '../../../../shared/layouts/menu-movil.php'; ?>

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
            <button class="notification-tray" aria-label="Notificaciones" aria-expanded="false">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge" aria-label="3 notificaciones sin leer"></span>
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

<div class="dashboard-layout">
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
                <a href="inventario.php" class="menu-item active">
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

<main id="main-content" class="main-content">

    <section class="page-hero">
        <div class="page-hero-text">
            <p class="text-eyebrow">Gestión de libros</p>
            <h1 class="headline-xl white-text">Inventario</h1>
        </div>
        <button class="btn-add-book" id="add-book-btn">
            <span class="material-symbols-outlined">add</span>
            Añadir Nuevo Libro
        </button>
    </section>

    <!-- Toolbar -->
    <section class="inventory-toolbar" aria-label="Filtros de inventario">
        <div class="toolbar-stats" id="toolbar-stats">
            <div class="stat-item">
                <span class="material-symbols-outlined stat-icon">menu_book</span>
                <div class="stat-info"><strong id="stat-total">0</strong><span>Títulos</span></div>
            </div>
            <div class="stat-item available">
                <span class="material-symbols-outlined stat-icon">check_circle</span>
                <div class="stat-info"><strong id="stat-available">0</strong><span>Disponibles</span></div>
            </div>
            <div class="stat-item borrowed">
                <span class="material-symbols-outlined stat-icon">swap_horiz</span>
                <div class="stat-info"><strong id="stat-borrowed">0</strong><span>Prestados</span></div>
            </div>
            <div class="stat-item">
                <span class="material-symbols-outlined stat-icon">inventory_2</span>
                <div class="stat-info"><strong id="stat-copies">0</strong><span>Ejemplares</span></div>
            </div>
        </div>
        <div class="toolbar-filters">
            <div class="filter-group">
                <label for="filter-status" class="filter-label">Estado</label>
                <select id="filter-status" class="filter-select">
                    <option value="">Todos</option>
                    <option value="available">Disponibles</option>
                    <option value="borrowed">Prestados</option>
                    <option value="out">Sin stock</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-category" class="filter-label">Categoría</label>
                <select id="filter-category" class="filter-select">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="sort-by" class="filter-label">Ordenar</label>
                <select id="sort-by" class="filter-select">
                    <option value="title-asc">Título A-Z</option>
                    <option value="title-desc">Título Z-A</option>
                    <option value="author-asc">Autor A-Z</option>
                    <option value="date-desc">Más recientes</option>
                    <option value="copies-desc">Más ejemplares</option>
                </select>
            </div>
        </div>
    </section>

    <!-- Grid -->
    <section class="books-section" aria-label="Colección de libros">
        <div class="books-grid" id="books-grid" role="list"></div>

        <div class="empty-state" id="empty-state" hidden>
            <span class="material-symbols-outlined empty-icon">menu_book</span>
            <h2 class="empty-title">No hay libros en el inventario</h2>
            <p class="empty-desc">Comienza agregando tu primer libro a la colección.</p>
            <button class="btn-add-book" id="empty-add-btn">
                <span class="material-symbols-outlined">add</span>
                Agregar primer libro
            </button>
        </div>

        <nav class="pagination" id="pagination" aria-label="Paginación de libros" hidden>
            <button class="pag-btn" id="pagination-prev" aria-label="Página anterior">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <div class="pag-pages" id="pagination-pages"></div>
            <button class="pag-btn" id="pagination-next" aria-label="Página siguiente">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
        </nav>
    </section>

    <!-- ═══════════════════════════════════════════════════
         OVERLAY DETALLE DEL LIBRO
         ═══════════════════════════════════════════════════ -->
    <div class="book-detail-overlay" id="book-detail-overlay" role="dialog" aria-modal="true" aria-labelledby="detail-panel-title" hidden>
        <div class="detail-backdrop" id="detail-backdrop"></div>

        <div class="detail-panel" role="document">
            <header class="detail-header">
                <h2 id="detail-panel-title" class="detail-title">Nuevo libro</h2>
                <button type="button" class="detail-close-btn" id="detail-close-btn" aria-label="Cerrar">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </header>

            <form class="detail-body" id="detail-form" novalidate>

                <!-- Información básica -->
                <section class="detail-section" data-section="basic">
                    <div class="section-label">
                        <span class="material-symbols-outlined">info</span>
                        Información básica
                    </div>
                    <div class="section-fields">

                        <div class="field-cover">
                            <div class="cover-preview" id="cover-preview">
                                <img id="detail-cover-img" src="" alt="Portada del libro" hidden>
                                <div class="cover-placeholder" id="cover-placeholder">
                                    <span class="material-symbols-outlined">image</span>
                                    <span>Sin portada</span>
                                </div>
                                <div class="cover-hover-overlay">
                                    <button type="button" class="btn btn-secondary btn-sm" id="detail-cover-change">
                                        <span class="material-symbols-outlined">camera_alt</span>
                                        Cambiar
                                    </button>
                                </div>
                            </div>
                            <input type="file" id="detail-cover-file" accept="image/*" hidden>
                        </div>

                        <div class="form-field full" style="display:flex; gap:10px; align-items:flex-end;">
                            <div style="flex:1;">
                                <label for="detail-title-input" class="form-label">Título del libro <span class="req">*</span></label>
                                <input type="text" id="detail-title-input" class="form-input" placeholder="Ej: Cien años de soledad" autocomplete="off">
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" id="btn-buscar-portada" title="Buscar portada automáticamente">
                                <span class="material-symbols-outlined">image_search</span>
                                Portada
                            </button>
                        </div>

                        <div class="field-row two-cols">
                            <div class="form-field">
                                <label for="detail-author-input" class="form-label">Autor principal <span class="req">*</span></label>
                                <input type="text" id="detail-author-input" class="form-input" placeholder="Nombre del autor" autocomplete="off">
                            </div>
                            <div class="form-field">
                                <label for="detail-category-input" class="form-label">Categoría / Materia <span class="req">*</span></label>
                                <select id="detail-category-input" class="form-select">
                                    <option value="">Selecciona materia</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-field full">
                            <label for="detail-coauthors-input" class="form-label">Coautor(es) <span class="opt">(opcional)</span></label>
                            <div class="tags-input" id="coauthors-wrapper">
                                <div class="tags-list" id="coauthors-list"></div>
                                <input type="text" id="detail-coauthors-input" class="tags-input-field" placeholder="Escribe y presiona Enter">
                            </div>
                        </div>

                        <div class="form-field full">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <label for="detail-synopsis-input" class="form-label">Sinopsis <span class="opt">(opcional)</span></label>
                                <button type="button" class="btn btn-ghost btn-sm" id="btn-generar-sinopsis">
                                    <span class="material-symbols-outlined">auto_awesome</span>
                                    Generar
                                </button>
                            </div>
                            <textarea id="detail-synopsis-input" class="form-textarea" maxlength="2000" placeholder="Breve descripción del contenido del libro..."></textarea>
                            <span class="field-hint"><span id="synopsis-count">0</span>/2000</span>
                        </div>
                    </div>
                </section>

                <!-- Información bibliográfica -->
                <section class="detail-section" data-section="bibliographic">
                    <div class="section-label">
                        <span class="material-symbols-outlined">description</span>
                        Información bibliográfica
                    </div>
                    <div class="section-fields">

                        <div class="form-field full">
                            <label for="detail-isbn-input" class="form-label">ISBN / ISSN <span class="req">*</span></label>
                            <input type="text" id="detail-isbn-input" class="form-input" placeholder="978-3-16-148410-0" autocomplete="off">
                        </div>

                        <div class="field-row two-cols">
                            <div class="form-field">
                                <label for="detail-publisher-input" class="form-label">Editorial</label>
                                <select id="detail-publisher-input" class="form-select">
                                    <option value="">Selecciona o escribe abajo</option>
                                </select>
                                <input type="text" id="detail-publisher-text" class="form-input" placeholder="O escribe una editorial nueva" style="margin-top:6px;">
                            </div>
                            <div class="form-field">
                                <label for="detail-year-input" class="form-label">Año de publicación</label>
                                <input type="number" id="detail-year-input" class="form-input" min="1000" max="2099" placeholder="2024" autocomplete="off">
                            </div>
                        </div>

                        <div class="field-row two-cols">
                            <div class="form-field">
                                <label for="detail-edition-input" class="form-label">Edición</label>
                                <input type="text" id="detail-edition-input" class="form-input" placeholder="1ª, 2ª, Revisada..." autocomplete="off">
                            </div>
                            <div class="form-field">
                                <label for="detail-city-input" class="form-label">Ciudad de publicación</label>
                                <input type="text" id="detail-city-input" class="form-input" placeholder="Madrid, Bogotá..." autocomplete="off">
                            </div>
                        </div>

                        <div class="field-row two-cols">
                            <div class="form-field">
                                <label for="detail-serie-input" class="form-label">Serie <span class="opt">(opcional)</span></label>
                                <input type="text" id="detail-serie-input" class="form-input" placeholder="Nombre de la serie">
                            </div>
                            <div class="form-field">
                                <label for="detail-volumen-input" class="form-label">Volumen <span class="opt">(opcional)</span></label>
                                <input type="number" id="detail-volumen-input" class="form-input" min="1" placeholder="1">
                            </div>
                        </div>

                        <div class="field-row two-cols">
                            <div class="form-field">
                                <label for="detail-pages-input" class="form-label">Número de páginas</label>
                                <input type="number" id="detail-pages-input" class="form-input" min="1" placeholder="320">
                            </div>
                            <div class="form-field">
                                <label for="detail-language" class="form-label">Idioma</label>
                                <select id="detail-language" class="form-select">
                                    <option value="">Selecciona o escribe abajo</option>
                                </select>
                                <input type="text" id="detail-language-text" class="form-input" placeholder="O escribe un idioma nuevo" style="margin-top:6px;">
                            </div>
                        </div>

                        <div class="form-field full">
                            <label for="detail-material-type" class="form-label">Tipo de material</label>
                            <select id="detail-material-type" class="form-select">
                                <option value="">Selecciona tipo</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Inventario y ejemplares -->
                <section class="detail-section" data-section="inventory">
                    <div class="section-label">
                        <span class="material-symbols-outlined">inventory_2</span>
                        Inventario
                    </div>
                    <div class="section-fields">

                        <div class="field-row two-cols">
                            <div class="form-field">
                                <label for="detail-total-copies" class="form-label">Cantidad de ejemplares <span class="req">*</span></label>
                                <div class="stepper">
                                    <button type="button" class="stepper-btn" id="copies-minus" aria-label="Disminuir">
                                        <span class="material-symbols-outlined">remove</span>
                                    </button>
                                    <input type="number" id="detail-total-copies" class="stepper-input" min="1" max="999" value="1">
                                    <button type="button" class="stepper-btn" id="copies-plus" aria-label="Aumentar">
                                        <span class="material-symbols-outlined">add</span>
                                    </button>
                                </div>
                            </div>
                            <div class="form-field">
                                <label for="detail-initial-status" class="form-label">Estado inicial</label>
                                <select id="detail-initial-status" class="form-select">
                                    <option value="Disponible" selected>Disponible</option>
                                </select>
                            </div>
                        </div>

                        <div class="field-row two-cols">
                            <div class="form-field">
                                <label for="detail-location-input" class="form-label">Ubicación / Colección <span class="req">*</span></label>
                                <select id="detail-location-input" class="form-select">
                                    <option value="">Selecciona colección</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label for="detail-branch" class="form-label">Biblioteca / Sede</label>
                                <select id="detail-branch" class="form-select">
                                    <option value="">Principal</option>
                                </select>
                            </div>
                        </div>

                        <div class="exemplars-block">
                            <div class="exemplars-label-row">
                                <span class="form-label">Ejemplares</span>
                                <span class="exemplars-badge" id="exemplars-count">0 ejemplares</span>
                            </div>
                            <div class="exemplars-table-wrap" id="exemplars-table-wrap" hidden>
                                <table class="exemplars-table">
                                    <thead>
                                        <tr><th>ID Ejemplar</th><th>Estado</th></tr>
                                    </thead>
                                    <tbody id="exemplars-tbody"></tbody>
                                </table>
                            </div>
                            <p class="field-hint" id="exemplars-hint" style="padding:12px 16px;">
                                Cada ejemplar se generará automáticamente con su propio ID (EJ-0001, EJ-0002...) para poder prestarse de forma independiente.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Información automática -->
                <section class="detail-section" data-section="auto">
                    <div class="section-label">
                        <span class="material-symbols-outlined">badge</span>
                        Información automática
                    </div>
                    <div class="auto-info-grid">
                        <div class="auto-info-item">
                            <span class="auto-info-key">ID del libro</span>
                            <output class="auto-info-val" id="auto-book-id">—</output>
                        </div>
                        <div class="auto-info-item">
                            <span class="auto-info-key">Fecha de registro</span>
                            <output class="auto-info-val" id="auto-created-date">—</output>
                        </div>
                        <div class="auto-info-item">
                            <span class="auto-info-key">Última modificación</span>
                            <output class="auto-info-val" id="auto-modified-date">—</output>
                        </div>
                    </div>
                </section>

                <!-- Salud del inventario -->
                <section class="health-block" aria-label="Salud del inventario">
                    <div class="health-block-header">
                        <span class="material-symbols-outlined">monitor_heart</span>
                        Salud del inventario
                    </div>
                    <div class="health-stats">
                        <div class="health-stat">
                            <span class="material-symbols-outlined health-icon">menu_book</span>
                            <div class="health-info"><strong id="health-total">0</strong><span>Ejemplares totales</span></div>
                        </div>
                        <div class="health-stat on">
                            <span class="material-symbols-outlined health-icon">check_circle</span>
                            <div class="health-info"><strong id="health-available">0</strong><span>Disponibles</span></div>
                        </div>
                        <div class="health-stat">
                            <span class="material-symbols-outlined health-icon">swap_horiz</span>
                            <div class="health-info"><strong id="health-borrowed">0</strong><span>Prestados</span></div>
                        </div>
                        <div class="health-stat">
                            <span class="material-symbols-outlined health-icon">inventory</span>
                            <div class="health-info"><strong id="health-out">0</strong><span>Sin stock</span></div>
                        </div>
                        <div class="health-stat rate">
                            <span class="material-symbols-outlined health-icon">trending_up</span>
                            <div class="health-info"><strong id="health-rate">0%</strong><span>Tasa de circulación</span></div>
                        </div>
                    </div>
                </section>

                <!-- Historial -->
                <section class="history-block" aria-label="Historial del libro">
                    <div class="history-block-header">
                        <span class="material-symbols-outlined">history</span>
                        Historial del libro
                    </div>
                    <div class="history-timeline" id="history-timeline">
                        <div class="history-empty" id="history-empty">
                            <span class="material-symbols-outlined">history</span>
                            <p>No hay actividad reciente<br><small>El historial aparecerá aquí tras el primer guardado.</small></p>
                        </div>
                    </div>
                </section>

            </form>

            <footer class="detail-footer">
                <div class="detail-footer-left">
                    <div class="detail-status-indicator">
                        <span class="status-dot" id="detail-status-dot"></span>
                        <span id="detail-status-text">Nuevo libro</span>
                    </div>
                    <span class="dirty-badge" id="dirty-badge" hidden>
                        <span class="material-symbols-outlined">edit</span>
                        Cambios sin guardar
                    </span>
                </div>
                <div class="detail-footer-right">
                    <button type="button" class="btn btn-ghost" id="detail-cancel-btn">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="detail-delete-btn" hidden>
                        <span class="material-symbols-outlined">delete_forever</span>
                        Eliminar
                    </button>
                    <button type="button" class="btn btn-primary" id="detail-save-btn">
                        <span class="material-symbols-outlined">save</span>
                        <span id="detail-save-text">Guardar</span>
                    </button>
                </div>
            </footer>
        </div>
    </div>

    <!-- Modal confirmar eliminar -->
    <div class="confirm-modal" id="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title" hidden>
        <div class="confirm-backdrop" id="confirm-backdrop"></div>
        <div class="confirm-box">
            <div class="confirm-icon">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <h3 id="confirm-modal-title">Eliminar libro</h3>
            <p>¿Estás seguro de que deseas eliminar <strong id="confirm-book-title"></strong>? Esta acción eliminará también todos sus ejemplares y no se puede deshacer.</p>
            <div class="confirm-actions">
                <button type="button" class="btn btn-ghost" id="confirm-cancel-btn">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                    <span class="material-symbols-outlined">delete_forever</span>
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal seleccionar portada -->
    <div class="confirm-modal" id="cover-select-modal" role="dialog" aria-modal="true" hidden>
        <div class="confirm-backdrop" id="cover-select-backdrop"></div>
        <div class="confirm-box" style="max-width:640px; text-align:left;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0;">Elige una portada</h3>
                <button type="button" class="detail-close-btn" id="cover-select-close" aria-label="Cerrar">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="cover-select-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:12px; max-height:400px; overflow:auto;"></div>
        </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container" id="toast-container"></div>

</main>
</div>
<?php include '../../../../shared/layouts/footer.php'; ?>

<script src="/Appjoteca/shared/js/components/notifications.js"></script>

<script src="../../../../shared/js/global.js"></script>
<script src="inventario.js"></script>
</body>
</html>