<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../Database/conexion.php';

// Solo admin
$mi_rol = (int)($_SESSION['rol'] ?? 0);
if ($mi_rol !== 4) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$mi_id  = (int)($_SESSION['usuario_id'] ?? 0);
$action = $_POST['action'] ?? '';

$rolesPermitidos = [
    'estudiante'    => 1,
    'profesor'      => 2,
    'bibliotecario' => 3,
    'administrador' => 4
];

try {

// ════════════════════════════════════
// LISTAR BIBLIOTECARIOS
// ════════════════════════════════════
if ($action === 'listar') {
    $sql = "SELECT id_usuario, documento, nombre_apellido, correo_institucional, fecha_registro
            FROM usuarios
            WHERE id_rol = 3 AND estado = '1'
            ORDER BY id_usuario DESC";
    $query = $connection->prepare($sql);
    $query->execute();
    $result = $query->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $query->close();
    echo json_encode($data);
    exit;
}

// ════════════════════════════════════
// LISTAR CUENTAS PENDIENTES (signup)
// ════════════════════════════════════
if ($action === 'listar_pendientes') {
    $sql = "SELECT u.id_usuario, u.documento, u.nombre_apellido, u.nombre_usuario,
                   u.correo_institucional, u.foto_documento, u.fecha_registro,
                   r.nombre AS rol_nombre
            FROM usuarios u
            LEFT JOIN roles r ON r.id_rol = u.id_rol
            WHERE u.estado = 'pendiente'
            ORDER BY u.fecha_registro DESC";
    $query = $connection->prepare($sql);
    $query->execute();
    $result = $query->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $query->close();
    echo json_encode($data);
    exit;
}

// ════════════════════════════════════
// CONTAR PENDIENTES
// ════════════════════════════════════
if ($action === 'contar_pendientes') {
    $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE estado = 'pendiente'";
    $query = $connection->prepare($sql);
    $query->execute();
    $row = $query->get_result()->fetch_assoc();
    $query->close();
    echo json_encode(['total' => (int)($row['total'] ?? 0)]);
    exit;
}

// ════════════════════════════════════
// APROBAR CUENTA PENDIENTE
// ════════════════════════════════════
if ($action === 'aprobar') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }

    $sql = "UPDATE usuarios SET estado = '1' WHERE id_usuario = ? AND estado = 'pendiente'";
    $query = $connection->prepare($sql);
    $query->bind_param('i', $id);
    $query->execute();

    if ($query->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario aprobado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo aprobar (no encontrado o ya aprobado)']);
    }
    $query->close();
    exit;
}

// ════════════════════════════════════
// RECHAZAR CUENTA PENDIENTE
// ════════════════════════════════════
if ($action === 'rechazar') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }

    // Obtener foto para borrarla
    $sql = "SELECT foto_documento FROM usuarios WHERE id_usuario = ? AND estado = 'pendiente'";
    $query = $connection->prepare($sql);
    $query->bind_param('i', $id);
    $query->execute();
    $fila = $query->get_result()->fetch_assoc();
    $query->close();

    $fotoRelativa = $fila['foto_documento'] ?? null;

    $sql = "DELETE FROM usuarios WHERE id_usuario = ? AND estado = 'pendiente'";
    $query = $connection->prepare($sql);
    $query->bind_param('i', $id);
    $query->execute();

    if ($query->affected_rows > 0) {
        if (!empty($fotoRelativa)) {
            $ruta = __DIR__ . '/../../' . $fotoRelativa;
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
        echo json_encode(['status' => 'success', 'message' => 'Solicitud rechazada y eliminada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo rechazar (usuario no encontrado)']);
    }
    $query->close();
    exit;
}

// ════════════════════════════════════
// CREAR USUARIO
// ════════════════════════════════════
if ($action === 'crear') {
    $rolRecibido      = trim($_POST['rol'] ?? '');
    $documento        = trim($_POST['documento'] ?? '');
    $nombre_apellido  = trim($_POST['nombre_apellido'] ?? '');
    $nombre_usuario   = trim($_POST['nombre_usuario'] ?? '');
    $correo           = trim($_POST['correo_institucional'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!isset($rolesPermitidos[$rolRecibido])) {
        echo json_encode(['status' => 'error', 'message' => 'El rol seleccionado no es válido']);
        exit;
    }

    if ($documento === '' || $nombre_apellido === '' || $nombre_usuario === '' || $correo === '' || $password === '') {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios']);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'El correo electrónico no es válido']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden']);
        exit;
    }

    if (strlen($password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener al menos 8 caracteres']);
        exit;
    }

    // Verificar duplicados (actual o nombre reservado)
    $sql = "SELECT id_usuario FROM usuarios
            WHERE nombre_usuario = ?
               OR correo_institucional = ?
               OR documento = ?
               OR (nombre_usuario_anterior = ? AND fecha_liberacion_nombre > NOW())
            LIMIT 1";
    $query = $connection->prepare($sql);
    $query->bind_param('ssss', $nombre_usuario, $correo, $documento, $nombre_usuario);
    $query->execute();
    if ($query->get_result()->num_rows > 0) {
        $query->close();
        echo json_encode(['status' => 'error', 'message' => 'Ya existe un usuario con ese nombre, correo o documento']);
        exit;
    }
    $query->close();

    // Foto del documento (opcional)
    $ruta_foto_doc = null;

    if (isset($_FILES['foto_documento']) && $_FILES['foto_documento']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto_documento'];

        if ($file['size'] > 3 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'La foto del documento no puede superar 3 MB']);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $permitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($permitidos[$mime])) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de foto no válido. Solo JPG, PNG o WEBP']);
            exit;
        }

        $ext = $permitidos[$mime];
        $nombre_arch = 'doc_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dir = __DIR__ . '/../../uploads/profiles/documents/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ruta_fisica = $dir . $nombre_arch;
        $ruta_bd     = 'uploads/profiles/documents/' . $nombre_arch;

        if (!move_uploaded_file($file['tmp_name'], $ruta_fisica)) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar la foto del documento']);
            exit;
        }

        $ruta_foto_doc = $ruta_bd;
    }

    $id_rol = $rolesPermitidos[$rolRecibido];
    $hash   = password_hash($password, PASSWORD_BCRYPT);
    $estado = '1';

    date_default_timezone_set('America/Bogota');
    $fecha = date('Y-m-d H:i:s');

    $sql = "INSERT INTO usuarios
            (id_rol, nombre_apellido, nombre_usuario, correo_institucional,
             documento, foto_documento, password, estado, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $connection->prepare($sql);
    $query->bind_param(
        'issssssss',
        $id_rol,
        $nombre_apellido,
        $nombre_usuario,
        $correo,
        $documento,
        $ruta_foto_doc,
        $hash,
        $estado,
        $fecha
    );

    if ($query->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario creado correctamente']);
    } else {
        if ($ruta_foto_doc) {
            $ruta = __DIR__ . '/../../' . $ruta_foto_doc;
            if (is_file($ruta)) @unlink($ruta);
        }
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar el usuario']);
    }
    $query->close();
    exit;
}

// ════════════════════════════════════
// ELIMINAR BIBLIOTECARIO
// ════════════════════════════════════
if ($action === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }

    // No eliminarse a sí mismo
    if ($id === $mi_id) {
        echo json_encode(['status' => 'error', 'message' => 'No puedes eliminarte a ti mismo']);
        exit;
    }

    $sql = "DELETE FROM usuarios WHERE id_usuario = ? AND id_rol = 3";
    $query = $connection->prepare($sql);
    $query->bind_param('i', $id);
    $query->execute();

    if ($query->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Bibliotecario eliminado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar (no encontrado)']);
    }
    $query->close();
    exit;
}

// ════════════════════════════════════
// LISTAR SOLICITUDES DE CAMBIO
// ════════════════════════════════════
if ($action === 'listar_solicitudes') {
    $sql = "SELECT s.id_solicitud, s.id_usuario, s.tipo, s.valor_actual, s.valor_nuevo,
                   s.estado, s.fecha_solicitud, s.comentario,
                   u.nombre_usuario, u.nombre_apellido, u.correo_institucional
            FROM solicitudes_cambio s
            INNER JOIN usuarios u ON u.id_usuario = s.id_usuario
            WHERE s.estado = 'pendiente'
            ORDER BY s.fecha_solicitud DESC";
    $query = $connection->prepare($sql);
    $query->execute();
    $result = $query->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $query->close();
    echo json_encode($data);
    exit;
}

// ════════════════════════════════════
// RESOLVER SOLICITUD DE CAMBIO
// ════════════════════════════════════
if ($action === 'resolver_solicitud') {
    $id_solicitud = (int)($_POST['id_solicitud'] ?? 0);
    $decision     = $_POST['decision'] ?? ''; // aprobar | rechazar
    $comentario   = trim($_POST['comentario'] ?? '');

    if ($id_solicitud <= 0 || !in_array($decision, ['aprobar', 'rechazar'], true)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        exit;
    }

    // Traer la solicitud
    $sql = "SELECT s.*, u.id_usuario
            FROM solicitudes_cambio s
            INNER JOIN usuarios u ON u.id_usuario = s.id_usuario
            WHERE s.id_solicitud = ? AND s.estado = 'pendiente'
            LIMIT 1";
    $query = $connection->prepare($sql);
    $query->bind_param('i', $id_solicitud);
    $query->execute();
    $sol = $query->get_result()->fetch_assoc();
    $query->close();

    if (!$sol) {
        echo json_encode(['status' => 'error', 'message' => 'Solicitud no encontrada o ya resuelta']);
        exit;
    }

    $nuevo_estado = ($decision === 'aprobar') ? 'aprobada' : 'rechazada';

    // Si se aprueba, aplicar el cambio
    if ($decision === 'aprobar') {
        $tipo  = $sol['tipo'];
        $valor = $sol['valor_nuevo'];
        $uid   = (int)$sol['id_usuario'];

        if ($tipo === 'email') {
            if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['status' => 'error', 'message' => 'El correo nuevo no es válido']);
                exit;
            }
            // Verificar que no esté en uso
            $sql = "SELECT id_usuario FROM usuarios WHERE correo_institucional = ? AND id_usuario != ? LIMIT 1";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $uid);
            $query->execute();
            if ($query->get_result()->num_rows > 0) {
                $query->close();
                echo json_encode(['status' => 'error', 'message' => 'Ese correo ya está en uso']);
                exit;
            }
            $query->close();

            $sql = "UPDATE usuarios SET correo_institucional = ? WHERE id_usuario = ?";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $uid);
            $query->execute();
            $query->close();

        } elseif ($tipo === 'nombre') {
            if ($valor === '') {
                echo json_encode(['status' => 'error', 'message' => 'El nombre no puede estar vacío']);
                exit;
            }
            $sql = "UPDATE usuarios SET nombre_apellido = ? WHERE id_usuario = ?";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $uid);
            $query->execute();
            $query->close();

        } elseif ($tipo === 'documento') {
            // Admin puede dejar documento vacío; otros no deberían llegar aquí vacíos
            $sql = "SELECT id_usuario FROM usuarios WHERE documento = ? AND id_usuario != ? AND documento != '' LIMIT 1";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $uid);
            $query->execute();
            if ($query->get_result()->num_rows > 0) {
                $query->close();
                echo json_encode(['status' => 'error', 'message' => 'Ese documento ya está registrado']);
                exit;
            }
            $query->close();

            $sql = "UPDATE usuarios SET documento = ? WHERE id_usuario = ?";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $uid);
            $query->execute();
            $query->close();

        } elseif ($tipo === 'eliminacion') {
            // Marcar cuenta como eliminada (soft delete)
            $sql = "UPDATE usuarios SET estado = 'eliminado' WHERE id_usuario = ?";
            $query = $connection->prepare($sql);
            $query->bind_param('i', $uid);
            $query->execute();
            $query->close();
        }
    }

    // Actualizar la solicitud
    $sql = "UPDATE solicitudes_cambio
            SET estado = ?, fecha_resolucion = NOW(), id_admin_resuelve = ?, comentario = ?
            WHERE id_solicitud = ?";
    $query = $connection->prepare($sql);
    $query->bind_param('sisi', $nuevo_estado, $mi_id, $comentario, $id_solicitud);
    $query->execute();
    $query->close();

    $msg = ($decision === 'aprobar')
        ? 'Solicitud aprobada y cambio aplicado'
        : 'Solicitud rechazada';

    echo json_encode(['status' => 'success', 'message' => $msg]);
    exit;
}

// Acción desconocida
echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);

} catch (Exception $e) {
    error_log('api_bibliotecarios: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor', 'debug' => $e->getMessage()]);
}