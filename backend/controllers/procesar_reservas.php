<?php
/**
 * Controlador POST — Procesar reservas y devoluciones
 * Acciones: aceptar | rechazar | devolver
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../models/reserva.php';
require_once __DIR__ . '/../models/prestamo.php';
require_once __DIR__ . '/../models/notificacion.php';
require_once __DIR__ . '/../models/historial.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit();
}

$accion      = $_POST['accion']      ?? '';
$id_reserva  = (int) ($_POST['id_reserva']  ?? 0);
$id_prestamo = (int) ($_POST['id_prestamo'] ?? 0);
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

// ── ACEPTAR RESERVA ──────────────────────────────────────
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

// ── RECHAZAR RESERVA ─────────────────────────────────────
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
// ── DEVOLVER PRÉSTAMO ────────────────────────────────────
if ($accion === 'devolver') {
    if ($id_prestamo <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID de préstamo inválido']);
        exit();
    }

    // Obtener datos antes de devolver (para historial y notificación)
    $detalle = $modeloPrestamo->obtenerPorId($id_prestamo);
    if (!$detalle) {
        echo json_encode(['ok' => false, 'error' => 'Préstamo no encontrado']);
        exit();
    }

    $ok = $modeloPrestamo->registrarDevolucion($id_prestamo, $observacion ?: null);

    if ($ok) {
        $id_usuario = (int) $detalle['id_usuario'];

        $modeloNotif->crear(
            "Tu préstamo del libro «{$detalle['libro']}» fue marcado como devuelto.",
            "/Appjoteca/pages/history/historial.php",
            $id_usuario
        );

        $modeloHistorial->registrar(
            $id_usuario,
            'devolucion',
            'Devolución registrada',
            "Préstamo #{$id_prestamo} · {$detalle['libro']}"
        );

        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo registrar la devolución.']);
    }
    exit();
}