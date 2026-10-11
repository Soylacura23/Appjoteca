<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/Database/conexion.php';

try {
    $sql = "SELECT
                l.id_libro,
                l.titulo AS titulo_libro,
                l.portada,
                l.sinopsis AS resumen,
                m.nombre AS categoria,
                COALESCE(GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', '), 'Autor desconocido') AS autor,
                COALESCE(prestamos.total_prestamos, 0) AS total_prestamos,
                COALESCE(reservas.total_reservas, 0) AS total_reservas
            FROM libros l
            LEFT JOIN materias m ON m.id_materia = l.id_materia
            LEFT JOIN libro_autor la ON la.id_libro = l.id_libro
            LEFT JOIN autores a ON a.id_autor = la.id_autor
            LEFT JOIN (
                SELECT e.fk_id_libro_ejemplar AS id_libro, COUNT(*) AS total_prestamos
                FROM prestamos p
                JOIN ejemplares e ON e.id_ejemplar = p.id_ejemplar
                WHERE p.estado IN ('activo', 'vencido', 'devuelto')
                GROUP BY e.fk_id_libro_ejemplar
            ) prestamos ON prestamos.id_libro = l.id_libro
            LEFT JOIN (
                SELECT fk_id_libro_reserva AS id_libro, COUNT(*) AS total_reservas
                FROM reservas
                WHERE estado IN ('activa', 'aceptada', 'pendiente', 'aprobada')
                GROUP BY fk_id_libro_reserva
            ) reservas ON reservas.id_libro = l.id_libro
            WHERE l.fecha_eliminacion_libro IS NULL
            GROUP BY l.id_libro
            ORDER BY total_prestamos DESC, total_reservas DESC, l.fecha_registro_libro DESC, l.id_libro DESC
            LIMIT 6";

    $result = $connection->query($sql);
    if (!$result) {
        throw new RuntimeException('No se pudieron consultar las obras destacadas.');
    }

    $libros = [];
    while ($row = $result->fetch_assoc()) {
        $row['id_libro'] = (int) $row['id_libro'];
        $row['total_prestamos'] = (int) $row['total_prestamos'];
        $row['total_reservas'] = (int) $row['total_reservas'];
        $libros[] = $row;
    }
    $result->free();

    echo json_encode([
        'libros' => $libros,
        'recomendado' => $libros[0] ?? null
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $error) {
    error_log('Error en get_landing_books: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudieron cargar las obras destacadas.'
    ], JSON_UNESCAPED_UNICODE);
}
