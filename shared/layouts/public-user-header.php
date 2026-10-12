<?php if (!empty($publicUserRole)): ?>
    <div id="overlay" class="overlay" aria-hidden="true"></div>
    <?php include __DIR__ . '/notifications.php'; ?>
    <?php include __DIR__ . '/menu-off-canvas.php'; ?>
    <?php require_once __DIR__ . '/../../backend/config/user_context.php'; ?>
    <?php include __DIR__ . '/topbar.php'; ?>
    <?php include_once __DIR__ . '/menu-movil.php'; ?>
<?php endif; ?>
