<?php
require_once __DIR__ . '/../../../backend/config/security.php';
require_once __DIR__ . '/../../../backend/config/auth.php';
require_once __DIR__ . '/../../../backend/config/user_context.php';
require_once __DIR__ . '/../../../backend/Database/conexion.php';
require_once __DIR__ . '/../../../backend/models/libro.php';
require_once __DIR__ . '/../../../backend/models/reserva.php';
require_once __DIR__ . '/../../../backend/helpers/libros.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$base_url = '/Appjoteca/';

$id_libro = (int) ($_GET['id'] ?? 0);
if ($id_libro <= 0) {
    header('Location: /Appjoteca/pages/biblioteca-catalogo/catalogo/catalogo.php');
    exit;
}

$modeloLibro = new libro($connection);
$libro = $modeloLibro->obtenerLibro($id_libro);

if (!$libro) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Libro no encontrado</title><link rel="icon" type="image/png" href="/Appjoteca/shared/images/favicon.png" sizes="48x48"><link rel="manifest" href="/Appjoteca/manifest.json"><meta name="theme-color" content="#1a1a2e"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"><meta name="apple-mobile-web-app-title" content="AppJoteca"></head><body style="background:#000;color:#e2e2e2;font-family:sans-serif;padding:40px;text-align:center;"><h1>Libro no encontrado</h1><a href="/Appjoteca/pages/biblioteca-catalogo/catalogo/catalogo.php" style="color:#f2ca50;">Volver al catálogo</a><script src="/Appjoteca/shared/js/register-sw.js"></script></body></html>';
    exit;
}

$disp = disponibilidadLibro($libro);

$titulo    = $libro['titulo'] ?? 'Sin título';
$autores   = $libro['autores'] ?: 'Autor desconocido';
$materia   = $libro['materia'] ?? 'General';
$sinopsis  = $libro['sinopsis'] ?? 'Sin descripción disponible.';
$idioma    = $libro['nombre_idioma'] ?? 'Español';
$edicion   = $libro['edicion'] ?? '';
$anio      = $libro['publicacion_year'] ?? '';
$edicionLabel = trim(($edicion !== '' ? $edicion : 'Edición') . ($anio ? ' · ' . $anio : ''));
$portada   = urlPortada($libro['portada'] ?? '', $base_url);

$modeloReserva = new Reserva($connection);
$reservaPendiente = null;
$wishlistSaved = false;
if (!empty($_SESSION['usuario_id'])) {
    $reservaPendiente = $modeloReserva->obtenerPendienteDeUsuario((int) $_SESSION['usuario_id'], $id_libro);
    if (in_array((int) ($_SESSION['rol'] ?? 0), [1, 2, 4], true)) {
        $wishlistQuery = $connection->prepare(
            'SELECT id FROM lista_deseos WHERE id_usuario = ? AND id_libro = ? LIMIT 1'
        );
        $wishlistQuery->bind_param('ii', $_SESSION['usuario_id'], $id_libro);
        $wishlistQuery->execute();
        $wishlistSaved = (bool) $wishlistQuery->get_result()->fetch_assoc();
        $wishlistQuery->close();
    }
}

$librosRelacionados = $modeloLibro->obtenerRelacionados($id_libro, 4);

requiereRol([1, 2, 4]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?> — AppJoteca</title>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../../shared/css/theme.css">
    <link rel="stylesheet" href="../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../shared/css/components/book-card.css">
    <link rel="stylesheet" href="book-view.css">
    <link rel="stylesheet" href="../../../shared/css/components/footer.css">
    <link rel="stylesheet" href="../../../shared/css/components/related-section.css">

    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
    <?php require __DIR__ . '/../../../shared/layouts/favicon.php'; ?>
</head>
<body>

    <!-- OVERLAY GLOBAL -->
    <div id="overlay" class="overlay" aria-hidden="true"></div>
    <?php include __DIR__ . '/../../../shared/layouts/notifications.php'; ?>
    <?php include __DIR__ . '/../../../shared/layouts/menu-off-canvas.php'; ?>
    <?php include __DIR__ . '/../../../shared/layouts/topbar.php'; ?>
    <?php include_once __DIR__ . '/../../../shared/layouts/menu-movil.php'; ?>


    <!-- CONTENIDO PRINCIPAL -->
    <main class="catalogo-main" id="main-content">
        <div class="catalogo-inner">

            <!-- Botón volver -->
            <button class="back-btn" onclick="history.back()" aria-label="Volver al catálogo">
                <span class="material-symbols-outlined">arrow_back</span>
                Volver al Catálogo
            </button>


            <!-- HERO: Portada + Detalles -->
            <section class="book-hero" aria-label="Detalle del libro">

                <!-- Portada -->
                <div class="book-cover-col">
                    <div class="book-cover-glow" aria-hidden="true"></div>
                    <div class="book-cover-frame">
                        <img
                            src="<?php echo htmlspecialchars($portada, ENT_QUOTES, 'UTF-8'); ?>"
                            alt="Portada de <?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?>"
                            class="book-cover-img"
                            loading="eager"
                        >
                    </div>
                </div>

                <!-- Detalles -->
                <div class="book-details-col">

                    <!-- Estado y edición -->
                    <div class="book-meta-row">
                        <span class="availability-badge <?php echo $disp['clase']; ?>">
                            <?php echo htmlspecialchars($disp['texto'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ($disp['disponible']): ?>
                                · <?php echo (int) $disp['cantidad']; ?> en stock
                            <?php endif; ?>
                        </span>
                        <?php if ($edicionLabel): ?>
                            <span class="book-edition-label"><?php echo htmlspecialchars($edicionLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Título y autor -->
                    <div>
                        <h1 class="book-title-main"><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p class="book-author-main">Por <?php echo htmlspecialchars($autores, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>

                    <!-- Metadatos (sin calificación) -->
                    <div class="book-metadata" role="list" aria-label="Información del libro">
                        <div class="meta-item" role="listitem">
                            <span class="meta-label">Género</span>
                            <span class="meta-value"><?php echo htmlspecialchars($materia, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="meta-item" role="listitem">
                            <span class="meta-label">Idioma</span>
                            <span class="meta-value"><?php echo htmlspecialchars($idioma, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="meta-item" role="listitem">
                            <span class="meta-label">Ejemplares</span>
                            <span class="meta-value"><?php echo (int) $disp['cantidad']; ?> disponibles</span>
                        </div>
                    </div>

                    <!-- Sinopsis -->
                    <div class="synopsis-section">
                        <h2 class="synopsis-title">Sinopsis</h2>
                        <p class="synopsis-text"><?php echo nl2br(htmlspecialchars($sinopsis, ENT_QUOTES, 'UTF-8')); ?></p>
                        <button type="button" class="synopsis-toggle" aria-expanded="false" hidden>Ver más</button>
                    </div>

                    <!-- Tags -->
                    <div class="book-tags-detail" role="list" aria-label="Etiquetas">
                        <span class="book-tag-detail" role="listitem"><?php echo htmlspecialchars($materia, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($idioma)): ?>
                            <span class="book-tag-detail" role="listitem"><?php echo htmlspecialchars($idioma, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                        <span class="book-tag-detail" role="listitem">Biblioteca</span>
                    </div>

                    <!-- Acciones -->
                    <div class="book-actions">
                        <button
                            class="btn-wishlist <?= $wishlistSaved ? 'is-saved' : '' ?>"
                            id="btn-wishlist"
                            type="button"
                            data-id="<?= (int) $id_libro ?>"
                            aria-pressed="<?= $wishlistSaved ? 'true' : 'false' ?>">
                            <span class="material-symbols-outlined"><?= $wishlistSaved ? 'favorite' : 'favorite_border' ?></span>
                            <?= $wishlistSaved ? 'Quitar de mi lista' : 'Agregar a mi lista de deseos' ?>
                        </button>
                        <?php if ($reservaPendiente): ?>
                            <!-- El usuario YA tiene una reserva pendiente → botón Cancelar -->
                            <button
                                class="btn-reservar btn-cancelar"
                                id="btn-cancelar-reserva"
                                data-id-reserva="<?php echo (int) $reservaPendiente['id_reserva']; ?>"
                            >
                                <span class="material-symbols-outlined">cancel</span>
                                Cancelar solicitud
                            </button>
                        <?php elseif ($disp['disponible']): ?>
                            <button
                                class="btn-reservar"
                                id="btn-reservar"
                                data-id="<?php echo (int) $id_libro; ?>"
                            >
                                <span class="material-symbols-outlined">bookmark_add</span>
                                Reservar
                            </button>
                        <?php else: ?>
                            <button class="btn-reservar" disabled aria-disabled="true">
                                <span class="material-symbols-outlined">block</span>
                                No disponible
                            </button>
                        <?php endif; ?>
                    </div>

                </div><!-- /book-details-col -->
            </section><!-- /book-hero -->


            <!-- LIBROS RELACIONADOS (componente reutilizable) -->
            <?php if (!empty($librosRelacionados)): ?>
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
                            $relUrl    = 'book-view.php?id=' . $relId;
                        ?>
                            <article class="book-card" aria-label="<?php echo $relTitulo; ?>" tabindex="0" role="button"
                                     onclick="window.location.href='<?php echo $relUrl; ?>'">
                                <div class="book-cover-wrapper">
                                    <img src="<?php echo $relImg; ?>"
                                         alt="<?php echo $relTitulo; ?>"
                                         class="book-cover"
                                         loading="lazy">
                                    <span class="book-badge"><?php echo $relCat; ?></span>
                                    <div class="book-card-cta">
                                        <button class="book-card-cta-btn"
                                                onclick="event.stopPropagation(); window.location.href='<?php echo $relUrl; ?>'">
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
                    </div><!-- /related-grid -->
                </section>
            <?php endif; ?>

        </div><!-- /catalogo-inner -->
    </main>


    <!-- FOOTER -->
    <?php include __DIR__ . '/../../../shared/layouts/footer.php'; ?>

    <!-- Scripts -->
    <script src="../../../shared/js/components/navbar.js"></script>
    <script src="/Appjoteca/shared/js/components/notifications.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="../../../shared/js/components/alert.js"></script>
    <script src="../../../shared/js/global.js"></script>
    <script src="book-view.js"></script>

    <script src="/Appjoteca/shared/js/register-sw.js"></script>
</body>
</html>