<?php
require_once "../../backend/config/auth.php";
require_once "../../backend/config/user_context.php";
require_once "../../backend/Database/conexion.php";
require_once "../../backend/models/libro.php";

$base_url = '/Appjoteca/';
$modeloLibro = new libro($connection);
$librosCarrusel = $modeloLibro->listarLibrosPaginado(1, 5, '', 0, 'recommended')['libros'];
$librosCatalogo = $modeloLibro->listarLibrosPaginado(1, 8, '', 0, 'recommended')['libros'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estantería</title>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- Estilos: orden importante — theme (variables) primero -->
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/book-card.css">
    <link rel="stylesheet" href="biblioteca.css">
    <link rel="stylesheet" href="../../shared/css/components/catalog-filters.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
    <?php require __DIR__ . '/../../shared/layouts/favicon.php'; ?>
</head>
<body>

    <!-- OVERLAY GLOBAL (perfil + notificaciones) -->
    <div id="overlay" class="overlay" aria-hidden="true"></div>


    <?php include __DIR__ . '/../../shared/layouts/notifications.php'; ?>
    <?php include __DIR__ . '/../../shared/layouts/menu-off-canvas.php'; ?>

    <?php include __DIR__ . '/../../shared/layouts/topbar.php'; ?>

    <?php include_once __DIR__ . '/../../shared/layouts/menu-movil.php'; ?>


    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">

        <!-- HERO / CARRUSEL DESTACADOS -->
        <section class="hero-section">

            <!-- Fondo -->
            <div class="hero-background">
                <img
                    src="../../assets/images/headers/biblioteca-bg.png"
                    alt="Biblioteca grandiosa"
                    class="hero-image"
                    loading="eager"
                >
                <div class="hero-overlay" aria-hidden="true"></div>
            </div>

            <!-- Contenido del hero -->
            <div class="hero-content">
                <span class="hero-label">Ver libros</span>
                <h1 class="hero-title">
                    Estantería<br>
                    <span class="hero-title-accent">Digital</span>
                </h1>
            </div>

            <!-- Carrusel Destacados con PHP Dinámico -->
            <div class="carousel-wrapper">
                <div class="carousel-fade carousel-fade--left" aria-hidden="true"></div>
                <div class="carousel-fade carousel-fade--right" aria-hidden="true"></div>

                <button class="carousel-btn carousel-btn--prev" id="carouselPrev" aria-label="Anterior" disabled>
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <button class="carousel-btn carousel-btn--next" id="carouselNext" aria-label="Siguiente">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>

                <div class="featured-carousel" id="featuredCarousel" role="list" aria-label="Libros destacados">
                    <?php foreach ($librosCarrusel as $libro): ?>
        <?php
            $libroTitulo = htmlspecialchars($libro['titulo'] ?? '', ENT_QUOTES, 'UTF-8');
            $libroCat    = htmlspecialchars($libro['materia'] ?? 'General', ENT_QUOTES, 'UTF-8');
            $libroDesc   = htmlspecialchars($libro['sinopsis'] ?? 'Sin descripción disponible.', ENT_QUOTES, 'UTF-8');
            $libroImg    = htmlspecialchars($base_url . (!empty($libro['portada']) ? $libro['portada'] : 'assets/images/books/default-cover.jpg'), ENT_QUOTES, 'UTF-8');
            $libroId     = (int) ($libro['id_libro'] ?? 0);
        ?>
        <div class="featured-card" role="listitem">
            <div class="featured-cover">
                <img src="<?php echo $libroImg; ?>" alt="<?php echo $libroTitulo; ?>" loading="lazy">
            </div>
            <div class="featured-info">
                <span class="featured-tag"><?php echo $libroCat; ?></span>
                <p class="featured-title"><?php echo $libroTitulo; ?></p>
                <p class="featured-desc"><?php echo $libroDesc; ?></p>
                <a href="../biblioteca-catalogo/vista-libro/book-view.php?id=<?php echo $libroId; ?>" class="btn-primary" style="align-self:flex-start">
                    <span class="material-symbols-outlined">auto_stories</span>
                    Ver libro
                </a>
            </div>
        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </section><!-- /hero-section -->


        <!-- FILTROS -->
        <section class="filters-section" aria-label="Filtros del catálogo">
            <?php include __DIR__ . '/../../shared/layouts/catalog-filters.php'; ?>
        </section>


        <!-- CATÁLOGO DINÁMICO DESDE MYSQL -->
        <section class="catalog-section" aria-label="Catálogo de libros">
            <div class="catalog-header">
                <div class="catalog-header-left">
                    <h2 class="catalog-title">El <span>Catálogo</span></h2>
                    <button class="btn-ver-todo">
                        Ver todo
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                </div>

            </div>

            <!-- Grid de tarjetas dinámicas -->
            <div class="book-grid" id="landing-book-grid">
                <?php if (!empty($librosCatalogo)): ?>
                    <?php foreach ($librosCatalogo as $libro): ?>
                    <article class="book-card" aria-label="<?php echo htmlspecialchars($libro['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="book-cover-wrapper">
                            <img 
                                src="<?php echo htmlspecialchars($base_url . ($libro['portada'] ?? 'assets/images/books/default-cover.jpg'), ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo htmlspecialchars($libro['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="book-cover" 
                                loading="lazy" 
                                onclick="window.location.href='../biblioteca-catalogo/vista-libro/book-view.php?id=<?php echo (int) $libro['id_libro']; ?>'"
                            >
                            <span class="book-badge"><?php echo htmlspecialchars($libro['materia'] ?? 'Literatura', ENT_QUOTES, 'UTF-8'); ?></span>
                            <div class="book-card-cta">
                                <button class="book-card-cta-btn" type="button" onclick="window.location.href='../biblioteca-catalogo/vista-libro/book-view.php?id=<?php echo (int) $libro['id_libro']; ?>">
                                    <span class="material-symbols-outlined">auto_stories</span>
                                    Ver Libro
                                </button>
                            </div>
                        </div>
                        <div class="book-info">
                            <h4 class="book-title"><?php echo htmlspecialchars($libro['titulo'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="book-author"><?php echo htmlspecialchars($libro['autores'] ?? 'Sin autor', ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php
                                $disponibles = (int) ($libro['disponibles'] ?? 0);
                                $reservados = (int) ($libro['reservados'] ?? 0);
                                $prestados = (int) ($libro['prestados'] ?? 0);
                                $estadoLibro = $disponibles > 0 ? 'Disponible' : ($reservados > 0 ? 'Reservado' : ($prestados > 0 ? 'Prestado' : 'No disponible'));
                            ?>
                            <div class="landing-book-stock" aria-label="Estado y existencias">
                                <strong><?php echo htmlspecialchars($estadoLibro, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span>Disponible: <?php echo $disponibles; ?></span>
                                <span>Reservado: <?php echo $reservados; ?></span>
                                <span>Prestado: <?php echo $prestados; ?></span>
                                <span>Stock total: <?php echo (int) ($libro['total_ejemplares'] ?? 0); ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #fff; grid-column: 1 / -1; padding: 20px;">
                        No hay libros disponibles en la base de datos.
                    </p>
                <?php endif; ?>
            </div><!-- /book-grid -->
        </section><!-- /catalog-section -->

    </main><!-- /main-content -->


    <!-- FOOTER -->
    <?php include __DIR__ . '/../../shared/layouts/footer.php'; ?>

    <!-- Scripts -->
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="/Appjoteca/shared/js/components/notifications.js"></script>
    <script src="../../shared/js/components/book-cards.js"></script>
    <script src="../../shared/js/components/catalog-filters.js"></script>
    <script src="../../shared/js/global.js"></script>
    <script src="biblioteca.js"></script>

</body>
</html>