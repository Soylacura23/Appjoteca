<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';
requiereRol([2]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <title>Mi Tablero | AppJoteca</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Material+Symbols+Outlined">
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="docente.css">
    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <?php require __DIR__ . '/../../shared/layouts/favicon.php'; ?>
</head>
<body>
    <div id="overlay" class="overlay" aria-hidden="true"></div>
    <?php include __DIR__ . '/../../shared/layouts/notifications.php'; ?>
    <?php include __DIR__ . '/../../shared/layouts/menu-off-canvas.php'; ?>
    <?php include __DIR__ . '/../../shared/layouts/topbar.php'; ?>
    <?php include_once __DIR__ . '/../../shared/layouts/menu-movil.php'; ?>

    <main class="teacher-board">
        <section class="teacher-board-heading">
            <div>
                <span class="section-label">Espacio personal</span>
                <h1>Mi Tablero</h1>
                <p>Notas privadas y recordatorios para organizar tus clases.</p>
            </div>
            <button class="teacher-primary-button" id="new-note-button" type="button">
                <span class="material-symbols-outlined">add</span> Nueva nota
            </button>
        </section>

        <form class="teacher-note-form" id="note-form" hidden>
            <input type="hidden" name="id" value="">
            <label>Título<input name="titulo" maxlength="120" required></label>
            <label>Nota<textarea name="contenido" rows="5" maxlength="10000" required></textarea></label>
            <div class="teacher-note-options">
                <label>Recordatorio<input type="date" name="fecha_recordatorio"></label>
                <label class="teacher-important-toggle"><input type="checkbox" name="es_importante" value="1"> Fijar como importante</label>
            </div>
            <div class="teacher-form-actions">
                <button class="teacher-primary-button" type="submit">Guardar nota</button>
                <button class="teacher-secondary-button" id="cancel-note-button" type="button">Cancelar</button>
            </div>
            <p class="teacher-form-message" id="note-form-message" role="status"></p>
        </form>

        <section class="teacher-note-grid" id="teacher-note-list" aria-live="polite">
            <p class="teacher-empty">Cargando tus notas…</p>
        </section>
    </main>

    <?php include __DIR__ . '/../../shared/layouts/footer.php'; ?>
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="/Appjoteca/shared/js/components/notifications.js"></script>
    <script src="../../shared/js/global.js"></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
    <script src="tablero.js" defer></script>
</body>
</html>
