<?php
require_once "../../backend/config/auth.php";
require_once "../../backend/config/user_context.php";
require_once "../../backend/Database/conexion.php";

// Consulta de libros
$resultado_libros = false;
if (isset($connection) && $connection) {
    $sql = "SELECT * FROM libros";
    $resultado_libros = mysqli_query($connection, $sql);
}
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
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
</head>
<body>

    <!-- ══════════════════════════════════════════
         OVERLAY GLOBAL (perfil + notificaciones)
    ══════════════════════════════════════════════ -->
    <div id="overlay" class="overlay" aria-hidden="true"></div>


    <?php include __DIR__ . '/../../shared/layouts/notifications.php'; ?>
    <?php include __DIR__ . '/../../shared/layouts/menu-off-canvas.php'; ?>

    <?php include __DIR__ . '/../../shared/layouts/topbar.php'; ?>

    <?php include __DIR__ . '/../../shared/layouts/menu-movil.php'; ?>


    <!-- ══════════════════════════════════════════
         CONTENIDO PRINCIPAL
    ══════════════════════════════════════════════ -->
    <main class="main-content">

        <!-- ──────────────────────────────────────
             HERO / CARRUSEL DESTACADOS
        ─────────────────────────────────────────── -->
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
                    <?php if ($resultado_libros && mysqli_num_rows($resultado_libros) > 0): ?>
                        <?php while ($libro = mysqli_fetch_assoc($resultado_libros)): ?>
                            <div class="featured-card" role="listitem">
                                <div class="featured-cover">
                                    <img src="<?php echo htmlspecialchars($libro['imagen'] ?? '../../assets/images/books/cien-años-de-soledad.jpg'); ?>" alt="<?php echo htmlspecialchars($libro['titulo']); ?>" loading="lazy">
                                </div>
                                <div class="featured-info">
                                    <span class="featured-tag"><?php echo htmlspecialchars($libro['categoria'] ?? 'General'); ?></span>
                                    <p class="featured-title"><?php echo htmlspecialchars($libro['titulo']); ?></p>
                                    <p class="featured-desc"><?php echo htmlspecialchars($libro['descripcion'] ?? 'Sin descripción disponible.'); ?></p>
                                    <a href="../biblioteca-catalogo/vista-libro/book-view.php?id=<?php echo $libro['id']; ?>" class="btn-primary" style="align-self:flex-start">
                                        <span class="material-symbols-outlined">auto_stories</span>
                                        Ver libro
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

        </section><!-- /hero-section -->


        <!-- ──────────────────────────────────────
             FILTROS
        ─────────────────────────────────────────── -->
        <section class="filters-section" aria-label="Filtros del catálogo">
            <div class="filters-container">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label" for="filterCategory">Categoría</label>
                        <select class="filter-select" id="filterCategory">
                            <option value="">Todas las categorías</option>
                            <option value="literatura">Literatura</option>
                            <option value="ciencia">Ciencia</option>
                            <option value="filosofia">Filosofía</option>
                            <option value="historia">Historia</option>
                            <option value="arquitectura">Arquitectura</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="filterAvailability">Disponibilidad</label>
                        <select class="filter-select" id="filterAvailability">
                            <option value="">Cualquier estado</option>
                            <option value="disponible">Disponible</option>
                            <option value="prestado">En préstamo</option>
                            <option value="reservado">Reservado</option>
                        </select>
                    </div>
                </div>
                <div class="filters-actions">
                    <button class="btn-filter">
                        <span class="material-symbols-outlined">filter_list</span>
                        Aplicar
                    </button>
                </div>
            </div>
        </section>


        <!-- ──────────────────────────────────────
             CATÁLOGO DINÁMICO DESDE MYSQL
        ─────────────────────────────────────────── -->
        <section class="catalog-section" aria-label="Catálogo de libros">
            <div class="catalog-header">
                <div class="catalog-header-left">
                    <h2 class="catalog-title">El <span>Catálogo</span></h2>
                    <button class="btn-ver-todo">
                        Ver todo
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                </div>

                <div class="catalog-tabs" role="tablist" aria-label="Ordenar por">
                    <button class="tab-btn active" role="tab" aria-selected="true">Más recomendados</button>
                    <button class="tab-btn" role="tab" aria-selected="false">Agregados Recientemente</button>
                    <button class="tab-btn" role="tab" aria-selected="false">Más Prestados</button>
                </div>
            </div>

            <!-- Grid de tarjetas dinámicas -->
            <div class="book-grid">
                <?php 
                if ($resultado_libros) {
                    mysqli_data_seek($resultado_libros, 0); // Reiniciar el puntero para leer los datos de nuevo
                }
                
                if ($resultado_libros && mysqli_num_rows($resultado_libros) > 0): 
                    while ($libro = mysqli_fetch_assoc($resultado_libros)): 
                ?>
                    <article class="book-card" aria-label="<?php echo htmlspecialchars($libro['titulo']); ?>">
                        <div class="book-cover-wrapper">
                            <img 
                                src="<?php echo htmlspecialchars($libro['imagen'] ?? '../../assets/images/books/cien-años-de-soledad.jpg'); ?>" 
                                alt="<?php echo htmlspecialchars($libro['titulo']); ?>" 
                                class="book-cover" 
                                loading="lazy" 
                                onclick="window.location.href='../biblioteca-catalogo/vista-libro/book-view.php?id=<?php echo $libro['id']; ?>'"
                            >
                            <span class="book-badge"><?php echo htmlspecialchars($libro['categoria'] ?? 'Literatura'); ?></span>
                            <div class="book-card-cta">
                                <button class="book-card-cta-btn" onclick="window.location.href='../biblioteca-catalogo/vista-libro/book-view.php?id=<?php echo $libro['id']; ?>'">
                                    <span class="material-symbols-outlined">auto_stories</span>
                                    Ver Libro
                                </button>
                            </div>
                        </div>
                        <div class="book-info">
                            <h4 class="book-title"><?php echo htmlspecialchars($libro['titulo']); ?></h4>
                            <p class="book-author"><?php echo htmlspecialchars($libro['autor']); ?></p>
                            <?php if (!empty($libro['etiqueta'])): ?>
                                <div class="book-tags">
                                    <span class="book-tag"><?php echo htmlspecialchars($libro['etiqueta']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <p style="color: #fff; grid-column: 1 / -1; padding: 20px;">
                        No hay libros disponibles en la base de datos.
                    </p>
                <?php endif; ?>
            </div><!-- /book-grid -->
        </section><!-- /catalog-section -->

    </main><!-- /main-content -->


    <!-- ══════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════ -->
    <?php include __DIR__ . '/../../shared/layouts/footer.php'; ?>

    <!-- Scripts -->
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="/Appjoteca/shared/js/components/notifications.js"></script>
    <script src="../../shared/js/components/book-cards.js"></script>
    <script src="../../shared/js/global.js"></script>
    <script src="biblioteca.js"></script>

</body>
</html>