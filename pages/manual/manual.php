<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario - AppJoteca</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Noto+Serif:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300,0,0" />

    <link rel="stylesheet" href="../../auth/components/theme.css">
    <!-- Estilos de la página -->
    <link rel="stylesheet" href="manual.css">
    <!-- Topbar compartido -->
    <link rel="stylesheet" href="../../shared/css/components/topbar-landing.css">
    
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="icon" type="image/png" href="../../shared/images/logo-appjoteca.png">

    <!-- PDF.js desde CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
</head>
<body>

<header class="main-header" id="mainHeader">
    <div class="header-container">
        <a href="index.php" class="logo-link">
            <img src="../../shared/images/logo-appjoteca.png" alt="Appjoteca Logo" class="logo-img">
            <span class="logo-text">APPJOTECA</span>
        </a>
        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="../../index.php" class="nav-link">Inicio</a></li>
                <li><a href="../sobre-nosotros/about-us.php" class="nav-link">Sobre Nosotros</a></li>
                <li><a href="manual.php" class="nav-link active">Manual de Usuario</a></li>
            </ul>
        </nav>
        <div class="header-actions">
            <a href="auth/login/login.php" class="btn btn-primary btn-header">
                <span class="material-symbols-outlined">login</span>
                Acceder
            </a>
        </div>
        <button class="mobile-menu-btn" aria-label="Menú">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
</header>

    <!-- CABECERA DE LA PÁGINA -->
    <section class="page-header">
        <div class="page-header-inner">
            <span class="section-label">✦ Documentación ✦</span>
            <h1 class="page-title">Manual de <span class="gradient-text">Usuario</span></h1>
            <p class="page-subtitle">Guía completa para el uso de AppJoteca.</p>
        </div>
    </section>

    <!-- VISOR PDF -->
    <section class="pdf-section">
        <div class="pdf-toolbar">
            <div class="pdf-controls">
                <button id="prevPage" class="pdf-btn" aria-label="Página anterior" disabled>
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <span class="pdf-page-info">
                    Página <span id="currentPage">—</span> de <span id="totalPages">—</span>
                </span>
                <button id="nextPage" class="pdf-btn" aria-label="Página siguiente" disabled>
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>
            <a id="downloadPdf" class="btn btn-primary btn-download" href="#" download>
                <span class="material-symbols-outlined">download</span>
                Descargar PDF
            </a>
        </div>

        <div class="pdf-viewer" id="pdfViewer">
            <div class="pdf-loading" id="pdfLoading">
                <span class="material-symbols-outlined spin">progress_activity</span>
                <p>Cargando manual...</p>
            </div>
            <canvas id="pdfCanvas"></canvas>
        </div>
    </section>

    <?php if (file_exists('../../shared/layouts/footer.php')) include '../../shared/layouts/footer.php'; ?>

    <script src="../../shared/js/menu.js" onerror="this.remove()"></script>
    <script src="manual.js"></script>
</body>
</html>