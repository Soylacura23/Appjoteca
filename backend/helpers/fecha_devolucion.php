<?php

function rangoFechaDevolucion(?DateTimeImmutable $fechaBase = null): array
{
    $inicio = ($fechaBase ?? new DateTimeImmutable('today'))->setTime(0, 0);
    if ((int) $inicio->format('N') >= 6) {
        $inicio = $inicio->modify('next monday');
    }

    return [
        'inicio' => $inicio,
        'maximo' => $inicio->modify('+11 days')
    ];
}

function validarFechaDevolucionEstimada($fecha): bool
{
    if (!is_string($fecha)) {
        return false;
    }

    $fechaSeleccionada = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    if (!$fechaSeleccionada || $fechaSeleccionada->format('Y-m-d') !== $fecha) {
        return false;
    }

    $rango = rangoFechaDevolucion();
    return $fechaSeleccionada >= $rango['inicio'] && $fechaSeleccionada <= $rango['maximo'];
}

function fechaDevolucionPredeterminada(): string
{
    return rangoFechaDevolucion()['maximo']->format('Y-m-d');
}
