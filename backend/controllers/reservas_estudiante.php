<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../models/reserva.php';
require_once __DIR__ . '/../models/libro.php';
require_once __DIR__ . '/../models/notificacion.php';
require_once __DIR__ . '/../models/historial.php';
require_once __DIR__ . '/../helpers/libros.php';
require_once __DIR__ . '/../helpers/fecha_devolucion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit();
}

requiereRol([1, 2]);

$id_usuario = (int) ($_SESSION['usuario_id'] ?? 0);
if ($id_usuario <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Debes iniciar sesión']);
    exit();
}

$accion = $_POST['accion'] ?? '';
$modelo = new Reserva($connection);
$notif  = new Notificacion($connection);
$hist   = new Historial($connection);

// Crear reserva
if ($accion === 'crear') {
    $id_libro  = (int) ($_POST['id_libro'] ?? 0);
    $razon     = trim($_POST['razon'] ?? '');
    $cantidad  = (int) ($_POST['cantidad'] ?? 1);
    $fechaDevolucionRaw = $_POST['fecha_devolucion'] ?? '';
    $fechaDevolucion = is_string($fechaDevolucionRaw) ? trim($fechaDevolucionRaw) : '';

    if ($id_libro <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Libro inválido']);
        exit();
    }

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    if (!validarFechaDevolucionEstimada($fechaDevolucion)) {
        echo json_encode([
            'ok' => false,
            'error' => 'La fecha debe estar entre el próximo día hábil y el límite de 10 días, con un día de tolerancia.'
        ]);
        exit();
    }

    $modeloLibro = new libro($connection);
    $libroData = $modeloLibro->obtenerLibro($id_libro);
    if (!$libroData) {
        echo json_encode(['ok' => false, 'error' => 'Libro no encontrado']);
        exit();
    }

    $disp = disponibilidadLibro($libroData);
    $maxDisponibles = max(0, (int) $disp['cantidad']);

    if ($maxDisponibles < 1) {
        echo json_encode(['ok' => false, 'error' => 'No hay ejemplares disponibles de este libro.']);
        exit();
    }

    if ($cantidad > $maxDisponibles) {
        $cantidad = $maxDisponibles;
    }

    if ($modelo->obtenerPendienteDeUsuario($id_usuario, $id_libro)) {
        echo json_encode(['ok' => false, 'error' => 'Ya tienes una solicitud pendiente de este libro.']);
        exit();
    }

    $tituloLibro = $libroData['titulo'] ?? ('Libro #' . $id_libro);
    $id_reserva = $modelo->crearReserva($id_usuario, $id_libro, $cantidad, $razon, $fechaDevolucion);

    if ($id_reserva) {
        $bibliotecarios = $notif->obtenerUsuariosPorRol(3);
        if (!empty($bibliotecarios)) {
            $ejemplaresTxt = $cantidad === 1 ? '1 ejemplar' : "{$cantidad} ejemplares";
            $notif->crear(
                "Nueva solicitud de reserva #$id_reserva · «{$tituloLibro}» ({$ejemplaresTxt})",
                "/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php",
                $bibliotecarios
            );
        }

        $hist->registrar(
            $id_usuario,
            'reserva',
            'Solicitud de reserva',
            "Reserva #$id_reserva · «{$tituloLibro}» · Cantidad: {$cantidad}"
        );

        echo json_encode([
            'ok'      => true,
            'mensaje' => 'Solicitud enviada. El bibliotecario la revisará pronto.'
        ]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo crear la reserva.']);
    }
    exit();
}

// CANCELAR
if ($accion === 'cancelar') {
    $id_reserva = (int) ($_POST['id_reserva'] ?? 0);

    if ($id_reserva <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Reserva inválida']);
        exit();
    }

    $resultadoCancelacion = $modelo->cancelarReserva($id_reserva, $id_usuario);

    if ($resultadoCancelacion === 'cancelada') {
        $hist->registrar(
            $id_usuario,
            'reserva',
            'Reserva cancelada',
            "Reserva #$id_reserva cancelada por el usuario"
        );

        echo json_encode([
            'ok'      => true,
            'mensaje' => 'Solicitud cancelada correctamente.'
        ]);
        exit();
    }

    if ($resultadoCancelacion === 'ya_cancelada') {
        echo json_encode([
            'ok'      => true,
            'mensaje' => 'Esta solicitud ya estaba cancelada.'
        ]);
        exit();
    }

    echo json_encode(['ok' => false, 'error' => 'No se pudo cancelar. Solo puedes cancelar si sigue pendiente.']);
    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción inválida']);