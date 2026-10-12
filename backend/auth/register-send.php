<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../helpers/RateLimiter.php';
require_once __DIR__ . '/../helpers/Validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

if (!empty($_POST['middle_name'])) {
    echo json_encode(['status' => 'success', 'message' => 'Solicitud recibida.']);
    exit;
}

$limiter = new RateLimiter($connection, 5, 3600);
if (!$limiter->check('register:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Demasiados intentos. Inténtalo más tarde.']);
    exit;
}

$id_rol            = 1;
$nombre            = trim($_POST['nombre']    ?? '');
$usuario           = mb_strtolower(trim($_POST['usuario'] ?? ''));
$correo            = mb_strtolower(trim($_POST['email']   ?? ''));
$documento         = trim($_POST['cedula']    ?? '');
$contrasena_plana  = $_POST['contrasena']     ?? '';
$contrasena2       = $_POST['contrasena2']    ?? '';

$valor = new Validator();

$valor->requerido($nombre, 'nombre')
  ->longitud($nombre, 3, 100, 'nombre');

$valor->requerido($usuario, 'usuario')
  ->longitud($usuario, 3, 30, 'usuario')
  ->regex(
      $usuario,
      '/^[a-z0-9._\-]+$/',
      'El usuario solo puede contener letras, números, punto, guión y guión bajo.'
  );

$valor->requerido($correo, 'correo')
  ->email($correo)
  ->longitud($correo, 5, 150, 'correo')
  ->emailInstitucional($correo, [
      '.edu', '.edu.co', '.edu.mx', '.edu.ar', '.edu.pe', '.edu.cl',
      '.ac.', '.gob.', '.gov.',
  ]);

$valor->requerido($documento, 'documento')
  ->longitud($documento, 1, 20, 'documento')
  ->regex(
      $documento,
      '/^[0-9\-]+$/',
      'El documento solo puede contener números y guiones.'
  );

$valor->password($contrasena_plana, 8, 72);
$valor->iguales($contrasena_plana, $contrasena2, 'Las contraseñas no coinciden.');

if ($valor->falla()) {
    echo json_encode([
        'status'  => 'error',
        'message' => $valor->primero(),
        'errores' => $valor->errores(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$uploadDir    = __DIR__ . '/../../uploads/profiles/documents/';
$maxSizeBytes = 10 * 1024 * 1024;

if (!isset($_FILES['cedula_file']) || $_FILES['cedula_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Error al subir el archivo o archivo no recibido.']);
    exit;
}

$file = $_FILES['cedula_file'];

if ($file['size'] <= 0 || $file['size'] > $maxSizeBytes) {
    echo json_encode(['status' => 'error', 'message' => 'El archivo supera el tamaño máximo permitido (10MB).']);
    exit;
}

$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimeTypes = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
    'application/pdf' => 'pdf',
];

if (!array_key_exists($mimeType, $allowedMimeTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Formato de archivo no válido. Solo se permite PDF, JPG, PNG o WEBP.']);
    exit;
}

$extension   = $allowedMimeTypes[$mimeType];
$nuevoNombre = 'doc_' . bin2hex(random_bytes(16)) . '.' . $extension;
$rutaDestino = $uploadDir . $nuevoNombre;

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        error_log('register-send: no se pudo crear uploadDir: ' . $uploadDir);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo preparar el almacenamiento.']);
        exit;
    }
}

$checkSql = "SELECT id_usuario FROM usuarios
             WHERE nombre_usuario = ? OR correo_institucional = ? OR documento = ?
             LIMIT 1";
$checkquery = $connection->prepare($checkSql);

if ($checkquery) {
    $checkquery->bind_param('sss', $usuario, $correo, $documento);
    $checkquery->execute();
    $checkquery->store_result();

    if ($checkquery->num_rows > 0) {
        $checkquery->close();
        echo json_encode([
            'status'  => 'error',
            'message' => 'Ya existe un usuario con ese nombre, correo o documento.',
        ]);
        exit;
    }
    $checkquery->close();
}

$contrasena_encriptada = password_hash($contrasena_plana, PASSWORD_BCRYPT, ['cost' => 12]);

date_default_timezone_set('America/Bogota');
$fecha_creacion = date('Y-m-d H:i:s');
$estado         = 'pendiente';

if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
    error_log('register-send: move_uploaded_file falló hacia ' . $rutaDestino);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el archivo en el servidor.']);
    exit;
}

$rutaRelativaBD = 'uploads/profiles/documents/' . $nuevoNombre;

$sql = "INSERT INTO usuarios
        (id_rol, nombre_apellido, nombre_usuario, correo_institucional, documento,
         foto_documento, password, estado, fecha_registro)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$query = $connection->prepare($sql);

if (!$query) {
    @unlink($rutaDestino);
    error_log('register-send: prepare falló: ' . $connection->error);
    echo json_encode(['status' => 'error', 'message' => 'Error interno al preparar el registro.']);
    exit;
}

$query->bind_param(
    'issssssss',
    $id_rol,
    $nombre,
    $usuario,
    $correo,
    $documento,
    $rutaRelativaBD,
    $contrasena_encriptada,
    $estado,
    $fecha_creacion
);

if ($query->execute()) {
    $idUsuario = (int) $connection->insert_id;
    $query->close();

    require_once __DIR__ . '/../models/notificacion.php';
    $modeloNotif = new Notificacion($connection);
    $admins      = $modeloNotif->obtenerUsuariosPorRol(4);

    if (!empty($admins)) {
        $modeloNotif->crear(
            "Nueva solicitud de cuenta: $nombre ($usuario). Pendiente de aprobación.",
            "/Appjoteca/dashboards/Administrador/index.php?tab=pendientes&focus_usuario={$idUsuario}",
            $admins
        );
    }

    echo json_encode([
        'status'  => 'success',
        'message' => "¡Registro exitoso, $nombre! Tu cuenta quedó en estado PENDIENTE hasta que un administrador la apruebe.",
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

@unlink($rutaDestino);

if ($connection->errno === 1062) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Ya existe un usuario con ese nombre, correo o documento.',
    ], JSON_UNESCAPED_UNICODE);
} else {
    error_log('register-send: execute falló: ' . $query->error);
    echo json_encode([
        'status'  => 'error',
        'message' => 'No se pudo registrar la cuenta. Inténtalo de nuevo.',
    ], JSON_UNESCAPED_UNICODE);
}

$query->close();
$connection->close();