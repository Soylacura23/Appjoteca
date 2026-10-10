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
    <meta name="robots" content="index,follow">
    <title>Términos de Uso · AppJoteca</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <link rel="stylesheet" href="../../shared/css/theme.css">
    <link rel="stylesheet" href="../../shared/css/components/footer.css">
    <link rel="stylesheet" href="legal.css">
    <?php require __DIR__ . '/../../shared/layouts/favicon.php'; ?>
</head>
<body class="legal-page">
    <header class="legal-header">
        <a href="../../index.php">AppJoteca</a>
    </header>
    <main class="legal-main">
        <p class="legal-eyebrow">Información legal</p>
        <h1>Términos de Uso</h1>
        <div class="legal-notice">
            <strong>Contenido en preparación</strong>
            <p>Los términos de uso de AppJoteca están en proceso de elaboración y revisión institucional. Esta página es pública y se actualizará cuando el documento oficial esté disponible.</p>
        </div>
    </main>
    <?php include __DIR__ . '/../../shared/layouts/footer.php'; ?>
</body>
</html>
