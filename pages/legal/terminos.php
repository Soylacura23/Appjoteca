<?php
require_once __DIR__ . '/../../backend/config/security.php';
require_once __DIR__ . '/../../backend/helpers/current_user.php';
$publicUserRole = appCurrentUserRole();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index,follow">
    <title>Términos de Uso · AppJoteca</title>

    <?php include_once '/Appjoteca/shared/layouts/anti-fouc.php'; ?>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="legal.css">
    <?php if ($publicUserRole): ?>
        <link rel="stylesheet" href="../../shared/css/components/navbar.css">
        <link rel="stylesheet" href="../../shared/css/components/notifications.css">
        <link rel="stylesheet" href="/Appjoteca/shared/css/components/topbar-search.css">
    <?php endif; ?>
    <?php require __DIR__ . '/../../shared/layouts/favicon.php'; ?>
</head>
<body class="legal-page <?= $publicUserRole ? 'authenticated-public-page' : '' ?>">
    <?php include __DIR__ . '/../../shared/layouts/public-user-header.php'; ?>
    <?php if (!$publicUserRole): ?>
    <header class="legal-header">
        <a href="../../index.php">AppJoteca</a>
    </header>
    <?php endif; ?>
    <main class="legal-main">
        <p class="legal-eyebrow">Información legal</p>
        <h1>Términos de Uso</h1>
        <div class="legal-notice">
            <strong>Contenido en preparación</strong>
            <p>Los términos de uso de AppJoteca están en proceso de elaboración y revisión institucional. Esta página es pública y se actualizará cuando el documento oficial esté disponible.</p>
        </div>
    </main>
    <?php include __DIR__ . '/../../shared/layouts/footer.php'; ?>
    <?php if ($publicUserRole): ?>
        <script src="../../shared/js/components/navbar.js"></script>
        <script src="/Appjoteca/shared/js/components/notifications.js"></script>
        <script src="../../shared/js/global.js"></script>
        <script src="/Appjoteca/shared/js/components/topbar-search.js" defer></script>
    <?php endif; ?>
    <script src="/Appjoteca/shared/js/register-sw.js"></script>
</body>
</html>
