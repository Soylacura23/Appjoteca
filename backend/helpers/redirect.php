<?php
function redirect_to(string $context, int $role, int $id = 0): ?string
{
    $base = '/Appjoteca/';

    if ($context === 'home') {
        $home = [
            1 => 'dashboards/estudiante/index.php',
            2 => 'dashboards/docente/index.php',
            3 => 'dashboards/bibliotecario/index.php',
            4 => 'dashboards/Administrador/index.php',
        ];
        return isset($home[$role]) ? $base . $home[$role] : null;
    }

    if ($context === 'catalog' && in_array($role, [1, 2, 3, 4], true)) {
        return $base . ($role === 3
            ? 'dashboards/bibliotecario/pages/inventario/inventario.php'
            : 'pages/biblioteca-digital/index.php');
    }

    if ($context === 'history' && in_array($role, [1, 2, 4], true)) {
        return $base . 'pages/history/historial.php';
    }

    if ($context === 'book' && $id > 0 && in_array($role, [1, 2, 3, 4], true)) {
        return $base . ($role === 3
            ? 'dashboards/bibliotecario/pages/inventario/inventario.php?open_book=' . $id
            : 'pages/biblioteca-catalogo/vista-libro/book-view.php?id=' . $id);
    }

    if ($context === 'author' && $id > 0 && in_array($role, [1, 2, 3, 4], true)) {
        return $base . ($role === 3
            ? 'dashboards/bibliotecario/pages/inventario/inventario.php?autor=' . $id
            : 'pages/biblioteca-catalogo/catalogo/catalogo.php?autor=' . $id);
    }

    if ($context === 'profile' && $id > 0 && in_array($role, [3, 4], true)) {
        if ($role === 4 && $id === (int) ($_SESSION['usuario_id'] ?? 0)) {
            return $base . 'pages/settings/configuracion.php';
        }
        return $base . ($role === 3
            ? 'dashboards/bibliotecario/pages/usuarios/usuarios.php?id=' . $id
            : 'dashboards/Administrador/index.php?tab=usuarios&focus_user=' . $id);
    }

    if ($context === 'pending' && $role === 4) {
        return $base . 'dashboards/Administrador/index.php?tab=pendientes'
            . ($id > 0 ? '&focus_usuario=' . $id : '');
    }

    if ($context === 'request' && $role === 4 && $id > 0) {
        return $base . 'dashboards/Administrador/index.php?tab=solicitudes&focus_solicitud=' . $id;
    }

    if ($context === 'comment' && $role === 4) {
        return $base . 'dashboards/Administrador/comentarios/comentarios.php';
    }

    if ($context === 'reservation' && $id > 0 && in_array($role, [1, 2, 4], true)) {
        return $base . 'pages/biblioteca-catalogo/biblioteca-reservacion/reservacion.php?id=' . $id;
    }

    return null;
}
