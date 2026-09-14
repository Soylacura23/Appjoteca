
<?php

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require("backend/Database/conexion.php");

if (!isset($_SESSION['totales_biblioteca'])) {
    
    $sql = "SELECT 'libros' as tipo, COUNT(*) as total FROM libros
            UNION ALL
            SELECT 'categorias', COUNT(*) FROM materias
            UNION ALL
            SELECT 'autores', COUNT(*) FROM autores
            UNION ALL
            SELECT 'colecciones', COUNT(*) FROM colecciones";
                
    $query = $connection->prepare($sql);
    $query->execute();
    $resultado = $query->get_result();
    
    $datos_temporales = [];
    
    while ($fila = $resultado->fetch_assoc()) {
        $datos_temporales[$fila['tipo']] = $fila['total'];
    }
    
    $_SESSION['totales_biblioteca'] = $datos_temporales;
}

$cantidad_libros     = $_SESSION['totales_biblioteca']['libros'] ?? 0;
$cantidad_categorias = $_SESSION['totales_biblioteca']['categorias'] ?? 0;
$cantidad_autores    = $_SESSION['totales_biblioteca']['autores'] ?? 0;
$cantidad_colecciones= $_SESSION['totales_biblioteca']['colecciones'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APPJOTECA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Noto+Serif:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="shared/css/landing.css">
    <link rel="stylesheet" href="shared/css/components/footer.css">
    <link rel="icon" type="image/png"  href="shared/images/logo-appjoteca.png">

</head>

<body>


<header class="main-header" id="mainHeader">
    <div class="header-container">
        <a href="#" class="logo-link">
            <img src="shared/images/logo-appjoteca.png" alt="Appjoteca Logo" class="logo-img">
            <span class="logo-text">APPJOTECA</span>
        </a>

        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="pages/sobre-nosotros/about-us.php" class="nav-link">Sobre Nosotros</a></li>
                <li><a href="#" class="nav-link">Contacto</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <span class="header-divider"></span>
            <a href="auth/login/login.php" class="btn btn-primary btn-header">
                <span>Acceder</span>
            </a>
        </div>

        <!-- Menú móvil -->
        <button class="mobile-menu-btn" aria-label="Menú">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
</header>

<section class="hero-header">
    <div class="hero-bg" data-parallax="0.15"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">

        <h1 class="hero-title">
            <span class="title-line">PRESERVANDO</span>
            <span class="title-accent">EL LEGADO DE LA</span>
            <span class="title-line">HUMANIDAD</span>
        </h1>

        <p class="hero-description">
            Appjoteca se fundó bajo la convicción de que un futuro donde el conocimiento sea universal y universalmente accesible es fundamentalmente más emocionante que uno donde no lo sea.
        </p>

        <div class="hero-actions">
            <a href="#" class="btn btn-primary btn-hero">
                <span>Explorar Colección</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
            <a href="pages/sobre-nosotros/about-us.php" class="btn btn-secondary btn-hero">
                <span>Más Información</span>
            </a>
        </div>

    </div>
</section>

<section class="books-scroll" id="booksScroll">
    <div class="books-scroll-sticky">

        <div class="section-header">
            <div class="section-title-group">
                <span class="section-label">Muestra</span>
                <h2 class="section-title">Libros Más Interesantes</h2>
            </div>
            <a href="#" class="btn btn-ghost btn-view-all">
                <span>Ver Todas las Obras</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="books-track-viewport">
            <div class="books-track" id="booksTrack">

                <div class="book-slide">
                    <div class="card">
                        <div class="card-image-wrapper">
                            <img src="assets/images/books/la_metamorfosis.jpg" alt="La Metamorfosis" loading="lazy">
                            <div class="card-overlay">
                                <span class="tag">Surrealismo Lógico</span>
                                <h3>La Metamorfosis</h3>
                                <p>Franz Kafka</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="book-slide">
                    <div class="card">
                        <div class="card-image-wrapper">
                            <img src="assets/images/books/cosmos.jpg" alt="Cosmos" loading="lazy">
                            <div class="card-overlay">
                                <span class="tag">Astronomía</span>
                                <h3>Cosmos</h3>
                                <p>Carl Sagan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="book-slide book-slide-cta">
                    <div class="book-slide-cta-inner">
                        <p>El catálogo completo crece cada semana. Esto es solo una muestra.</p>
                        <a href="#" class="btn btn-secondary">
                            <span>Ver todas las obras</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════
     3. EXPLORA EL CONOCIMIENTO
     ══════════════════════════════════════════ -->
<section class="categories">

    <div class="section-header" data-reveal>
        <div class="section-title-group">
            <span class="section-label">Categorías</span>
            <h2 class="section-title">Explora el Conocimiento</h2>
            <p class="section-copy">Cada disciplina abre una puerta distinta hacia la comprensión del mundo.</p>
        </div>
    </div>

    <div class="categories-grid">

        <div class="category-card">
            <div class="category-icon"><span class="material-symbols-outlined">auto_stories</span></div>
            <h3>Literatura</h3>
            <p>Historias, novelas y obras que exploran la experiencia humana.</p>
        </div>

        <div class="category-card">
            <div class="category-icon"><span class="material-symbols-outlined">account_balance</span></div>
            <h3>Historia</h3>
            <p>Civilizaciones, acontecimientos y personajes que transformaron el mundo.</p>
        </div>

        <div class="category-card">
            <div class="category-icon"><span class="material-symbols-outlined">psychology</span></div>
            <h3>Filosofía</h3>
            <p>Ideas, preguntas y reflexiones sobre nuestra existencia.</p>
        </div>

        <div class="category-card">
            <div class="category-icon"><span class="material-symbols-outlined">science</span></div>
            <h3>Ciencias</h3>
            <p>El estudio de la naturaleza, la materia y el universo.</p>
        </div>

        <div class="category-card">
            <div class="category-icon"><span class="material-symbols-outlined">nightlight</span></div>
            <h3>Astronomía</h3>
            <p>Una mirada hacia aquello que existe más allá de nuestro planeta.</p>
        </div>

        <div class="category-card">
            <div class="category-icon"><span class="material-symbols-outlined">palette</span></div>
            <h3>Arte y Cultura</h3>
            <p>Expresiones que preservan la creatividad y la identidad humana.</p>
        </div>

        <div class="category-card">
            <div class="category-icon"><span class="material-symbols-outlined">memory</span></div>
            <h3>Tecnología</h3>
            <p>El conocimiento que transforma nuestra manera de vivir.</p>
        </div>

        <div class="category-card">
            <div class="category-icon"><span class="material-symbols-outlined">groups</span></div>
            <h3>Ciencias Sociales</h3>
            <p>El estudio de las sociedades y de las personas que las construyen.</p>
        </div>

    </div>

</section>

-->
<section class="philosophy">
    <div class="philosophy-bg" data-parallax="0.2"></div>
    <div class="philosophy-overlay"></div>

    <div class="philosophy-inner" data-reveal>
        <div class="section-title-group section-title-group--center">
            <span class="section-label">Nuestra Filosofía</span>
            <h2 class="section-title">Una biblioteca es más que una colección.</h2>
        </div>

        <p class="philosophy-copy">
            Una biblioteca no es simplemente un lugar donde se almacenan libros. Es un espacio donde las ideas sobreviven, donde las preguntas encuentran nuevas generaciones y donde el conocimiento puede viajar mucho más allá de su época.
        </p>

        <p class="philosophy-quote">
            «Cada obra conserva una parte de aquello que la humanidad decidió no olvidar.»
        </p>
    </div>
</section>

<!-- ══════════════════════════════════════════
     5. LA COLECCIÓN EN NÚMEROS
     ══════════════════════════════════════════
    
-->
<section class="stats">
    <div class="section-header" data-reveal>
        <div class="section-title-group">
            <span class="section-label">Nuestra Colección</span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat">
            <div class="stat-number"><?= $cantidad_libros ?></div>
            <div class="stat-label">Obras</div>
        </div>
        <div class="stat">
            <div class="stat-number"><?= $cantidad_categorias ?></div>
            <div class="stat-label">Categorías</div>
        </div>
        <div class="stat">
            <div class="stat-number"><?= $cantidad_autores ?></div>
            <div class="stat-label">Autores</div>
        </div>
        <div class="stat">
            <div class="stat-number"><?= $cantidad_colecciones ?></div>
            <div class="stat-label">Colecciones</div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     6. COLECCIONES
     ══════════════════════════════════════════ -->
<section class="collections">

    <div class="section-header" data-reveal>
        <div class="section-title-group">
            <span class="section-label">Colecciones</span>
            <h2 class="section-title">Un Lugar para Cada Forma de Conocimiento</h2>
        </div>
    </div>

    <div class="collections-grid">

        <div class="collection-card">
            <span class="collection-number">01</span>
            <h3>Colección General</h3>
            <p>Obras disponibles para consulta y lectura general.</p>
        </div>

        <div class="collection-card">
            <span class="collection-number">02</span>
            <h3>Colección de Referencia</h3>
            <p>Diccionarios, enciclopedias y materiales destinados a la consulta.</p>
        </div>

        <div class="collection-card">
            <span class="collection-number">03</span>
            <h3>Colección Patrimonial</h3>
            <p>Obras de valor histórico, cultural o institucional.</p>
        </div>

        <div class="collection-card">
            <span class="collection-number">04</span>
            <h3>Colección Audiovisual</h3>
            <p>CD, DVD, VHS, Betamax y otros materiales audiovisuales.</p>
        </div>

        <div class="collection-card">
            <span class="collection-number">05</span>
            <h3>Hemeroteca</h3>
            <p>Revistas, periódicos y publicaciones periódicas.</p>
        </div>

        <div class="collection-card">
            <span class="collection-number">06</span>
            <h3>Colección Semilla</h3>
            <p>Material seleccionado para fomentar el descubrimiento y la lectura.</p>
        </div>

    </div>

</section>

<!-- ══════════════════════════════════════════
     7. LIBRO DE LA SEMANA
     ══════════════════════════════════════════ -->
<section class="book-week">

    <div class="book-week-cover" data-reveal>
        <img src="assets/images/books/cosmos.jpg" alt="Cosmos, de Carl Sagan">
    </div>

    <div class="book-week-content" data-reveal>
        <div class="book-week-eyebrow">
            <span class="section-label">Recomendación</span>
            <span class="book-week-eyebrow-dot"></span>
            <span>El Libro de la Semana</span>
        </div>
        <h2 class="book-week-title">Cosmos</h2>
        <p class="book-week-author">Carl Sagan</p>
        <p class="book-week-copy">
            Una exploración de la historia del universo, de la ciencia y de nuestro lugar dentro de una realidad mucho más grande que nosotros mismos.
        </p>
        <a href="#" class="btn btn-primary btn-hero">
            <span>Descubrir la Obra</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

</section>

<!-- ══════════════════════════════════════════
     8. AUTORES DESTACADOS
     ══════════════════════════════════════════ -->
<section class="authors">

    <div class="section-header" data-reveal>
        <div class="section-title-group">
            <span class="section-label">Voces que Trascienden</span>
            <h2 class="section-title">Autores Destacados</h2>
        </div>
    </div>

    <div class="authors-grid">

        <a href="#" class="author-card">
            <div class="author-card-inner">
                <span class="author-field">Literatura</span>
                <h3 class="author-name">Franz Kafka</h3>
                <span class="author-reveal">
                    1 obras disponibles
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </span>
            </div>
        </a>

        <a href="#" class="author-card">
            <div class="author-card-inner">
                <span class="author-field">Astronomía</span>
                <h3 class="author-name">Carl Sagan</h3>
                <span class="author-reveal">
                    1 obras disponibles
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </span>
            </div>
        </a>

    </div>

</section>

<!-- ══════════════════════════════════════════
     9. PRESERVAR PARA EL FUTURO
     ══════════════════════════════════════════ -->
<section class="preserve">

    <div class="preserve-header" data-reveal>
        <span class="section-label">Compromiso Institucional</span>
        <h2 class="preserve-title">El conocimiento no debería perderse.</h2>
        <p class="preserve-copy">
            Cada libro representa una idea que alguien decidió conservar. AppJoteca busca facilitar la organización, preservación y acceso al conocimiento dentro de nuestra comunidad educativa.
        </p>
    </div>

    <div class="preserve-grid">

        <div class="preserve-item">
            <div class="preserve-icon"><span class="material-symbols-outlined">inventory_2</span></div>
            <h3>Preservar</h3>
            <p>Organizar y proteger nuestra colección.</p>
        </div>

        <div class="preserve-item">
            <div class="preserve-icon"><span class="material-symbols-outlined">diversity_2</span></div>
            <h3>Conectar</h3>
            <p>Acercar estudiantes, docentes y conocimiento.</p>
        </div>

        <div class="preserve-item">
            <div class="preserve-icon"><span class="material-symbols-outlined">search</span></div>
            <h3>Descubrir</h3>
            <p>Hacer que encontrar una nueva obra sea sencillo.</p>
        </div>

    </div>

</section>

<section class="cta-final">
    <div class="cta-final-bg" data-parallax="0.1"></div>
    <div class="cta-final-overlay"></div>

    <div class="cta-final-inner" data-reveal>
        <span class="cta-final-line"></span>
        <h2 class="cta-final-title">Tu próxima lectura está esperando.</h2>
        <p class="cta-final-copy">
            Explora la colección de AppJoteca y descubre una nueva historia, una nueva idea o una nueva pregunta.
        </p>
        <a href="#" class="btn btn-primary btn-hero">
            <span>Explorar Colección</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</section>

    <!-- ══════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════ -->
    <?php include 'shared/layouts/footer.php'; ?>

<script src="shared/js/menu.js"></script>
<script src="shared/js/landing.js"></script>
</body>
</html>