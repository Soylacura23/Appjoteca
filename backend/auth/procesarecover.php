<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/verify-csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require("../Database/conexion.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'send_code':
        $correo = filter_var(trim($_POST["correo"] ?? ''), FILTER_VALIDATE_EMAIL);
        
        if (!$correo) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de correo inválido.']);
            exit;
        }

        $sql = "SELECT nombre_usuario FROM usuarios WHERE correo_institucional = ? AND estado = '1'";
        $query = $connection->prepare($sql);
        $query->bind_param("s", $correo);
        $query->execute();
        $resultado = $query->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            $codigo = rand(100000, 999999);
            
            $_SESSION['recover_email'] = $correo;
            $_SESSION['recover_code'] = (string)$codigo;

            

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'appjotecainformation@gmail.com';
                $mail->Password   = 'yudtydunnmcchnmd'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
            
                $mail->setFrom('appjotecainformation@gmail.com', 'Información Appjoteca');
                $mail->addAddress($correo, $usuario['nombre_usuario']); 
                
                $mail->isHTML(true);
                $rutaImagen = dirname(__DIR__, 2) . '/shared/images/logo-appjoteca.png';
                $mail->addEmbeddedImage($rutaImagen, 'logo_appjoteca');
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Appjoteca: Código de recuperación';
                $mail->Body    = <<<HTML
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Código de Recuperación</title>
                </head>
                <body style="margin: 0; padding: 0; background-color: #f4f4f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-font-smoothing: antialiased;">
                    
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f5; padding: 40px 20px;">
                        <tr>
                            <td align="center">
                                
                                <table width="100%" maxWidth="500" cellpadding="0" cellspacing="0" border="0" style="background-color: #0a0a0a; border-radius: 16px; overflow: hidden; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                                    
                                    <tr>
                                        <td align="center" style="padding: 40px 40px 20px 40px;">
                                            <table cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="vertical-align: middle; padding-right: 12px;">
                                                        <img src="cid:logo_appjoteca" alt="Logo Appjoteca" height="32" style="display: block; height: 32px; width: auto; border: 0;">
                                                    </td>
                                                    <td style="vertical-align: middle;">
                                                        <h1 style="margin: 0; font-family: Georgia, serif; font-style: italic; color: #f2ca50; font-size: 28px; letter-spacing: -0.5px; line-height: 32px;">Appjoteca</h1>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 20px 40px 30px 40px; color: #f0f0f0;">
                                            <h2 style="margin: 0 0 15px 0; font-size: 20px; font-weight: 600; color: #ffffff;">Recuperación de acceso</h2>
                                            <p style="margin: 0 0 25px 0; font-size: 15px; line-height: 1.6; color: #a0a0a0;">
                                                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. Usa el siguiente código de verificación de 6 dígitos para continuar con el proceso:
                                            </p>

                                            <!-- Código -->
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td align="center" style="background-color: #1a1a1a; border: 1px solid #3a3a3a; border-radius: 12px; padding: 25px;">
                                                        <span style="font-family: monospace, sans-serif; font-size: 32px; font-weight: 700; color: #f2ca50; letter-spacing: 8px;">{$codigo}</span>
                                                    </td>
                                                </tr>
                                            </table>

                                            <p style="margin: 25px 0 0 0; font-size: 13px; line-height: 1.5; color: #777777;">
                                                Si no solicitaste este cambio, puedes ignorar este correo de forma segura. Tu cuenta sigue protegida.
                                            </p>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="padding: 20px 40px 30px 40px; background-color: #050505; border-top: 1px solid #1a1a1a;">
                                            <p style="margin: 0 0 5px 0; font-size: 11px; color: #555555; text-transform: uppercase; letter-spacing: 1px;">
                                                © 2026 Appjoteca
                                            </p>
                                            <p style="margin: 0; font-size: 11px; color: #444444;">
                                                Sistema de Biblioteca Institucional
                                            </p>
                                        </td>
                                    </tr>
                                    
                                </table>

                            </td>
                        </tr>
                    </table>

                </body>
                </html>
            HTML;

            
                $mail->send();
                echo json_encode(['status' => 'success', 'message' => 'Código enviado con éxito.']);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error al enviar correo.']);
            }
        
            
        } else {
            echo json_encode(['status' => 'error', 'message' => 'El correo no existe o está inactivo.']);
        }
        break;

    case 'verify_code':
        $codigo_ingresado = trim($_POST['codigo'] ?? '');
        
        if (isset($_SESSION['recover_code']) && $_SESSION['recover_code'] === $codigo_ingresado) {
            echo json_encode(['status' => 'success', 'message' => 'Código verificado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'El código es incorrecto.']);
        }
        break;

    case 'update_password':
        $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
        $correo_guardado = $_SESSION['recover_email'] ?? '';

        if (empty($correo_guardado)) {
            echo json_encode(['status' => 'error', 'message' => 'La sesión ha expirado. Recarga la página.']);
            exit;
        }
        if (strlen($nueva_contrasena) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            exit;
        }

        $sql = "SELECT password FROM usuarios WHERE correo_institucional = ?";
        $query = $connection->prepare($sql);
        $query->bind_param("s", $correo_guardado);
        $query->execute();
        $resultado = $query->get_result();
        
        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            
            // ¿Es igual a la antigua?
            if (password_verify($nueva_contrasena, $fila['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'La nueva contraseña no puede ser igual a la anterior.']);
                exit;
            }

            $hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);
            $sqlUpdate = "UPDATE usuarios SET password = ? WHERE correo_institucional = ?";
            $queryUpdate = $connection->prepare($sqlUpdate);
            $queryUpdate->bind_param("ss", $hash, $correo_guardado);
            
            if ($queryUpdate->execute()) {
                session_destroy();
                echo json_encode(['status' => 'success', 'message' => 'Contraseña actualizada.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error de base de datos.']);
            }
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción inválida.']);
        break;
}
?>