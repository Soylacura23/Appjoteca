<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../backend/config/auth.php';
require_once __DIR__ . '/../../../backend/Database/conexion.php';
require_once __DIR__ . '/../../../backend/models/reserva.php';
require_once __DIR__ . '/../../../backend/models/prestamo.php';

requiereRol([3]);

$tipo  = $_GET['tipo']  ?? 'solicitudes';
$page  = max(1, (int) ($_GET['page']  ?? 1));
$limit = min(100, max(1, (int) ($_GET['limit'] ?? 30)));
$offset = ($page - 1) * $limit;
$id    = (int) ($_GET['id'] ?? 0);

$modeloReserva  = new Reserva($connection);
$modeloPrestamo = new Prestamo($connection);

try {
    switch ($tipo) {

        case 'solicitudes':
            $items = $modeloReserva->listarReservasActivas($offset, $limit);
            $total = $modeloReserva->contarReservasActivas();
            echo json_encode([
                'ok'     => true,
                'data'   => $items,
                'total'  => $total,
                'page'   => $page,
                'limit'  => $limit,
                'pages'  => (int) ceil($total / $limit)
            ]);
            break;

        case 'devoluciones':
            $items = $modeloPrestamo->listarActivos($offset, $limit);
            $total = $modeloPrestamo->contarActivos();
            echo json_encode([
                'ok'     => true,
                'data'   => $items,
                'total'  => $total,
                'page'   => $page,
                'limit'  => $limit,
                'pages'  => (int) ceil($total / $limit)
            ]);
            break;

        case 'historial':
            $items = $modeloPrestamo->listarMovimientos($offset, $limit);
            $total = $modeloPrestamo->contarMovimientos();
            echo json_encode([
                'ok'     => true,
                'data'   => $items,
                'total'  => $total,
                'page'   => $page,
                'limit'  => $limit,
                'pages'  => (int) ceil($total / $limit)
            ]);
            break;

        case 'stats':
            echo json_encode([
                'ok' => true,
                'data' => [
                    'nuevas_solicitudes' => $modeloReserva->contarReservasActivas(),
                    'items_atrasados'    => $modeloPrestamo->contarVencidos(),
                    'vencen_hoy'         => $modeloPrestamo->contarVencenHoy(),
                    'prestamos_activos'  => $modeloPrestamo->contarActivos()
                ]
            ]);
            break;

        case 'detalle':
            if ($id <= 0) {
                echo json_encode(['ok' => false, 'error' => 'ID inválido']);
                break;
            }
            $item = $modeloPrestamo->obtenerPorId($id);
            if (!$item) {
                echo json_encode(['ok' => false, 'error' => 'Préstamo no encontrado']);
                break;
            }
            echo json_encode(['ok' => true, 'data' => $item]);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Tipo no válido']);
    }
} catch (Exception $e) {
    error_log("Error get_reservaciones: " . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
}