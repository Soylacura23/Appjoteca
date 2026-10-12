<?php
function appCurrentUserRole(): int
{
    $id = (int) ($_SESSION['usuario_id'] ?? 0);
    $sessionRole = (int) ($_SESSION['rol'] ?? 0);
    if ($id <= 0 || !in_array($sessionRole, [1, 2, 3, 4], true)) return 0;

    require_once __DIR__ . '/../Database/conexion.php';

    try {
        $connection = db_connect(true);
    } catch (Throwable $e) {
        error_log('No se pudo validar la sesión para la navegación pública: ' . $e->getMessage());
        return 0;
    }

    try {
        $query = $connection->prepare('SELECT id_rol, estado FROM usuarios WHERE id_usuario = ? LIMIT 1');
        $query->bind_param('i', $id);
        $query->execute();
        $user = $query->get_result()->fetch_assoc();
        $query->close();
    } catch (Throwable $error) {
        error_log('No se pudo validar la sesión para la navegación pública: ' . $error->getMessage());
        return 0;
    }

    if (!$user || $user['estado'] !== '1' || (int) $user['id_rol'] !== $sessionRole) {
        unset($_SESSION['usuario_id'], $_SESSION['rol']);
        return 0;
    }
    return $sessionRole;
}
