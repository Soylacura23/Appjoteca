<?php
$readingUserId = (int) ($_SESSION['usuario_id'] ?? 0);
$readingGoalsQuery = $connection->prepare(
    'SELECT titulo, cantidad_objetivo, cantidad_actual, fecha_limite, completada
     FROM metas WHERE id_usuario = ? ORDER BY completada ASC, fecha_creacion DESC LIMIT 3'
);
$readingGoalsQuery->bind_param('i', $readingUserId);
$readingGoalsQuery->execute();
$readingGoalsResult = $readingGoalsQuery->get_result();
$readingGoals = [];
while ($goal = $readingGoalsResult->fetch_assoc()) $readingGoals[] = $goal;
$readingGoalsQuery->close();

$readingBadgesQuery = $connection->prepare(
    'SELECT i.nombre, i.descripcion, i.icono
     FROM usuario_insignias ui JOIN insignias i ON i.id = ui.id_insignia
     WHERE ui.id_usuario = ? ORDER BY ui.fecha_obtencion DESC LIMIT 4'
);
$readingBadgesQuery->bind_param('i', $readingUserId);
$readingBadgesQuery->execute();
$readingBadgesResult = $readingBadgesQuery->get_result();
$readingBadges = [];
while ($badge = $readingBadgesResult->fetch_assoc()) $readingBadges[] = $badge;
$readingBadgesQuery->close();

$readingWishlistQuery = $connection->prepare(
    'SELECT l.id_libro, l.titulo, l.portada
     FROM lista_deseos w JOIN libros l ON l.id_libro = w.id_libro
     WHERE w.id_usuario = ? AND l.fecha_eliminacion_libro IS NULL
     ORDER BY w.fecha_agregado DESC LIMIT 3'
);
$readingWishlistQuery->bind_param('i', $readingUserId);
$readingWishlistQuery->execute();
$readingWishlistResult = $readingWishlistQuery->get_result();
$readingWishlist = [];
while ($book = $readingWishlistResult->fetch_assoc()) $readingWishlist[] = $book;
$readingWishlistQuery->close();
?>
<section class="reading-summary" aria-label="Tus metas, insignias y libros deseados">
    <div class="reading-summary-card">
        <div class="reading-summary-heading">
            <span class="material-symbols-outlined">flag</span>
            <h2>Metas de lectura</h2>
        </div>
        <?php if ($readingGoals): ?>
            <?php foreach ($readingGoals as $goal):
                $target = max(1, (int) $goal['cantidad_objetivo']);
                $current = min($target, (int) $goal['cantidad_actual']);
                $percent = (int) round($current / $target * 100);
            ?>
                <div class="reading-goal-item">
                    <div><strong><?= htmlspecialchars($goal['titulo'], ENT_QUOTES, 'UTF-8') ?></strong><span><?= $current ?> / <?= $target ?></span></div>
                    <progress value="<?= $current ?>" max="<?= $target ?>" aria-label="<?= htmlspecialchars($goal['titulo'], ENT_QUOTES, 'UTF-8') ?>"></progress>
                    <small><?= $percent ?>% completado</small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aún no tienes metas creadas.</p>
        <?php endif; ?>
        <form class="reading-goal-form" data-reading-goal-form>
            <label>Crear meta
                <input name="titulo" maxlength="120" placeholder="Ej. Leer 5 libros este periodo" required>
            </label>
            <div>
                <label>Libros<input name="cantidad_objetivo" type="number" min="1" max="500" required></label>
                <label>Fecha límite<input name="fecha_limite" type="date"></label>
            </div>
            <button type="submit">Añadir meta</button>
            <p data-goal-message role="status"></p>
        </form>
        <a class="reading-summary-link" href="/Appjoteca/pages/settings/configuracion.php#lectura">Gestionar metas</a>
    </div>

    <div class="reading-summary-card">
        <div class="reading-summary-heading">
            <span class="material-symbols-outlined">military_tech</span>
            <h2>Insignias</h2>
        </div>
        <?php if ($readingBadges): ?>
            <div class="reading-badges">
                <?php foreach ($readingBadges as $badge): ?>
                    <span title="<?= htmlspecialchars($badge['descripcion'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="material-symbols-outlined"><?= htmlspecialchars($badge['icono'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?= htmlspecialchars($badge['nombre'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Al devolver libros comenzarás a desbloquear insignias.</p>
        <?php endif; ?>
        <a class="reading-summary-link" href="/Appjoteca/pages/settings/configuracion.php#lectura">Ver mis logros</a>
    </div>

    <div class="reading-summary-card">
        <div class="reading-summary-heading">
            <span class="material-symbols-outlined">favorite</span>
            <h2>Quiero leer</h2>
        </div>
        <?php if ($readingWishlist): ?>
            <ul class="reading-wishlist">
                <?php foreach ($readingWishlist as $book): ?>
                    <li><a href="/Appjoteca/pages/biblioteca-catalogo/vista-libro/book-view.php?id=<?= (int) $book['id_libro'] ?>"><?= htmlspecialchars($book['titulo'], ENT_QUOTES, 'UTF-8') ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Guarda aquí los libros que quieres leer más adelante.</p>
        <?php endif; ?>
        <a class="reading-summary-link" href="/Appjoteca/pages/history/historial.php?section=lista-deseos">Ver lista completa</a>
    </div>
</section>
