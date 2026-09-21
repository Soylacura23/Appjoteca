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

  <title>Registro en AppJoteca</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,700;0,900;1,700;1,900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300..700,0..1,0&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../components/theme.css">
  <link rel="stylesheet" href="signup.css">

  <link rel="icon" type="image/png" href="../../shared/images/logo-appjoteca.png">
</head>

<body class="signup-body">

  <header class="signup-header" role="banner">
    <div class="signup-header-container">
      <a href="../../index.php" class="logo-link" aria-label="Ir al inicio de Appjoteca">
        <img src="../../shared/images/logo-appjoteca.png" alt="Logo Appjoteca" class="logo-img">
        <span class="logo-text">Appjoteca</span>
      </a>
      <a href="../../auth/login/login.php" class="btn-login-link" aria-label="Iniciar sesión">
        <span class="material-symbols-outlined" style="font-size:15px;">login</span>
        <span>Iniciar sesión</span>
      </a>
    </div>
  </header>

  <main class="signup-shell" role="main">

    <!-- ══ PANEL FORMULARIO ══ -->
    <section class="signup-form-panel" aria-label="Formulario de registro">
      <div class="signup-card">

        <!-- Stepper -->
        <div class="stepper" aria-label="Progreso del registro">
          <div class="stepper-track" aria-hidden="true">
            <div class="stepper-progress" id="stepper-progress"></div>
          </div>
          <div class="stepper-steps">
            <div class="step is-active" data-step="1">
              <div class="step-bullet">
                <span class="material-symbols-outlined step-icon">badge</span>
                <span class="step-num">1</span>
              </div>
              <span class="step-label">Identidad</span>
            </div>
            <div class="step" data-step="2">
              <div class="step-bullet">
                <span class="material-symbols-outlined step-icon">key</span>
                <span class="step-num">2</span>
              </div>
              <span class="step-label">Acceso</span>
            </div>
          </div>
        </div>

        <!-- Título -->
        <header class="signup-heading">
          <h1 class="signup-title" id="signup-title">Datos de identidad</h1>
          <p class="signup-subtitle" id="signup-subtitle">Cuéntanos quién eres para verificar tu cuenta institucional.</p>
        </header>

        <form id="signup-form" class="signup-form" novalidate autocomplete="on"
              action="../../backend/auth/register-send.php" method="POST" enctype="multipart/form-data">

          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

          <!-- ══ PASO 1 ══ -->
          <fieldset class="step-panel is-active" data-step="1">

            <div class="field-group">
              <label class="field-label" for="nombre">Nombres y apellidos <span class="required-mark">*</span></label>
              <div class="field-wrapper">
                <span class="material-symbols-outlined field-icon">person</span>
                <input type="text" id="nombre" name="nombre" class="field-input"
                       placeholder="Juan Pérez García" autocomplete="name" required>
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="cedula">Tarjeta de identidad / Cédula <span class="required-mark">*</span></label>
              <div class="field-wrapper">
                <span class="material-symbols-outlined field-icon">fingerprint</span>
                <input type="text" id="cedula" name="cedula" class="field-input"
                       placeholder="Ej: 1-1234-5678 o 123456789" autocomplete="off"
                       required maxlength="20">
              </div>
            </div>

            <div class="field-group">
              <label class="field-label">Documento adjunto <span class="required-mark">*</span></label>
              <div class="file-upload-wrapper">
                <input type="file" id="cedula-file" name="cedula_file" class="file-input"
                       accept=".pdf, image/png, image/jpeg, image/webp" required
                       aria-label="Subir documento de identidad">
                <label for="cedula-file" class="file-upload-label">
                  <span class="material-symbols-outlined file-upload-icon">cloud_upload</span>
                  <div class="file-upload-text">
                    <strong>Suelta el archivo o búscalo</strong>
                    <span>PDF, PNG, JPG o WEBP · Máx. 10MB</span>
                  </div>
                </label>
                <div id="cedula-preview" class="file-preview" hidden></div>
              </div>
            </div>

          </fieldset>

          <!-- ══ PASO 2 ══ -->
          <fieldset class="step-panel" data-step="2">

            <div class="role-selector" role="group" aria-labelledby="role-label">
              <p class="role-selector-label" id="role-label">Tipo de acceso</p>
              <div class="role-grid" role="radiogroup" aria-labelledby="role-label">

                <label class="role-card" title="Estudiante">
                  <input type="radio" name="rol" value="estudiante" class="role-radio"
                         checked aria-label="Estudiante" form="signup-form">
                  <div class="role-card-inner">
                    <span class="material-symbols-outlined role-icon">school</span>
                    <span class="role-name">Estudiante</span>
                  </div>
                </label>

                <label class="role-card" title="Profesor">
                  <input type="radio" name="rol" value="profesor" class="role-radio"
                         aria-label="Profesor" form="signup-form">
                  <div class="role-card-inner">
                    <span class="material-symbols-outlined role-icon">history_edu</span>
                    <span class="role-name">Profesor</span>
                  </div>
                </label>

                <label class="role-card" title="Bibliotecario">
                  <input type="radio" name="rol" value="bibliotecario" class="role-radio"
                         aria-label="Bibliotecario" form="signup-form">
                  <div class="role-card-inner">
                    <span class="material-symbols-outlined role-icon">local_library</span>
                    <span class="role-name">Bibliotecario</span>
                  </div>
                </label>

              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="usuario">Nombre de usuario <span class="required-mark">*</span></label>
              <div class="field-wrapper">
                <span class="material-symbols-outlined field-icon">alternate_email</span>
                <input type="text" id="usuario" name="usuario" class="field-input"
                       placeholder="juan.perez" autocomplete="username"
                       required minlength="3" maxlength="30" pattern="[a-zA-Z0-9._\-]+"">
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="email">Correo institucional <span class="required-mark">*</span></label>
              <div class="field-wrapper">
                <span class="material-symbols-outlined field-icon">mail</span>
                <input type="email" id="email" name="email" class="field-input"
                       placeholder="juan.perez@institucion.edu" autocomplete="email" required>
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="contrasena">Contraseña <span class="required-mark">*</span></label>
              <div class="field-wrapper field-wrapper--password">
                <span class="material-symbols-outlined field-icon">lock</span>
                <input type="password" id="contrasena" name="contrasena" class="field-input"
                       placeholder="••••••••" autocomplete="new-password" required minlength="8">
                <button type="button" class="field-toggle-pass" id="toggle-pass" aria-label="Mostrar u ocultar contraseña">
                  <span class="material-symbols-outlined" id="toggle-pass-icon">visibility</span>
                </button>
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="contrasena2">Confirmar contraseña <span class="required-mark">*</span></label>
              <div class="field-wrapper field-wrapper--password">
                <span class="material-symbols-outlined field-icon">lock_reset</span>
                <input type="password" id="contrasena2" name="contrasena2" class="field-input"
                       placeholder="••••••••" autocomplete="new-password" required>
                <button type="button" class="field-toggle-pass" id="toggle-pass2" aria-label="Mostrar u ocultar contraseña">
                  <span class="material-symbols-outlined" id="toggle-pass2-icon">visibility</span>
                </button>
              </div>
            </div>

          </fieldset>

          <!-- Mensajes -->
          <div id="signup-error" class="signup-error" role="alert" aria-live="polite" hidden>
            <span class="material-symbols-outlined" aria-hidden="true">error</span>
            <span id="signup-error-text">Error en el registro.</span>
          </div>

          <div id="signup-success" class="signup-success" role="status" aria-live="polite" hidden>
            <span class="material-symbols-outlined" aria-hidden="true">hourglass_top</span>
            <span id="signup-success-text">Cuenta creada. Espera la aprobación.</span>
          </div>

          <!-- Acciones -->
          <div class="form-actions">
            <button type="button" class="btn-ghost" id="btn-back" hidden>
              <span class="material-symbols-outlined">arrow_back</span>
              <span>Atrás</span>
            </button>

            <button type="button" class="btn-signup" id="btn-next">
              <span id="btn-next-text">Siguiente</span>
              <span class="material-symbols-outlined btn-arrow">arrow_forward</span>
            </button>

            <button type="submit" class="btn-signup" id="btn-submit" hidden>
              <span id="btn-signup-text">Crear cuenta</span>
              <span class="btn-signup-shine" aria-hidden="true"></span>
            </button>
          </div>

        </form>

        <div class="signup-login-link">
          <p>¿Ya tienes cuenta? <a href="../login/login.php">Inicia sesión</a></p>
        </div>

      </div>
    </section>

    <!-- ══ PANEL VISUAL ══ -->
    <aside class="signup-visual" aria-hidden="true">
      <div class="signup-visual-overlay"></div>
      <img
        src="../../assets/images/headers/auth.png"
        alt=""
        class="signup-visual-img"
        loading="eager"
      >
      <div class="signup-visual-content">
        <div class="signup-visual-top">
          <h2 class="signup-brand-name">Appjoteca</h2>
        </div>
        <div class="signup-visual-mid">
          <h3 class="signup-visual-title">Tu identidad,<br><span class="italic">curada</span>.</h3>
          <p class="signup-visual-subtitle">
            Únete al ecosistema digital donde el conocimiento institucional se preserva, comparte y evoluciona.
          </p>
          <ul class="signup-visual-features">
            <li><span class="material-symbols-outlined">verified_user</span> Verificación segura</li>
            <li><span class="material-symbols-outlined">auto_stories</span> Acceso al catálogo</li>
            <li><span class="material-symbols-outlined">shield_lock</span> Datos cifrados</li>
          </ul>
        </div>
        <div class="signup-visual-meta">
          <span>Acceso institucional</span>
          <span class="signup-visual-sep"></span>
          <span>Paso <span id="visual-step">1</span> de 2</span>
        </div>
      </div>
    </aside>

  </main>

  <script src="signup.js"></script>
</body>
</html>