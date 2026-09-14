<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("../Database/conexion.php");
require_once __DIR__ . '/../config/verify-csrf.php';

header('Content-Type: application/json');

$rol_recibido = $_POST['rol'] ?? '';

$tabla_roles = [
    'estudiante' => 1,
    'profesor'   => 2,
    'bibliotecario' => 3  
];

if (!array_key_exists($rol_recibido, $tabla_roles)) {
    echo json_encode(['status' => 'error', 'message' => 'El rol seleccionado no es válido.']);
    exit;
}

$id_rol = $tabla_roles[$rol_recibido];

    


    if($_SERVER["REQUEST_METHOD"]==="POST"){
        $nombre = $_POST["nombre"];
        $usuario = $_POST["usuario"];
        $correo = $_POST["email"];
        $documento = $_POST["cedula"];
        $contrasena_plana = $_POST["contrasena"];

       
        $contrasena_encriptada = password_hash($contrasena_plana, PASSWORD_BCRYPT);

        date_default_timezone_set('America/Bogota');
        $fecha_creacion = date("Y-m-d");

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

        $nuevoNombre = 'doc_' . bin2hex(random_bytes(16)) . '.' . $extension;

        $rutaDestino = $uploadDir . $nuevoNombre;

        move_uploaded_file($file['tmp_name'], $rutaDestino);

        $rutaRelativaBD = 'uploads/profiles/documents/' . $nuevoNombre;

        $estado = '1';
        

        $sql = "INSERT INTO usuarios (id_rol, nombre_apellido, nombre_usuario, correo_institucional, documento, foto_documento, password, estado, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $query = $connection->prepare($sql);

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
            'status' => 'success', 
            'message' => "El usuario " . $nombre . " ha sido registrado con éxito"
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => "Hubo un error al registrar al usuario " . $nombre
        ]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
