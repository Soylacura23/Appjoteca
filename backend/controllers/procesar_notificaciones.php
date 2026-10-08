<?php
/**
 * GET  ?accion=listar&limite=5&offset=0
 * GET  ?accion=contar
 * POST accion=marcar_una  + id_notificacion
 * POST accion=marcar_todas
 * POST accion=eliminar    + id_notificacion
 */
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../models/notificacion.php';

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$id_usuario = (int) $_SESSION['usuario_id'];
$modelo     = new Notificacion($connection);
$metodo     = $_SERVER['REQUEST_METHOD'];
$accion     = $_REQUEST['accion'] ?? '';

// ── LISTAR
if ($metodo === 'GET' && $accion === 'listar') {
    $limite = (int) ($_GET['limite'] ?? 5);
    $offset = (int) ($_GET['offset'] ?? 0);

    if ($limite > 50) {
        $limite = 50;
    }

    $lista  = $modelo->listar($id_usuario, $limite, $offset);
    $total  = $modelo->contar($id_usuario);
    $noLei  = $modelo->contarNoLeidas($id_usuario);

    echo json_encode([
        'ok'         => true,
        'items'      => $lista,
        'total'      => $total,
        'no_leidas'  => $noLei,
        'offset'     => $offset,
        'limite'     => $limite,
        'hay_mas'    => ($offset + count($lista)) < $total,
    ]);
    exit;
}

// ── CONTAR (badge)
if ($metodo === 'GET' && $accion === 'contar') {
    echo json_encode([
        'ok'        => true,
        'no_leidas' => $modelo->contarNoLeidas($id_usuario),
        'total'     => $modelo->contar($id_usuario),
    ]);
    exit;
}

// ── MARCAR UNA
if ($metodo === 'POST' && $accion === 'marcar_una') {
    $id = (int) ($_POST['id_notificacion'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID inválido']);
        exit;
    }

    $modelo->marcarLeida($id, $id_usuario);

    echo json_encode([
        'ok'        => true,
        'no_leidas' => $modelo->contarNoLeidas($id_usuario),
    ]);
    exit;
}

// ── MARCAR TODAS
if ($metodo === 'POST' && $accion === 'marcar_todas') {
    $modelo->marcarTodasLeidas($id_usuario);

    echo json_encode([
        'ok'        => true,
        'no_leidas' => 0,
    ]);
    exit;
}

// ── ELIMINAR
if ($metodo === 'POST' && $accion === 'eliminar') {
    $id = (int) ($_POST['id_notificacion'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID inválido']);
        exit;
    }

    $ok = $modelo->eliminar($id, $id_usuario);

    echo json_encode([
        'ok'        => $ok,
        'no_leidas' => $modelo->contarNoLeidas($id_usuario),
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Acción no válida']);