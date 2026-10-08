<?php
function urlPortada(?string $portada, string $base = '/Appjoteca/'): string
{
    $default = $base . 'assets/images/books/default-cover.jpg';
    $portada = trim((string) $portada);

    if ($portada === '') {
        return $default;
    }

    if (preg_match('#^https?://#i', $portada)) {
        return $portada;
    }

    return $base . ltrim($portada, '/');
}

function disponibilidadLibro(array $libro): array
{
    $disponibles = 0;

    if (isset($libro['ejemplares']) && is_array($libro['ejemplares'])) {
        foreach ($libro['ejemplares'] as $ejemplar) {
            if (strcasecmp((string) ($ejemplar['estado'] ?? ''), 'Disponible') === 0) {
                $disponibles++;
            }
        }
    } else {
        $disponibles = (int) ($libro['disponibles'] ?? 0);
    }

    $ok = $disponibles > 0;

    return [
        'disponible' => $ok,
        'cantidad'   => $disponibles,
        'texto'      => $ok ? 'Disponible' : 'No disponible',
        'clase'      => $ok ? 'available' : 'unavailable',
    ];
}
