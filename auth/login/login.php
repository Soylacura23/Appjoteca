<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión en AppJoteca</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,700;0,900;1,700;1,900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300..700,0..1,0&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../auth/components/theme.css">
  <link rel="stylesheet" href="login.css">

  <link rel="icon" type="image/png" href="../../shared/images/logo-appjoteca.png">
  <link rel="stylesheet" href="../../shared/css/components/footer.css">
</head>

<body>

  <!-- ════════════════════════════════════════════════════════
       HEADER FIJO
       ════════════════════════════════════════════════════════ -->
  <header class="login-header" role="banner">
    <div class="login-header-container">
      <a href="../../index.php" class="logo-link" aria-label="Ir al inicio de Appjoteca">
        <img src="../../shared/images/logo-appjoteca.png" alt="Logo Appjoteca" class="logo-img">
        <span class="logo-text">Appjoteca</span>
      </a>
      <a href="../signup/signup.php" class="btn-register">
        <span class="material-symbols-outlined" style="font-size:15px;">person_add</span>
        <span>Registrarse</span>
      </a>
    </div>
  </header>

  <!-- ════════════════════════════════════════════════════════
       BARRA DE REGRESO (debajo del header fijo)
       ════════════════════════════════════════════════════════ -->
  <div class="back-bar">
    <a href="../../index.php" class="btn-back" aria-label="Volver al inicio">
      <span class="material-symbols-outlined">arrow_back</span>
      <span>Volver al inicio</span>
    </a>
  </div>

  <!-- ════════════════════════════════════════════════════════
       SHELL PRINCIPAL — Single global scroll
       ════════════════════════════════════════════════════════ -->
  <main class="login-shell" role="main">

    <!-- ── Panel del formulario (izquierda en desktop, arriba en móvil) ── -->
    <section class="login-form-panel" aria-label="Formulario de inicio de sesión">
      <div class="login-form-wrapper">

        <!-- ── SELECTOR DE ROL ── -->
        <div class="role-selector" role="group" aria-label="Selecciona tu tipo de acceso">
          <p class="role-selector-label" id="role-label">¿Quién eres?</p>
          <div class="role-grid" role="radiogroup" aria-labelledby="role-label">

            <label class="role-card" title="Acceso como Estudiante">
              <input type="radio" name="rol" value="estudiante" class="role-radio" checked aria-label="Estudiante" form= "login-form">
              <div class="role-card-inner">
                <span class="material-symbols-outlined role-icon" aria-hidden="true">school</span>
                <span class="role-name">Estudiante</span>
                <span class="role-desc">Accede a tu biblioteca</span>
              </div>
            </label>

            <label class="role-card" title="Acceso como Profesor">
              <input type="radio" name="rol" value="profesor" class="role-radio" aria-label="Profesor" form="login-form">
              <div class="role-card-inner">
                <span class="material-symbols-outlined role-icon" aria-hidden="true">history_edu</span>
                <span class="role-name">Profesor</span>
                <span class="role-desc">Gestiona tus asignaturas</span>
              </div>
            </label>

            <label class="role-card" title="Acceso como Bibliotecario">
              <input type="radio" name="rol" value="bibliotecario" class="role-radio" aria-label="Bibliotecario" form="login-form">
              <div class="role-card-inner">
                <span class="material-symbols-outlined role-icon" aria-hidden="true">local_library</span>
                <span class="role-name">Bibliotecario</span>
                <span class="role-desc">Administra la biblioteca</span>
              </div>
            </label>

          </div>
        </div>
        <!-- /role-selector -->

        <!-- ── Encabezado del formulario ── -->
        <header class="login-heading">
          <h3 class="login-title">Bienvenido de Vuelta</h3>
          <p class="login-subtitle">Ingresa tus credenciales para acceder a la biblioteca.</p>
        </header>

        <!-- ── Formulario ── -->
        <form id="login-form" class="login-form" novalidate autocomplete="on" action="../../backend/auth/login-send.php" method="POST">

          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          <!-- Campo: Usuario -->
          <div class="field-group">
            <label class="field-label" for="usuario">Nombre único</label>
            <div class="field-wrapper">
              <input
                type="text"
                id="usuario"
                name="usuario"
                class="field-input"
                placeholder="nombre de usuario"
                autocomplete="username"
                required
              >
            </div>
          </div>

          <!-- Campo: Contraseña -->
          <div class="field-group">
            <div class="field-label-row">
              <label class="field-label" for="contrasena">Contraseña</label>
              <a href="../recover/recover.php" class="field-forgot" tabindex="0">¿Olvidaste tu contraseña?</a>
            </div>
            <div class="field-wrapper field-wrapper--password">
              <input
                type="password"
                id="contrasena"
                name="contrasena"
                class="field-input"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              >
              <button
                type="button"
                class="field-toggle-pass"
                id="toggle-pass"
                aria-label="Mostrar u ocultar contraseña"
              >
                <span class="material-symbols-outlined" id="toggle-pass-icon">visibility</span>
              </button>
            </div>
          </div>

          <!-- Mensaje de error -->
          <div
            id="login-error"
            class="login-error"
            role="alert"
            aria-live="polite"
            hidden
          >
            <span class="material-symbols-outlined" aria-hidden="true">error</span>
            <span id="login-error-text">Credenciales incorrectas.</span>
          </div>

          <!-- Mensaje de éxito (para futuras integraciones) -->
          <div
            id="login-success"
            class="login-success"
            role="status"
            aria-live="polite"
            hidden
          >
            <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
            <span id="login-success-text">Acceso correcto. Redirigiendo…</span>
          </div>

          <!-- Botón de submit -->
          <button type="submit" class="btn-login">
            <span id="btn-login-text">Autenticar Acceso</span>
            <span class="btn-login-shine" aria-hidden="true"></span>
          </button>

        </form>
        <!-- /login-form -->

        <!-- Enlace de registro -->
        <div class="login-register-link">
          <p>¿No tienes cuenta institucional? <a href="../signup/signup.php">Crea una</a></p>
        </div>

      </div>
      <!-- /login-form-wrapper -->

      <!-- Footer simplificado -->
      <footer class="login-footer-simple" role="contentinfo">
        <p class="footer-copyright">© 2024 AppJoteca · Sistema de Biblioteca Institucional</p>
        <nav class="login-footer-links" aria-label="Enlaces legales">
          <a href="#">Política de Privacidad</a>
          <a href="#">Términos de Uso</a>
        </nav>
      </footer>

    </section>
    <!-- /login-form-panel -->

    <!-- ── Panel visual (imagen de biblioteca) — oculto en móvil, visible en desktop (DERECHA, STICKY) ── -->
    <section class="login-visual" aria-hidden="true">
      <div class="login-visual-overlay"></div>
      <img
        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDh90-d0HDfPWjO0zWZSjQ37GedrEaIhx7jonR6q6mbV7J8HIq_-ZVZtBVC630JozQkh5qqcwdxpcoek36JrW2hs-rGgvXudQRN4T3cToBlyemRpSTd5ZNcxDn7Sr7nX6qD6oZGxXi3uEFa3dd9wgceURUteG5XPQiC93aQ1i4vwsN8kBvshVOYsz6ult6OvyPPe9YaUsVDzmEBplHHrVbv3av82bb-VHpDcvEaO7j3CNba79tbCVsbxYakDTBaqdUknb1XKqaBRtrw"
        alt="Biblioteca clásica con estanterías de madera y luz cálida dorada"
        class="login-visual-img"
        loading="eager"
      >
      <div class="login-visual-content">
        <div>
          <h1 class="login-brand-name">Appjoteca</h1>
        </div>
        <div>
          <h2 class="login-visual-title">La puerta a los archivos de la institución.</h2>
          <p class="login-visual-subtitle">
            Entra al espacio curado donde siglos de conocimiento se encuentran con la accesibilidad moderna. Tu legado digital comienza aquí.
          </p>
        </div>
        <div class="login-visual-meta" aria-hidden="true">
          <span>Experiencia Curada</span>
          <span class="login-visual-sep"></span>
          <span>Grado Institucional</span>
        </div>
      </div>
    </section>

  </main>
  <!-- /login-shell -->

  <script src="login.js"></script>
</body>
</html>