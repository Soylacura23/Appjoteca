<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

if (!$connection) {
    fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
    exit(1);
}
if (empty($_ENV['SMTP_USER']) || empty($_ENV['SMTP_PASS'])) {
    fwrite(STDERR, "Faltan las credenciales SMTP del entorno.\n");
    exit(1);
}

$query = $connection->prepare(
    "SELECT n.id, n.titulo, n.contenido, n.fecha_recordatorio,
            DATEDIFF(n.fecha_recordatorio, CURDATE()) AS dias,
            u.correo_institucional, u.nombre_apellido
     FROM notas_docente n
     JOIN usuarios u ON u.id_usuario = n.id_usuario
     WHERE u.id_rol = 2 AND u.estado = '1'
       AND n.fecha_recordatorio BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
       AND n.recordatorio_enviado = 0
     ORDER BY n.fecha_recordatorio ASC, n.id ASC"
);
$query->execute();
$result = $query->get_result();
$reminders = [];
while ($row = $result->fetch_assoc()) $reminders[] = $row;
$query->close();

$failed = false;
foreach ($reminders as $reminder) {
    if (!filter_var($reminder['correo_institucional'], FILTER_VALIDATE_EMAIL)) {
        error_log('Recordatorio omitido: correo docente no válido para nota #' . (int) $reminder['id']);
        $failed = true;
        continue;
    }

    $tomorrow = (int) $reminder['dias'] === 1;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($_ENV['SMTP_USER'], 'AppJoteca');
        $mail->addAddress($reminder['correo_institucional'], $reminder['nombre_apellido'] ?? '');
        $mail->isHTML(true);
        $mail->Subject = 'Recordatorio AppJoteca: ' . $reminder['titulo'];
        $title = htmlspecialchars($reminder['titulo'], ENT_QUOTES, 'UTF-8');
        $content = nl2br(htmlspecialchars($reminder['contenido'], ENT_QUOTES, 'UTF-8'));
        $when = $tomorrow ? 'mañana' : 'hoy';
        $mail->Body = "<h2>Recordatorio para {$when}</h2><h3>{$title}</h3><p>{$content}</p>";
        $mail->AltBody = "Recordatorio para {$when}: {$reminder['titulo']}\n{$reminder['contenido']}";
        $mail->send();

        $markSent = $connection->prepare(
            'UPDATE notas_docente SET recordatorio_enviado = 1 WHERE id = ? AND recordatorio_enviado = 0'
        );
        $noteId = (int) $reminder['id'];
        $markSent->bind_param('i', $noteId);
        $markSent->execute();
        $markSent->close();
    } catch (Throwable $error) {
        error_log('No se pudo enviar recordatorio docente #' . (int) $reminder['id'] . ': ' . $error->getMessage());
        $failed = true;
    }
}

if ($failed) exit(1);
echo count($reminders) . " recordatorio(s) procesado(s).\n";
