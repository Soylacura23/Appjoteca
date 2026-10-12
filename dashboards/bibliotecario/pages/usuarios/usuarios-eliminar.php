<?php
require_once __DIR__ . '/../../../../backend/config/auth.php';
require_once __DIR__ . '/../../../../backend/Database/conexion.php';

require_once __DIR__ . '/../../../../backend/config/verify-csrf.php';
requiereRol([3]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json; charset=utf-8');

    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

    if (!$id_usuario) {
        echo json_encode(['ok' => false, 'error' => 'ID de usuario inválido']);
        exit();
    } 
    elseif

    ($id_usuario == (int) $_SESSION['usuario_id']) {
        echo json_encode(['ok' => false, 'error' => 'No puedes eliminar tu propia cuenta']);
        exit();
    }
    else {
        $consulta = $connection->prepare(
            "SELECT id_rol, estado FROM usuarios
             WHERE id_usuario = ? AND estado IN ('1', 'inactivo', '0')
             LIMIT 1"
        );
        $consulta->bind_param('i', $id_usuario);
        $consulta->execute();
        $objetivo = $consulta->get_result()->fetch_assoc();
        $consulta->close();

        if (!$objetivo || !in_array((int) $objetivo['id_rol'], [1, 2], true)) {
            echo json_encode(['ok' => false, 'error' => 'Solo puedes eliminar cuentas de estudiantes o docentes activas/inactivas']);
            exit();
        }

        $sql = $connection->prepare(
            "UPDATE usuarios SET estado = 'eliminado', remember_token = NULL
             WHERE id_usuario = ? AND id_rol IN (1, 2) AND estado IN ('1', 'inactivo', '0')"
        );
        $sql->bind_param('i', $id_usuario);
        
        $eliminado = $sql->execute() && $sql->affected_rows === 1;
        $sql->close();
        $connection->close();
        echo json_encode($eliminado
            ? ['ok' => true]
            : ['ok' => false, 'error' => 'No se pudo eliminar la cuenta']);
        exit();
    }
} else{
    
    http_response_code(405);
    header('Allow: POST');

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE );
    exit();

};
