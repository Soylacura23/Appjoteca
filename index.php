<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once __DIR__ . '/backend/config/auth-check.php';

require("backend/Database/conexion.php");

// totales
if (!isset($_SESSION['totales_biblioteca'])) {
    $sql_totales = "SELECT 'libros' as tipo, COUNT(*) as total FROM libros
            UNION ALL
            SELECT 'categorias', COUNT(*) FROM materias
            UNION ALL
            SELECT 'autores', COUNT(*) FROM autores
            UNION ALL
            SELECT 'colecciones', COUNT(*) FROM colecciones";
                
    if ($query = $connection->prepare($sql_totales)) {
        $query->execute();
        $resultado = $query->get_result();
        
        $datos_temporales = [];
        while ($fila = $resultado->fetch_assoc()) {
            $datos_temporales[$fila['tipo']] = $fila['total'];
        }
        $_SESSION['totales_biblioteca'] = $datos_temporales;
    }
}

$cantidad_libros      = $_SESSION['totales_biblioteca']['libros'] ?? 0;
$cantidad_categorias  = $_SESSION['totales_biblioteca']['categorias'] ?? 0;
$cantidad_autores     = $_SESSION['totales_biblioteca']['autores'] ?? 0;
$cantidad_colecciones = $_SESSION['totales_biblioteca']['colecciones'] ?? 0;

// Autores Destacados
$sql_autores = "SELECT a.id_autor,
                       a.nombre AS nombre_autor,
                       COUNT(DISTINCT l.id_libro) AS obras_disponibles,
                       COUNT(p.id_prestamo)       AS total_prestamos
                FROM autores a
                INNER JOIN libro_autor la ON a.id_autor = la.id_autor
                INNER JOIN libros l       ON l.id_libro = la.id_libro
                INNER JOIN ejemplares e   ON e.fk_id_libro_ejemplar = l.id_libro
                INNER JOIN prestamos p    ON p.id_ejemplar = e.id_ejemplar
                GROUP BY a.id_autor, a.nombre
                ORDER BY total_prestamos DESC
                LIMIT 2";

$autores_destacados = [];
if ($query_autores = $connection->prepare($sql_autores)) {
    $query_autores->execute();
    $result = $query_autores->get_result();
    if($result){
        $autores_destacados = $result->fetch_all(MYSQLI_ASSOC);
    }
}
$autores_placeholder = max(2 - count($autores_destacados), 0);

// Materias (Categorías)
$sql_materias = "SELECT * FROM materias LIMIT 8";
$materias = [];
if ($query_materias = $connection->prepare($sql_materias)) {
    $query_materias->execute();
    $result_mat = $query_materias->get_result();
    if($result_mat){
        $materias = $result_mat->fetch_all(MYSQLI_ASSOC);
    }
}

// Colecciones
$sql_colecciones = "SELECT * FROM colecciones LIMIT 6";
$colecciones = [];
if ($query_colecciones = $connection->prepare($sql_colecciones)) {
    $query_colecciones->execute();
    $result_col = $query_colecciones->get_result();
    if($result_col){
        $colecciones = $result_col->fetch_all(MYSQLI_ASSOC);
    }
}

function autor_avatar($nombre) {
    $nombre = trim($nombre);
    $partes = preg_split('/\s+/', $nombre);
    $ini = '';
    foreach ($partes as $p) {
        if ($p !== '') { $ini .= mb_strtoupper(mb_substr($p, 0, 1)); }
        if (mb_strlen($ini) >= 2) break;
    }
    if ($ini === '') $ini = '?';

    $hash = crc32($nombre);
    $hue = $hash % 360;
    $hue2 = ($hue + 40) % 360;
    return [
        'ini' => $ini,
        'grad' => "linear-gradient(135deg, hsl({$hue}, 60%, 45%) 0%, hsl({$hue2}, 70%, 25%) 100%)"
    ];
}

$iconos_categorias = ['auto_stories', 'account_balance', 'psychology', 'science', 'nightlight', 'palette', 'memory', 'groups'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPJOTECA | El Legado de la Humanidad</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Noto+Serif:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300,0,0" />

    <!-- AOS para animaciones -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

        <!-- Landing específico -->
    <link rel="stylesheet" href="shared/css/landing.css">
    <!-- Componente topbar compartido -->
    <link rel="stylesheet" href="shared/css/components/topbar-landing.css">

    <link rel="stylesheet" href="shared/css/components/footer.css">
    <link rel="icon" type="image/png" href="shared/images/logo-appjoteca.png">
</head>

<body>

<header class="main-header" id="mainHeader">
    <div class="header-container">
            <a href="../../index.php" class="logo-link">
    <img src="shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
</a>
        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="pages/sobre-nosotros/about-us.php" class="nav-link">Sobre Nosotros</a></li>
                <li><a href="pages/manual/manual.php" class="nav-link">Manual de Usuario</a></li>
            </ul>
        </nav>
        <div class="header-actions">
            <a href="auth/login/login.php" class="btn btn-primary btn-header">
                <span class="material-symbols-outlined">login</span>
                Acceder
            </a>
        </div>
        <button class="mobile-menu-btn" aria-label="Menú">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
</header>

<!-- HERO CON IMAGEN DE FONDO -->
<section class="hero-header">
    <div class="hero-bg" data-parallax="0.15"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content" data-aos="fade-up" data-aos-duration="1000">
        <span class="hero-badge">✦ El Legado de la Humanidad ✦</span>
        <h1 class="hero-title">
            <span class="title-outline">PRESERVANDO</span>
            <span class="title-gradient">El Legado de la</span>
            <span class="title-outline">HUMANIDAD</span>
        </h1>
        <p class="hero-description">
            Un santuario digital donde el conocimiento es universal. Le apostamos al fomento de la lectura para impulsar el conocimiento universal.
        </p>
        <div class="hero-actions">
            <a href="#booksScroll" class="btn btn-primary btn-glow">
                <span class="material-symbols-outlined">explore</span>
                Explorar Colección
            </a>
            <a href="auth/login/login.php" class="btn btn-secondary">
                <span class="material-symbols-outlined">login</span>
                Ingresar
            </a>
        </div>
    </div>
    <div class="scroll-indicator">
        <span class="material-symbols-outlined">expand_more</span>
    </div>
</section>

<!-- SCROLL HORIZONTAL DE LIBROS -->
<section class="books-scroll" id="booksScroll">
    <div class="books-scroll-sticky">
        <div class="section-header" data-aos="fade-up">
            <div class="section-title-group">
                <span class="section-label">✦ Muestra Destacada ✦</span>
                <h2 class="section-title">Obras Más <span class="gradient-text">Interesantes</span></h2>
            </div>
        </div>
        <div class="books-track-viewport">
            <div class="books-track" id="booksTrack"></div>
        </div>
    </div>
</section>

<!-- MARQUESINA ELEGANTE -->
<div class="marquee-section">
    <div class="marquee">
        <div class="marquee-content">
            <span>Conocimiento</span><span class="dot">✦</span>
            <span>Historia</span><span class="dot">✦</span>
            <span>Cultura</span><span class="dot">✦</span>
            <span>Ciencia</span><span class="dot">✦</span>
            <span>Filosofía</span><span class="dot">✦</span>
            <span>Literatura</span><span class="dot">✦</span>
            <span>Conocimiento</span><span class="dot">✦</span>
            <span>Historia</span><span class="dot">✦</span>
            <span>Cultura</span><span class="dot">✦</span>
            <span>Ciencia</span><span class="dot">✦</span>
            <span>Filosofía</span><span class="dot">✦</span>
            <span>Literatura</span><span class="dot">✦</span>
        </div>
    </div>
</div>

<!-- CATEGORÍAS -->
<section class="categories">
    <div class="section-header" data-aos="fade-up">
        <div class="section-title-group">
            <span class="section-label">✦ Disciplinas ✦</span>
            <h2 class="section-title">Explora el <span class="gradient-text">Conocimiento</span></h2>
            <p class="section-copy">Cada disciplina abre una puerta distinta hacia la comprensión del mundo.</p>
        </div>
    </div>
    <div class="categories-grid">
        <?php if (!empty($materias)): ?>
            <?php foreach ($materias as $index => $materia): 
                $nombre_materia = htmlspecialchars($materia['nombre_materia'] ?? $materia['nombre'] ?? 'Categoría');
                $icono = $iconos_categorias[$index % count($iconos_categorias)];
            ?>
            <div class="category-card" data-aos="zoom-in" data-aos-delay="<?= ($index % 8) * 60 ?>">
                <div class="category-icon"><span class="material-symbols-outlined"><?= $icono ?></span></div>
                <h3><?= $nombre_materia ?></h3>
                <p>Explora los archivos y registros de nuestra colección en <?= strtolower($nombre_materia) ?>.</p>
                <span class="category-arrow">
                    <span class="material-symbols-outlined">arrow_forward</span>
                </span>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="category-card empty" style="grid-column: 1 / -1;">
                <p>Aún no hay categorías registradas en la base de datos.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- FILOSOFÍA -->
<section class="philosophy">
    <div class="philosophy-bg" data-parallax="0.2"></div>
    <div class="philosophy-overlay"></div>
    <div class="philosophy-inner" data-aos="fade-up">
        <div class="section-title-group section-title-group--center">
            <span class="section-label">✦ Nuestra Filosofía ✦</span>
        </div>
        <p class="philosophy-quote">
            «Cada obra conserva una parte de aquello que la humanidad decidió no olvidar.»
        </p>
        <p class="philosophy-copy">
            Una biblioteca no es simplemente un lugar donde se almacenan libros. Es un espacio donde las ideas sobreviven, donde las preguntas encuentran nuevas generaciones y donde el conocimiento puede viajar mucho más allá de su época.
        </p>
    </div>
</section>

<!-- ESTADÍSTICAS -->
<section class="stats">
    <div class="stats-grid">
        <div class="stat" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-icon"><span class="material-symbols-outlined">auto_stories</span></div>
            <div class="stat-number"><?= $cantidad_libros ?></div>
            <div class="stat-label">Libros registrados</div>
        </div>
        <div class="stat" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-icon"><span class="material-symbols-outlined">category</span></div>
            <div class="stat-number"><?= $cantidad_categorias ?></div>
            <div class="stat-label">Categorías</div>
        </div>
        <div class="stat" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-icon"><span class="material-symbols-outlined">person</span></div>
            <div class="stat-number"><?= $cantidad_autores ?></div>
            <div class="stat-label">Autores registrados</div>
        </div>
        <div class="stat" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-icon"><span class="material-symbols-outlined">collections_bookmark</span></div>
            <div class="stat-number"><?= $cantidad_colecciones ?></div>
            <div class="stat-label">Colecciones Curadas</div>
        </div>
    </div>
</section>

<!-- LIBRO DE LA SEMANA -->
<section class="book-week" id="bookWeekContainer"></section>

<!-- AUTORES DESTACADOS (con avatares generados) -->
<section class="authors">
    <div class="section-header" data-aos="fade-up">
        <div class="section-title-group">
            <span class="section-label">✦ Voces que Trascienden ✦</span>
            <h2 class="section-title">Autores <span class="gradient-text">Destacados</span></h2>
        </div>
    </div>
    <div class="authors-grid">
        <?php foreach ($autores_destacados as $index => $autor): 
            $av = autor_avatar($autor['nombre_autor'] ?? 'Autor');
        ?>
        <a href="#" class="author-card" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
            <div class="author-avatar" style="background: <?= $av['grad'] ?>;">
                <span><?= htmlspecialchars($av['ini']) ?></span>
            </div>
            <div class="author-card-inner">
                <span class="author-field">✦ Más Leído</span>
                <h3 class="author-name"><?= htmlspecialchars($autor['nombre_autor'] ?? 'Autor') ?></h3>
                <span class="author-reveal">
                    <?= (int) ($autor['obras_disponibles'] ?? 0) ?> obras disponibles
                    <span class="material-symbols-outlined">arrow_forward</span>
                </span>
            </div>
        </a>
        <?php endforeach; ?>

        <?php for ($i = 0; $i < $autores_placeholder; $i++): ?>
        <a href="#" class="author-card placeholder" data-aos="fade-up" data-aos-delay="<?= ($i + 2) * 100 ?>">
            <div class="author-avatar placeholder-avatar">
                <span>?</span>
            </div>
            <div class="author-card-inner">
                <span class="author-field">Próximamente</span>
                <h3 class="author-name">Mente por Descubrir</h3>
                <span class="author-reveal">Aún sin registros</span>
            </div>
        </a>
        <?php endfor; ?>
    </div>
</section>

<!-- COLECCIONES -->
<section class="collections">
    <div class="section-header" data-aos="fade-up">
        <div class="section-title-group">
            <span class="section-label">✦ Archivos ✦</span>
            <h2 class="section-title">Nuestras <span class="gradient-text">Colecciones</span></h2>
        </div>
    </div>
    <div class="collections-grid">
        <?php if (!empty($colecciones)): ?>
            <?php 
            $contador = 1;
            foreach ($colecciones as $index => $coleccion): 
                $nombre_col = htmlspecialchars($coleccion['nombre_coleccion'] ?? $coleccion['nombre'] ?? 'Colección');
                $numero_str = str_pad($contador, 2, '0', STR_PAD_LEFT);
            ?>
            <div class="collection-card" data-aos="fade-up" data-aos-delay="<?= $index * 80 ?>">
                <span class="collection-number"><?= $numero_str ?></span>
                <div class="collection-icon">
                    <span class="material-symbols-outlined">collections_bookmark</span>
                </div>
                <h3><?= $nombre_col ?></h3>
                <p>Archivos y registros asignados a esta colección.</p>
                <span class="collection-line"></span>
            </div>
            <?php 
                $contador++;
            endforeach; 
            ?>
        <?php else: ?>
            <div class="collection-card empty" style="grid-column: 1 / -1;">
                <p>Aún no hay colecciones registradas en la base de datos.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- DIVISOR CON TRANSICIÓN DIFUMINADA HACIA EL CTA FINAL -->
<div class="section-transition"></div>

<!-- CTA FINAL CON IMAGEN DE FONDO -->
<section class="cta-final">
    <div class="cta-final-bg" data-parallax="0.1"></div>
    <div class="cta-final-overlay"></div>
    <div class="cta-final-inner" data-aos="fade-up">
        <span class="cta-final-line"></span>
        <h2 class="cta-final-title">El Viaje <span class="gradient-text">Comienza Aquí</span></h2>
        <p class="cta-final-copy">Tu próxima lectura te está esperando. Ingresa al archivo y descubre el legado.</p>
        <a href="auth/login/login.php" class="btn btn-primary btn-glow">
            <span class="material-symbols-outlined">login</span>
            Ingresar al Archivo
        </a>
    </div>
</section>

<?php 
if (file_exists('shared/layouts/footer.php')) {
    include 'shared/layouts/footer.php'; 
}
?>

<script src="shared/js/menu.js" onerror="this.remove()"></script>
<script src="shared/js/landing.js"></script>
</body>
</html>