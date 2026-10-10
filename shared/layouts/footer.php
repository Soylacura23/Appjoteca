<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$footer_role = isset($_SESSION['usuario_id']) ? (int) ($_SESSION['rol'] ?? 0) : 0;
$footer_base = '/Appjoteca/';
$footer_catalog = $footer_role === 3
    ? $footer_base . 'dashboards/bibliotecario/pages/inventario/inventario.php'
    : $footer_base . 'pages/biblioteca-digital/index.php';
$footer_history_roles = [1, 2];
$footer_logged_in = $footer_role > 0;
$footer_login = $footer_base . 'auth/login/login.php';
?>

  <footer class="footer" role="contentinfo">
    <div class="footer-inner">
      <div class="footer-brand">
        <span class="footer-logo">AppJoteca</span>
        <p class="footer-tagline">
          Punto de acceso institucional para fomentar la lectura en los estudiantes de la institución.
        </p>
        <div class="footer-social">
          <a class="footer-social-btn" href="https://www.iemanueljbetancur.edu.co" target="_blank" rel="noopener noreferrer" aria-label="Sitio web de la institución">
            <span class="material-symbols-outlined" aria-hidden="true">language</span>
          </a>
          <button class="footer-social-btn" type="button" data-footer-share aria-label="Compartir AppJoteca">
            <span class="material-symbols-outlined" aria-hidden="true">share</span>
          </button>
          <a class="footer-social-btn" href="mailto:appjotecainformation@gmail.com?subject=Contacto%20desde%20AppJoteca" aria-label="Enviar correo a AppJoteca">
            <span class="material-symbols-outlined" aria-hidden="true">mail</span>
          </a>
        </div>
      </div>

      <div class="footer-nav-cols">
        <div class="footer-col">
          <p class="footer-col-title">Explorar</p>
          <nav aria-label="Explorar">
            <a href="<?= htmlspecialchars($footer_logged_in ? $footer_catalog : $footer_login . '?return_to=catalog', ENT_QUOTES, 'UTF-8') ?>">El Catálogo</a>
            <?php if (!$footer_logged_in || in_array($footer_role, $footer_history_roles, true)): ?>
              <a href="<?= htmlspecialchars($footer_logged_in ? $footer_base . 'pages/history/historial.php' : $footer_login . '?return_to=history', ENT_QUOTES, 'UTF-8') ?>">Mi Biblioteca</a>
            <?php endif; ?>
          </nav>
        </div>
        <div class="footer-col">
          <p class="footer-col-title">Sistema</p>
          <nav aria-label="Sistema">
            <a href="<?= $footer_base ?>pages/legal/terminos.php">Términos de Uso</a>
            <a href="<?= $footer_base ?>pages/legal/privacidad.php">Privacidad</a>
            <a href="<?= $footer_base ?>pages/sobre-nosotros/about-us.php#contacto">Soporte</a>
            <a href="<?= $footer_base ?>pages/manual/manual.php">Manual de usuario</a>
          </nav>
        </div>
        <div class="footer-col">
          <p class="footer-col-title">Acceso</p>
          <nav aria-label="Acceso">
            <a href="<?= htmlspecialchars($footer_login, ENT_QUOTES, 'UTF-8') ?>">Acceso Institucional</a>
            <?php if ($footer_role === 4): ?>
              <a href="<?= $footer_base ?>dashboards/Administrador/index.php">Panel Administrativo</a>
            <?php endif; ?>
            <a href="<?= $footer_base ?>pages/sobre-nosotros/about-us.php#contacto">Contacto</a>
          </nav>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p class="footer-copyright">
        &copy; <?= date('Y') ?> AppJoteca &nbsp;·&nbsp; Sistema de Biblioteca Institucional
      </p>
    </div>
    <div class="footer-toast" data-footer-toast role="status" aria-live="polite" aria-atomic="true"></div>
  </footer>

  <script>
    (() => {
      const shareButton = document.querySelector('[data-footer-share]');
      const toast = document.querySelector('[data-footer-toast]');
      let toastTimeout;

      if (!shareButton || !toast) return;

      const showToast = (message) => {
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.clearTimeout(toastTimeout);
        toastTimeout = window.setTimeout(() => toast.classList.remove('is-visible'), 2600);
      };

      const copyCurrentUrl = async () => {
        try {
          if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(window.location.href);
          } else {
            const input = document.createElement('textarea');
            input.value = window.location.href;
            input.setAttribute('readonly', '');
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            let copied = false;
            try {
              input.select();
              copied = document.execCommand('copy');
            } finally {
              input.remove();
            }
            if (!copied) throw new Error('El navegador no permitió copiar el enlace.');
          }
          showToast('¡Enlace copiado!');
        } catch (error) {
          console.error('No se pudo copiar el enlace:', error);
          showToast('No se pudo copiar el enlace.');
        }
      };

      shareButton.addEventListener('click', async () => {
        if (!navigator.share) {
          await copyCurrentUrl();
          return;
        }

        try {
          await navigator.share({
            title: 'AppJoteca',
            text: 'Conoce AppJoteca, la biblioteca institucional.',
            url: window.location.href
          });
        } catch (error) {
          if (error?.name === 'AbortError') return;
          console.error('No se pudo abrir el menú para compartir:', error);
          await copyCurrentUrl();
        }
      });
    })();
  </script>
