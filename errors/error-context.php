<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_dashboard_urls = [
    1 => '/Appjoteca/dashboards/estudiante/index.php',
    2 => '/Appjoteca/dashboards/docente/index.php',
    3 => '/Appjoteca/dashboards/bibliotecario/index.php',
    4 => '/Appjoteca/dashboards/Administrador/index.php',
];

$error_role = isset($_SESSION['usuario_id']) ? (int) ($_SESSION['rol'] ?? 0) : 0;
$error_return_url = $error_dashboard_urls[$error_role] ?? '/Appjoteca/index.php';
$error_return_label = $error_role > 0 ? 'Ir a mi panel' : 'Volver al inicio';
