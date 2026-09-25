<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../../backend/config/auth-check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

  <title>Iniciar Sesión en AppJoteca</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,700;0,900;1,700;1,900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300..700,0..1,0&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../auth/components/theme.css">
  <link rel="stylesheet" href="login.css">

  <link rel="icon" type="image/png" href="../../shared/images/logo-appjoteca.png">
</head>

<body class="login-body">

  <!-- ════════════════════════════════════════════════════════
       HEADER (compacto, fijo)
       ════════════════════════════════════════════════════════ -->
  <header class="login-header" role="banner">
    <div class="login-header-container">
          <a href="../../index.php" class="logo-link">
    <img src="../../shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
</a>
      <a href="../signup/signup.php" class="btn-register" aria-label="Registrarse">
        <span class="material-symbols-outlined" style="font-size:15px;">person_add</span>
        <span>Registrarse</span>
      </a>
    </div>
  </header>

  <!-- ════════════════════════════════════════════════════════
       SHELL PRINCIPAL — sin scroll global
       ════════════════════════════════════════════════════════ -->
  <main class="login-shell" role="main">

    <!-- ── Panel del formulario ── -->
    <section class="login-form-panel" aria-label="Formulario de inicio de sesión">
      <div class="login-card">

        <!-- Badge de bienvenida (sustituye al stepper) -->
        <div class="welcome-badge" aria-hidden="true">
          <span class="material-symbols-outlined welcome-icon">lock_open</span>
        </div>

        <!-- Título -->
        <header class="login-heading">
          <h1 class="login-title">Bienvenido de vuelta</h1>
          <p class="login-subtitle">Ingresa tus credenciales para acceder a la biblioteca.</p>
        </header>

        <!-- Formulario -->
        <form id="login-form" class="login-form" novalidate autocomplete="on"
              action="../../backend/auth/login-send.php" method="POST">

          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

          <!-- Usuario -->
          <div class="field-group">
            <label class="field-label" for="usuario">Usuario o correo <span class="required-mark">*</span></label>
            <div class="field-wrapper">
              <span class="material-symbols-outlined field-icon">person</span>
              <input
                type="text"
                id="usuario"
                name="usuario"
                class="field-input"
                placeholder="nombre de usuario o correo"
                autocomplete="username"
                required
              >
            </div>
          </div>

          <!-- Contraseña -->
          <div class="field-group">
            <div class="field-label-row">
              <label class="field-label" for="contrasena">Contraseña <span class="required-mark">*</span></label>
              <a href="../recover/recover.php" class="field-forgot" tabindex="0">¿Olvidaste tu contraseña?</a>
            </div>
            <div class="field-wrapper field-wrapper--password">
              <span class="material-symbols-outlined field-icon">lock</span>
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

          <!-- Recordarme -->
          <label class="remember-row" for="remember">
            <input type="checkbox" id="remember" name="remember" class="remember-input">
            <span class="remember-box" aria-hidden="true">
              <span class="material-symbols-outlined remember-check">check</span>
            </span>
            <span class="remember-text">Mantener sesión iniciada</span>
          </label>

          <!-- Mensajes -->
          <div id="login-error" class="login-error" role="alert" aria-live="polite" hidden>
            <span class="material-symbols-outlined" aria-hidden="true">error</span>
            <span id="login-error-text">Credenciales incorrectas.</span>
          </div>

          <div id="login-success" class="login-success" role="status" aria-live="polite" hidden>
            <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
            <span id="login-success-text">Acceso correcto. Redirigiendo…</span>
          </div>

          <!-- Botón principal -->
          <button type="submit" class="btn-login">
            <span id="btn-login-text">Iniciar sesión</span>
            <span class="btn-login-shine" aria-hidden="true"></span>
          </button>

        </form>

        <!-- Enlace a registro -->
        <div class="login-register-link">
          <p>¿No tienes cuenta? <a href="../signup/signup.php">Crea una aquí</a></p>
        </div>

      </div>
    </section>

    <!-- ── Panel visual ── -->
    <aside class="login-visual" aria-hidden="true">
      <div class="login-visual-overlay"></div>
      <img
        src="../../assets/images/headers/auth.png"
        alt=""
        class="login-visual-img"
        loading="eager"
      >
      <div class="login-visual-content">
        <div class="login-visual-top">
          <h2 class="login-brand-name">Appjoteca</h2>
        </div>
        <div class="login-visual-mid">
          <h3 class="login-visual-title">La puerta a los<br><span class="italic">archivos</span> de la institución.</h3>
          <p class="login-visual-subtitle">
            Entra al espacio curado donde el conocimiento institucional se encuentra con la accesibilidad moderna.
          </p>
          <ul class="login-visual-features">
            <li><span class="material-symbols-outlined">bolt</span> Acceso inmediato</li>
            <li><span class="material-symbols-outlined">auto_stories</span> Catálogo completo</li>
            <li><span class="material-symbols-outlined">shield_lock</span> Sesión cifrada</li>
          </ul>
        </div>
        <div class="login-visual-meta">
          <span>Acceso institucional</span>
          <span class="login-visual-sep"></span>
          <span>Sesión segura</span>
        </div>
      </div>
    </aside>

  </main>

  <script src="login.js"></script>
  
</body>
</html>