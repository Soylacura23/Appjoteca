<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../models/reserva.php';
require_once __DIR__ . '/../models/prestamo.php';
require_once __DIR__ . '/../models/notificacion.php';
require_once __DIR__ . '/../models/historial.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit();
}

requiereRol([3, 4]);

$accion      = $_POST['accion']      ?? '';
$id_reserva  = (int) ($_POST['id_reserva']  ?? 0);
$id_prestamo = (int) ($_POST['id_prestamo'] ?? 0);
$cantidadDevolver = (int) ($_POST['cantidad'] ?? 1);
$observacion = trim($_POST['observacion'] ?? '');

$accionesValidas = ['aceptar', 'rechazar', 'devolver'];

if (!in_array($accion, $accionesValidas)) {
    echo json_encode(['ok' => false, 'error' => 'Acción inválida']);
    exit();
}

$modeloReserva   = new Reserva($connection);
$modeloPrestamo  = new Prestamo($connection);
$modeloNotif     = new Notificacion($connection);
$modeloHistorial = new Historial($connection);

// ACEPTAR RESERVA
if ($accion === 'aceptar') {
    if ($id_reserva <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID de reserva inválido']);
        exit();
    }

    $detalle = $modeloReserva->obtenerDetalleReserva($id_reserva);
    if (!$detalle) {
        echo json_encode(['ok' => false, 'error' => 'Reserva no encontrada']);
        exit();
    }
    if ($detalle['estado'] !== 'activa') {
        echo json_encode(['ok' => false, 'error' => 'La reserva ya fue procesada.']);
        exit();
    }

    $id_usuario = (int) $detalle['id_usuario'];
    $tituloLibro = $detalle['libro'] ?? 'el libro';
    $cantidadSolicitada = max(1, (int) ($detalle['cantidad'] ?? 1));
    $ids_prestamo = $modeloReserva->aceptarReserva($id_reserva);

    if ($ids_prestamo && is_array($ids_prestamo) && count($ids_prestamo) > 0) {
        $n = count($ids_prestamo);
        $ejemplaresTxt = $n === 1 ? '1 ejemplar' : "{$n} ejemplares";
        $idsTxt = implode(', #', $ids_prestamo);

        $modeloNotif->crear(
            "Tu reserva #$id_reserva del libro «{$tituloLibro}» fue aceptada ({$ejemplaresTxt}). Ya puedes reclamarlo.",
            "/Appjoteca/pages/history/historial.php",
            $id_usuario
        );

        $modeloHistorial->registrar(
            $id_usuario,
            'reserva',
            'Reserva aceptada',
            "Reserva #$id_reserva · «{$tituloLibro}» · {$ejemplaresTxt} · Préstamo(s) #{$idsTxt}"
        );

        echo json_encode([
            'ok'           => true,
            'id_prestamo'  => $ids_prestamo[0],
            'id_prestamos' => $ids_prestamo,
            'cantidad'     => $n,
            'solicitados'  => $cantidadSolicitada
        ]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo aceptar la reserva. Verifica disponibilidad.']);
    }
    exit();
}

// RECHAZAR RESERVA
if ($accion === 'rechazar') {
    if ($id_reserva <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID de reserva inválido']);
        exit();
    }

    $detalle = $modeloReserva->obtenerDetalleReserva($id_reserva);
    if (!$detalle) {
        echo json_encode(['ok' => false, 'error' => 'Reserva no encontrada']);
        exit();
    }
    if ($detalle['estado'] !== 'activa') {
        echo json_encode(['ok' => false, 'error' => 'La reserva ya fue procesada.']);
        exit();
    }

    $id_usuario = (int) $detalle['id_usuario'];
    $tituloLibro = $detalle['libro'] ?? 'el libro';
    $ok = $modeloReserva->rechazarReserva($id_reserva, $observacion ?: 'Rechazada por bibliotecario');

    if ($ok) {
        $modeloNotif->crear(
            "Tu reserva #$id_reserva del libro «{$tituloLibro}» fue rechazada.",
            "/Appjoteca/pages/history/historial.php",
            $id_usuario
        );

        $modeloHistorial->registrar(
            $id_usuario,
            'reserva',
            'Reserva rechazada',
            "Reserva #$id_reserva · «{$tituloLibro}» · Rechazada por bibliotecario"
        );

        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo rechazar la reserva.']);
    }
    exit();
}
// DEVOLVER PRÉSTAMO
if ($accion === 'devolver') {
    if ($id_prestamo <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID de préstamo inválido']);
        exit();
    }
    if ($cantidadDevolver < 1) {
        echo json_encode(['ok' => false, 'error' => 'La cantidad a devolver debe ser al menos 1.']);
        exit();
    }

    // Obtener datos antes de devolver (para historial y notificación)
    $detalle = $modeloPrestamo->obtenerPorId($id_prestamo);
    if (!$detalle) {
        echo json_encode(['ok' => false, 'error' => 'Préstamo no encontrado']);
        exit();
    }

    $maximoDevolucion = (int) ($detalle['cantidad_devolver'] ?? 0);
    if ($cantidadDevolver > $maximoDevolucion) {
        echo json_encode([
            'ok' => false,
            'error' => "Solo puedes devolver hasta {$maximoDevolucion} ejemplar(es) de esta reserva."
        ]);
        exit();
    }
    $idsDevueltos = $modeloPrestamo->registrarDevolucionesRelacionadas(
        $id_prestamo,
        $cantidadDevolver,
        $observacion ?: null
    );

    if (is_array($idsDevueltos) && count($idsDevueltos) === $cantidadDevolver) {
        $id_usuario = (int) $detalle['id_usuario'];
        $cantidadTxt = count($idsDevueltos) === 1
            ? '1 ejemplar'
            : count($idsDevueltos) . ' ejemplares';
        $idsTxt = implode(', #', $idsDevueltos);

        $modeloNotif->crear(
            "Se registró la devolución de {$cantidadTxt} del libro «{$detalle['libro']}».",
            "/Appjoteca/pages/history/historial.php",
            $id_usuario
        );

        $modeloHistorial->registrar(
            $id_usuario,
            'devolucion',
            'Devoluciones registradas',
            "{$cantidadTxt} · Préstamo(s) #{$idsTxt} · {$detalle['libro']}"
        );

        echo json_encode(['ok' => true, 'cantidad' => count($idsDevueltos)]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo registrar la devolución.']);
    }
    exit();
}