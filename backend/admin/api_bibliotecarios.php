<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../Database/conexion.php';

$action = $_POST['action'] ?? '';

$rolesPermitidos = [
    'estudiante'    => 1,
    'profesor'      => 2,
    'bibliotecario' => 3,
    'administrador' => 4
];

if ($action === 'listar') {
    $sql = "SELECT id_usuario, documento, nombre_apellido, correo_institucional, fecha_registro
            FROM usuarios
            WHERE id_rol = 3 AND estado = '1'
            ORDER BY id_usuario DESC";

    $query  = $connection->prepare($sql);
    $query->execute();
    $result = $query->get_result();

    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $query->close();
    echo json_encode($data);
    exit;
}

if ($action === 'listar_pendientes') {
    $sql = "SELECT u.id_usuario, u.documento, u.nombre_apellido, u.nombre_usuario,
                   u.correo_institucional, u.foto_documento, u.fecha_registro,
                   r.nombre AS rol_nombre
            FROM usuarios u
            LEFT JOIN roles r ON r.id_rol = u.id_rol
            WHERE u.estado = 'pendiente'
            ORDER BY u.fecha_registro DESC";

    $query  = $connection->prepare($sql);
    $query->execute();
    $result = $query->get_result();

    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $query->close();
    echo json_encode($data);
    exit;
}

if ($action === 'contar_pendientes') {
    $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE estado = 'pendiente'";

    $query  = $connection->prepare($sql);
    $query->execute();
    $result = $query->get_result();

    $total = 0;
    if ($result && $row = $result->fetch_assoc()) {
        $total = (int)$row['total'];
    }
    $query->close();
    echo json_encode(['total' => $total]);
    exit;
}

if ($action === 'aprobar') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
        exit;
    }

    $sql = "UPDATE usuarios SET estado = '1' WHERE id_usuario = ? AND estado = 'pendiente'";
    $query = $connection->prepare($sql);
    $query->bind_param("i", $id);
    $query->execute();

    if ($query->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario aprobado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo aprobar (usuario no encontrado o ya aprobado).']);
    }
    $query->close();
    exit;
}

if ($action === 'rechazar') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
        exit;
    }

    $fotoRelativa = null;
    $sqlFoto = "SELECT foto_documento FROM usuarios WHERE id_usuario = ? AND estado = 'pendiente'";
    $queryFoto = $connection->prepare($sqlFoto);
    $queryFoto->bind_param("i", $id);
    $queryFoto->execute();
    $resFoto = $queryFoto->get_result();

    if ($resFoto && $resFoto->num_rows === 1) {
        $fila = $resFoto->fetch_assoc();
        $fotoRelativa = $fila['foto_documento'] ?? null;
    }
    $queryFoto->close();

    $sql = "DELETE FROM usuarios WHERE id_usuario = ? AND estado = 'pendiente'";
    $query = $connection->prepare($sql);
    $query->bind_param("i", $id);
    $query->execute();

    if ($query->affected_rows > 0) {
        if (!empty($fotoRelativa)) {
            $rutaFisica = __DIR__ . '/../../' . $fotoRelativa;
            if (is_file($rutaFisica)) {
                @unlink($rutaFisica);
            }
        }
        echo json_encode(['status' => 'success', 'message' => 'Solicitud rechazada y eliminada.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo rechazar (usuario no encontrado).']);
    }
    $query->close();
    exit;
}

if ($action === 'crear') {
    $rolRecibido      = trim($_POST['rol'] ?? '');
    $documento        = trim($_POST['documento'] ?? '');
    $nombre_apellido  = trim($_POST['nombre_apellido'] ?? '');
    $nombre_usuario   = trim($_POST['nombre_usuario'] ?? '');
    $correo           = trim($_POST['correo_institucional'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!array_key_exists($rolRecibido, $rolesPermitidos)) {
        echo json_encode(['status' => 'error', 'message' => 'El rol seleccionado no es válido.']);
        exit;
    }

    if ($documento === '' || $nombre_apellido === '' || $nombre_usuario === '' || $correo === '' || $password === '') {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'El correo electrónico no es válido.']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden.']);
        exit;
    }

    if (strlen($password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener al menos 8 caracteres.']);
        exit;
    }

    $sqlCheck = "SELECT id_usuario FROM usuarios
                 WHERE nombre_usuario = ? OR correo_institucional = ? OR documento = ?
                 LIMIT 1";
    $queryCheck = $connection->prepare($sqlCheck);
    if ($queryCheck) {
        $queryCheck->bind_param("sss", $nombre_usuario, $correo, $documento);
        $queryCheck->execute();
        $queryCheck->store_result();

        if ($queryCheck->num_rows > 0) {
            $queryCheck->close();
            echo json_encode(['status' => 'error', 'message' => 'Ya existe un usuario con ese nombre, correo o documento.']);
            exit;
        }
        $queryCheck->close();
    }

    $id_rol          = $rolesPermitidos[$rolRecibido];
    $passHash        = password_hash($password, PASSWORD_BCRYPT);
    $estado          = '1';

    date_default_timezone_set('America/Bogota');
    $fecha_registro  = date("Y-m-d H:i:s");

    $sql = "INSERT INTO usuarios
            (id_rol, nombre_apellido, nombre_usuario, correo_institucional,
             documento, password, estado, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $connection->prepare($sql);

    if (!$query) {
        echo json_encode(['status' => 'error', 'message' => 'Error interno al preparar el registro.']);
        exit;
    }

    $query->bind_param(
        "isssssss",
        $id_rol,
        $nombre_apellido,
        $nombre_usuario,
        $correo,
        $documento,
        $passHash,
        $estado,
        $fecha_registro
    );

    if ($query->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario creado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar el usuario.']);
    }
    $query->close();
    exit;
}

if ($action === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
        exit;
    }

    $sql = "DELETE FROM usuarios WHERE id_usuario = ? AND id_rol = 3";
    $query = $connection->prepare($sql);
    $query->bind_param("i", $id);
    $query->execute();

    if ($query->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Bibliotecario eliminado.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar (no encontrado).']);
    }
    $query->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);