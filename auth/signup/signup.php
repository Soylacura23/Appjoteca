<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro en AppJoteca</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,700;0,900;1,700;1,900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300..700,0..1,0&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../components/theme.css">
  <link rel="stylesheet" href="signup.css">

  <link rel="icon" type="image/png" href="../../shared/images/logo-appjoteca.png">
  <link rel="stylesheet" href="../../shared/css/components/footer.css">
</head>

<body>

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

    <section class="signup-form-panel" aria-label="Formulario de registro">
      <div class="signup-form-wrapper">

        <div class="role-selector" role="group" aria-label="Selecciona tu tipo de acceso">
          <p class="role-selector-label" id="role-label">¿Quién eres?</p>
          <div class="role-grid" role="radiogroup" aria-labelledby="role-label">

            <label class="role-card" title="Registro como Estudiante">
              <input type="radio" name="rol" value="estudiante" class="role-radio" checked aria-label="Estudiante" form="signup-form">
              <div class="role-card-inner">
                <span class="material-symbols-outlined role-icon" aria-hidden="true">school</span>
                <span class="role-name">Estudiante</span>
                <span class="role-desc">Accede a tu biblioteca</span>
              </div>
            </label>

            <label class="role-card" title="Registro como Profesor">
              <input type="radio" name="rol" value="profesor" class="role-radio" aria-label="Profesor" form="signup-form">
              <div class="role-card-inner">
                <span class="material-symbols-outlined role-icon" aria-hidden="true">history_edu</span>
                <span class="role-name">Profesor</span>
                <span class="role-desc">Gestiona tus asignaturas</span>
              </div>
            </label>

          </div>
        </div>
        <header class="signup-heading">
          <h3 class="signup-title">Crear Cuenta Institucional</h3>
          <p class="signup-subtitle">Completa tus datos para acceder a la biblioteca digital.</p>
        </header>

        <form id="signup-form" class="signup-form" novalidate autocomplete="on" action="../../backend/auth/register-send.php" method="POST" enctype="multipart/form-data">

          <div class="field-group">
            <label class="field-label" for="nombre">Nombres y apellidos <span class="required-mark" aria-hidden="true">*</span></label>
            <div class="field-wrapper">
              <input
                type="text"
                id="nombre"
                name="nombre"
                class="field-input"
                placeholder="Juan Pérez García"
                autocomplete="name"
                required
              >
            </div>
          </div>

          <div class="field-group">
            <label class="field-label" for="usuario">Nombre de usuario único <span class="required-mark" aria-hidden="true">*</span></label>
            <div class="field-wrapper">
              <input
                type="text"
                id="usuario"
                name="usuario"
                class="field-input"
                placeholder="juan.perez"
                autocomplete="username"
                required
                minlength="3"
                maxlength="30"
                pattern="[a-zA-Z0-9._-]+"
              >
            </div>
            <p class="field-hint">Solo letras, números, punto, guión y guión bajo. No se podrá cambiar después.</p>
          </div>

          <div class="field-group">
            <label class="field-label" for="email">Correo institucional <span class="required-mark" aria-hidden="true">*</span></label>
            <div class="field-wrapper">
              <input
                type="email"
                id="email"
                name="email"
                class="field-input"
                placeholder="juan.perez@institucion.edu"
                autocomplete="email"
                required
              >
            </div>
            <p class="field-hint">Solo dominios institucionales permitidos.</p>
          </div>

          <div class="field-group">
            <label class="field-label" for="cedula">Tarjeta de identidad / Cédula <span class="required-mark" aria-hidden="true">*</span></label>
            <div class="field-wrapper">
              <input
                type="text"
                id="cedula"
                name="cedula"
                class="field-input"
                placeholder="Ej: 1-1234-5678 o 123456789"
                autocomplete="off"
                required
                maxlength="20"
              >
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Documento adjunto <span class="required-mark" aria-hidden="true">*</span></label>
            <div class="file-upload-wrapper">
              <input 
                type="file" 
                id="cedula-file" 
                name="cedula_file" 
                class="file-input" 
                accept=".pdf, image/png, image/jpeg, image/webp" 
                required 
                aria-label="Subir documento de identidad"
              >
              <label for="cedula-file" class="file-upload-label">
                <span class="material-symbols-outlined" aria-hidden="true">cloud_upload</span>
                <div class="file-upload-text">
                  <strong>Suelta el archivo aquí o busca en el dispositivo</strong>
                  <span>Formatos: PDF, PNG, JPG o WEBP (Máx. 10MB)</span>
                </div>
              </label>
              <div id="cedula-preview" class="file-preview" hidden></div>
            </div>
          </div>
        
          <div class="field-group">
            <label class="field-label" for="contrasena">Contraseña <span class="required-mark" aria-hidden="true">*</span></label>
            <div class="field-wrapper field-wrapper--password">
              <input
                type="password"
                id="contrasena"
                name="contrasena"
                class="field-input"
                placeholder="••••••••"
                autocomplete="new-password"
                required
                minlength="8"
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
            <p class="field-hint">Mínimo 8 caracteres.</p>
          </div>

          <div class="field-group">
            <label class="field-label" for="contrasena2">Confirmar contraseña <span class="required-mark" aria-hidden="true">*</span></label>
            <div class="field-wrapper field-wrapper--password">
              <input
                type="password"
                id="contrasena2"
                name="contrasena2"
                class="field-input"
                placeholder="••••••••"
                autocomplete="new-password"
                required
              >
              <button
                type="button"
                class="field-toggle-pass"
                id="toggle-pass2"
                aria-label="Mostrar u ocultar contraseña"
              >
                <span class="material-symbols-outlined" id="toggle-pass2-icon">visibility</span>
              </button>
            </div>
          </div>

          <div
            id="signup-error"
            class="signup-error"
            role="alert"
            aria-live="polite"
            hidden
          >
            <span class="material-symbols-outlined" aria-hidden="true">error</span>
            <span id="signup-error-text">Error en el registro.</span>
          </div>

          <div
            id="signup-success"
            class="signup-success"
            role="status"
            aria-live="polite"
            hidden
          >
            <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
            <span id="signup-success-text">Cuenta creada correctamente.</span>
          </div>

          <button type="submit" class="btn-signup">
            <span id="btn-signup-text">Crear Cuenta</span>
            <span class="btn-signup-shine" aria-hidden="true"></span>
          </button>

        </form>
        <div class="signup-login-link">
          <p>¿Ya tienes cuenta? <a href="../login/login.php">Inicia sesión</a></p>
        </div>

      </div>
      </section>
    <section class="signup-visual" aria-hidden="true">
      <div class="signup-visual-overlay"></div>
      <img
        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDh90-d0HDfPWjO0zWZSjQ37GedrEaIhx7jonR6q6mbV7J8HIq_-ZVZtBVC630JozQkh5qqcwdxpcoek36JrW2hs-rGgvXudQRN4T3cToBlyemRpSTd5ZNcxDn7Sr7nX6qD6oZGxXi3uEFa3dd9wgceURUteG5XPQiC93aQ1i4vwsN8kBvshVOYsz6ult6OvyPPe9YaUsVDzmEBplHHrVbv3av82bb-VHpDcvEaO7j3CNba79tbCVsbxYakDTBaqdUknb1XKqaBRtrw"
        alt="Biblioteca clásica con estanterías de madera y luz cálida dorada"
        class="signup-visual-img"
        loading="eager"
      >
      <div class="signup-visual-content">
        <div>
          <h1 class="signup-brand-name">Appjoteca</h1>
        </div>
        <div>
          <h2 class="signup-visual-title">Tu identidad, <br><span class="italic">curada</span>.</h2>
          <p class="signup-visual-subtitle">
            Únete al ecosistema digital donde el conocimiento institucional se preserva, comparte y evoluciona. Tu archivo comienza hoy.
          </p>
        </div>
        <div class="signup-visual-meta" aria-hidden="true">
          <span>Acceso Institucional</span>
          <span class="signup-visual-sep"></span>
          <span>Verificación Segura</span>
        </div>
      </div>
    </section>

  </main>
  <footer class="footer" role="contentinfo">
    <div class="footer-inner">
      <div class="footer-brand">
        <p class="footer-logo">Appjoteca</p>
        <p class="footer-tagline">Sistema de Biblioteca Institucional · El conocimiento curado para la excelencia académica.</p>
        <div class="footer-social">
          <button class="footer-social-btn" aria-label="Twitter">
            <span class="material-symbols-outlined">alternate_email</span>
          </button>
          <button class="footer-social-btn" aria-label="GitHub">
            <span class="material-symbols-outlined">code</span>
          </button>
          <button class="footer-social-btn" aria-label="Email">
            <span class="material-symbols-outlined">mail</span>
          </button>
        </div>
      </div>
      <div class="footer-nav-cols">
        <div class="footer-col">
          <p class="footer-col-title">Producto</p>
          <nav>
            <a href="#">Biblioteca Digital</a>
            <a href="#">Catálogo</a>
            <a href="#">Préstamos</a>
            <a href="#">Reservas</a>
          </nav>
        </div>
        <div class="footer-col">
          <p class="footer-col-title">Institución</p>
          <nav>
            <a href="#">Nosotros</a>
            <a href="#">Equipo</a>
            <a href="#">Políticas</a>
            <a href="#">Contacto</a>
          </nav>
        </div>
        <div class="footer-col">
          <p class="footer-col-title">Legal</p>
          <nav>
            <a href="#">Privacidad</a>
            <a href="#">Términos</a>
            <a href="#">Cookies</a>
            <a href="#">Accesibilidad</a>
          </nav>
        </div>
        <div class="footer-col">
          <p class="footer-col-title">Soporte</p>
          <nav>
            <a href="#">Centro de Ayuda</a>
            <a href="#">Documentación</a>
            <a href="#">API</a>
            <a href="#">Estado</a>
          </nav>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p class="footer-copyright">© 2024 Appjoteca · Sistema de Biblioteca Institucional</p>
    </div>
  </footer>

  <script src="signup.js"></script>
</body>
</html>