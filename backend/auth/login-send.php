<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/verify-csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require("../Database/conexion.php"); 

if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['remember_me'])) {
    $token_cookie = $_COOKIE['remember_me'];

    $sql_cookie = "SELECT * FROM usuarios WHERE remember_token = ?";
    $query_cookie = $connection->prepare($sql_cookie);

    $query_cookie->bind_param("s", $token_cookie);

    $query_cookie->execute();

    $resultado_cookie = $query_cookie->get_result();

    if ($resultado_cookie->num_rows === 1) {
        $usuario_db = $resultado_cookie->fetch_assoc();

        session_regenerate_id(true);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['usuario_id'] = $usuario_db['id_usuario'];
        $_SESSION['usuario'] = $usuario_db['nombre_usuario'];
        $_SESSION['nombre'] = $usuario_db['nombre_apellido'];
        $_SESSION['rol'] = $usuario_db['id_rol'];
        $_SESSION['foto_perfil'] = $usuario_db['foto_perfil'];
        $_SESSION['documento'] = $usuario_db['documento'];

    } else {
        setcookie("remember_me", "", time() - 3600, "/");
    }
}


header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_input   = $_POST['usuario'] ?? ''; 
    $pass_input   = $_POST['contrasena'] ?? ''; 



    $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? OR correo_institucional = ?";
    $query = $connection->prepare($sql);
    $query->bind_param("ss", $user_input, $user_input);
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

            if (isset($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                $expiracion = time() + (86400 * 30); 

                $sql_update_token = "UPDATE usuarios SET remember_token = ? WHERE id_usuario = ?";

                $query_update = $connection->prepare($sql_update_token);

                $query_update->bind_param("si", $token, $usuario_db['id_usuario']);

                $query_update->execute();

                setcookie(
                    "remember_me",       
                    $token,              
                    $expiracion,         
                    "/",              
                    "",                 
                    true,                
                    true                 
                );
            }

            

            $destinos = [
                1 => '../../dashboards/estudiante/index.php',
                2 => '../../dashboards/docente/index.php',
                3 => '../../dashboards/bibliotecario/index.php',
                4 => '../../dashboards/Administrador/index.php'
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
            'message' => 'El usuario o la contraseña no son correctos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'El usuario o la contraseña no son correctos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}
?>