<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../../backend/config/auth.php';
require_once __DIR__ . '/../../../../backend/config/user_context.php';
requiereRol([3]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- 1. THEME GLOBAL -->
    <link rel="stylesheet" href="../../../../shared/css/theme.css">
    
    <!-- 2. Componentes Compartidos -->
    <link rel="stylesheet" href="../../../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../../../shared/css/components/footer.css">
    
    <!-- 3. Estilos Locales del Dashboard -->
    <link rel="stylesheet" href="../../css/global.css">
    <link rel="stylesheet" href="ajustes.css">
    <link rel="stylesheet" href="../../css/typography.css">
    
    <link rel="icon" type="image/png" href="../../../../shared/images/logo-appjoteca.png">
</head>             
<body>
<div id="overlay" class="overlay" aria-hidden="true"></div>

<?php include '../../../../shared/layouts/notifications.php'; ?>
<?php include '../../../../shared/layouts/menu-off-canvas.php'; ?>
<?php include '../../../../shared/layouts/menu-movil.php'; ?>

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
        <div class="topbar-actions">
            <button class="icon-btn search-toggle-btn" aria-label="Buscar" aria-expanded="false">
                <span class="material-symbols-outlined">search</span>
            </button>
            <button class="notification-tray" aria-label="Notificaciones" aria-expanded="false">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge" aria-label="3 notificaciones sin leer"></span>
            </button>
            
            <!-- Botón de perfil inyectado por JS -->
            <div id="profile-button-topbar"></div>

            <button class="icon-btn menu-toggle-btn" id="menu-activar" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobileMenu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>
    <div class="topbar-search-mobile" aria-hidden="true">
        <input type="text" placeholder="Buscar título o autor..." aria-label="Buscar en el catálogo">
    </div>
</header>

    <!-- Nav SIDEBAR -->

    <!-- Nav SIDEBAR -->
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

    <!-- ^Main content -->

    <main class="config-main" id="main-content">
        <div class="config-container">
            <header class="config-page-header">
                <h1 class="config-page-title">Configuración de Perfil <span><?= htmlspecialchars($mi_rol, ENT_QUOTES, 'UTF-8'); ?></span></h1>
                <p class="config-page-subtitle">Administre su identidad digital y credenciales de acceso a la biblioteca institucional.</p>
            </header>

            <div class="config-layout">
                <aside class="config-sidebar">
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <div class="avatar-ring">
                                <img src="<?= htmlspecialchars($mi_foto, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil" id="profileAvatarImg">
                            </div>
                            <button class="avatar-edit" id="avatarEditBtn" type="button" aria-label="Cambiar foto de perfil">
                                <span class="material-symbols-outlined">photo_camera</span>
                            </button>
                            <input type="file" id="avatarInput" accept="image/*" name="nueva_foto" hidden>
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
                                        <span class="meta-value" id="displayDocument">DNI | <?= htmlspecialchars($mi_documento ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="config-content">
                    <section class="config-section" data-section="account">
                        <div class="section-header">
                            <div class="section-icon"><span class="material-symbols-outlined">person</span></div>
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
                                        <button class="input-action-btn" id="saveUsernameBtn" type="button"><span class="material-symbols-outlined">save</span><span class="btn-text">Guardar</span></button>
                                    </div>
                                </div>
                                <div class="form-group locked">
                                    <label for="documentInput">Número de Documento</label>
                                    <div class="input-wrapper">
                                        <span class="material-symbols-outlined input-icon">badge</span>
                                        <input type="text" id="documentInput" value="DNI | <?= htmlspecialchars($mi_documento ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                        <span class="material-symbols-outlined lock-icon">lock</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="config-section config-section--security" data-section="security">
                        <div class="section-header">
                            <div class="section-icon section-icon--gold"><span class="material-symbols-outlined">shield_lock</span></div>
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
                                        <button class="toggle-password" data-target="currentPassword" type="button" aria-label="Mostrar contraseña"><span class="material-symbols-outlined">visibility_off</span></button>
                                    </div>
                                </div>
                                <div class="form-grid form-grid--2">
                                    <div class="form-group">
                                        <label for="newPassword">Nueva Contraseña</label>
                                        <div class="input-wrapper password-wrapper">
                                            <span class="material-symbols-outlined input-icon">lock</span>
                                            <input type="password" id="newPassword" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                                            <button class="toggle-password" data-target="newPassword" type="button" aria-label="Mostrar contraseña"><span class="material-symbols-outlined">visibility_off</span></button>
                                        </div>
                                        <div class="password-strength" id="passwordStrength"><div class="strength-track"><div class="strength-bar" id="strengthBar"></div></div><span class="strength-text" id="strengthText">Fortaleza: <em>Débil</em></span></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmPassword">Confirmar Contraseña</label>
                                        <div class="input-wrapper password-wrapper">
                                            <span class="material-symbols-outlined input-icon">lock_person</span>
                                            <input type="password" id="confirmPassword" placeholder="Repita la nueva contraseña" autocomplete="new-password">
                                            <button class="toggle-password" data-target="confirmPassword" type="button" aria-label="Mostrar contraseña"><span class="material-symbols-outlined">visibility_off</span></button>
                                        </div>
                                        <span class="input-hint" id="matchHint"></span>
                                    </div>
                                </div>
                                <div class="form-actions"><button class="btn-gold" id="updatePasswordBtn" type="button"><span class="material-symbols-outlined">lock_reset</span>Actualizar Contraseña</button></div>
                            </div>
                        </div>
                    </section>

                    <section class="config-section" data-section="requests">
                        <div class="section-header">
                            <div class="section-icon section-icon--blue"><span class="material-symbols-outlined">support_agent</span></div>
                            <div class="section-title-group">
                                <h3>Solicitudes al Administrador</h3>
                                <p>Gestione cambios que requieren aprobación institucional</p>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="requests-grid">
                                <article class="request-card"><div class="request-card-header"><div class="request-icon request-icon--blue"><span class="material-symbols-outlined">alternate_email</span></div><span class="request-status">ESTABLE</span></div><div class="request-card-body"><h4>Cambio de Correo Institucional</h4><p>Actualice su dirección de correo electrónico institucional.</p></div><div class="request-card-footer"><button class="btn-outline" data-request="email" type="button"><span class="material-symbols-outlined">send</span>Solicitar cambio</button></div></article>
                                <article class="request-card"><div class="request-card-header"><div class="request-icon request-icon--purple"><span class="material-symbols-outlined">badge</span></div><span class="request-status">ESTABLE</span></div><div class="request-card-body"><h4>Cambio de Nombre y Apellidos</h4><p>Solicite la actualización de su nombre completo registrado.</p></div><div class="request-card-footer"><button class="btn-outline" data-request="name" type="button"><span class="material-symbols-outlined">send</span>Solicitar cambio</button></div></article>
                                <article class="request-card"><div class="request-card-header"><div class="request-icon request-icon--green"><span class="material-symbols-outlined">contact_page</span></div><span class="request-status">ESTABLE</span></div><div class="request-card-body"><h4>Cambio de Documento</h4><p>Solicite la actualización de su documento de identidad.</p></div><div class="request-card-footer"><button class="btn-outline" data-request="document" type="button"><span class="material-symbols-outlined">send</span>Solicitar cambio</button></div></article>
                            </div>
                        </div>
                    </section>

                    <section class="config-section config-section--danger" data-section="danger">
                        <div class="danger-content">
                            <div class="danger-info"><div class="danger-icon-wrap"><span class="material-symbols-outlined">warning</span></div><div class="danger-text"><h4>Eliminación de Cuenta</h4><p>Esta acción enviará una solicitud de baja definitiva al administrador del sistema.</p></div></div>
                            <button class="btn-danger" id="deleteAccountBtn" type="button"><span class="material-symbols-outlined">delete_forever</span>Solicitar eliminación de cuenta</button>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <?php include '../../../../shared/layouts/footer.php'; ?>
    </main>

<script src="../../../../shared/js/global.js"></script>
    <script src="ajustes.js"></script>

    
</body>
</html>

