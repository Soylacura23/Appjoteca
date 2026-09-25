<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../../backend/config/auth.php';
require_once __DIR__ . '/../../../backend/Database/conexion.php';
require_once __DIR__ . '/../../../backend/models/reserva.php';

requiereRol([3]);

$modeloReserva = new Reserva($connection);
$reservas = $modeloReserva->listarReservasActivas();

echo json_encode($reservas);