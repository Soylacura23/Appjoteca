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

$rol = (int) ($_SESSION['rol'] ?? 0);
$busqueda = new Busqueda($connection, $limit);
$resultados = [];

// Todos pueden buscar libros
$resultados = array_merge($resultados, $busqueda->libros($q, $rol));

// Bibliotecario
if ($rol === 3) {
    $resultados = array_merge(
        $resultados,
        $busqueda->usuarios($q),
        $busqueda->reservas($q),
        $busqueda->prestamos($q)
    );
}

// Administrador
if ($rol === 4) {
    $resultados = array_merge(
        $resultados,
        $busqueda->comentarios($q),
        $busqueda->usuarios($q)
    );
}

echo json_encode([
    'ok'         => true,
    'q'          => $q,
    'resultados' => $resultados,
]);