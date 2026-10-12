<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/user_context.php';
requiereRol([4]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
    <title>Panel de Administrador - AppJoteca</title>

    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,300;1,400&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://unpkg.com/tabulator-tables@5.5.0/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/notifications.css">
    <link rel="stylesheet" href="../../shared/css/components/navbar.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="../../shared/css/components/reading-summary.css">
    <link rel="stylesheet" href="administrador.css">
    <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0" defer></script>
    <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
    <?php require __DIR__ . '/../../shared/layouts/favicon.php'; ?>
</head>
<body>

<div id="overlay" class="overlay" aria-hidden="true"></div>

<?php include '../../shared/layouts/notifications.php'; ?>
<?php include '../../shared/layouts/menu-off-canvas.php'; ?> 

<?php include __DIR__ . '/../../shared/layouts/topbar.php'; ?>

<?php include_once __DIR__ . '/../../shared/layouts/menu-movil.php'; ?>

<main class="config-main">
    <div class="config-container">

        <header class="config-page-header">
            <h1 class="config-page-title">Gestión de <span>Usuarios</span></h1>
            <p class="config-page-subtitle">Administra perfiles, estados de cuenta, solicitudes de cambio y nuevas altas.</p>
        </header>

        <?php include __DIR__ . '/../../shared/layouts/reading-summary.php'; ?>

        <div class="admin-actions-bar">
            <button id="btnAbrirModal" class="btn-gold" type="button">
                <span class="material-symbols-outlined">person_add</span>
                Crear Usuario
            </button>
        </div>

        <!-- PESTAÑAS -->
        <div class="admin-tabs-wrapper">
            <nav class="admin-tabs" role="tablist">
                <button class="admin-tab is-active" data-tab="bibliotecarios" role="tab" type="button">
                    <span class="material-symbols-outlined">groups</span>
                    Bibliotecarios
                </button>
                <button class="admin-tab" data-tab="pendientes" role="tab" type="button">
                    <span class="material-symbols-outlined">pending_actions</span>
                    Cuentas pendientes
                    <span class="tab-badge" id="badge-pendientes" hidden>0</span>
                </button>
                <button class="admin-tab" data-tab="solicitudes" role="tab" type="button">
                    <span class="material-symbols-outlined">assignment</span>
                    Solicitudes de cambio
                    <span class="tab-badge" id="badge-solicitudes" hidden>0</span>
                </button>
                <button class="admin-tab" data-tab="usuarios" role="tab" type="button">
                    <span class="material-symbols-outlined">manage_accounts</span>
                    Directorio de usuarios
                </button>
            </nav>
        </div>

        <!-- PANEL: BIBLIOTECARIOS -->
        <section class="admin-panel is-active" data-panel="bibliotecarios">
            <div class="table-scroll">
                <div id="tabla-bibliotecarios"></div>
            </div>
        </section>

        <!-- PANEL: CUENTAS PENDIENTES (signup) -->
        <section class="admin-panel" data-panel="pendientes">
            <div class="table-scroll">
                <div id="tabla-pendientes"></div>
            </div>
        </section>

        <!-- PANEL: SOLICITUDES DE CAMBIO -->
        <section class="admin-panel" data-panel="solicitudes">
            <div class="table-scroll">
                <div id="tabla-solicitudes"></div>
            </div>
        </section>

        <section class="admin-panel" data-panel="usuarios">
            <div class="directory-toolbar">
                <input id="directory-search" type="search" placeholder="Buscar nombre, usuario o correo" aria-label="Buscar usuarios">
                <select id="directory-role" aria-label="Filtrar por rol">
                    <option value="">Todos los roles</option>
                    <option value="1">Estudiante</option>
                    <option value="2">Docente</option>
                    <option value="3">Bibliotecario</option>
                    <option value="4">Administrador</option>
                </select>
                <select id="directory-status" aria-label="Filtrar por estado">
                    <option value="">Activos e inactivos</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
            </div>
            <div class="table-scroll">
                <div id="tabla-usuarios"></div>
            </div>
            <div class="directory-pagination">
                <button class="btn-outline" id="directory-prev" type="button">Anterior</button>
                <span id="directory-page-label" aria-live="polite">Página 1</span>
                <button class="btn-outline" id="directory-next" type="button">Siguiente</button>
            </div>
        </section>

    </div>
</main>

<!-- MODAL CREAR USUARIO -->
<div class="modal-overlay" id="modalRegistro" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Crear Nuevo Usuario</h3>
            <button type="button" class="modal-close" id="btnCerrarModal" aria-label="Cerrar">&times;</button>
        </div>

        <form id="formBibliotecario" class="form-grid" enctype="multipart/form-data">

            <div class="form-group">
                <label for="rolInput">Tipo de usuario</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">badge</span>
                    <select id="rolInput" name="rol" required>
                        <option value="estudiante">Estudiante</option>
                        <option value="profesor">Profesor</option>
                        <option value="bibliotecario" selected>Bibliotecario</option>
                        <option value="administrador">Administrador</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="docInput">Documento de Identidad</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">badge</span>
                    <input type="text" id="docInput" name="documento" placeholder="Número de documento" required>
                </div>
            </div>

            <div class="form-group">
                <label for="fotoDocInput">Foto del documento (opcional)</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">image</span>
                    <input type="file" id="fotoDocInput" name="foto_documento" accept="image/jpeg,image/png,image/webp">
                </div>
                <img id="docPhotoPreview" class="doc-photo-preview" alt="Vista previa del documento">
            </div>

            <div class="form-grid form-grid--2">
                <div class="form-group">
                    <label for="nombreInput">Nombre y Apellido</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">person</span>
                        <input type="text" id="nombreInput" name="nombre_apellido" placeholder="Nombre completo" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="usuarioInput">Nombre de Usuario</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">account_circle</span>
                        <input type="text" id="usuarioInput" name="nombre_usuario" placeholder="Ej: juan.perez" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="emailInput">Correo Institucional</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">alternate_email</span>
                    <input type="email" id="emailInput" name="correo_institucional" placeholder="correo@institucional.edu" required>
                </div>
            </div>

            <div class="form-grid form-grid--2">
                <div class="form-group">
                    <label for="passInput">Contraseña</label>
                    <div class="input-wrapper password-wrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input type="password" id="passInput" name="password" placeholder="Mínimo 8 caracteres" required minlength="8" autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="passInput" aria-label="Mostrar contraseña">
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
                    <label for="passConfirmInput">Confirmar Contraseña</label>
                    <div class="input-wrapper password-wrapper">
                        <span class="material-symbols-outlined input-icon">lock_reset</span>
                        <input type="password" id="passConfirmInput" name="confirm_password" placeholder="Repite la contraseña" required minlength="8" autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="passConfirmInput" aria-label="Mostrar contraseña">
                            <span class="material-symbols-outlined">visibility_off</span>
                        </button>
                    </div>
                    <span class="match-hint" id="matchHint"></span>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 15px;">
                <button type="submit" class="btn-gold" style="width: 100%; justify-content: center;">
                    <span class="material-symbols-outlined">save</span>
                    Guardar Registro
                </button>
            </div>
        </form>
    </div>
</div>

<div class="admin-profile-overlay" id="admin-profile-overlay" role="dialog" aria-modal="true" aria-labelledby="admin-profile-name" hidden>
    <div class="admin-profile-backdrop" id="admin-profile-backdrop"></div>
    <article class="admin-profile-card">
        <button class="admin-profile-close" id="admin-profile-close" type="button" aria-label="Cerrar perfil">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="admin-profile-heading">
            <img id="admin-profile-avatar" src="/Appjoteca/assets/images/default-avatar.png" alt="">
            <div>
                <span id="admin-profile-role" class="admin-profile-role"></span>
                <h2 id="admin-profile-name"></h2>
                <p id="admin-profile-username"></p>
            </div>
        </div>
        <p id="admin-profile-bio" class="admin-profile-bio"></p>
        <dl class="admin-profile-details">
            <div><dt>Correo</dt><dd id="admin-profile-email"></dd></div>
            <div><dt>Documento</dt><dd id="admin-profile-document"></dd></div>
            <div><dt>Estado</dt><dd id="admin-profile-status"></dd></div>
            <div><dt>Registro</dt><dd id="admin-profile-date"></dd></div>
            <div><dt>Reserva pendiente</dt><dd id="admin-profile-reservation"></dd></div>
            <div><dt>Préstamo activo</dt><dd id="admin-profile-loan"></dd></div>
        </dl>
        <section class="admin-profile-document" id="admin-profile-document-preview" hidden>
            <h3>Documento de identidad</h3>
            <img id="admin-profile-document-image" alt="Documento de identidad" hidden>
            <iframe id="admin-profile-document-pdf" title="Documento de identidad en PDF" hidden></iframe>
            <a id="admin-profile-document-link" target="_blank" rel="noopener noreferrer" hidden>Abrir documento</a>
        </section>
        <a id="admin-profile-request" class="admin-profile-request" hidden></a>
        <div class="admin-profile-actions">
            <button id="admin-profile-state" class="btn-outline" type="button"></button>
            <button id="admin-profile-delete" class="btn-danger" type="button">
                <span class="material-symbols-outlined">delete_forever</span> Eliminar usuario
            </button>
        </div>
    </article>
</div>

<?php include '../../shared/layouts/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js"></script>
<script src="../../shared/js/components/navbar.js"></script>
<script src="/Appjoteca/shared/js/components/notifications.js"></script>
<script src="/Appjoteca/shared/js/components/reading-summary.js" defer></script>
<script src="../../shared/js/global.js"></script>
<script src="administrador.js"></script>

<script src="/Appjoteca/shared/js/register-sw.js"></script>
</body>
</html>