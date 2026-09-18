<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../Database/conexion.php';

// Verificar autenticación del usuario
$mi_id = $_SESSION['usuario_id'] ?? null;

if (!$mi_id) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Sesión no autorizada'
    ]);
    exit;
}

// Lectura de parámetros GET
$page      = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$categoria = trim($_GET['categoria'] ?? 'General');
$busqueda  = trim($_GET['query'] ?? '');
$limite    = 10;
$offset    = ($page - 1) * $limite;

// Construcción de condiciones SQL dinámicas
$condiciones = [];
$tipos       = "";
$valores     = [];

if (!empty($categoria) && strcasecmp($categoria, 'General') !== 0) {
    $condiciones[] = "categoria = ?";
    $tipos        .= "s";
    $valores[]     = $categoria;
}

if (!empty($busqueda)) {
    $condiciones[] = "(titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?)";
    $tipos        .= "sss";
    $termino       = "%{$busqueda}%";
    $valores[]     = $termino;
    $valores[]     = $termino;
    $valores[]     = $termino;
}

$whereSql = !empty($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

// 1. Obtener el total de libros para la paginación
$sqlCount = "SELECT COUNT(*) AS total FROM libros {$whereSql}";
$stmtCount = $connection->prepare($sqlCount);

if (!empty($valores)) {
    $stmtCount->bind_param($tipos, ...$valores);
}

$stmtCount->execute();
$resCount    = $stmtCount->get_result()->fetch_assoc();
$totalLibros = (int)($resCount['total'] ?? 0);
$totalPaginas = max(1, (int)ceil($totalLibros / $limite));

// 2. Consulta de libros paginados
$sqlLibros = "SELECT id, titulo, autor, portada, categoria, isbn 
              FROM libros 
              {$whereSql} 
              ORDER BY id DESC 
              LIMIT ? OFFSET ?";

$stmtLibros = $connection->prepare($sqlLibros);

$tiposPaginados   = $tipos . "ii";
$valoresPaginados = array_merge($valores, [$limite, $offset]);

$stmtLibros->bind_param($tiposPaginados, ...$valoresPaginados);
$stmtLibros->execute();
$resultado = $stmtLibros->get_result();

$libros = [];
while ($row = $resultado->fetch_assoc()) {
    $libros[] = $row;
}

echo json_encode([
    'exito'        => true,
    'paginaActual' => $page,
    'totalPaginas' => $totalPaginas,
    'totalLibros'  => $totalLibros,
    'libros'       => $libros
], JSON_UNESCAPED_UNICODE);
exit;