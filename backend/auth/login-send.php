<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/verify-csrf.php';

require("../Database/conexion.php");
require_once __DIR__ . '/../helpers/RateLimiter.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

if (!empty($_POST['middle_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo iniciar sesión.']);
    exit;
}

$limiter = new RateLimiter($connection, 10, 900);
if (!$limiter->check('login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Demasiados intentos. Inténtalo más tarde.']);
    exit;
}

if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['remember_me'])) {
    $token_cookie = $_COOKIE['remember_me'];

    $sql_cookie = "SELECT id_usuario, nombre_usuario, nombre_apellido, id_rol, foto_perfil, documento
                   FROM usuarios WHERE remember_token = ? AND estado = '1'";
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
        setcookie("remember_me", "", [
            'expires' => time() - 3600,
            'path' => '/Appjoteca',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_input   = is_string($_POST['usuario'] ?? null) ? trim($_POST['usuario']) : '';
    $pass_input   = is_string($_POST['contrasena'] ?? null) ? $_POST['contrasena'] : '';



    $sql = "SELECT id_usuario, nombre_usuario, nombre_apellido, id_rol, foto_perfil,
                   documento, password, estado
            FROM usuarios WHERE nombre_usuario = ? OR correo_institucional = ?";
    $query = $connection->prepare($sql);
    $query->bind_param("ss", $user_input, $user_input);
    $query->execute();
    
    $resultado = $query->get_result();

    if ($resultado->num_rows < 1) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El usuario o la contraseña no son correctos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $usuario_db = $resultado->fetch_assoc();

    if (isset($usuario_db['estado']) && strtolower($usuario_db['estado']) === 'pendiente') {
        echo json_encode([
            'status' => 'error',
            'message' => 'El usuario o la contraseña no son correctos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

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

            setcookie("remember_me", $token, [
                'expires' => $expiracion,
                'path' => '/Appjoteca',
                'secure' => true,       
                'httponly' => true,      
                'samesite' => 'Lax'
            ]);
        }

        $destinos = [
            1 => '../../dashboards/estudiante/index.php',
            2 => '../../dashboards/docente/index.php',
            3 => '../../dashboards/bibliotecario/index.php',
            4 => '../../dashboards/Administrador/index.php'
        ];

        $rol = (int) $usuario_db['id_rol'];
        $url_destino = $destinos[$rol] ?? '../../index.php';
        $return_to = $_POST['return_to'] ?? '';

        if ($return_to === 'catalog') {
            $url_destino = $rol === 3
                ? '../../dashboards/bibliotecario/pages/inventario/inventario.php'
                : '../../pages/biblioteca-digital/index.php';
        } elseif ($return_to === 'history' && in_array($rol, [1, 2], true)) {
            $url_destino = '../../pages/history/historial.php';
        }

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
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}
?>