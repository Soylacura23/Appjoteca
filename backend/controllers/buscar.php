<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../models/busqueda.php';

$q = trim($_GET['q'] ?? '');
$limit = (int) ($_GET['limit'] ?? 8);

$longitud = function_exists('mb_strlen') ? mb_strlen($q, 'UTF-8') : strlen($q);
if ($longitud < 2) {
    echo json_encode(['ok' => true, 'resultados' => []]);
    exit();
}

if ($longitud > 80) {
    $q = function_exists('mb_substr')
        ? mb_substr($q, 0, 80, 'UTF-8')
        : substr($q, 0, 80);
}

$rolSesion = (int) ($_SESSION['rol'] ?? 0);
$idSesion = (int) ($_SESSION['usuario_id'] ?? 0);
$rol = 0;
if ($idSesion > 0) {
    $consultaUsuario = $connection->prepare('SELECT id_rol, estado FROM usuarios WHERE id_usuario = ? LIMIT 1');
    $consultaUsuario->bind_param('i', $idSesion);
    $consultaUsuario->execute();
    $usuarioActual = $consultaUsuario->get_result()->fetch_assoc();
    $consultaUsuario->close();
    if (!$usuarioActual || $usuarioActual['estado'] !== '1' || (int) $usuarioActual['id_rol'] !== $rolSesion) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'La sesión ya no está activa.']);
        exit();
    }
    $rol = $rolSesion;
}
$busqueda = new Busqueda($connection, $limit);
$resultados = [];

if (in_array($rol, [1, 2, 3, 4], true)) {
    $resultados = array_merge($resultados, $busqueda->libros($q, $rol));
}

if ($rol === 3) {
    $resultados = array_merge(
        $resultados,
        $busqueda->usuarios($q, $rol),
        $busqueda->reservas($q),
        $busqueda->prestamos($q)
    );
} elseif ($rol === 4) {
    $resultados = array_merge(
        $resultados,
        $busqueda->usuarios($q, $rol),
        $busqueda->pendientes($q),
        $busqueda->solicitudes($q),
        $busqueda->comentarios($q),
    );
}

echo json_encode([
    'ok'         => true,
    'q'          => $q,
    'resultados' => $resultados,
]);