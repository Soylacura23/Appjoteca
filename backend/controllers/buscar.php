<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../models/busqueda.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$q = trim($_GET['q'] ?? '');
$limit = (int) ($_GET['limit'] ?? 8);

if (mb_strlen($q) < 2) {
    echo json_encode(['ok' => true, 'resultados' => []]);
    exit();
}

if (mb_strlen($q) > 80) {
    $q = mb_substr($q, 0, 80);
}

$rol = (int) ($_SESSION['rol'] ?? 0);
$busqueda = new Busqueda($connection, $limit);
$resultados = [];

// Todos pueden buscar libros
$resultados = array_merge($resultados, $busqueda->libros($q));

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