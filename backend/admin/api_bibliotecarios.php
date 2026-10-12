<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/DocumentUpload.php';

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

// DIRECTORIO PAGINADO
if ($action === 'listar_usuarios') {
    $pagina = max(1, (int) ($_POST['pagina'] ?? 1));
    $limite = max(10, min(50, (int) ($_POST['limite'] ?? 25)));
    $rolFiltro = (int) ($_POST['rol'] ?? 0);
    $estadoFiltro = is_string($_POST['estado'] ?? null) ? $_POST['estado'] : '';
    $texto = is_string($_POST['busqueda'] ?? null) ? trim($_POST['busqueda']) : '';
    $texto = function_exists('mb_substr')
        ? mb_substr($texto, 0, 80, 'UTF-8')
        : substr($texto, 0, 80);
    $like = '%' . $texto . '%';
    $condiciones = ["u.estado IN ('1', 'inactivo', '0')"];
    $tipos = '';
    $parametros = [];

    if (in_array($rolFiltro, [1, 2, 3, 4], true)) {
        $condiciones[] = 'u.id_rol = ?';
        $tipos .= 'i';
        $parametros[] = $rolFiltro;
    }
    if (in_array($estadoFiltro, ['activo', 'inactivo'], true)) {
        $condiciones[] = $estadoFiltro === 'activo'
            ? "u.estado = '1'"
            : "u.estado IN ('inactivo', '0')";
    }
    if ($texto !== '') {
        $condiciones[] = '(u.nombre_apellido LIKE ? OR u.nombre_usuario LIKE ? OR u.correo_institucional LIKE ?)';
        $tipos .= 'sss';
        array_push($parametros, $like, $like, $like);
    }
    $where = implode(' AND ', $condiciones);

    $count = $connection->prepare("SELECT COUNT(*) AS total FROM usuarios u WHERE {$where}");
    if ($tipos !== '') $count->bind_param($tipos, ...$parametros);
    $count->execute();
    $total = (int) ($count->get_result()->fetch_assoc()['total'] ?? 0);
    $count->close();

    $paginas = max(1, (int) ceil($total / $limite));
    $pagina = min($pagina, $paginas);
    $offset = ($pagina - 1) * $limite;
    $sql = "SELECT u.id_usuario, u.id_rol, r.nombre AS rol, u.nombre_apellido,
                   u.nombre_usuario, u.correo_institucional, u.documento,
                   u.foto_perfil, u.biografia, u.estado, u.fecha_registro,
                   (SELECT s.id_solicitud FROM solicitudes_cambio s
                    WHERE s.id_usuario = u.id_usuario AND s.estado = 'pendiente'
                    ORDER BY s.fecha_solicitud DESC LIMIT 1) AS id_solicitud_pendiente
            FROM usuarios u
            LEFT JOIN roles r ON r.id_rol = u.id_rol
            WHERE {$where}
            ORDER BY u.nombre_apellido ASC, u.id_usuario ASC
            LIMIT ? OFFSET ?";
    $query = $connection->prepare($sql);
    $tiposPagina = $tipos . 'ii';
    $parametrosPagina = $parametros;
    $parametrosPagina[] = $limite;
    $parametrosPagina[] = $offset;
    $query->bind_param($tiposPagina, ...$parametrosPagina);
    $query->execute();
    $rows = $query->get_result();
    $usuarios = [];
    while ($row = $rows->fetch_assoc()) {
        $row['foto_perfil'] = !empty($row['foto_perfil'])
            ? '/Appjoteca/' . ltrim($row['foto_perfil'], '/')
            : '/Appjoteca/assets/images/default-avatar.png';
        $row['estado'] = $row['estado'] === '1' ? 'activo' : 'inactivo';
        $row['id_solicitud_pendiente'] = $row['id_solicitud_pendiente']
            ? (int) $row['id_solicitud_pendiente']
            : null;
        $usuarios[] = $row;
    }
    $query->close();
    echo json_encode([
        'status' => 'success',
        'usuarios' => $usuarios,
        'total' => $total,
        'pagina' => $pagina,
        'paginas' => $paginas,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// PERFIL DE USUARIO
if ($action === 'obtener_perfil') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }
    $query = $connection->prepare(
        "SELECT u.id_usuario, u.id_rol, r.nombre AS rol, u.nombre_apellido,
                u.nombre_usuario, u.correo_institucional, u.documento,
                u.foto_perfil, u.foto_documento, u.biografia, u.estado, u.fecha_registro,
                (SELECT r.id_reserva FROM reservas r
                 WHERE r.fk_id_usuario_reserva = u.id_usuario AND r.estado = 'activa'
                 ORDER BY r.fecha_reserva ASC, r.id_reserva ASC LIMIT 1) AS id_reserva_pendiente,
                (SELECT MIN(p.id_prestamo) FROM prestamos p
                 JOIN reservas r ON r.id_reserva = p.id_reserva
                 WHERE r.fk_id_usuario_reserva = u.id_usuario AND p.estado IN ('activo', 'vencido')
                 GROUP BY r.id_reserva ORDER BY MIN(p.fecha_devolucion_prevista) ASC LIMIT 1) AS id_prestamo_activo,
                (SELECT s.id_solicitud FROM solicitudes_cambio s
                 WHERE s.id_usuario = u.id_usuario AND s.estado = 'pendiente'
                 ORDER BY s.fecha_solicitud DESC LIMIT 1) AS id_solicitud_pendiente
         FROM usuarios u
         LEFT JOIN roles r ON r.id_rol = u.id_rol
         WHERE u.id_usuario = ? AND u.estado IN ('1', 'inactivo', '0', 'pendiente')
         LIMIT 1"
    );
    $query->bind_param('i', $id);
    $query->execute();
    $perfil = $query->get_result()->fetch_assoc();
    $query->close();
    if (!$perfil) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró el perfil solicitado']);
        exit;
    }
    $perfil['foto_perfil'] = !empty($perfil['foto_perfil'])
        ? '/Appjoteca/' . ltrim($perfil['foto_perfil'], '/')
        : '/Appjoteca/assets/images/default-avatar.png';
    $perfil['foto_documento'] = !empty($perfil['foto_documento'])
        ? '/Appjoteca/' . ltrim($perfil['foto_documento'], '/')
        : null;
    $perfil['estado'] = $perfil['estado'] === '1'
        ? 'activo'
        : ($perfil['estado'] === '0' ? 'inactivo' : $perfil['estado']);
    $perfil['id_solicitud_pendiente'] = $perfil['id_solicitud_pendiente']
        ? (int) $perfil['id_solicitud_pendiente']
        : null;
    $perfil['id_reserva_pendiente'] = $perfil['id_reserva_pendiente']
        ? (int) $perfil['id_reserva_pendiente']
        : null;
    $perfil['id_prestamo_activo'] = $perfil['id_prestamo_activo']
        ? (int) $perfil['id_prestamo_activo']
        : null;
    echo json_encode(['status' => 'success', 'perfil' => $perfil], JSON_UNESCAPED_UNICODE);
    exit;
}

// CAMBIAR ESTADO
if ($action === 'cambiar_estado_usuario') {
    $id = (int) ($_POST['id'] ?? 0);
    $nuevoEstado = is_string($_POST['estado'] ?? null) ? $_POST['estado'] : '';
    if ($id <= 0 || $id === $mi_id || !in_array($nuevoEstado, ['1', 'inactivo'], true)) {
        echo json_encode(['status' => 'error', 'message' => 'Acción o usuario no válido']);
        exit;
    }

    $query = $connection->prepare("SELECT id_rol, estado FROM usuarios WHERE id_usuario = ? AND estado IN ('1', 'inactivo', '0') LIMIT 1");
    $query->bind_param('i', $id);
    $query->execute();
    $objetivo = $query->get_result()->fetch_assoc();
    $query->close();
    if (!$objetivo) {
        echo json_encode(['status' => 'error', 'message' => 'No se puede cambiar una cuenta pendiente o eliminada']);
        exit;
    }

    if ($nuevoEstado === 'inactivo' && (int) $objetivo['id_rol'] === 4 && $objetivo['estado'] === '1') {
        $activos = $connection->query("SELECT COUNT(*) AS total FROM usuarios WHERE id_rol = 4 AND estado = '1'");
        if ((int) ($activos->fetch_assoc()['total'] ?? 0) <= 1) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede desactivar al último administrador activo']);
            exit;
        }
    }

    $query = $connection->prepare("UPDATE usuarios SET estado = ?, remember_token = IF(? = 'inactivo', NULL, remember_token) WHERE id_usuario = ? AND estado IN ('1', 'inactivo', '0')");
    $query->bind_param('ssi', $nuevoEstado, $nuevoEstado, $id);
    $query->execute();
    $ok = $query->affected_rows === 1 || $objetivo['estado'] === $nuevoEstado;
    $query->close();
    echo json_encode([
        'status' => $ok ? 'success' : 'error',
        'message' => $ok ? 'Estado de la cuenta actualizado' : 'No se pudo actualizar el estado',
    ]);
    exit;
}

// ELIMINACIÓN MODERADA
if ($action === 'eliminar_usuario') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0 || $id === $mi_id) {
        echo json_encode(['status' => 'error', 'message' => 'No puedes eliminar tu propia cuenta desde este panel']);
        exit;
    }

    $query = $connection->prepare("SELECT id_rol, estado FROM usuarios WHERE id_usuario = ? AND estado IN ('1', 'inactivo', '0') LIMIT 1");
    $query->bind_param('i', $id);
    $query->execute();
    $objetivo = $query->get_result()->fetch_assoc();
    $query->close();
    if (!$objetivo) {
        echo json_encode(['status' => 'error', 'message' => 'Solo se pueden eliminar cuentas activas o inactivas']);
        exit;
    }
    if ((int) $objetivo['id_rol'] === 4 && $objetivo['estado'] === '1') {
        $activos = $connection->query("SELECT COUNT(*) AS total FROM usuarios WHERE id_rol = 4 AND estado = '1'");
        if ((int) ($activos->fetch_assoc()['total'] ?? 0) <= 1) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar al último administrador activo']);
            exit;
        }
    }

    $query = $connection->prepare("UPDATE usuarios SET estado = 'eliminado', remember_token = NULL WHERE id_usuario = ? AND estado IN ('1', 'inactivo', '0')");
    $query->bind_param('i', $id);
    $query->execute();
    $ok = $query->affected_rows === 1;
    $query->close();
    echo json_encode([
        'status' => $ok ? 'success' : 'error',
        'message' => $ok ? 'La cuenta se marcó como eliminada' : 'No se pudo eliminar la cuenta',
    ]);
    exit;
}

// LISTAR BIBLIOTECARIOS
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

// LISTAR CUENTAS PENDIENTES (signup)
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

// CONTAR PENDIENTES
if ($action === 'contar_pendientes') {
    $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE estado = 'pendiente'";
    $query = $connection->prepare($sql);
    $query->execute();
    $row = $query->get_result()->fetch_assoc();
    $query->close();
    echo json_encode(['total' => (int)($row['total'] ?? 0)]);
    exit;
}

// APROBAR CUENTA PENDIENTE
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

// RECHAZAR CUENTA PENDIENTE
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
        if (!empty($fotoRelativa) && !DocumentUpload::deleteManaged($fotoRelativa)) {
            error_log('No se pudo eliminar el documento de la cuenta rechazada #' . $id);
        }
        echo json_encode(['status' => 'success', 'message' => 'La cuenta pendiente fue rechazada y eliminada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo rechazar (usuario no encontrado)']);
    }
    $query->close();
    exit;
}

// CREAR USUARIO
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

// ELIMINAR BIBLIOTECARIO
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

    $sql = "UPDATE usuarios SET estado = 'eliminado', remember_token = NULL
            WHERE id_usuario = ? AND id_rol = 3 AND estado = '1'";
    $query = $connection->prepare($sql);
    $query->bind_param('i', $id);
    $query->execute();

    if ($query->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Bibliotecario marcado como eliminado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar (no encontrado)']);
    }
    $query->close();
    exit;
}

// LISTAR SOLICITUDES DE CAMBIO
if ($action === 'listar_solicitudes') {
    $sql = "SELECT s.id_solicitud, s.id_usuario, s.tipo, s.valor_actual, s.valor_nuevo,
                   s.archivo_nuevo, s.archivo_mime,
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

// RESOLVER SOLICITUD DE CAMBIO
if ($action === 'resolver_solicitud') {
    $id_solicitud = (int)($_POST['id_solicitud'] ?? 0);
    $decision     = is_string($_POST['decision'] ?? null) ? $_POST['decision'] : '';
    $comentario   = is_string($_POST['comentario'] ?? null) ? trim($_POST['comentario']) : '';
    $comentario   = function_exists('mb_substr')
        ? mb_substr($comentario, 0, 160, 'UTF-8')
        : substr($comentario, 0, 160);

    if ($id_solicitud <= 0 || !in_array($decision, ['aprobar', 'rechazar'], true)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        exit;
    }

    $connection->begin_transaction();

    // Traer la solicitud
    $sql = "SELECT s.id_usuario, s.tipo, s.valor_nuevo, s.archivo_nuevo, s.archivo_mime,
                   u.id_rol, u.estado AS estado_usuario, u.nombre_usuario, u.nombre_apellido
            FROM solicitudes_cambio s
            INNER JOIN usuarios u ON u.id_usuario = s.id_usuario
            WHERE s.id_solicitud = ? AND s.estado = 'pendiente'
            LIMIT 1
            FOR UPDATE";
    $query = $connection->prepare($sql);
    $query->bind_param('i', $id_solicitud);
    $query->execute();
    $sol = $query->get_result()->fetch_assoc();
    $query->close();

    if (!$sol) {
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Solicitud no encontrada o ya resuelta']);
        exit;
    }

    $nuevo_estado = ($decision === 'aprobar') ? 'aprobada' : 'rechazada';
    $archivoParaBorrar = null;
    $fotoAnterior = null;

    // Si se aprueba, aplicar el cambio
    if ($decision === 'aprobar') {
        $tipo  = $sol['tipo'];
        $valor = $sol['valor_nuevo'];
        $uid   = (int)$sol['id_usuario'];

        if ($tipo === 'email') {
            $validator = new Validator();
            $validator->requerido($valor, 'correo')->email($valor)->longitud($valor, 5, 60, 'correo');
            if ($validator->falla()) {
                $connection->rollback();
                echo json_encode(['status' => 'error', 'message' => $validator->primero()]);
                exit;
            }
            // Verificar que no esté en uso
            $sql = "SELECT id_usuario FROM usuarios WHERE correo_institucional = ? AND id_usuario != ? LIMIT 1";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $uid);
            $query->execute();
            if ($query->get_result()->num_rows > 0) {
                $query->close();
                $connection->rollback();
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
            $validator = new Validator();
            $validator->requerido($valor, 'nombre y apellidos')
                ->longitud($valor, 3, 100, 'nombre y apellidos')
                ->regex($valor, '/^[\p{L}\p{M} .\'-]+$/u', 'El nombre solicitado no es válido.');
            if ($validator->falla()) {
                $connection->rollback();
                echo json_encode(['status' => 'error', 'message' => $validator->primero()]);
                exit;
            }
            $sql = "UPDATE usuarios SET nombre_apellido = ? WHERE id_usuario = ?";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $uid);
            $query->execute();
            $query->close();

        } elseif ($tipo === 'documento') {
            $validator = new Validator();
            $validator->requerido($valor, 'documento')
                ->longitud($valor, 1, 25, 'documento')
                ->regex($valor, '/^[0-9-]+$/', 'El documento solicitado no es válido.');
            if ($validator->falla()) {
                $connection->rollback();
                echo json_encode(['status' => 'error', 'message' => $validator->primero()]);
                exit;
            }

            $sql = "SELECT id_usuario FROM usuarios WHERE documento = ? AND id_usuario != ? AND documento != '' LIMIT 1";
            $query = $connection->prepare($sql);
            $query->bind_param('si', $valor, $uid);
            $query->execute();
            if ($query->get_result()->num_rows > 0) {
                $query->close();
                $connection->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Ese documento ya está registrado']);
                exit;
            }
            $query->close();

            $rutaArchivo = $sol['archivo_nuevo'] ?? '';
            if ($rutaArchivo === '' || empty($sol['archivo_mime'])) {
                $connection->rollback();
                echo json_encode(['status' => 'error', 'message' => 'La solicitud no tiene un documento adjunto válido.']);
                exit;
            }

            $sql = 'SELECT foto_documento FROM usuarios WHERE id_usuario = ?';
            $query = $connection->prepare($sql);
            $query->bind_param('i', $uid);
            $query->execute();
            $fotoAnterior = $query->get_result()->fetch_assoc()['foto_documento'] ?? null;
            $query->close();

            $sql = "UPDATE usuarios SET documento = ?, foto_documento = ? WHERE id_usuario = ?";
            $query = $connection->prepare($sql);
            $query->bind_param('ssi', $valor, $rutaArchivo, $uid);
            $query->execute();
            $query->close();

        } elseif ($tipo === 'eliminacion') {
            if ((int) $sol['id_rol'] === 4 && $sol['estado_usuario'] === '1') {
                $activos = $connection->query("SELECT COUNT(*) AS total FROM usuarios WHERE id_rol = 4 AND estado = '1'");
                if ((int) ($activos->fetch_assoc()['total'] ?? 0) <= 1) {
                    $connection->rollback();
                    echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar al último administrador activo.']);
                    exit;
                }
            }
            // Marcar cuenta como eliminada (soft delete)
            $sql = "UPDATE usuarios SET estado = 'eliminado', remember_token = NULL WHERE id_usuario = ?";
            $query = $connection->prepare($sql);
            $query->bind_param('i', $uid);
            $query->execute();
            $query->close();
        }
    }

    // Actualizar la solicitud
    $sql = "UPDATE solicitudes_cambio
            SET estado = ?, fecha_resolucion = NOW(), id_admin_resuelve = ?, comentario = ?
            WHERE id_solicitud = ? AND estado = 'pendiente'";
    $query = $connection->prepare($sql);
    $query->bind_param('sisi', $nuevo_estado, $mi_id, $comentario, $id_solicitud);
    $query->execute();
    if ($query->affected_rows !== 1) {
        $query->close();
        $connection->rollback();
        echo json_encode(['status' => 'error', 'message' => 'La solicitud ya fue resuelta por otro administrador.']);
        exit;
    }
    $query->close();

    if ($decision === 'rechazar') {
        $archivoParaBorrar = $sol['archivo_nuevo'] ?? null;
    }

    $connection->commit();

    if ((int) $sol['id_usuario'] > 0) {
        require_once __DIR__ . '/../models/notificacion.php';
        $nombreSolicitante = $sol['nombre_apellido'] ?: $sol['nombre_usuario'];
        $resultadoSolicitud = $decision === 'aprobar' ? 'aprobada' : 'rechazada';
        $mensaje = "Tu solicitud de {$sol['tipo']} fue {$resultadoSolicitud}.";
        if ($comentario !== '') {
            $mensaje .= ' Comentario del administrador: ' . $comentario;
        }
        $notificacion = new Notificacion($connection);
        $creada = $notificacion->crear($mensaje, '/Appjoteca/pages/settings/configuracion.php', (int) $sol['id_usuario']);
        if ($creada === false) error_log('No se pudo notificar resultado de solicitud #' . $id_solicitud);
    }

    if ($archivoParaBorrar !== null && $archivoParaBorrar !== ''
        && !DocumentUpload::deleteManaged($archivoParaBorrar)) {
        error_log('No se pudo eliminar documento rechazado de solicitud #' . $id_solicitud);
        echo json_encode(['status' => 'error', 'message' => 'La solicitud se rechazó, pero no se pudo eliminar el archivo adjunto.']);
        exit;
    }
    if ($fotoAnterior && $decision === 'aprobar' && $fotoAnterior !== ($sol['archivo_nuevo'] ?? null)
        && !DocumentUpload::deleteManaged($fotoAnterior)) {
        error_log('No se pudo eliminar documento anterior del usuario #' . (int)$sol['id_usuario']);
    }

    $msg = ($decision === 'aprobar')
        ? 'Solicitud aprobada y cambio aplicado'
        : 'Solicitud rechazada';

    echo json_encode(['status' => 'success', 'message' => $msg]);
    exit;
}

// Acción desconocida
echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);

} catch (Exception $e) {
    $connection->rollback();
    error_log('api_bibliotecarios: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor']);
}