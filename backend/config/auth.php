<?php
require_once __DIR__ . '/security.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache"); 
header("Expires: 0"); 

if ((int) ($_SESSION['usuario_id'] ?? 0) <= 0) {

    $current = $_SERVER['REQUEST_URI'] ?? '/Appjoteca/';

    $path = parse_url($current, PHP_URL_PATH);

    $queryString = parse_url($current, PHP_URL_QUERY) ?? '';

    parse_str($queryString, $queryParams);
    
    $context = null;
    $returnId = 0;

    if ($path === '/Appjoteca/pages/biblioteca-digital/index.php'
        || $path === '/Appjoteca/pages/biblioteca-catalogo/catalogo/catalogo.php') {
        $context = 'catalog';
        if ($path === '/Appjoteca/pages/biblioteca-catalogo/catalogo/catalogo.php'
            && filter_var($queryParams['autor'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false) {
            $context = 'author';
            $returnId = (int) $queryParams['autor'];
        }
    } elseif ($path === '/Appjoteca/pages/history/historial.php') {
        $context = 'history';
    } elseif ($path === '/Appjoteca/pages/biblioteca-catalogo/vista-libro/book-view.php') {
        $idLibro = filter_var($queryParams['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($idLibro !== false) {
            $context = 'book';
            $returnId = (int) $idLibro;
        }
    }

    $loginParams = $context === null ? [] : ['return_to' => $context];
    if ($returnId > 0) $loginParams['id'] = $returnId;
    $loginUrl = '/Appjoteca/auth/login/login.php'
        . ($loginParams === [] ? '' : '?' . http_build_query($loginParams));
    header('Location: ' . $loginUrl);
    exit();
}

require_once __DIR__ . '/../Database/conexion.php';
if (!$connection) {
    http_response_code(503);
    exit('El servicio de cuentas no está disponible temporalmente.');
}
$consultaEstado = $connection->prepare('SELECT estado FROM usuarios WHERE id_usuario = ? LIMIT 1');
$idSesion = (int) $_SESSION['usuario_id'];
$consultaEstado->bind_param('i', $idSesion);
$consultaEstado->execute();
$estadoSesion = $consultaEstado->get_result()->fetch_assoc()['estado'] ?? null;
$consultaEstado->close();
if ($estadoSesion !== '1') {
    $_SESSION = [];
    session_destroy();
    header('Location: /Appjoteca/auth/login/login.php');
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