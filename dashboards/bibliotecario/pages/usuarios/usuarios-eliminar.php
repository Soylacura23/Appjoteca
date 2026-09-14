<?php
require_once __DIR__ . '/../../../../backend/config/auth.php';
require_once __DIR__ . '/../../../../backend/Database/conexion.php';

require_once __DIR__ . '/../../../../backend/config/verify-csrf.php';

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

        $sql = $connection->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
        $sql->bind_param('i', $id_usuario);
        
        if ($sql->execute()){

            if ($sql->affected_rows > 0) {
                echo json_encode(['ok' => true]);
            } else {
                echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']);
            }
        };

        $sql->close();
        $connection->close();
    }
} else{
    
    http_response_code(405);
    header('Allow: POST');

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE );
    exit();

};
