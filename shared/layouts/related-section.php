<?php
require_once __DIR__ . '/../../backend/helpers/libros.php';
$librosRelacionados = $librosRelacionados ?? [];
$base_url = $base_url ?? '/Appjoteca/';

if (empty($librosRelacionados)) {
    return;
}
?>
<section class="related-section" aria-label="Libros relacionados">
    <div class="related-header">
        <div class="related-header-text">
            <h2 class="related-title">Libros Relacionados</h2>
            <p class="related-subtitle">Seleccionados a partir de tu descubrimiento reciente.</p>
        </div>
        <button class="related-view-all"
                onclick="window.location.href='<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>pages/biblioteca-catalogo/catalogo/catalogo.php'">
            Ver todos
        </button>
    </div>

    <div class="related-grid">
        <?php foreach ($librosRelacionados as $rel):
            $relTitulo = htmlspecialchars($rel['titulo'] ?? '', ENT_QUOTES, 'UTF-8');
            $relAutor  = htmlspecialchars($rel['autores'] ?? 'Autor desconocido', ENT_QUOTES, 'UTF-8');
            $relCat    = htmlspecialchars($rel['materia'] ?? 'General', ENT_QUOTES, 'UTF-8');
            $relImg    = htmlspecialchars(urlPortada($rel['portada'] ?? '', $base_url), ENT_QUOTES, 'UTF-8');
            $relId     = (int) ($rel['id_libro'] ?? 0);
            $relUrl    = 'pages/biblioteca-catalogo/vista-libro/book-view.php?id=' . $relId;
        ?>
            <article class="book-card" aria-label="<?php echo $relTitulo; ?>" tabindex="0" role="button"
                     onclick="window.location.href='<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . $relUrl; ?>'">
                <div class="book-cover-wrapper">
                    <img src="<?php echo $relImg; ?>" alt="<?php echo $relTitulo; ?>" class="book-cover" loading="lazy">
                    <span class="book-badge"><?php echo $relCat; ?></span>
                    <div class="book-card-cta">
                        <button class="book-card-cta-btn"
                                onclick="event.stopPropagation(); window.location.href='<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . $relUrl; ?>'">
                            <span class="material-symbols-outlined">auto_stories</span>
                            Ver Libro
                        </button>
                    </div>
                </div>
                <div class="book-info">
                    <h4 class="book-title"><?php echo $relTitulo; ?></h4>
                    <p class="book-author"><?php echo $relAutor; ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>