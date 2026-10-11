<?php
require_once __DIR__ . '/../../backend/config/security.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<div id="app-csrf-token" hidden data-token="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="notification-container" id="notification-container" role="dialog" aria-label="Notificaciones" aria-hidden="true">
    <div class="notification-header">
        <h3 class="notification-header-title">
            Notificaciones
            <span class="count-pill" id="notif-count-pill" hidden>0</span>
        </h3>
        <button type="button" class="notification-mark-all" id="mark-all-read">Marcar leídas</button>
    </div>

    <div class="notification-list" id="notification-list">
        
    </div>

    <div class="notification-footer">
        <button type="button" class="notification-see-all" id="notif-see-all">Ver más</button>
    </div>
</div>