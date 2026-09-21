<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Recurso Perdido | AppJoteca</title>
    
    <base href="/Appjoteca/errors/404/">
    
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="404.css">
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

    <div class="particles-container">
        <div class="particle" style="top: 20%; left: 15%; width: 6px; height: 6px;"></div>
        <div class="particle neon" style="top: 75%; left: 25%; width: 4px; height: 4px;"></div>
        <div class="particle line" style="top: 30%; left: 80%;"></div>
        <div class="particle" style="top: 65%; left: 85%; width: 8px; height: 8px;"></div>
        <div class="particle neon" style="top: 15%; left: 60%; width: 3px; height: 3px;"></div>
        <div class="particle line" style="top: 80%; left: 10%; transform: rotate(45deg);"></div>
    </div>

    <main class="error-content">
        <div class="huge-404">404</div>
        
        <div class="book-wrapper">
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <g fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M50 75 L20 60 L20 25 L50 40 Z" stroke="var(--blue-accent)" />
                    <path d="M50 75 L80 60 L80 25 L50 40 Z" stroke="var(--blue-accent)" />
                    <path d="M50 40 L50 75" stroke="var(--neon-green)" />
                    <path d="M28 33 L45 41" stroke="rgba(226,226,226,0.3)" />
                    <path d="M72 33 L55 41" stroke="rgba(226,226,226,0.3)" />
                    <circle cx="50" cy="20" r="2" fill="var(--neon-green)" stroke="none" />
                </g>
            </svg>
        </div>

        <p class="message">Parece que este recurso se perdió entre los estantes.</p>

        <div class="actions">
            <button class="btn btn-primary" id="inicio" onclick="window.location.href='/'">Volver al inicio</button>
        </div>
    </main>

    <script src="404.js"></script>

</body>
</html>