<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("../Database/conexion.php");
require_once __DIR__ . '/../config/verify-csrf.php';

header('Content-Type: application/json');


$rol_recibido = $_POST['rol'] ?? '';

$tabla_roles = [
    'estudiante'    => 1,
    'profesor'      => 2,
    'bibliotecario' => 3
];

if (!array_key_exists($rol_recibido, $tabla_roles)) {
    echo json_encode(['status' => 'error', 'message' => 'El rol seleccionado no es válido.']);
    exit;
}

$id_rol = $tabla_roles[$rol_recibido];


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

$nombre            = trim($_POST["nombre"]  ?? '');
$usuario           = trim($_POST["usuario"] ?? '');
$correo            = trim($_POST["email"]   ?? '');
$documento         = trim($_POST["cedula"]  ?? '');
$contrasena_plana  = $_POST["contrasena"]   ?? '';

if ($nombre === '' || $usuario === '' || $correo === '' || $documento === '' || $contrasena_plana === '') {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'El correo electrónico no es válido.']);
    exit;
}

$contrasena_encriptada = password_hash($contrasena_plana, PASSWORD_BCRYPT);

date_default_timezone_set('America/Bogota');
$fecha_creacion = date("Y-m-d H:i:s");


$uploadDir = __DIR__ . '/../../uploads/profiles/documents/';

if (!isset($_FILES['cedula_file']) || $_FILES['cedula_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Error al subir el archivo o archivo no recibido.']);
    exit;
}

$file = $_FILES['cedula_file'];

$maxSizeBytes = 10 * 1024 * 1024;

if ($file['size'] > $maxSizeBytes) {
    echo json_encode(['status' => 'error', 'message' => 'El archivo supera el tamaño máximo permitido (10MB).']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimeTypes = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
    'application/pdf' => 'pdf'
];

if (!array_key_exists($mimeType, $allowedMimeTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Formato de archivo no válido. Solo se permite PDF, JPG, PNG o WEBP.']);
    exit;
}

$extension = $allowedMimeTypes[$mimeType];

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo crear la carpeta de documentos.']);
        exit;
    }
}

$nuevoNombre   = 'doc_' . bin2hex(random_bytes(16)) . '.' . $extension;
$rutaDestino   = $uploadDir . $nuevoNombre;

if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el archivo en el servidor.']);
    exit;
}

$rutaRelativaBD = 'uploads/profiles/documents/' . $nuevoNombre;

$estado = 'pendiente';

/* ─────────────────────────────────────────────────────────────
   COMPROBAR DUPLICADOS 
   ───────────────────────────────────────────────────────────── */
$checkSql  = "SELECT id FROM usuarios
              WHERE nombre_usuario = ? OR correo_institucional = ? OR documento = ?
              LIMIT 1";
$checkStmt = $connection->prepare($checkSql);
if ($checkStmt) {
    $checkStmt->bind_param("sss", $usuario, $correo, $documento);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        @unlink($rutaDestino); 
        echo json_encode([
            'status'  => 'error',
            'message' => 'Ya existe un usuario con ese nombre, correo o documento.'
        ]);
        exit;
    }
    $checkStmt->close();
}

$sql = "INSERT INTO usuarios
        (id_rol, nombre_apellido, nombre_usuario, correo_institucional, documento,
         foto_documento, password, estado, fecha_registro)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$query = $connection->prepare($sql);

if (!$query) {
    @unlink($rutaDestino);
    echo json_encode(['status' => 'error', 'message' => 'Error interno al preparar el registro.']);
    exit;
}

$query->bind_param(
    "issssssss",
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
    echo json_encode([
        'status'  => 'success',
        'message' => "¡Registro exitoso, $nombre! Tu cuenta quedó en estado PENDIENTE hasta que un administrador la apruebe."
    ]);
} else {
    @unlink($rutaDestino);
    echo json_encode([
        'status'  => 'error',
        'message' => "Hubo un error al registrar al usuario $nombre."
    ]);
}

$query->close();
$connection->close();