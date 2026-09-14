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
  <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
  <title>Recuperar Acceso en AppJoteca</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,700;0,900;1,700;1,900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300..700,0..1,0&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../components/theme.css">
  <link rel="stylesheet" href="recover.css">

  <link rel="icon" type="image/png" href="../../shared/images/logo-appjoteca.png">
  <link rel="stylesheet" href="../../shared/css/components/footer.css">
</head>

<body>

  <!-- ════════════════════════════════════════════════════════
       HEADER FIJO
       ════════════════════════════════════════════════════════ -->
  <header class="recover-header" role="banner">
    <div class="recover-header-container">
      <a href="../../index.php" class="logo-link" aria-label="Ir al inicio de Appjoteca">
        <img src="../../shared/images/logo-appjoteca.png" alt="Logo Appjoteca" class="logo-img">
        <span class="logo-text">Appjoteca</span>
      </a>
      <a href="../login/login.php" class="btn-login-link">
        <span class="material-symbols-outlined" style="font-size:15px;">login</span>
        <span>Iniciar Sesión</span>
      </a>
    </div>
  </header>

  <!-- ════════════════════════════════════════════════════════
       BARRA DE REGRESO (debajo del header fijo)
       ════════════════════════════════════════════════════════ -->
  <div class="back-bar">
    <a href="../login/login.php" class="btn-back" aria-label="Volver a iniciar sesión">
      <span class="material-symbols-outlined">arrow_back</span>
      <span>Volver a iniciar sesión</span>
    </a>
  </div>

  <!-- ════════════════════════════════════════════════════════
       SHELL PRINCIPAL — Single global scroll
       ════════════════════════════════════════════════════════ -->
  <main class="recover-shell" role="main">

    <!-- ── Panel del formulario (izquierda en desktop, arriba en móvil) ── -->
    <section class="recover-form-panel" aria-label="Formulario de recuperación de acceso">
      <div class="recover-form-wrapper">

        <!-- ── Encabezado del formulario ── -->
        <header class="recover-heading">
          <h3 class="recover-title">Recuperar Acceso</h3>
          <p class="recover-subtitle">Sigue los pasos para restablecer tu llave digital.</p>
        </header>

        <!-- ── Formulario ── -->
        <form id="recover-form" class="recover-form" novalidate autocomplete="on" action="../../backend/auth/procesarecover.php" method="POST">

          
          <!-- ── PASO 1: Correo institucional ── -->
          <section class="recover-step" id="step-1" aria-label="Paso 1: Identifica tu cuenta">
            <div class="step-header">
              <span class="step-number" aria-hidden="true">01</span>
              <h4 class="step-title">Identifica tu cuenta</h4>
            </div>

            <div class="field-group">
              <label class="field-label" for="correo">Correo institucional</label>
              <div class="field-wrapper">
                <input
                  type="email"
                  id="correo"
                  name="correo"
                  class="field-input"
                  placeholder="correo@institución.edu"
                  autocomplete="email"
                  required
                >
              </div>
              <p class="field-hint">Te enviaremos un código de verificación de 6 dígitos.</p>
            </div>

            <button type="button" class="btn-step" id="btn-send-code">
              <span id="btn-send-text">Enviar Código de Verificación</span>
              <span class="btn-step-shine" aria-hidden="true"></span>
            </button>
          </section>
          <!-- /step-1 -->

          <!-- ── PASO 2: Código de verificación ── -->
          <section class="recover-step step-inactive" id="step-2" aria-label="Paso 2: Código de verificación">
            <div class="step-header">
              <span class="step-number" aria-hidden="true">02</span>
              <h4 class="step-title">Código de verificación</h4>
            </div>

            <div class="code-grid" role="group" aria-label="Ingresa el código de 6 dígitos">
              <input type="text" class="code-input" maxlength="1" inputmode="numeric" aria-label="Dígito 1" disabled>
              <input type="text" class="code-input" maxlength="1" inputmode="numeric" aria-label="Dígito 2" disabled>
              <input type="text" class="code-input" maxlength="1" inputmode="numeric" aria-label="Dígito 3" disabled>
              <input type="text" class="code-input" maxlength="1" inputmode="numeric" aria-label="Dígito 4" disabled>
              <input type="text" class="code-input" maxlength="1" inputmode="numeric" aria-label="Dígito 5" disabled>
              <input type="text" class="code-input" maxlength="1" inputmode="numeric" aria-label="Dígito 6" disabled>
            </div>
            <p class="field-hint">Ingresa el código enviado a tu correo. El paso 3 se activará solo.</p>
          </section>
          <!-- /step-2 -->

          <!-- ── PASO 3: Nueva contraseña ── -->
          <section class="recover-step step-inactive" id="step-3" aria-label="Paso 3: Nueva contraseña">
            <div class="step-header">
              <span class="step-number" aria-hidden="true">03</span>
              <h4 class="step-title">Asegura tu bóveda</h4>
            </div>

            <div class="field-group">
              <label class="field-label" for="nueva-contrasena">Nueva contraseña</label>
              <div class="field-wrapper">
                <input
                  type="password"
                  id="nueva-contrasena"
                  name="nueva_contrasena"
                  class="field-input"
                  placeholder="••••••••"
                  autocomplete="new-password"
                  disabled
                  required
                >
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="confirmar-contrasena">Confirmar contraseña</label>
              <div class="field-wrapper">
                <input
                  type="password"
                  id="confirmar-contrasena"
                  name="confirmar_contrasena"
                  class="field-input"
                  placeholder="••••••••"
                  autocomplete="new-password"
                  disabled
                  required
                >
              </div>
            </div>

            <!-- Mensaje de error -->
            <div
              id="recover-error"
              class="recover-error"
              role="alert"
              aria-live="polite"
              hidden
            >
              <span class="material-symbols-outlined" aria-hidden="true">error</span>
              <span id="recover-error-text">Las contraseñas no coinciden.</span>
            </div>

            <!-- Mensaje de éxito -->
            <div
              id="recover-success"
              class="recover-success"
              role="status"
              aria-live="polite"
              hidden
            >
              <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
              <span id="recover-success-text">Contraseña restablecida. Redirigiendo…</span>
            </div>

            <!-- Botón de submit -->
            <button type="submit" class="btn-recover" id="btn-recover" disabled>
              <span id="btn-recover-text">Restablecer Contraseña</span>
              <span class="btn-recover-shine" aria-hidden="true"></span>
            </button>
          </section>
          <!-- /step-3 -->

        </form>

        <div class="recover-login-link">
          <p>¿Ya recordaste tu contraseña? <a href="../login/login.php">Inicia sesión</a></p>
        </div>

      </div>

      <!-- Footer simplificado -->
      <footer class="recover-footer-simple" role="contentinfo">
        <p class="footer-copyright">© 2024 AppJoteca · Sistema de Biblioteca Institucional</p>
        <nav class="recover-footer-links" aria-label="Enlaces legales">
          <a href="#">Política de Privacidad</a>
          <a href="#">Términos de Uso</a>
        </nav>
      </footer>

    </section>
    <!-- /recover-form-panel -->

    <section class="recover-visual" aria-hidden="true">
      <div class="recover-visual-overlay"></div>
      <img
        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDdsp1Uns8izSni1VCVGCXqN8VO9zTrDvp3KHhrH82Z4FTn9nQIEtYrn_03sOP_MIG_GaSsv204a2bGSu9ku9eDNZ1h-z_JrITtLg51pjhUKsRTRA7nilQEBOCpTaBsIJLYB1FENDKCOoozHuNHyRDpiYEHdU_kW4ej_uaVGskdVO8LfJUE8Wpsr7Zd1PzBParhKH7ru4uRIPlFBM8CEJPr-iShoClFK3moneKaHpRCk6a9ufe9MykMpRc1cpiBRrr-jJSrgky7xkrm"
        alt="Biblioteca oscura con estanterías y luz cálida de lámpara"
        class="recover-visual-img"
        loading="eager"
      >
      <div class="recover-visual-content">
        <div>
          <h1 class="recover-brand-name">Appjoteca</h1>
        </div>
        <div>
          <h2 class="recover-visual-title">Recupera tu acceso a Appjoteca</h2>
          <p class="recover-visual-subtitle">
            El bibliotecario espera tu regreso. Verifica tu contraseña para continuar a la biblioteca institucional.
          </p>
        </div>
        <div class="recover-visual-meta" aria-hidden="true">
          <span>Acceso Seguro</span>
          <span class="recover-visual-sep"></span>
          <span>Grado Institucional</span>
        </div>
      </div>
    </section>

  </main>
 

  <script src="recover.js"></script>
</body>
</html>
