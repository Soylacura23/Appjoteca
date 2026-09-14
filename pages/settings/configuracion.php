<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';
require_once __DIR__ . '/../../backend/settings/configuracion-back.php';
requiereRol([1, 2]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Configuración de Perfil - Appjoteca</title>

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Estilos compartidos -->
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">

    <!-- Estilos específicos de configuración -->
    <link rel="stylesheet" href="configuracion.css">
</head>
<body>

    <!-- OVERLAY GLOBAL -->
    <div id="overlay" class="overlay" aria-hidden="true"></div>

    <!-- ══════════════════════════════════════════
        BANDEJA DE NOTIFICACIONES 
   ══════════════════════════════════════════════ -->

   <?php include '../../shared/layouts/notifications.php'; ?>


    <!-- MENÚ OFF-CANVAS DE PERFIL -->
    <?php include '../../shared/layouts/menu-off-canvas.php'; ?>

    <!-- BARRA DE NAVEGACIÓN -->
    <header class="topbar" role="banner">
        <div class="topbar-inner">
            <a href="#" class="topbar-logo">
                <div class="logo" aria-hidden="true"></div>
                <span class="logo-text">AppJoteca</span>
            </a>
            <div class="topbar-search">
                <input type="text" class="topbar-search-input" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
                <span class="material-symbols-outlined topbar-search-icon">search</span>
            </div>
            <nav class="topbar-nav" aria-label="Navegación principal">
                <a href="#" class="nav-link" data-nav="catalogo">Catálogo</a>
                <a href="#" class="nav-link" data-nav="biblioteca">Mi Biblioteca</a>
                <a href="#" class="nav-link" data-nav="panel">Panel</a>
            </nav>
            <div class="topbar-actions">
                <button class="icon-btn search-toggle-btn" aria-label="Buscar" aria-expanded="false">
                    <span class="material-symbols-outlined">search</span>
                </button>
                <button class="notification-tray" aria-label="Notificaciones" aria-expanded="false">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notification-badge" aria-label="3 notificaciones sin leer"></span>
                </button>
                <div id="profile-button-topbar"></div>
                <button class="icon-btn menu-toggle-btn" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobileMenu">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </div>
        <div class="topbar-search-mobile" aria-hidden="true">
            <input type="text" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
        </div>
    </header>

    <!-- MENÚ MÓVIL -->
    <?php include '../../shared/layouts/menu-movil.php'; ?>
    

    <!-- CONTENIDO PRINCIPAL: CONFIGURACIÓN -->
    <main class="config-main">
        <div class="config-container">
            <header class="config-page-header">
                <h1 class="config-page-title">Configuración de Perfil <span><?= $mi_rol; ?></span></h1>
                <p class="config-page-subtitle">Administre su identidad digital y credenciales de acceso a la biblioteca institucional.</p>
            </header>

            <div class="config-layout">
                <!-- SIDEBAR -->
                <aside class="config-sidebar">
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <div class="avatar-ring">
                                <img src="<?= htmlspecialchars($foto_actual, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil" id="profileAvatarImg">
                            </div>
                            <button class="avatar-edit" id="avatarEditBtn" aria-label="Cambiar foto de perfil">
                                <span class="material-symbols-outlined">photo_camera</span>
                            </button>
                            <input type="file" id="avatarInput" accept="image/*" name="nueva_foto" hidden >
                        </div>
                        <div class="profile-info">
                            <h2 class="profile-name"><?= htmlspecialchars($mi_nombre, ENT_QUOTES, 'UTF-8'); ?></h2>
                            <div class="profile-role">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">verified</span>
                                <span><?= $mi_rol; ?> Verificado</span>
                            </div>
                            <div class="profile-meta">
                                <div class="meta-item">
                                    <span class="material-symbols-outlined">person_outline</span>
                                    <div class="meta-content">
                                        <span class="meta-label">Usuario</span>
                                        <span class="meta-value" id="displayUsername"><?= $mi_usuario; ?></span>
                                    </div>
                                </div>
                                <div class="meta-item">
                                    <span class="material-symbols-outlined">badge</span>
                                    <div class="meta-content">
                                        <span class="meta-label">Documento</span>
                                        <span class="meta-value" id="displayDocument">DNI | <?= htmlspecialchars($documento, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="id-doc-card">
                        <div class="id-doc-header">
                            <span class="material-symbols-outlined">contact_page</span>
                            <span>Documento de Identidad</span>
                        </div>
                        <div class="id-doc-preview" id="idDocPreview">
                            <img src="<?= htmlspecialchars($foto_documento, ENT_QUOTES, 'UTF-8'); ?>" alt="Documento ID" id="idDocImage" loading="lazy">
                            <div class="id-doc-blur-overlay">
                                <span class="material-symbols-outlined">visibility_off</span>
                                <span>Click para visualizar</span>
                            </div>
                            
                            <input type="file" id="idDocInput" accept="image/*" hidden>
                        </div>
                        <div class="id-doc-footer">
                            <span class="material-symbols-outlined">lock</span>
                            <span>Solo lectura (Verificado por Bibliotecario)</span>
                        </div>
                    </div>
                </aside>

                <!-- MAIN CONTENT -->
                <div class="config-content">
                    <!-- SECCIÓN 1: INFORMACIÓN DE CUENTA -->
                    <section class="config-section" data-section="account">
                        <div class="section-header">
                            <div class="section-icon">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <div class="section-title-group">
                                <h3>Información de Cuenta</h3>
                                <p>Datos principales de su identidad en el sistema</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="usernameInput">Nombre de Usuario</label>
                                    <div class="input-wrapper">
                                        <span class="material-symbols-outlined input-icon">person_outline</span>
                                        <input type="text" id="usernameInput" value="<?= htmlspecialchars($mi_usuario, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="username">
                                        <button class="input-action-btn" id="saveUsernameBtn">
                                            <span class="material-symbols-outlined">save</span>
                                            <span class="btn-text">Guardar</span>
                                        </button>
                                    </div>
                                    <span class="input-hint hint-success">Puede modificar su nombre de usuario en cualquier momento</span>
                                </div>
                                <div class="form-group locked">
                                    <label for="documentInput">Número de Documento</label>
                                    <div class="input-wrapper">
                                        <span class="material-symbols-outlined input-icon">badge</span>
                                        <input type="text" id="documentInput" value="DNI | <?= htmlspecialchars($documento); ?>" readonly>
                                        <span class="material-symbols-outlined lock-icon">lock</span>
                                    </div>
                                    <span class="input-hint hint-warning">Requiere solicitud al bibliotecario para modificar</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN 2: SEGURIDAD -->
                    <section class="config-section config-section--security" data-section="security">
                        <div class="section-header">
                            <div class="section-icon section-icon--gold">
                                <span class="material-symbols-outlined">shield_lock</span>
                            </div>
                            <div class="section-title-group">
                                <h3>Configuración de Seguridad</h3>
                                <p>Actualice su contraseña de acceso al sistema</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="password-form">
                                <div class="form-group">
                                    <label for="currentPassword">Contraseña Actual</label>
                                    <div class="input-wrapper password-wrapper">
                                        <span class="material-symbols-outlined input-icon">lock_open</span>
                                        <input type="password" id="currentPassword" placeholder="Ingrese su contraseña actual" autocomplete="current-password">
                                        <button class="toggle-password" data-target="currentPassword" type="button" aria-label="Mostrar contraseña">
                                            <span class="material-symbols-outlined">visibility_off</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-grid form-grid--2">
                                    <div class="form-group">
                                        <label for="newPassword">Nueva Contraseña</label>
                                        <div class="input-wrapper password-wrapper">
                                            <span class="material-symbols-outlined input-icon">lock</span>
                                            <input type="password" id="newPassword" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                                            <button class="toggle-password" data-target="newPassword" type="button" aria-label="Mostrar contraseña">
                                                <span class="material-symbols-outlined">visibility_off</span>
                                            </button>
                                        </div>
                                        <div class="password-strength" id="passwordStrength">
                                            <div class="strength-track">
                                                <div class="strength-bar" id="strengthBar"></div>
                                            </div>
                                            <span class="strength-text" id="strengthText">Fortaleza: <em>Débil</em></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmPassword">Confirmar Contraseña</label>
                                        <div class="input-wrapper password-wrapper">
                                            <span class="material-symbols-outlined input-icon">lock_person</span>
                                            <input type="password" id="confirmPassword" placeholder="Repita la nueva contraseña" autocomplete="new-password">
                                            <button class="toggle-password" data-target="confirmPassword" type="button" aria-label="Mostrar contraseña">
                                                <span class="material-symbols-outlined">visibility_off</span>
                                            </button>
                                        </div>
                                        <span class="input-hint" id="matchHint"></span>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="btn-gold" id="updatePasswordBtn" type="button">
                                        <span class="material-symbols-outlined">lock_reset</span>
                                        Actualizar Contraseña
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN 3: SOLICITUDES AL BIBLIOTECARIO -->
                    <section class="config-section" data-section="requests">
                        <div class="section-header">
                            <div class="section-icon section-icon--blue">
                                <span class="material-symbols-outlined">support_agent</span>
                            </div>
                            <div class="section-title-group">
                                <h3>Solicitudes al Bibliotecario</h3>
                                <p>Gestione cambios que requieren aprobación institucional</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="requests-grid">
                                <article class="request-card">
                                    <div class="request-card-header">
                                        <div class="request-icon request-icon--blue">
                                            <span class="material-symbols-outlined">alternate_email</span>
                                        </div>
                                        <span class="request-status">ESTABLE</span>
                                    </div>
                                    <div class="request-card-body">
                                        <h4>Cambio de Correo Institucional</h4>
                                        <p>Actualice su dirección de correo electrónico institucional para recibir notificaciones del sistema.</p>
                                    </div>
                                    <div class="request-card-footer">
                                        <button class="btn-outline" data-request="email" type="button">
                                            <span class="material-symbols-outlined">send</span>
                                            Solicitar cambio
                                        </button>
                                    </div>
                                </article>

                                <article class="request-card">
                                    <div class="request-card-header">
                                        <div class="request-icon request-icon--purple">
                                            <span class="material-symbols-outlined">badge</span>
                                        </div>
                                        <span class="request-status">ESTABLE</span>
                                    </div>
                                    <div class="request-card-body">
                                        <h4>Cambio de Nombre y Apellidos</h4>
                                        <p>Solicite la actualización de su nombre completo registrado en el sistema institucional.</p>
                                    </div>
                                    <div class="request-card-footer">
                                        <button class="btn-outline" data-request="name" type="button">
                                            <span class="material-symbols-outlined">send</span>
                                            Solicitar cambio
                                        </button>
                                    </div>
                                </article>

                                <article class="request-card">
                                    <div class="request-card-header">
                                        <div class="request-icon request-icon--green">
                                            <span class="material-symbols-outlined">contact_page</span>
                                        </div>
                                        <span class="request-status">ESTABLE</span>
                                    </div>
                                    <div class="request-card-body">
                                        <h4>Cambio de Documento y Foto</h4>
                                        <p>Actualice su número de documento de identidad y la fotografía del documento físico.</p>
                                    </div>
                                    <div class="request-card-footer">
                                        <button class="btn-outline" data-request="document" type="button">
                                            <span class="material-symbols-outlined">send</span>
                                            Solicitar cambio
                                        </button>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN 4: ZONA DE PELIGRO -->
                    <section class="config-section config-section--danger" data-section="danger">
                        <div class="danger-content">
                            <div class="danger-info">
                                <div class="danger-icon-wrap">
                                    <span class="material-symbols-outlined">warning</span>
                                </div>
                                <div class="danger-text">
                                    <h4>Eliminación de Cuenta</h4>
                                    <p>Esta acción enviará una solicitud de baja definitiva al administrador del sistema. Esta operación no se puede deshacer y perderá acceso a todos sus préstamos y reservas activas.</p>
                                </div>
                            </div>
                            <button class="btn-danger" id="deleteAccountBtn" type="button">
                                <span class="material-symbols-outlined">delete_forever</span>
                                Solicitar eliminación de cuenta
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <!-- OVERLAY DE DOCUMENTO ID -->
    <div class="id-doc-overlay" id="idDocOverlay" aria-hidden="true">
        <button class="id-doc-overlay-close" id="idDocOverlayClose" aria-label="Cerrar visualización">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="id-doc-overlay-content">
            <img src="<?= htmlspecialchars($foto_documento, ENT_QUOTES, 'UTF-8'); ?>" alt="Documento de Identidad" id="idDocOverlayImg">
        </div>
    </div>

    <!-- FOOTER -->
    <?php include '../../shared/layouts/footer.php'; ?>

    <!-- FAB -->
    <div class="fab-container">
        <button class="fab" aria-label="Explorar el archivo">
            <span class="material-symbols-outlined">auto_stories</span>
            <span class="fab-text">Explorar</span>
        </button>
    </div>

    <script>
        const PROFILE_IMAGE = "<?php echo $_SESSION['foto_perfil']; ?>";
    </script>

    <!-- Scripts compartidos -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../shared/js/components/navbar.js"></script>
    <script src="../../shared/js/global.js"></script>
    <script src="configuracion.js"></script>

</body>
</html>