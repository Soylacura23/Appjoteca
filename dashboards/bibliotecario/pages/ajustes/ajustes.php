<?php
require_once __DIR__ . '/../../../../backend/config/auth.php';
require_once __DIR__ . '/../../../../backend/config/user_context.php';
require_once __DIR__ . '/../../../../backend/Database/conexion.php';
requiereRol([3]);

$documento = $mi_documento ?? '';
$foto_documento = '';
$consultaDocumento = $connection->prepare('SELECT foto_documento, biografia FROM usuarios WHERE id_usuario = ?');
if (!$consultaDocumento) {
    throw new RuntimeException('No se pudo consultar la foto del documento: ' . $connection->error);
}
$consultaDocumento->bind_param('i', $mi_id);
$consultaDocumento->execute();
$datosCuenta = $consultaDocumento->get_result()->fetch_assoc() ?: [];
$fotoDocumento = $datosCuenta['foto_documento'] ?? '';
$mi_biografia = (string) ($datosCuenta['biografia'] ?? '');
$consultaDocumento->close();
if ($fotoDocumento !== '') {
    $foto_documento = '/Appjoteca/' . ltrim($fotoDocumento, '/');
}
$foto_documento_es_pdf = str_ends_with(strtolower($foto_documento), '.pdf');
$tiene_foto_personalizada = !empty($_SESSION['foto_perfil'])
    && strpos($_SESSION['foto_perfil'], 'default-avatar') === false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
    <title>Ajustes · Bibliotecario · AppJoteca</title>

    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="../../../../shared/css/theme.css">
    <link rel="stylesheet" href="../../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../../shared/css/components/footer.css">
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="../../css/typography.css">
    <link rel="stylesheet" href="ajustes.css">
    <link rel="stylesheet" href="../../css/layout.css">

    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>

    <?php require __DIR__ . '/../../../../shared/layouts/favicon.php'; ?>
</head>
<body>

<div id="overlay" class="overlay" aria-hidden="true"></div>

<?php include '../../../../shared/layouts/notifications.php'; ?>
<?php include '../../../../shared/layouts/menu-off-canvas.php'; ?>
<?php include_once __DIR__ . '/../../../../shared/layouts/menu-movil.php'; ?>

<header class="topbar" role="banner">
    <div class="topbar-inner">
               <a href="../../index.php" class="logo-link">
    <img src="/Appjoteca/shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
</a>
        <div class="topbar-search">
            <input type="text" class="topbar-search-input" placeholder="Buscar título o autor..." aria-label="Buscar">
            <span class="material-symbols-outlined topbar-search-icon">search</span>
        </div>
        <div class="topbar-actions">
            <button class="icon-btn search-toggle-btn" aria-label="Buscar" aria-expanded="false">
                <span class="material-symbols-outlined">search</span>
            </button>
            <button class="notification-tray" aria-label="Notificaciones" aria-expanded="false">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge"></span>
            </button>
            <div id="profile-button-topbar"></div>
            <button class="icon-btn menu-toggle-btn" id="menu-activar" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobileMenu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>
    <div class="topbar-search-mobile" aria-hidden="true">
        <input type="text" placeholder="Buscar título o autor..." aria-label="Buscar">
    </div>
</header>

<div class="dashboard-layout">
<aside id="sidebar" class="sidebar">
    <nav class="sidebar-navigator">
        <ul class="menu-items">
            <li>
                <a href="../../index.php" class="menu-item">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="menu-texto">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="../inventario/inventario.php" class="menu-item">
                    <span class="material-symbols-outlined">menu_book</span>
                    <span class="menu-texto">Inventario</span>
                </a>
            </li>
            <li>
                <a href="../reservaciones/reservaciones.php" class="menu-item">
                    <span class="material-symbols-outlined">event_available</span>
                    <span class="menu-texto">Reservaciones</span>
                </a>
            </li>
            <li>
                <a href="../usuarios/usuarios.php" class="menu-item">
                    <span class="material-symbols-outlined">group</span>
                    <span class="menu-texto">Usuarios</span>
                </a>
            </li>
            <li>
                <a href="../reportes/reportes.php" class="menu-item">
                    <span class="material-symbols-outlined">analytics</span>
                    <span class="menu-texto">Reportes</span>
                </a>
            </li>
            <li>
                <a href="../configuracion-catalogo/configuracion-catalogo.php" class="menu-item">
                    <span class="material-symbols-outlined">auto_stories</span>
                    <span class="menu-texto">Catálogo</span>
                </a>
            </li>
            <li>
                <a href="ajustes.php" class="menu-item active">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="menu-texto">Ajustes</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<main class="config-main" id="main-content">
    <div class="config-container">

        <header class="config-page-header">
            <h1 class="config-page-title">Configuración de Perfil <span><?= htmlspecialchars($mi_rol, ENT_QUOTES, 'UTF-8'); ?></span></h1>
            <p class="config-page-subtitle">Administre su identidad digital y credenciales de acceso a la biblioteca institucional.</p>
        </header>

        <div class="config-layout">

            <!-- SIDEBAR PERFIL -->
            <aside class="config-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <div class="avatar-ring">
                            <img src="<?= htmlspecialchars($mi_foto, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil" id="profileAvatarImg">
                        </div>
                        <button class="avatar-edit" id="avatarEditBtn" type="button" aria-label="Cambiar foto">
                            <span class="material-symbols-outlined">photo_camera</span>
                        </button>
                        <button class="avatar-delete" id="deletePhotoBtn" type="button" aria-label="Eliminar foto" title="Eliminar foto" <?= $tiene_foto_personalizada ? '' : 'hidden'; ?>>
                            <span class="material-symbols-outlined">close</span>
                        </button>
                        <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp" hidden>
                    </div>

                    <div class="profile-info">
                        <h2 class="profile-name"><?= htmlspecialchars($mi_nombre, ENT_QUOTES, 'UTF-8'); ?></h2>
                        <div class="profile-role">
                            <span class="material-symbols-outlined">verified</span>
                            <span><?= htmlspecialchars($mi_rol, ENT_QUOTES, 'UTF-8'); ?> Verificado</span>
                        </div>

                        <div class="profile-meta">
                            <div class="meta-item">
                                <span class="material-symbols-outlined">person_outline</span>
                                <div class="meta-content">
                                    <span class="meta-label">Usuario</span>
                                    <span class="meta-value" id="displayUsername"><?= htmlspecialchars($mi_usuario, ENT_QUOTES, 'UTF-8'); ?></span>
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

                    <div class="avatar-actions">
                        <button type="button" class="btn-outline" id="previewConfirmBtn" hidden>
                            <span class="material-symbols-outlined">check</span> Confirmar foto
                        </button>
                    </div>
                </div>
                <div class="id-doc-card">
                    <div class="id-doc-header">
                        <span class="material-symbols-outlined">contact_page</span>
                        <span>Documento de identidad</span>
                    </div>
                    <div class="id-doc-preview" id="idDocPreview">
                        <?php if ($foto_documento !== ''): ?>
                            <?php if ($foto_documento_es_pdf): ?>
                                <div class="id-doc-pdf-preview">
                                    <span class="material-symbols-outlined">picture_as_pdf</span>
                                    <span>Documento PDF</span>
                                </div>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($foto_documento, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto actual del documento" loading="lazy">
                            <?php endif; ?>
                            <div class="id-doc-blur-overlay">
                                <span class="material-symbols-outlined">visibility_off</span>
                                <span>Click para visualizar</span>
                            </div>
                        <?php else: ?>
                            <div class="id-doc-placeholder" role="status">
                                <span class="material-symbols-outlined">image_not_supported</span>
                                <p>No hay foto de documento registrada.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($foto_documento !== ''): ?>
                        <a class="id-doc-footer" href="<?= htmlspecialchars($foto_documento, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                            <span class="material-symbols-outlined">open_in_new</span>
                            <span>Ver documento actual</span>
                        </a>
                    <?php else: ?>
                        <div class="id-doc-footer">
                            <span class="material-symbols-outlined">lock</span>
                            <span>Se agrega al solicitar el cambio de documento</span>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- CONTENIDO -->
            <div class="config-content">

                <!-- Información de cuenta -->
                <section class="config-section">
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
                                    <button class="input-action-btn" id="saveUsernameBtn" type="button">
                                        <span class="material-symbols-outlined">save</span>
                                        <span class="btn-text">Guardar</span>
                                    </button>
                                </div>
                                <span class="input-hint hint-success">Puede cambiarlo cada 7 días. El anterior queda reservado 30 días.</span>
                            </div>

                            <div class="form-group biography-editor">
                                <label for="biographyInput">Biografía</label>
                                <textarea id="biographyInput" data-endpoint="../../../../backend/settings/configuracion-back.php" maxlength="500" rows="4" placeholder="Cuéntanos un poco sobre ti..."><?= htmlspecialchars($mi_biografia, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <div class="biography-editor-footer">
                                    <span class="input-hint">Hasta 500 caracteres</span>
                                    <span class="biography-counter" id="biographyCounter">0 / 500</span>
                                </div>
                                <div class="biography-actions" id="biographyActions" hidden>
                                    <button class="btn-outline" id="cancelBiographyBtn" type="button">
                                        <span class="material-symbols-outlined">close</span> Cancelar
                                    </button>
                                    <button class="btn-gold" id="saveBiographyBtn" type="button">
                                        <span class="material-symbols-outlined">save</span> Guardar
                                    </button>
                                </div>
                            </div>

                            <div class="form-group locked">
                                <label for="documentInput">Número de Documento</label>
                                <div class="input-wrapper">
                                    <span class="material-symbols-outlined input-icon">badge</span>
                                    <input type="text" id="documentInput" value="DNI | <?= htmlspecialchars($documento, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    <span class="material-symbols-outlined lock-icon">lock</span>
                                </div>
                                <span class="input-hint hint-warning">Requiere solicitud al administrador</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Seguridad -->
                <section class="config-section config-section--security">
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
                                    <button class="toggle-password" data-target="currentPassword" type="button" aria-label="Mostrar">
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
                                        <button class="toggle-password" data-target="newPassword" type="button" aria-label="Mostrar">
                                            <span class="material-symbols-outlined">visibility_off</span>
                                        </button>
                                    </div>
                                    <div class="password-strength">
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
                                        <button class="toggle-password" data-target="confirmPassword" type="button" aria-label="Mostrar">
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

                <!-- Solicitudes al Admin -->
                <section class="config-section">
                    <div class="section-header">
                        <div class="section-icon section-icon--blue">
                            <span class="material-symbols-outlined">support_agent</span>
                        </div>
                        <div class="section-title-group">
                            <h3>Solicitudes al Administrador</h3>
                            <p>Cambios que requieren aprobación</p>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="requests-grid">
                            <article class="request-card">
                                <div class="request-card-header">
                                    <div class="request-icon request-icon--blue">
                                        <span class="material-symbols-outlined">alternate_email</span>
                                    </div>
                                    <span class="request-status">SOLICITUD</span>
                                </div>
                                <div class="request-card-body">
                                    <h4>Cambio de Correo</h4>
                                    <p>Actualice su correo institucional.</p>
                                </div>
                                <div class="request-card-footer">
                                    <button class="btn-outline" data-request="email" type="button">
                                        <span class="material-symbols-outlined">send</span>
                                        Solicitar
                                    </button>
                                </div>
                            </article>

                            <article class="request-card">
                                <div class="request-card-header">
                                    <div class="request-icon request-icon--purple">
                                        <span class="material-symbols-outlined">badge</span>
                                    </div>
                                    <span class="request-status">SOLICITUD</span>
                                </div>
                                <div class="request-card-body">
                                    <h4>Cambio de Nombre</h4>
                                    <p>Actualice su nombre y apellidos.</p>
                                </div>
                                <div class="request-card-footer">
                                    <button class="btn-outline" data-request="nombre" type="button">
                                        <span class="material-symbols-outlined">send</span>
                                        Solicitar
                                    </button>
                                </div>
                            </article>

                            <article class="request-card">
                                <div class="request-card-header">
                                    <div class="request-icon request-icon--green">
                                        <span class="material-symbols-outlined">contact_page</span>
                                    </div>
                                    <span class="request-status">SOLICITUD</span>
                                </div>
                                <div class="request-card-body">
                                    <h4>Cambio de Documento</h4>
                                    <p>Indique el nuevo número y adjunte una foto o PDF del documento.</p>
                                </div>
                                <div class="request-card-footer">
                                    <button class="btn-outline" data-request="documento" type="button">
                                        <span class="material-symbols-outlined">send</span>
                                        Solicitar
                                    </button>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <!-- Zona de peligro -->
                <section class="config-section config-section--danger">
                    <div class="danger-content">
                        <div class="danger-info">
                            <div class="danger-icon-wrap">
                                <span class="material-symbols-outlined">warning</span>
                            </div>
                            <div class="danger-text">
                                <h4>Eliminación de Cuenta</h4>
                                <p>Se enviará una solicitud de baja al administrador. Esta acción no se puede deshacer fácilmente.</p>
                            </div>
                        </div>
                        <button class="btn-danger" id="deleteAccountBtn" type="button">
                            <span class="material-symbols-outlined">delete_forever</span>
                            Solicitar eliminación
                        </button>
                    </div>
                </section>

            </div>
        </div>
    </div>

</main>
</div>
<?php if ($foto_documento !== ''): ?>
    <div class="id-doc-overlay" id="idDocOverlay" aria-hidden="true">
        <button class="id-doc-overlay-close" id="idDocOverlayClose" type="button" aria-label="Cerrar">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="id-doc-overlay-content">
            <?php if ($foto_documento_es_pdf): ?>
                <iframe src="<?= htmlspecialchars($foto_documento, ENT_QUOTES, 'UTF-8'); ?>" title="Documento de identidad en PDF"></iframe>
            <?php else: ?>
                <img src="<?= htmlspecialchars($foto_documento, ENT_QUOTES, 'UTF-8'); ?>" alt="Documento de identidad">
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php include '../../../../shared/layouts/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/Appjoteca/shared/js/components/notifications.js"></script>
<script src="../../../../shared/js/global.js"></script>
<script src="ajustes.js"></script>
<script src="/Appjoteca/shared/js/components/biography-editor.js"></script>

<script src="/Appjoteca/shared/js/register-sw.js"></script>
</body>
</html>