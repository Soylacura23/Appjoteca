<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../Database/conexion.php';

$action = $_POST['action'] ?? '';


if ($action === 'listar') {

    $offset = intval($_POST['offset'] ?? 0);
    $limit  = 15;

    if ($offset < 0) $offset = 0;

    $sqlTotal = "SELECT COUNT(*) AS total FROM comentarios";
    $queryTotal = $connection->prepare($sqlTotal);
    $queryTotal->execute();
    $resTotal = $queryTotal->get_result();
    $total = 0;
    if ($resTotal && $fila = $resTotal->fetch_assoc()) {
        $total = (int)$fila['total'];
    }
    $queryTotal->close();

    $sql = "SELECT id_comentario, nombre, tipo_comentario, comentario, correo
            FROM comentarios
            ORDER BY id_comentario DESC
            LIMIT ? OFFSET ?";
    $query = $connection->prepare($sql);
    $query->bind_param("ii", $limit, $offset);
    $query->execute();
    $result = $query->get_result();

    $comentarios = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $comentarios[] = $row;
        }
    }
    $query->close();

    $hay_mas = ($offset + $limit) < $total;

    echo json_encode([
        'status'      => 'success',
        'comentarios' => $comentarios,
        'total'       => $total,
        'hay_mas'     => $hay_mas,
        'offset'      => $offset,
        'limit'       => $limit
    ]);
    exit;
}

if ($action === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
        exit;
    }

    $sql = "DELETE FROM comentarios WHERE id_comentario = ?";
    $query = $connection->prepare($sql);
    $query->bind_param("i", $id);
    $query->execute();

    if ($query->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Comentario eliminado.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró el comentario.']);
    }
    $query->close();
    exit;
}


echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);