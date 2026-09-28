<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../config/verify-csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    define('BASE_URL', '/Appjoteca/');

    $sql = "SELECT foto_documento FROM usuarios WHERE id_usuario = ?";
    $query = $connection->prepare($sql);
    $query->bind_param('i', $mi_id);
    $query->execute();
    $usuario = $query->get_result()->fetch_assoc();
    $query->close();

    $foto_perfil = $_SESSION['foto_perfil'] ?? '';
    $foto_actual = !empty($foto_perfil)
        ? BASE_URL . $foto_perfil
        : BASE_URL . 'assets/images/default-avatar.png';

    $documento = $_SESSION['documento'] ?? '';
    $foto_documento = !empty($usuario['foto_documento'])
        ? BASE_URL . $usuario['foto_documento']
        : BASE_URL . 'assets/images/documento_default.jpg';
}

header('Content-Type: application/json; charset=utf-8');

$mi_id  = $_SESSION['usuario_id'] ?? null;
$mi_rol = (int)($_SESSION['rol'] ?? 0);

if (!$mi_id || !in_array($mi_rol, [1, 2, 3, 4])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

define('BASE_URL', '/Appjoteca/');
define('UPLOAD_DIR', __DIR__ . '/../../uploads/profiles/photos/');
define('DEFAULT_AVATAR', 'assets/images/default-avatar.png');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$accion = $_POST['accion'] ?? '';

try {

    // Cambiar nombre de usuario ──
    if ($accion === 'update_username' || isset($_POST['update_username'])) {
        $nuevo = trim($_POST['username'] ?? '');

        if ($nuevo === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre no puede estar vacío']);
            exit;
        }

        if (strlen($nuevo) < 3 || strlen($nuevo) > 30) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener entre 3 y 30 caracteres']);
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $nuevo)) {
            echo json_encode(['success' => false, 'message' => 'Solo letras, números, puntos, guiones y guión bajo']);
            exit;
        }

        $sql = "SELECT nombre_usuario, ultimo_cambio_nombre FROM usuarios WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('i', $mi_id);
        $query->execute();
        $user = $query->get_result()->fetch_assoc();
        $query->close();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        if ($nuevo === $user['nombre_usuario']) {
            echo json_encode(['success' => false, 'message' => 'El nombre es igual al actual']);
            exit;
        }

        // Límite de 7 días
        if (!empty($user['ultimo_cambio_nombre'])) {
            $ultimo = new DateTime($user['ultimo_cambio_nombre']);
            $ahora  = new DateTime();
            $dias   = $ahora->diff($ultimo)->days;

            if ($dias < 7) {
                $restantes = 7 - $dias;
                echo json_encode(['success' => false, 'message' => "Debes esperar {$restantes} días más para cambiar tu nombre."]);
                exit;
            }
        }

        // Verificar que nadie lo tenga
        $sql = "SELECT id_usuario FROM usuarios 
                WHERE (nombre_usuario = ? OR (nombre_usuario_anterior = ? AND fecha_liberacion_nombre > NOW()))
                AND id_usuario != ?";
        $query = $connection->prepare($sql);
        $query->bind_param('ssi', $nuevo, $nuevo, $mi_id);
        $query->execute();
        if ($query->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Ese nombre de usuario no está disponible']);
            $query->close();
            exit;
        }
        $query->close();

        // Guardar el anterior y actualizar
        $sql = "UPDATE usuarios SET 
                    nombre_usuario_anterior = nombre_usuario,
                    fecha_liberacion_nombre = DATE_ADD(NOW(), INTERVAL 30 DAY),
                    nombre_usuario = ?,
                    ultimo_cambio_nombre = NOW()
                WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('si', $nuevo, $mi_id);

        if ($query->execute()) {
            $_SESSION['nombre_usuario'] = $nuevo;
            $_SESSION['usuario'] = $nuevo;
            echo json_encode(['success' => true, 'message' => 'Nombre actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el nombre']);
        }
        $query->close();
        exit;
    }

    // ── 2. Cambiar contraseña ──
    if ($accion === 'update_password' || isset($_POST['update_password'])) {
        $actual  = $_POST['current_password'] ?? '';
        $nueva   = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($actual === '' || $nueva === '' || $confirm === '') {
            echo json_encode(['success' => false, 'message' => 'Faltan campos por llenar']);
            exit;
        }

        if ($nueva !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden']);
            exit;
        }

        if (strlen($nueva) < 8) {
            echo json_encode(['success' => false, 'message' => 'La contraseña nueva debe tener al menos 8 caracteres']);
            exit;
        }

        $sql = "SELECT password FROM usuarios WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('i', $mi_id);
        $query->execute();
        $row = $query->get_result()->fetch_assoc();
        $query->close();

        if (!$row || !password_verify($actual, $row['password'])) {
            echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
            exit;
        }

        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $sql  = "UPDATE usuarios SET password = ? WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('si', $hash, $mi_id);

        if ($query->execute()) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
        }
        $query->close();
        exit;
    }

    // ── 3. Cambiar foto de perfil ──
    if ($accion === 'cambiar_foto') {
        if (!isset($_FILES['nueva_foto']) || $_FILES['nueva_foto']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ninguna imagen o es demasiado pesada']);
            exit;
        }

        $file = $_FILES['nueva_foto'];

        if ($file['size'] > 3 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'La imagen no puede superar 3 MB']);
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
            echo json_encode(['success' => false, 'message' => 'Formato no válido. Solo JPG, PNG o WEBP']);
            exit;
        }

        $ext         = $permitidos[$mime];
        $nombre_arch = 'profile_' . $mi_id . '_' . time() . '.' . $ext;
        $ruta_fisica = UPLOAD_DIR . $nombre_arch;
        $ruta_bd     = 'uploads/profiles/photos/' . $nombre_arch;

        // Obtener foto actual para borrarla después
        $sql = "SELECT foto_perfil FROM usuarios WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('i', $mi_id);
        $query->execute();
        $foto_vieja = $query->get_result()->fetch_assoc()['foto_perfil'] ?? null;
        $query->close();

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $ruta_fisica)) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
            exit;
        }

        $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('si', $ruta_bd, $mi_id);

        if ($query->execute()) {
            // Borrar la foto anterior si no es default
            if ($foto_vieja && strpos($foto_vieja, 'default-avatar') === false) {
                $ruta_borrar = __DIR__ . '/../../' . $foto_vieja;
                if (file_exists($ruta_borrar)) {
                    @unlink($ruta_borrar);
                }
            }
            $_SESSION['foto_perfil'] = $ruta_bd;
            echo json_encode(['success' => true, 'message' => 'Foto actualizada correctamente', 'ruta' => BASE_URL . $ruta_bd]);
        } else {
            @unlink($ruta_fisica);
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }
        $query->close();
        exit;
    }

    // ── 4. Eliminar foto de perfil ──
    if ($accion === 'eliminar_foto') {
        $sql = "SELECT foto_perfil FROM usuarios WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('i', $mi_id);
        $query->execute();
        $foto = $query->get_result()->fetch_assoc()['foto_perfil'] ?? null;
        $query->close();

        if (!$foto || strpos($foto, 'default-avatar') !== false) {
            echo json_encode(['success' => false, 'message' => 'No tienes una foto personalizada']);
            exit;
        }

        $sql = "UPDATE usuarios SET foto_perfil = NULL WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('i', $mi_id);

        if ($query->execute()) {
            $ruta = __DIR__ . '/../../' . $foto;
            if (file_exists($ruta)) {
                @unlink($ruta);
            }
            $_SESSION['foto_perfil'] = null;
            echo json_encode(['success' => true, 'message' => 'Foto eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la foto']);
        }
        $query->close();
        exit;
    }

    // ── 5. Solicitar cambio (email, nombre, documento, eliminacion) ──
    if ($accion === 'solicitar_cambio') {
        $tipo  = $_POST['tipo'] ?? '';
        $valor = trim($_POST['valor_nuevo'] ?? '');

        $tipos_ok = ['email', 'nombre', 'documento', 'eliminacion'];
        if (!in_array($tipo, $tipos_ok)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de solicitud no válido']);
            exit;
        }

        // Admin no necesita solicitar (excepto si quiere eliminarse con confirmación)
        if ($mi_rol === 4 && $tipo !== 'eliminacion') {
            echo json_encode(['success' => false, 'message' => 'Como administrador puedes cambiar esto directamente']);
            exit;
        }

        // Evitar solicitudes duplicadas pendientes
        $sql = "SELECT id_solicitud FROM solicitudes_cambio 
                WHERE id_usuario = ? AND tipo = ? AND estado = 'pendiente'";
        $query = $connection->prepare($sql);
        $query->bind_param('is', $mi_id, $tipo);
        $query->execute();
        if ($query->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya tienes una solicitud pendiente de este tipo']);
            $query->close();
            exit;
        }
        $query->close();

        // Obtener valor actual
        $campo_map = [
            'email'     => 'correo_institucional',
            'nombre'    => 'nombre_apellido',
            'documento' => 'documento',
            'eliminacion' => null
        ];

        $valor_actual = null;
        if ($campo_map[$tipo]) {
            $sql = "SELECT {$campo_map[$tipo]} FROM usuarios WHERE id_usuario = ?";
            $query = $connection->prepare($sql);
            $query->bind_param('i', $mi_id);
            $query->execute();
            $valor_actual = $query->get_result()->fetch_assoc()[$campo_map[$tipo]] ?? null;
            $query->close();
        }

        if ($tipo !== 'eliminacion' && $valor === '') {
            echo json_encode(['success' => false, 'message' => 'Debes indicar el nuevo valor']);
            exit;
        }

        $sql = "INSERT INTO solicitudes_cambio (id_usuario, tipo, valor_actual, valor_nuevo) 
                VALUES (?, ?, ?, ?)";
        $query = $connection->prepare($sql);
        $query->bind_param('isss', $mi_id, $tipo, $valor_actual, $valor);

        if ($query->execute()) {
            echo json_encode(['success' => true, 'message' => 'Solicitud enviada correctamente. Espera la revisión.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar la solicitud']);
        }
        $query->close();
        exit;
    }

    // ── 6. Cambiar datos directos (solo Admin) ──
    if ($accion === 'update_directo' && $mi_rol === 4) {
        $campo = $_POST['campo'] ?? '';
        $valor = trim($_POST['valor'] ?? '');

        $permitidos = ['correo_institucional', 'nombre_apellido', 'documento'];
        if (!in_array($campo, $permitidos)) {
            echo json_encode(['success' => false, 'message' => 'Campo no permitido']);
            exit;
        }

        // Documento puede quedar vacío solo para Admin
        if ($campo !== 'documento' && $valor === '') {
            echo json_encode(['success' => false, 'message' => 'El valor no puede estar vacío']);
            exit;
        }

        if ($campo === 'correo_institucional' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Correo no válido']);
            exit;
        }

        $sql = "UPDATE usuarios SET {$campo} = ? WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('si', $valor, $mi_id);

        if ($query->execute()) {
            if ($campo === 'correo_institucional') $_SESSION['correo'] = $valor;
            if ($campo === 'nombre_apellido') $_SESSION['nombre'] = $valor;
            if ($campo === 'documento') $_SESSION['documento'] = $valor;
            echo json_encode(['success' => true, 'message' => 'Dato actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }
        $query->close();
        exit;
    }

    // ── 7. Eliminar cuenta (solo Admin, directo) ──
    if ($accion === 'eliminar_cuenta' && $mi_rol === 4) {
        $sql = "UPDATE usuarios SET estado = 'eliminado' WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('i', $mi_id);

        if ($query->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cuenta marcada como eliminada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la cuenta']);
        }
        $query->close();
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);

} catch (Exception $e) {
    error_log('configuracion-back error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor', 'debug' => $e->getMessage()]);
}