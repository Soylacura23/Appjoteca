<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/DocumentUpload.php';
require_once __DIR__ . '/../models/notificacion.php';

header('Content-Type: application/json; charset=utf-8');

$mi_id  = (int) ($_SESSION['usuario_id'] ?? 0);
$mi_rol = (int) ($_SESSION['rol'] ?? 0);

if ($mi_id <= 0 || !in_array($mi_rol, [1, 2, 3, 4], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

define('BASE_URL', '/Appjoteca/');
define('UPLOAD_DIR', __DIR__ . '/../../uploads/profiles/photos/');
define('DEFAULT_AVATAR', 'assets/images/default-avatar.png');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$accion = is_string($_POST['accion'] ?? null) ? $_POST['accion'] : '';
$archivoTemporalRelativo = null;

try {

    if ($accion === 'update_biography') {
        $biografia = is_string($_POST['biografia'] ?? null) ? trim($_POST['biografia']) : '';
        $longitud = function_exists('mb_strlen') ? mb_strlen($biografia, 'UTF-8') : strlen($biografia);
        if ($longitud > 500) {
            echo json_encode(['success' => false, 'message' => 'La biografía no puede superar 500 caracteres.']);
            exit;
        }

        $query = $connection->prepare('UPDATE usuarios SET biografia = ? WHERE id_usuario = ? AND estado = ?');
        $estadoActivo = '1';
        $query->bind_param('sis', $biografia, $mi_id, $estadoActivo);
        if (!$query->execute() || $query->affected_rows < 0) {
            $query->close();
            echo json_encode(['success' => false, 'message' => 'No se pudo guardar la biografía.']);
            exit;
        }
        $query->close();
        echo json_encode(['success' => true, 'message' => 'Tu biografía se actualizó correctamente.']);
        exit;
    }

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
        $tipo = is_string($_POST['tipo'] ?? null) ? $_POST['tipo'] : '';
        $valor = is_string($_POST['valor_nuevo'] ?? null) ? trim($_POST['valor_nuevo']) : '';
        $contrasena_actual = is_string($_POST['contrasena_actual'] ?? null) ? $_POST['contrasena_actual'] : '';

        $tipos_ok = ['email', 'nombre', 'documento', 'eliminacion'];
        if (!in_array($tipo, $tipos_ok, true)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de solicitud no válido']);
            exit;
        }

        if ($mi_rol === 4 && $tipo !== 'eliminacion') {
            echo json_encode(['success' => false, 'message' => 'Como administrador puedes cambiar esto directamente']);
            exit;
        }

        // Validar contraseña actual
        if ($contrasena_actual === '') {
            echo json_encode(['success' => false, 'message' => 'Debes ingresar tu contraseña actual para continuar.']);
            exit;
        }

        $sql = "SELECT password FROM usuarios WHERE id_usuario = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('i', $mi_id);
        $query->execute();
        $row = $query->get_result()->fetch_assoc();
        $query->close();

        if (!$row || !password_verify($contrasena_actual, $row['password'])) {
            echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
            exit;
        }

        $validator = new Validator();
        if ($tipo === 'email') {
            $validator->requerido($valor, 'correo')
                ->email($valor)
                ->longitud($valor, 5, 60, 'correo');
        } elseif ($tipo === 'nombre') {
            $validator->requerido($valor, 'nombre y apellidos')
                ->longitud($valor, 3, 100, 'nombre y apellidos')
                ->regex($valor, '/^[\p{L}\p{M} .\'-]+$/u', 'El nombre solo puede contener letras, espacios, puntos, apóstrofes y guiones.');
        } elseif ($tipo === 'documento') {
            $validator->requerido($valor, 'documento')
                ->longitud($valor, 1, 25, 'documento')
                ->regex($valor, '/^[0-9-]+$/', 'El documento solo puede contener números y guiones.');
            if (!isset($_FILES['archivo_documento']) || $_FILES['archivo_documento']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Adjunta una foto o PDF del documento nuevo.']);
                exit;
            }
        }

        if ($validator->falla()) {
            echo json_encode(['success' => false, 'message' => $validator->primero(), 'errores' => $validator->errores()], JSON_UNESCAPED_UNICODE);
            exit;
        }

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

        if ($tipo === 'email' || $tipo === 'documento') {
            $campo = $tipo === 'email' ? 'correo_institucional' : 'documento';
            $sql = "SELECT id_usuario FROM usuarios WHERE {$campo} = ? AND id_usuario != ? LIMIT 1";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $mi_id);
            $query->execute();
            $duplicado = $query->get_result()->num_rows > 0;
            $query->close();
            if ($duplicado) {
                echo json_encode(['success' => false, 'message' => $tipo === 'email' ? 'Ese correo ya está registrado.' : 'Ese documento ya está registrado.']);
                exit;
            }
        }

        $archivo = null;
        if ($tipo === 'documento') {
            $archivo = DocumentUpload::store($_FILES['archivo_documento'], 'uploads/profiles/change-requests/');
            $archivoTemporalRelativo = $archivo['path'];
        }

        $sql = "INSERT INTO solicitudes_cambio (id_usuario, tipo, valor_actual, valor_nuevo, archivo_nuevo, archivo_mime)
                VALUES (?, ?, ?, ?, ?, ?)";
        $query = $connection->prepare($sql);
        $rutaArchivo = $archivo['path'] ?? null;
        $tipoArchivo = $archivo['mime'] ?? null;
        $query->bind_param('isssss', $mi_id, $tipo, $valor_actual, $valor, $rutaArchivo, $tipoArchivo);

        if ($query->execute()) {
            $archivoTemporalRelativo = null;
            $idSolicitud = (int) $connection->insert_id;
            $query->close();

            $notificacion = new Notificacion($connection);
            $administradores = $notificacion->obtenerUsuariosPorRol(4);
            if ($administradores !== []) {
                $nombreSolicitante = $_SESSION['nombre'] ?? $_SESSION['nombre_usuario'] ?? 'Usuario';
                $creada = $notificacion->crear(
                    "Nueva solicitud de {$tipo} #{$idSolicitud} de {$nombreSolicitante}.",
                    "/Appjoteca/dashboards/Administrador/index.php?tab=solicitudes&focus_solicitud={$idSolicitud}",
                    $administradores
                );
                if ($creada === false) {
                    error_log('No se pudo crear notificación para la solicitud #' . $idSolicitud);
                }
            }
            echo json_encode(['success' => true, 'message' => 'Solicitud enviada correctamente. Espera la revisión.']);
        } else {
            $query->close();
            if ($archivo !== null && !DocumentUpload::deleteManaged($archivo['path'])) {
                error_log('No se pudo eliminar archivo sin asociar: ' . $archivo['path']);
            }
            $archivoTemporalRelativo = null;
            echo json_encode(['success' => false, 'message' => 'Error al registrar la solicitud']);
        }
        exit;
    }

    // ── 6. Cambiar datos directos (solo Admin) ──
    if ($accion === 'update_directo' && $mi_rol === 4) {
        $campo = is_string($_POST['campo'] ?? null) ? $_POST['campo'] : '';
        $valor = is_string($_POST['valor'] ?? null) ? trim($_POST['valor']) : '';

        $permitidos = ['correo_institucional', 'nombre_apellido', 'documento'];
        if (!in_array($campo, $permitidos, true)) {
            echo json_encode(['success' => false, 'message' => 'Campo no permitido']);
            exit;
        }

        $validator = new Validator();
        if ($campo === 'correo_institucional') {
            $validator->requerido($valor, 'correo')
                ->email($valor)
                ->longitud($valor, 5, 60, 'correo');
        } elseif ($campo === 'nombre_apellido') {
            $validator->requerido($valor, 'nombre y apellidos')
                ->longitud($valor, 3, 100, 'nombre y apellidos')
                ->regex($valor, '/^[\p{L}\p{M} .\'-]+$/u', 'El nombre solo puede contener letras, espacios, puntos, apóstrofes y guiones.');
        } else {
            $validator->requerido($valor, 'documento')
                ->longitud($valor, 1, 25, 'documento')
                ->regex($valor, '/^[0-9-]+$/', 'El documento solo puede contener números y guiones.');
            if (!isset($_FILES['archivo_documento']) || $_FILES['archivo_documento']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Adjunta una foto o PDF del documento nuevo.']);
                exit;
            }
        }

        if ($validator->falla()) {
            echo json_encode(['success' => false, 'message' => $validator->primero(), 'errores' => $validator->errores()], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($campo === 'correo_institucional' || $campo === 'documento') {
            $sql = "SELECT id_usuario FROM usuarios WHERE {$campo} = ? AND id_usuario != ? LIMIT 1";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $mi_id);
            $query->execute();
            $duplicado = $query->get_result()->num_rows > 0;
            $query->close();
            if ($duplicado) {
                echo json_encode(['success' => false, 'message' => $campo === 'correo_institucional' ? 'Ese correo ya está registrado.' : 'Ese documento ya está registrado.']);
                exit;
            }
        }

        $archivo = null;
        $fotoAnterior = null;
        if ($campo === 'documento') {
            $archivo = DocumentUpload::store($_FILES['archivo_documento'], 'uploads/profiles/documents/');
            $archivoTemporalRelativo = $archivo['path'];
            $query = $connection->prepare('SELECT foto_documento FROM usuarios WHERE id_usuario = ?');
            $query->bind_param('i', $mi_id);
            $query->execute();
            $fotoAnterior = $query->get_result()->fetch_assoc()['foto_documento'] ?? null;
            $query->close();

            $query = $connection->prepare('UPDATE usuarios SET documento = ?, foto_documento = ? WHERE id_usuario = ?');
            $query->bind_param('ssi', $valor, $archivo['path'], $mi_id);
        } else {
            $query = $connection->prepare("UPDATE usuarios SET {$campo} = ? WHERE id_usuario = ?");
            $query->bind_param('si', $valor, $mi_id);
        }

        if ($query->execute()) {
            $query->close();
            $archivoTemporalRelativo = null;
            if ($campo === 'correo_institucional') $_SESSION['correo'] = $valor;
            if ($campo === 'nombre_apellido') $_SESSION['nombre'] = $valor;
            if ($campo === 'documento') $_SESSION['documento'] = $valor;
            if ($archivo !== null && $fotoAnterior && $fotoAnterior !== $archivo['path']
                && !DocumentUpload::deleteManaged($fotoAnterior)) {
                error_log('No se pudo eliminar foto de documento anterior de usuario #' . $mi_id);
            }
            echo json_encode(['success' => true, 'message' => 'Dato actualizado correctamente']);
        } else {
            $query->close();
            if ($archivo !== null && !DocumentUpload::deleteManaged($archivo['path'])) {
                error_log('No se pudo eliminar documento sin asociar: ' . $archivo['path']);
            }
            $archivoTemporalRelativo = null;
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }
        exit;
    }

    // ── 7. Eliminar cuenta (solo Admin, directo) ──
    if ($accion === 'eliminar_cuenta' && $mi_rol === 4) {
        $confirmacion = is_string($_POST['confirmacion'] ?? null) ? trim($_POST['confirmacion']) : '';
        $contrasena = is_string($_POST['contrasena_actual'] ?? null) ? $_POST['contrasena_actual'] : '';
        if ($confirmacion !== 'ELIMINAR' || $contrasena === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Escribe ELIMINAR y confirma con tu contraseña actual.']);
            exit;
        }

        $administradoresActivos = $connection->query(
            "SELECT COUNT(*) AS total FROM usuarios WHERE id_rol = 4 AND estado = '1'"
        );
        if ((int) ($administradoresActivos->fetch_assoc()['total'] ?? 0) <= 1) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'No puedes eliminar la última cuenta de administrador activa.']);
            exit;
        }

        $consulta = $connection->prepare('SELECT password FROM usuarios WHERE id_usuario = ? AND id_rol = 4 AND estado = ?');
        $estadoActivo = '1';
        $consulta->bind_param('is', $mi_id, $estadoActivo);
        $consulta->execute();
        $usuarioAdmin = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        if (!$usuarioAdmin || !password_verify($contrasena, $usuarioAdmin['password'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'La contraseña actual no es correcta.']);
            exit;
        }

        $sql = "UPDATE usuarios SET estado = 'eliminado', remember_token = NULL WHERE id_usuario = ? AND id_rol = 4 AND estado = ?";
        $query = $connection->prepare($sql);
        $query->bind_param('is', $mi_id, $estadoActivo);

        if ($query->execute() && $query->affected_rows === 1) {
            setcookie('remember_me', '', [
                'expires' => time() - 3600,
                'path' => '/Appjoteca',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Cuenta marcada como eliminada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la cuenta']);
        }
        $query->close();
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);

} catch (InvalidArgumentException $e) {
    if ($archivoTemporalRelativo !== null) {
        try {
            if (!DocumentUpload::deleteManaged($archivoTemporalRelativo)) {
                error_log('No se pudo limpiar documento tras error de validación: ' . $archivoTemporalRelativo);
            }
        } catch (Throwable $cleanupError) {
            error_log('Error al limpiar documento temporal: ' . $cleanupError->getMessage());
        }
    }
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    if ($archivoTemporalRelativo !== null) {
        try {
            if (!DocumentUpload::deleteManaged($archivoTemporalRelativo)) {
                error_log('No se pudo limpiar documento tras error de base de datos: ' . $archivoTemporalRelativo);
            }
        } catch (Throwable $cleanupError) {
            error_log('Error al limpiar documento temporal: ' . $cleanupError->getMessage());
        }
    }
    error_log('configuracion-back error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}