<?php
require_once __DIR__ . '/security.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache"); 
header("Expires: 0"); 

if ((int) ($_SESSION['usuario_id'] ?? 0) <= 0) {
    $current = $_SERVER['REQUEST_URI'] ?? '/Appjoteca/';
    $loginUrl = '/Appjoteca/auth/login/login.php?return_to=' . urlencode($current);
    header('Location: ' . $loginUrl);
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mi_rol_num = (int) ($_SESSION['rol'] ?? 0);

function requiereRol(array $roles): void {
    $rol = (int) ($_SESSION['rol'] ?? 0);
    if (!in_array($rol, $roles, true)) {
        http_response_code(403);
        require __DIR__ . '/../../errors/403/403.php';
        exit();
    }
}
?>