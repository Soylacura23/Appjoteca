<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../Database/conexion.php';

require_once __DIR__ . '/../config/verify-csrf.php';

$mi_id = $_SESSION['usuario_id'] ?? null;
define('BASE_URL', '/Appjoteca/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // 1. Cambiar nombre de usuario
    if (isset($_POST['update_username'])) {
        $nuevo_nombre = trim($_POST['username'] ?? '');

        if (empty($nuevo_nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre no puede estar vacío']);
            exit;
        }

        $sql = "SELECT nombre_usuario, ultimo_cambio_nombre FROM usuarios WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param("i", $mi_id);
        $query->execute();
        $resultado = $query->get_result();
        $user_data = $resultado->fetch_assoc();

        if ($nuevo_nombre === $user_data['nombre_usuario']) {
            echo json_encode(['success' => false, 'message' => 'El nombre es igual al actual']);
            exit;
        }

        if (!empty($user_data['ultimo_cambio_nombre'])) {
            $ultimo_cambio = new DateTime($user_data['ultimo_cambio_nombre']);
            $ahora = new DateTime();
            $dias_pasados = $ahora->diff($ultimo_cambio)->days;

            if ($dias_pasados < 7) {
                $restantes = 7 - $dias_pasados;
                echo json_encode(['success' => false, 'message' => "Debes esperar {$restantes} días más para cambiar tu nombre."]);
                exit;
            }
        }

        $sql = "UPDATE usuarios SET nombre_usuario = ?, ultimo_cambio_nombre = NOW() WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param("si", $nuevo_nombre, $mi_id);

        if ($query->execute()) {
            $_SESSION['nombre_usuario'] = $nuevo_nombre;
            echo json_encode(['success' => true, 'message' => 'Nombre actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
        }
        exit;
    }

    // 2. Cambiar contraseña
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos por llenar']);
            exit;
        }

        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden']);
            exit;
        }

        $sql = "SELECT password FROM usuarios WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param("i", $mi_id);
        $query->execute();
        $resultado = $query->get_result();
        $user_data = $resultado->fetch_assoc();

        if (!password_verify($current_password, $user_data["password"])) {
            echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
            exit;
        }

        if (strlen($new_password) < 8) {
            echo json_encode(['success' => false, 'message' => 'La contraseña nueva debe tener al menos 8 caracteres']);
            exit;
        }

        $nuevo_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET password = ? WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param("si", $nuevo_hash, $mi_id);

        if ($query->execute()) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos']);
        }
        exit;
    }

    // 3. Cambiar foto de perfil
    if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_foto') {
        if (!isset($_FILES['nueva_foto']) || $_FILES['nueva_foto']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ninguna imagen o es demasiado pesada']);
            exit;
        }

        $file = $_FILES['nueva_foto'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/jpg'  => 'jpg'
        ];

        if (!array_key_exists($mimeType, $allowedMimeTypes)) {
            echo json_encode(['success' => false, 'message' => 'Formato no válido. Solo JPG, PNG o WEBP.']);
            exit;
        }

        $extension = $allowedMimeTypes[$mimeType];
        $nombre_archivo = "profile_" . $mi_id . "_" . time() . "." . $extension;
        $ruta_destino = __DIR__ . '/../../uploads/profiles/photos/' . $nombre_archivo;
        $ruta_guardar = 'uploads/profiles/photos/' . $nombre_archivo;

        if (!move_uploaded_file($file['tmp_name'], $ruta_destino)) {
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo. Verifica los permisos de la carpeta.']);
            exit;
        }

        $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param("si", $ruta_guardar, $mi_id);

        if ($query->execute()) {
            $_SESSION['foto_perfil'] = $ruta_guardar;
            echo json_encode(['success' => true, 'message' => 'Foto actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
    exit;
}


$sql = "SELECT foto_documento, estado, biografia FROM usuarios WHERE id_usuario = ?";
$query = $connection->prepare($sql);
$query->bind_param("i", $mi_id);
$query->execute();
$resultado = $query->get_result();
$usuario = $resultado->fetch_assoc();

$foto_perfil = $_SESSION['foto_perfil'] ?? '';

if (!empty($foto_perfil)) {
    $foto_actual = BASE_URL . $foto_perfil;
} else {
    $foto_actual = BASE_URL . 'assets/images/default-avatar.png';
}

$documento = $_SESSION['documento'] ?? '';
$foto_documento = !empty($usuario['foto_documento'])
    ? BASE_URL . $usuario['foto_documento']
    : BASE_URL . 'assets/images/documento_default.jpg';
