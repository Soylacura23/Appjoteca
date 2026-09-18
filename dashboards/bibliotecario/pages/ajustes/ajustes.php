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

    <main id="main-content" class="main-content">

        <header>
            <h1 class="headline-xl text-primary">Configuración de la Cuenta</h1>
            <p class="text-body label">Administre la información de su cuenta y protocolos de seguridad</p>
        </header>

        <section class="information">

            <div class="two-grid-content">

                <div class="personal-info card">

                    <div class="main-info">
                        <div id="image">
                            <img>
                        </div>
                        <div class="info">
                            <h2 class="headline-md white-text">Información Personal</h1>
                            <p>Modifique los detalles públicos de su perfil de bibliotecario</p>
                        </div>
                    </div>

                    <form>
                        <div>
                            <label for="name">Nombre completo</label>
                            <input id="name" type="text" placeholder="Ejemplo: Simón Montoya" maxlength="50">
                        </div>
                        <div>
                            <label for="bio">Biografía</label>
                            <textarea id="bio" name="Biografía" rows="7" placeholder="Trabajo aquí hace... Mi libro favorito es..." maxlength="500">

                            </textarea>
                        </div>

                        <div class="send-button">
                            <button id="reset" type="button">Descartar</button>
                            <button id="save" type="button">Guardar Cambios</button>

                        </div>
                    </form>


                </div>

                <div class="security card">
                    <h3 class="text-body white-text">Seguridad</h3>

                    <div id="email">
                        <span class="text-sm">Email Institucional</span>
                        <div class="editable-input">
                            <input type="text" placeholder="nombre.apellido@iemanueljbetancur.edu.co" readonly value="Correo Electrónico" id="emailblocked" class="infoinput">
                            <button type="button" class="edit" data-active-text="Guardar">editar</button>
                        </div>
                    </div>
                    <div id="Password">
                        <span class="text-sm">Contraseña</span>
                        <div class="editable-input">
                            <input type="text"  readonly value= "*******" id="passwordblocked" class="infoinput">
                            <button type="button" class="edit" data-active-text="Actualizar">Actualizar</button>
                        </div>

                    </div>
                    <p>último cambio: <span id="time"></span></p>

                    <div class="authentication">
                        <div>
                            <h4 class="text-sm text-tertiary">Autenticación en dos pasos</h4>
                            <p>Protección más segura</p>
                        </div>
                        <button type="button" class="authentique lever"></button>
                    </div>

                </div>

                <div class="notifications card">
                    <h3 class="text-body white-text">Notificaciones y alertas</h3>
                    <div class="summary">
                        <div>
                        
                        <p>Resumen diario de reservas</p> 
                        </div>
                        <button class="summary-button lever"></button>

                    </div>
                    <div class="alerts">
                        <div>
                        
                        <p>Alertas de libros vencidos</p> 
                        </div>
                        <button class="alert-button lever"></button>

                    </div>
                    <div class="logs">
                        <div>
                        
                        <p>Logs de actividad del sistema</p> 
                        </div>
                        <button class="log-button lever"></button>

                    </div>

                </div>

            </div>

            <div class="danger">
                <div class="danger-zone">
                    <div>
                        <h4 class="text-body error">Zona crítica</h4>
                        <p class="text-xs text-outline">La desactivación de la cuenta es permanente</p>
                    </div>
                    <button type="button" class="remove">
                        <span class="error text-xs">
                        Darse de baja
                        </span>
                    </button>

                </div>
            </div>
        </section>

        

<!-- Footer -->
<?php include '../../../../shared/layouts/footer.php'; ?>

        
</main>

<script src="../../../../shared/js/global.js"></script>
    <script src="ajustes.js"></script>

    
</body>
</html>