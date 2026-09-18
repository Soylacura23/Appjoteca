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
    <title>Inventario · APPJOTECA</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- 1. THEME GLOBAL -->
    <link rel="stylesheet" href="../../../../shared/css/theme.css">
    
    <!-- 2. Componentes Compartidos -->
    <link rel="stylesheet" href="../../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../../shared/css/components/footer.css">
    
    <!-- 3. Estilos Locales del Dashboard -->
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="inventario.css">
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

    <!-- Main content -->
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

        <!-- Filtros y estadísticas rápidas -->
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
                        <option value="repair">En reparación</option>
                        <option value="lost">Perdidos</option>
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

        <!-- Grid de libros -->
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
             OVERLAY DETALLE DEL LIBRO (con blur, no sidebar)
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

                            <div class="form-field full">
                                <label for="detail-title-input" class="form-label">Título del libro <span class="req">*</span></label>
                                <input type="text" id="detail-title-input" class="form-input" placeholder="Ej: Cien años de soledad" autocomplete="off">
                            </div>

                            <div class="field-row two-cols">
                                <div class="form-field">
                                    <label for="detail-author-input" class="form-label">Autor principal <span class="req">*</span></label>
                                    <input type="text" id="detail-author-input" class="form-input" placeholder="Nombre del autor" autocomplete="off">
                                </div>
                                <div class="form-field">
                                    <label for="detail-category-input" class="form-label">Categoría / Materia <span class="req">*</span></label>
                                    <input type="text" id="detail-category-input" class="form-input" list="category-suggestions" placeholder="Ej: Ficción, Historia..." autocomplete="off">
                                    <datalist id="category-suggestions"></datalist>
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
                                <label for="detail-synopsis-input" class="form-label">Sinopsis <span class="opt">(opcional, recomendada)</span></label>
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
                                    <label for="detail-publisher-input" class="form-label">Editorial <span class="opt">(recomendada)</span></label>
                                    <input type="text" id="detail-publisher-input" class="form-input" placeholder="Nombre de la editorial" autocomplete="off">
                                </div>
                                <div class="form-field">
                                    <label for="detail-year-input" class="form-label">Año de publicación <span class="opt">(recomendado)</span></label>
                                    <input type="number" id="detail-year-input" class="form-input" min="1000" max="2099" placeholder="2024" autocomplete="off">
                                </div>
                            </div>
                            <div class="field-row two-cols">
                                <div class="form-field">
                                    <label for="detail-edition-input" class="form-label">Edición <span class="opt">(recomendada)</span></label>
                                    <input type="text" id="detail-edition-input" class="form-input" placeholder="1ª, 2ª, Revisada..." autocomplete="off">
                                </div>
                                <div class="form-field">
                                    <label for="detail-city-input" class="form-label">Ciudad de publicación <span class="opt">(opcional)</span></label>
                                    <input type="text" id="detail-city-input" class="form-input" placeholder="Madrid, Bogotá..." autocomplete="off">
                                </div>
                            </div>
                            <div class="field-row two-cols">
                                <div class="form-field">
                                    <label for="detail-pages-input" class="form-label">Número de páginas <span class="opt">(opcional)</span></label>
                                    <input type="number" id="detail-pages-input" class="form-input" min="0" placeholder="320" autocomplete="off">
                                </div>
                                <div class="form-field">
                                    <label for="detail-material-type" class="form-label">Tipo de material</label>
                                    <select id="detail-material-type" class="form-select">
                                        <option value="Libro">Libro</option>
                                        <option value="Revista">Revista</option>
                                        <option value="Tesis">Tesis</option>
                                        <option value="CD/DVD">CD / DVD</option>
                                        <option value="Audiolibro">Audiolibro</option>
                                        <option value="eBook">eBook</option>
                                        <option value="Mapa">Mapa</option>
                                        <option value="Periódico">Periódico</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-field full">
                                <label for="detail-language" class="form-label">Idioma <span class="opt">(opcional)</span></label>
                                <select id="detail-language" class="form-select">
                                    <option value="">Selecciona...</option>
                                    <option value="Español">Español</option>
                                    <option value="Inglés">Inglés</option>
                                    <option value="Francés">Francés</option>
                                    <option value="Portugués">Portugués</option>
                                    <option value="Alemán">Alemán</option>
                                    <option value="Italiano">Italiano</option>
                                    <option value="Otro">Otro</option>
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
                                    <label for="detail-initial-status" class="form-label">Estado inicial <span class="req">*</span></label>
                                    <select id="detail-initial-status" class="form-select">
                                        <option value="Disponible">Disponible</option>
                                        <option value="En reparación">En reparación</option>
                                        <option value="Perdido">Perdido</option>
                                    </select>
                                </div>
                            </div>
                            <div class="field-row two-cols">
                                <div class="form-field">
                                    <label for="detail-location-input" class="form-label">Ubicación / Colección <span class="req">*</span></label>
                                    <input type="text" id="detail-location-input" class="form-input" list="location-suggestions" placeholder="Ej: Sala A - Estante 3" autocomplete="off">
                                    <datalist id="location-suggestions"></datalist>
                                </div>
                                <div class="form-field">
                                    <label for="detail-branch" class="form-label">Biblioteca / Sede <span class="opt">(si aplica)</span></label>
                                    <select id="detail-branch" class="form-select">
                                        <option value="">Principal</option>
                                        <option value="Sede Norte">Sede Norte</option>
                                        <option value="Sede Sur">Sede Sur</option>
                                        <option value="Sede Este">Sede Este</option>
                                        <option value="Sede Oeste">Sede Oeste</option>
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
                                <p class="field-hint" id="exemplars-hint" style="padding:12px 16px;">Cada ejemplar se generará automáticamente con su propio ID (EJ-0001, EJ-0002...) para poder prestarse de forma independiente.</p>
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
                                <span class="material-symbols-outlined health-icon">build</span>
                                <div class="health-info"><strong id="health-repair">0</strong><span>En reparación</span></div>
                            </div>
                            <div class="health-stat">
                                <span class="material-symbols-outlined health-icon">report</span>
                                <div class="health-info"><strong id="health-lost">0</strong><span>Perdidos</span></div>
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

        <!-- Modal confirmar eliminar (reemplaza alert) -->
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

        <!-- Modal cambiar portada -->
        <div class="image-modal" id="image-modal" role="dialog" aria-modal="true" aria-labelledby="image-modal-title" hidden>
            <div class="image-modal-backdrop" id="image-modal-backdrop"></div>
            <div class="image-modal-box">
                <div class="image-modal-header">
                    <h3 id="image-modal-title">Cambiar portada</h3>
                    <button type="button" class="detail-close-btn" id="image-modal-close-btn" aria-label="Cerrar">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="image-modal-body">
                    <div class="img-preview" id="img-preview">
                        <img id="image-modal-preview-img" src="" alt="Vista previa" hidden>
                        <div class="img-placeholder" id="image-modal-placeholder">
                            <span class="material-symbols-outlined">image</span>
                            <span>Sin imagen</span>
                        </div>
                    </div>
                    <div class="form-field full">
                        <label for="image-modal-url" class="form-label">URL de la imagen</label>
                        <input type="text" id="image-modal-url" class="form-input" placeholder="https://...">
                    </div>
                    <div class="form-field full">
                        <label class="form-label">O sube un archivo</label>
                        <div class="file-dropzone" id="image-modal-dropzone">
                            <input type="file" id="image-modal-file" accept="image/*" hidden>
                            <span class="material-symbols-outlined">cloud_upload</span>
                            <p>Arrastra una imagen o haz clic para seleccionar</p>
                            <small>PNG, JPG, WEBP · Máx. 5 MB</small>
                        </div>
                    </div>
                </div>
                <div class="image-modal-footer">
                    <button type="button" class="btn btn-ghost" id="image-modal-cancel-btn">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="image-modal-save-btn">
                        <span class="material-symbols-outlined">check</span>
                        Guardar
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include '../../../../shared/layouts/footer.php'; ?>

        
    </main>

    <script src="../../../../shared/js/global.js"></script>
    <script src="inventario.js"></script>
</body>
</html>