<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Restringido | AppJoteca</title>
    <base href="/Appjoteca/errors/403/">
    
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="403.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>

    
</head>
<body class="error-page">

    <header class="error-header">
            <a href="../../index.php" class="logo-link">
    <img src="../../shared/images/logo-appjoteca.svg" alt="AppJoteca" class="logo-img" style="height: 38px; width: auto;">
</a>
        <a href="/" class="btn-discreet">
            <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
            Inicio
        </a>
    </header>

    <!-- Partículas dispersas (estilo barrera/seguridad) -->
    <div class="particles-container">
        <div class="particle" style="top: 22%; left: 18%; width: 6px; height: 6px;"></div>
        <div class="particle neon" style="top: 70%; left: 22%; width: 4px; height: 4px;"></div>
        <div class="particle shield-dot" style="top: 35%; left: 78%;"></div>
        <div class="particle" style="top: 60%; left: 82%; width: 8px; height: 8px;"></div>
        <div class="particle neon" style="top: 18%; left: 62%; width: 3px; height: 3px;"></div>
        <div class="particle shield-dot" style="top: 75%; left: 12%;"></div>
    </div>

    <!-- Contenido Central -->
    <main class="error-content">
        <!-- 403 Gigante de fondo -->
        <div class="huge-403">403</div>
        
        <!-- Ilustración SVG: Libro cerrado + Candado/Anillo de seguridad -->
        <div class="illustration-wrapper">
            <svg class="svg-glow" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <!-- Grupo del Libro Cerrado -->
                <g id="closed-book" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <!-- Cubierta del libro cerrado (Isométrico tech) -->
                    <path d="M25 35 L50 22 L75 35 L75 65 L50 78 L25 65 Z" stroke="var(--blue-accent)" fill="rgba(8, 8, 8, 0.85)"/>
                    <!-- Lomo del libro -->
                    <path d="M25 35 L25 65" stroke="var(--blue-accent)" stroke-width="3"/>
                    <!-- Páginas cerradas (borde lateral) -->
                    <path d="M75 38 L50 50 L25 38" stroke="rgba(226, 226, 226, 0.2)"/>
                    <path d="M75 62 L50 74 L25 62" stroke="rgba(226, 226, 226, 0.15)"/>
                    <path d="M50 50 L50 74" stroke="rgba(30, 96, 145, 0.4)"/>
                </g>

                <!-- Elemento de Seguridad / Candado Tecnológico -->
                <g id="digital-lock" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <!-- Anillo/Barrera de protección neón -->
                    <ellipse cx="50" cy="48" rx="38" ry="20" stroke="var(--neon-green)" stroke-width="1.5" stroke-dasharray="6 4" opacity="0.8"/>
                    
                    <!-- Icono de Candado Minimalista Flotante -->
                    <g transform="translate(42, 36)">
                        <!-- Cuerpo del candado -->
                        <rect x="2" y="10" width="12" height="10" rx="2" fill="var(--surface-container-high)" stroke="var(--neon-green)" stroke-width="1.5"/>
                        <!-- Arco del candado (Cerrado) -->
                        <path d="M5 10 V6 A3 3 0 0 1 11 6 V10" stroke="var(--neon-green)" stroke-width="1.5"/>
                        <!-- Punto central -->
                        <circle cx="8" cy="15" r="1" fill="var(--neon-green)"/>
                    </g>
                </g>
            </svg>
        </div>

        <!-- Mensaje -->
        <p class="message">Este recurso existe, pero no tienes permiso para acceder a él.</p>

        <!-- Botones de Acción -->
        <div class="actions">
            <button class="btn btn-primary" onclick="window.location.href='/'">Volver al inicio</button>
            <button class="btn btn-secondary" onclick="window.location.href='/catalogo'">Ir al catálogo</button>
        </div>
    </main>

    
    <script src="403.js"></script>
</body>
</html>