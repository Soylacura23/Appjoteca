<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/verify-csrf.php';

header('Content-Type: application/json; charset=utf-8');

require("../Database/conexion.php");
require_once __DIR__ . '/../helpers/RateLimiter.php';
require_once __DIR__ . '/../helpers/Validator.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

$action = $_POST['action'] ?? '';

if (!empty($_POST['middle_name'])) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Si los datos son válidos, recibirás instrucciones.',
    ]);
    exit;
}

$MSG_GENERICO = 'Si los datos corresponden a una cuenta activa, recibirás un código.';

switch ($action) {

    case 'send_code':

        $limiter = new RateLimiter($connection, 5, 3600);
        if (!$limiter->check('recover-send:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
            http_response_code(429);
            echo json_encode(['status' => 'error', 'message' => 'Demasiadas solicitudes. Inténtalo más tarde.']);
            exit;
        }

        $correo = mb_strtolower(trim($_POST['correo'] ?? ''));

        $valor = new Validator();
        $valor->requerido($correo, 'correo')
          ->email($correo)
          ->longitud($correo, 5, 150, 'correo')
          ->emailInstitucional($correo, [
              '.edu', '.edu.co', '.edu.mx', '.edu.ar', '.edu.pe', '.edu.cl',
              '.ac.', '.gob.', '.gov.',
          ]);

        if ($valor->falla()) {
            echo json_encode(['status' => 'success', 'message' => $MSG_GENERICO]);
            exit;
        }

        $sql = "SELECT nombre_usuario FROM usuarios
                WHERE correo_institucional = ? AND estado = '1'";
        $query = $connection->prepare($sql);
        $query->bind_param("s", $correo);
        $query->execute();
        $resultado = $query->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            $codigo  = random_int(100000, 999999);

            $_SESSION['recover_email']        = $correo;
            $_SESSION['recover_code_hash']    = password_hash((string) $codigo, PASSWORD_BCRYPT);
            $_SESSION['recover_code_expires'] = time() + 600;
            $_SESSION['recover_verified']     = false;
            $_SESSION['recover_verified_at']  = null;
            $_SESSION['recover_intentos']     = 0;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['SMTP_USER'] ?? '';
                $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('appjotecainformation@gmail.com', 'Información Appjoteca');
                $mail->addAddress($correo, $usuario['nombre_usuario']);

                $mail->isHTML(true);
                $rutaImagen = dirname(__DIR__, 2) . '/shared/images/favicon.png';
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

                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td align="center" style="background-color: #1a1a1a; border: 1px solid #3a3a3a; border-radius: 12px; padding: 25px;">
                                                        <span style="font-family: monospace, sans-serif; font-size: 32px; font-weight: 700; color: #f2ca50; letter-spacing: 8px;">{$codigo}</span>
                                                    </td>
                                                </tr>
                                            </table>

                                            <h3 style="margin: 15px 0 15px 0; font-size: 15px; font-weight: 400; color: #ffffff;">El código vencerá en 10 minutos</h3>

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
                echo json_encode(['status' => 'success', 'message' => $MSG_GENERICO]);
            } catch (Exception $e) {
                error_log('Error sending password recovery email: ' . $e->getMessage());
                unset(
                    $_SESSION['recover_email'],
                    $_SESSION['recover_code_hash'],
                    $_SESSION['recover_code_expires'],
                    $_SESSION['recover_verified'],
                    $_SESSION['recover_verified_at'],
                    $_SESSION['recover_intentos']
                );
                echo json_encode(['status' => 'success', 'message' => $MSG_GENERICO]);
            }
        } else {

            echo json_encode(['status' => 'success', 'message' => $MSG_GENERICO]);
        }
        break;

    case 'verify_code':

        $limiter = new RateLimiter($connection, 10, 900);
        if (!$limiter->check('recover-verify:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
            http_response_code(429);
            echo json_encode(['status' => 'error', 'message' => 'Demasiados intentos. Inténtalo más tarde.']);
            exit;
        }

        $codigo_ingresado = trim($_POST['codigo'] ?? '');

        if (!preg_match('/^\d{6}$/', $codigo_ingresado)) {
            echo json_encode(['status' => 'error', 'message' => 'El código es incorrecto.']);
            exit;
        }

        if (empty($_SESSION['recover_code_hash']) || empty($_SESSION['recover_code_expires'])) {
            echo json_encode(['status' => 'error', 'message' => 'El código es incorrecto.']);
            exit;
        }

        if ((int) $_SESSION['recover_code_expires'] < time()) {
            unset(
                $_SESSION['recover_code_hash'],
                $_SESSION['recover_code_expires'],
                $_SESSION['recover_verified'],
                $_SESSION['recover_verified_at'],
                $_SESSION['recover_intentos']
            );
            echo json_encode(['status' => 'error', 'message' => 'El código ha expirado.']);
            exit;
        }

        $_SESSION['recover_intentos'] = ((int) ($_SESSION['recover_intentos'] ?? 0)) + 1;
        if ($_SESSION['recover_intentos'] > 5) {
            unset(
                $_SESSION['recover_code_hash'],
                $_SESSION['recover_code_expires'],
                $_SESSION['recover_verified'],
                $_SESSION['recover_verified_at'],
                $_SESSION['recover_intentos']
            );
            echo json_encode(['status' => 'error', 'message' => 'Demasiados intentos. Solicita un código nuevo.']);
            exit;
        }

        if (password_verify($codigo_ingresado, $_SESSION['recover_code_hash'])) {
            $_SESSION['recover_verified']    = true;
            $_SESSION['recover_verified_at'] = time();

            unset($_SESSION['recover_code_hash']);

            echo json_encode(['status' => 'success', 'message' => 'Código verificado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'El código es incorrecto.']);
        }
        break;

    case 'update_password':

        $limiter = new RateLimiter($connection, 5, 900);
        if (!$limiter->check('recover-update:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
            http_response_code(429);
            echo json_encode(['status' => 'error', 'message' => 'Demasiados intentos. Inténtalo más tarde.']);
            exit;
        }

        $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
        $correo_guardado  = $_SESSION['recover_email'] ?? '';

        if (empty($correo_guardado) || empty($_SESSION['recover_verified'])) {
            echo json_encode(['status' => 'error', 'message' => 'La sesión ha expirado. Recarga la página.']);
            exit;
        }

        if (empty($_SESSION['recover_verified_at'])
            || (time() - (int) $_SESSION['recover_verified_at']) > 300) {
            echo json_encode(['status' => 'error', 'message' => 'La verificación ha expirado. Vuelve a introducir el código.']);
            exit;
        }

        if ((int) ($_SESSION['recover_code_expires'] ?? 0) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'La sesión ha expirado. Recarga la página.']);
            exit;
        }

        $valor = new Validator();
        $valor->password($nueva_contrasena, 8, 72);

        if ($valor->falla()) {
            echo json_encode([
                'status'  => 'error',
                'message' => $valor->primero(),
                'errores' => $valor->errores(),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $sql = "SELECT password FROM usuarios WHERE correo_institucional = ?";
        $query = $connection->prepare($sql);
        $query->bind_param("s", $correo_guardado);
        $query->execute();
        $resultado = $query->get_result();

        if ($resultado->num_rows < 1) {
            echo json_encode(['status' => 'error', 'message' => 'Error de base de datos.']);
            exit;
        }

        $fila = $resultado->fetch_assoc();

        if (password_verify($nueva_contrasena, $fila['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'La nueva contraseña no puede ser igual a la anterior.']);
            exit;
        }

        $hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT, ['cost' => 12]);

        $sqlUpdate = "UPDATE usuarios
                      SET password = ?, remember_token = NULL
                      WHERE correo_institucional = ?";
        $queryUpdate = $connection->prepare($sqlUpdate);
        $queryUpdate->bind_param("ss", $hash, $correo_guardado);

        if (!$queryUpdate->execute()) {
            error_log('update_password: execute falló: ' . $queryUpdate->error);
            echo json_encode(['status' => 'error', 'message' => 'Error de base de datos.']);
            exit;
        }

        setcookie("remember_me", "", [
            'expires'  => time() - 3600,
            'path'     => '/Appjoteca',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        unset(
            $_SESSION['recover_email'],
            $_SESSION['recover_code_hash'],
            $_SESSION['recover_code_expires'],
            $_SESSION['recover_verified'],
            $_SESSION['recover_verified_at'],
            $_SESSION['recover_intentos']
        );

        session_regenerate_id(true);

        echo json_encode(['status' => 'success', 'message' => 'Contraseña actualizada.']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción inválida.']);
        break;
}