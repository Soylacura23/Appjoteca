<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
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
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <title>Configuración del Catálogo · APPJOTECA</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">

    <!-- SweetAlert2 (requerido por alert.js) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tabulator -->
    <link href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.3.1/dist/css/tabulator_midnight.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>

    <!-- 1. THEME GLOBAL -->
    <link rel="stylesheet" href="../../../../shared/css/theme.css">

    <!-- 2. Componentes Compartidos -->
    <link rel="stylesheet" href="../../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../../shared/css/components/footer.css">

    <!-- 3. Estilos Locales del Dashboard -->
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="configuracion-catalogo.css">
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
                    <a href="../reportes/reportes.php" class="menu-item">
                        <span class="material-symbols-outlined">analytics</span>
                        <span class="menu-texto">Reportes</span>
                    </a>
                </li>
                <li>
                    <a href="configuracion-catalogo.php" class="menu-item active">
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
                <p class="label-overline text-primary">Parámetros del Sistema</p>
                <h1 class="headline-xl white-text">Configuración del Catálogo</h1>
                <p class="text-body text-outline">Estructura el catálogo en cinco pasos ordenados. Los pasos base (bibliotecas, Dewey y tipos de material) deben existir antes de crear colecciones y materias asociadas.</p>
            </div>
            <div class="header-stats">
                <div class="header-stat">
                    <span class="header-stat-value" id="stat-bibliotecas">0</span>
                    <span class="header-stat-label">Bibliotecas</span>
                </div>
                <div class="header-stat">
                    <span class="header-stat-value" id="stat-dewey">0</span>
                    <span class="header-stat-label">Áreas Dewey</span>
                </div>
                <div class="header-stat">
                    <span class="header-stat-value" id="stat-colecciones">0</span>
                    <span class="header-stat-label">Colecciones</span>
                </div>
            </div>
        </section>

        <!-- Stepper / Pestañas -->
        <nav class="config-stepper" id="config-stepper" aria-label="Pasos de configuración">
            <button class="step-btn active" data-paso="1">
                <span class="step-number">1</span>
                <span class="step-info">
                    <span class="step-title">Bibliotecas</span>
                    <span class="step-sub">Sedes físicas</span>
                </span>
            </button>
            <button class="step-btn" data-paso="2">
                <span class="step-number">2</span>
                <span class="step-info">
                    <span class="step-title">Clasificación Dewey</span>
                    <span class="step-sub">Áreas del conocimiento</span>
                </span>
            </button>
            <button class="step-btn" data-paso="3">
                <span class="step-number">3</span>
                <span class="step-info">
                    <span class="step-title">Tipos de Material</span>
                    <span class="step-sub">Formatos soportados</span>
                </span>
            </button>
            <button class="step-btn locked" data-paso="4">
                <span class="step-number"><span class="material-symbols-outlined">lock</span></span>
                <span class="step-info">
                    <span class="step-title">Colecciones</span>
                    <span class="step-sub">Requiere bibliotecas</span>
                </span>
            </button>
            <button class="step-btn locked" data-paso="5">
                <span class="step-number"><span class="material-symbols-outlined">lock</span></span>
                <span class="step-info">
                    <span class="step-title">Materias</span>
                    <span class="step-sub">Requiere Dewey</span>
                </span>
            </button>
            <button class="step-btn records" data-paso="registros">
                <span class="step-number"><span class="material-symbols-outlined">table</span></span>
                <span class="step-info">
                    <span class="step-title">Registros</span>
                    <span class="step-sub">Vista general</span>
                </span>
            </button>
        </nav>

        <!-- PASO 1: BIBLIOTECAS -->
        <section id="paso-1" class="paso-content active">
            <div class="paso-card">
                <div class="paso-card-head">
                    <span class="paso-icon material-symbols-outlined">local_library</span>
                    <div>
                        <h2 class="headline-sm white-text">Registrar Biblioteca / Sede</h2>
                        <p class="text-body text-outline">Sedes físicas donde se alojan los volúmenes. Sin dependencias: es el primer escalón del catálogo.</p>
                    </div>
                </div>
                <form id="form-biblioteca" class="paso-form" autocomplete="off">
                    <div class="field">
                        <label for="bib-nombre">Nombre de la biblioteca</label>
                        <input type="text" id="bib-nombre" name="nombre" placeholder="Ej. Biblioteca Principal" required>
                    </div>
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined">save</span>
                        Guardar Biblioteca
                    </button>
                </form>
            </div>
        </section>

        <!-- PASO 2: DEWEY -->
        <section id="paso-2" class="paso-content">
            <div class="paso-card">
                <div class="paso-card-head">
                    <span class="paso-icon material-symbols-outlined">category</span>
                    <div>
                        <h2 class="headline-sm white-text">Registrar Área Dewey</h2>
                        <p class="text-body text-outline">Las 10 grandes áreas de la clasificación Dewey. Cada materia del catálogo dependerá de una de estas áreas.</p>
                    </div>
                </div>
                <form id="form-dewey" class="paso-form" autocomplete="off">
                    <div class="field">
                        <label for="dewey-codigo">Código Dewey</label>
                        <input type="text" id="dewey-codigo" name="codigo" placeholder="Ej. 400" maxlength="10" required>
                    </div>
                    <div class="field">
                        <label for="dewey-nombre">Nombre del área</label>
                        <input type="text" id="dewey-nombre" name="nombre" placeholder="Ej. Lengua" required>
                    </div>
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined">save</span>
                        Guardar Área Dewey
                    </button>
                </form>
            </div>
        </section>

        <!-- PASO 3: TIPOS DE MATERIAL -->
        <section id="paso-3" class="paso-content">
            <div class="paso-card">
                <div class="paso-card-head">
                    <span class="paso-icon material-symbols-outlined">inventory_2</span>
                    <div>
                        <h2 class="headline-sm white-text">Registrar Tipo de Material</h2>
                        <p class="text-body text-outline">Formatos que la biblioteca gestiona: libros, revistas, CD, etc.</p>
                    </div>
                </div>
                <form id="form-tipo-material" class="paso-form" autocomplete="off">
                    <div class="field">
                        <label for="tipo-nombre">Nombre del formato</label>
                        <input type="text" id="tipo-nombre" name="nombre" placeholder="Ej. Libro, Revista, CD..." required>
                    </div>
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined">save</span>
                        Guardar Tipo de Material
                    </button>
                </form>
            </div>
        </section>

        <!-- PASO 4: COLECCIONES (Requiere Biblioteca) -->
        <section id="paso-4" class="paso-content">
            <div class="paso-card">
                <div class="paso-card-head">
                    <span class="paso-icon material-symbols-outlined">collections_bookmark</span>
                    <div>
                        <h2 class="headline-sm white-text">Registrar Colección</h2>
                        <p class="text-body text-outline">Agrupaciones de material dentro de una sede. El selector se carga con las bibliotecas registradas en el Paso 1.</p>
                    </div>
                </div>
                <form id="form-coleccion" class="paso-form" autocomplete="off">
                    <div class="field">
                        <label for="select-bibliotecas">Biblioteca</label>
                        <select name="id_biblioteca" id="select-bibliotecas" required>
                            <option value="">Cargando bibliotecas...</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="col-nombre">Nombre de la colección</label>
                        <input type="text" id="col-nombre" name="nombre" placeholder="Ej. Colección General" required>
                    </div>
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined">save</span>
                        Guardar Colección
                    </button>
                </form>
            </div>
        </section>

        <!-- PASO 5: MATERIAS (Requiere Dewey) -->
        <section id="paso-5" class="paso-content">
            <div class="paso-card">
                <div class="paso-card-head">
                    <span class="paso-icon material-symbols-outlined">school</span>
                    <div>
                        <h2 class="headline-sm white-text">Registrar Materia</h2>
                        <p class="text-body text-outline">Categorías temáticas vinculadas a una gran área Dewey. El selector se carga con las áreas del Paso 2.</p>
                    </div>
                </div>
                <form id="form-materia" class="paso-form" autocomplete="off">
                    <div class="field">
                        <label for="select-dewey">Área Dewey</label>
                        <select name="id_dewey" id="select-dewey" required>
                            <option value="">Cargando áreas Dewey...</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="mat-nombre">Nombre de la materia</label>
                        <input type="text" id="mat-nombre" name="nombre" placeholder="Ej. Lenguaje y Lingüística" required>
                    </div>
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined">save</span>
                        Guardar Materia
                    </button>
                </form>
            </div>
        </section>

        <!-- REGISTROS: Tabulator -->
        <section id="paso-registros" class="paso-content">
            <div class="paso-card">
                <div class="paso-card-head">
                    <span class="paso-icon material-symbols-outlined">table</span>
                    <div>
                        <h2 class="headline-sm white-text">Registros del Catálogo</h2>
                        <p class="text-body text-outline">Consulta todo lo creado. Filtra por entidad y usa la barra de búsqueda integrada.</p>
                    </div>
                </div>

                <div class="registros-toolbar">
                    <div class="field registros-filter">
                        <label for="registro-tipo">Entidad</label>
                        <select id="registro-tipo">
                            <option value="bibliotecas">Bibliotecas</option>
                            <option value="dewey">Áreas Dewey</option>
                            <option value="tipos_materiales">Tipos de Material</option>
                            <option value="colecciones">Colecciones</option>
                            <option value="materias">Materias</option>
                        </select>
                    </div>
                    <button class="btn-ghost-sm" id="registros-recargar" type="button">
                        <span class="material-symbols-outlined">refresh</span>
                        Recargar
                    </button>
                </div>

                <div id="tabla-registros"></div>
            </div>
        </section>

        <!-- FOOTER -->
        <?php include '../../../../shared/layouts/footer.php'; ?>

    </main>

    <!-- Componente de alertas (define window.alerta con SweetAlert2) -->
    <script src="../../../../shared/js/components/alert.js"></script>
    <script src="../../../../shared/js/global.js"></script>
    <script src="configuracion-catalogo.js"></script>
</body>
</html>
