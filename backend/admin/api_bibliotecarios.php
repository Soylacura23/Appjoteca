<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/verify-csrf.php';

require_once __DIR__ . '/backend/Database/conexion.php';

$action = $_POST['action'] ?? '';

// 1. LISTAR BIBLIOTECARIOS
if ($action === 'listar') {
    $sql = "SELECT id_usuario, documento, nombre_apellido, correo_institucional, fecha_registro FROM usuarios WHERE id_rol = 3";
    $result = $connection->query($sql);
    
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode($data);
    exit;
}

// 2. CREAR BIBLIOTECARIO
if ($action === 'crear') {
    $documento = $_POST['documento'] ?? '';
    $nombre_apellido = $_POST['nombre_apellido'] ?? '';
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $correo = $_POST['correo_institucional'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($documento) || empty($nombre_apellido) || empty($nombre_usuario) || empty($correo) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden.']);
        exit;
    }

    $passHash = password_hash($password, PASSWORD_BCRYPT);
    date_default_timezone_set('America/Bogota');
    $fecha_registro = date("Y-m-d H:i:s");
    
    $id_rol = 3;
    $estado = '1';

    $sql = "INSERT INTO usuarios (id_rol, nombre_apellido, nombre_usuario, correo_institucional, documento, password, estado, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la consulta SQL.']);
        exit;
    }

    $stmt->bind_param("isssssss", $id_rol, $nombre_apellido, $nombre_usuario, $correo, $documento, $passHash, $estado, $fecha_registro);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Bibliotecario registrado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar el usuario.']);
    }
    
    $stmt->close();
    exit;
}

// 3. ELIMINAR BIBLIOTECARIO
if ($action === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);
    
    $stmt = $connection->prepare("DELETE FROM usuarios WHERE id = ? AND id_rol = 3");
    
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Error al preparar la eliminación.']);
        exit;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Bibliotecario eliminado.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar.']);
    }
    
    $stmt->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
?>