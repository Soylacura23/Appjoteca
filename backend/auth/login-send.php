<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/verify-csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require("../Database/conexion.php"); 


header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $rol_recibido = $_POST['rol'] ?? '';
    $user_input   = $_POST['usuario'] ?? ''; 
    $pass_input   = $_POST['contrasena'] ?? ''; 

    $tabla_roles = [
        'estudiante' => 1,
        'profesor'   => 2,
        'bibliotecario' => 3  
    ];

    if (!array_key_exists($rol_recibido, $tabla_roles)) {
        echo json_encode(['status' => 'error', 'message' => 'El rol seleccionado no es válido.']);
        exit;
    }

    $id_rol = $tabla_roles[$rol_recibido];

    $sql = "SELECT * FROM usuarios WHERE (nombre_usuario = ? OR correo_institucional = ?) AND id_rol = ?";
    $query = $connection->prepare($sql);
    $query->bind_param("ssi", $user_input, $user_input, $id_rol);
    $query->execute();
    
    $resultado = $query->get_result();

    if ($resultado->num_rows === 1) {
        $usuario_db = $resultado->fetch_assoc();

        if (password_verify($pass_input, $usuario_db['password'])) {

            session_regenerate_id(true);

            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            $_SESSION['usuario_id'] = $usuario_db['id_usuario'];
            $_SESSION['usuario'] = $usuario_db['nombre_usuario'];
            $_SESSION['nombre'] = $usuario_db['nombre_apellido'];
            $_SESSION['rol'] = $usuario_db['id_rol'];
            $_SESSION['foto_perfil'] = $usuario_db['foto_perfil'];
            $_SESSION['documento'] = $usuario_db['documento'];
            

            $destinos = [
                1 => '../../dashboards/estudiante/index.php',
                2 => '../../dashboards/docente/index.php',
                3 => '../../dashboards/bibliotecario/index.php'
            ];

            $url_destino = $destinos[$usuario_db['id_rol']] ?? '../../index.php';

            echo json_encode([
                'status' => 'success',
                'redirect' => $url_destino
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Las credenciales no coinciden con el rol seleccionado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } else {
        // Si el usuario no existe para ese rol
        echo json_encode([
            'status' => 'error',
            'message' => 'Las credenciales no coinciden con el rol seleccionado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}
?>