<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/verify-csrf.php';

require_once __DIR__ . '/../Database/conexion.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim(strip_tags($_POST['nombre'])) ?? '';
    $correo = trim($_POST['correo']) ?? '';
    $tipo_comentario = trim($_POST['tipo']) ?? '';
    $comentario = trim(strip_tags($_POST['mensaje'])) ?? '';
    $honeypot = $_POST['website_url_hp'] ?? '';

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)){
        echo json_encode([
            "ok" => false,
            "message" => "Ingrese un correo electrónico válido"
        ]);
        exit;
    }

    $acepto_privacidad = isset($_POST['privacidad']);

    if(!empty($honeypot)){
        http_response_code(403);
        echo json_encode([
            "ok" => false,
            "message" => "No está permitido el Spam ni el uso de bots para hacer spam"
        ]);
        exit;
    }

    if (!$acepto_privacidad){
        http_response_code(400);
        echo json_encode([
            "ok" => false,
            "message" => "Error: Debes aceptar la política de privacidad."
        ]);
        exit;
    }

    if (empty($nombre) || empty($correo) || empty($tipo_comentario) || empty($comentario)){

        echo json_encode([
            "ok" => false,
            "message" => "Debes de llenar todos los campos de este formulario para enviar tu comentario"

        ]);
        exit();

    } else{

        $sql = "INSERT INTO comentarios (nombre, tipo_comentario, comentario, correo) VALUES (?, ?, ?, ?) ";

        $query = $connection->prepare($sql);
        $query->bind_param(
            "ssss",
            $nombre,
            $tipo_comentario,
            $comentario,
            $correo
        );

        if($query->execute()){
            echo json_encode([
            "ok" => true,
            "message" => "Comentario enviado con éxito."

        ]);

        } else{
            echo json_encode([
            "ok" => false,
            "message" => "Hubo un error al mandar el comentario. Intenta de nuevo más tarde"

        ]);
        }

    }

} else{
    echo json_encode([
            "ok" => false,
            "message" => "Método no permitido"

        ]);
}
?>