<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../models/reserva.php';
require_once __DIR__ . '/../models/notificacion.php';
require_once __DIR__ . '/../models/historial.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

$id_reserva = (int)($_POST['id_reserva'] ?? 0);
$id_libro   = (int)($_POST['id_libro']   ?? 0); 
$accion     = $_POST['accion'] ?? '';

if ($id_reserva <= 0 || !in_array($accion, ['aceptar', 'rechazar'])) {
    echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
    exit();
}

$modeloReserva   = new Reserva($connection);
$modeloNotif     = new Notificacion($connection);
$modeloHistorial = new Historial($connection);

// Obtener el usuario dueño de la reserva antes de procesarla
$id_usuario = $modeloReserva->obtenerUsuarioDeReserva($id_reserva);

if ($accion === 'aceptar') {

    $id_prestamo = $modeloReserva->aceptarReserva($id_reserva);

    if ($id_prestamo) {

        $modeloNotif->crear(
            "Tu reserva #$id_reserva fue aceptada. Ya puedes reclamar el libro.",
            "/Appjoteca/pages/history/historial.php",
            $id_usuario
        );

        // Historial
        $modeloHistorial->registrar(
            $id_usuario,
            'reserva',
            'Reserva aceptada',
            "Reserva #$id_reserva · Préstamo #$id_prestamo generado"
        );

        echo json_encode(['success' => true]);

    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo aceptar la reserva.']);
    }

} else { // rechazar

    $ok = $modeloReserva->rechazarReserva($id_reserva, 'Rechazada por bibliotecario');

    if ($ok) {
        $modeloNotif->crear(
            "Tu reserva #$id_reserva fue rechazada.",
            "/Appjoteca/pages/history/historial.php",
            $id_usuario
        );

        $modeloHistorial->registrar(
            $id_usuario,
            'reserva',
            'Reserva rechazada',
            "Reserva #$id_reserva · Rechazada por bibliotecario"
        );

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo rechazar la reserva.']);
    }
}