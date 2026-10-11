<div class="menu-off-canva" role="dialog" aria-modal="true" aria-label="Menú de perfil">
    <button class="material-symbols-outlined arrow-back" aria-label="Cerrar menú de perfil">arrow_back_ios</button>
    <div id="profile-button-menu"></div>
    <div class="menu-off-canva-divider"></div>
    <div class="menu-buttons">
        <button class="config" type="button">
            <span class="material-symbols-outlined">settings</span>
            Configuración
        </button>
        <form method="POST" action="/Appjoteca/backend/auth/logout.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <button class="signout" type="submit">
                <span class="material-symbols-outlined">logout</span>
                Cerrar Sesión
            </button>
        </form>
    </div>
</div>