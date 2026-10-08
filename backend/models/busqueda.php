<?php
class Busqueda
{
    private $db;
    private $limite;

    public function __construct($connection, $limite = 8)
    {
        $this->db = $connection;
        $this->limite = max(1, min(20, (int) $limite));
    }

    // Libros por título, autor o isbn
    public function libros($texto)
    {
        $like = '%' . $texto . '%';
        $sql = "SELECT l.id_libro, l.titulo, l.isbn, l.portada,
                       m.nombre AS materia,
                       GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores
                FROM libros l
                LEFT JOIN materias m ON m.id_materia = l.id_materia
                LEFT JOIN libro_autor la ON la.id_libro = l.id_libro
                LEFT JOIN autores a ON a.id_autor = la.id_autor
                WHERE l.fecha_eliminacion_libro IS NULL
                  AND (
                      l.titulo LIKE ?
                      OR l.isbn LIKE ?
                      OR a.nombre LIKE ?
                  )
                GROUP BY l.id_libro
                ORDER BY l.titulo ASC
                LIMIT ?";

        $q = $this->db->prepare($sql);
        $lim = $this->limite;
        $q->bind_param('sssi', $like, $like, $like, $lim);
        $q->execute();
        $res = $q->get_result();

        $lista = [];
        while ($fila = $res->fetch_assoc()) {
            $lista[] = [
                'tipo'      => 'libro',
                'id'        => (int) $fila['id_libro'],
                'titulo'    => $fila['titulo'] ?? 'Sin título',
                'subtitulo' => trim(($fila['autores'] ?: 'Sin autor') . ' · ' . ($fila['materia'] ?: 'General')),
                'url'       => '/Appjoteca/pages/biblioteca-catalogo/vista-libro/book-view.php?id=' . (int) $fila['id_libro'],
            ];
        }
        $q->close();
        return $lista;
    }

    // Usuarios por nombre, usuario o correo
    public function usuarios($texto)
    {
        $like = '%' . $texto . '%';
        $sql = "SELECT u.id_usuario, u.nombre_apellido, u.nombre_usuario,
                       u.correo_institucional, r.nombre AS rol
                FROM usuarios u
                LEFT JOIN roles r ON r.id_rol = u.id_rol
                WHERE u.nombre_apellido LIKE ?
                   OR u.nombre_usuario LIKE ?
                   OR u.correo_institucional LIKE ?
                ORDER BY u.nombre_apellido ASC
                LIMIT ?";

        $q = $this->db->prepare($sql);
        $lim = $this->limite;
        $q->bind_param('sssi', $like, $like, $like, $lim);
        $q->execute();
        $res = $q->get_result();

        $lista = [];
        while ($fila = $res->fetch_assoc()) {
            $lista[] = [
                'tipo'      => 'usuario',
                'id'        => (int) $fila['id_usuario'],
                'titulo'    => $fila['nombre_apellido'] ?? $fila['nombre_usuario'],
                'subtitulo' => trim(($fila['rol'] ?? 'Usuario') . ' · @' . ($fila['nombre_usuario'] ?? '')),
                'url'       => '/Appjoteca/dashboards/bibliotecario/pages/usuarios/usuarios.php?q=' . urlencode($fila['nombre_usuario'] ?? ''),
            ];
        }
        $q->close();
        return $lista;
    }

    // Reservas activas por libro o usuario
    public function reservas($texto)
    {
        $like = '%' . $texto . '%';
        $sql = "SELECT r.id_reserva, r.cantidad, r.fecha_reserva,
                       l.titulo AS libro, u.nombre_apellido AS usuario
                FROM reservas r
                JOIN libros l ON l.id_libro = r.fk_id_libro_reserva
                JOIN usuarios u ON u.id_usuario = r.fk_id_usuario_reserva
                WHERE r.estado = 'activa'
                  AND (l.titulo LIKE ? OR u.nombre_apellido LIKE ? OR u.nombre_usuario LIKE ?)
                ORDER BY r.fecha_reserva DESC
                LIMIT ?";

        $q = $this->db->prepare($sql);
        $lim = $this->limite;
        $q->bind_param('sssi', $like, $like, $like, $lim);
        $q->execute();
        $res = $q->get_result();

        $lista = [];
        while ($fila = $res->fetch_assoc()) {
            $lista[] = [
                'tipo'      => 'reserva',
                'id'        => (int) $fila['id_reserva'],
                'titulo'    => 'Reserva #' . $fila['id_reserva'] . ' · ' . ($fila['libro'] ?? ''),
                'subtitulo' => ($fila['usuario'] ?? '') . ' · ' . (int) $fila['cantidad'] . ' ej.',
                'url'       => '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php',
            ];
        }
        $q->close();
        return $lista;
    }

    // Préstamos activos o vencidos por libro o usuario
    public function prestamos($texto)
    {
        $like = '%' . $texto . '%';
        $sql = "SELECT p.id_prestamo, p.estado, p.fecha_prestamo,
                       l.titulo AS libro, u.nombre_apellido AS usuario
                FROM prestamos p
                JOIN ejemplares e ON e.id_ejemplar = p.id_ejemplar
                JOIN libros l ON l.id_libro = e.fk_id_libro_ejemplar
                JOIN reservas r ON r.id_reserva = p.id_reserva
                JOIN usuarios u ON u.id_usuario = r.fk_id_usuario_reserva
                WHERE p.estado IN ('activo', 'vencido')
                  AND (l.titulo LIKE ? OR u.nombre_apellido LIKE ? OR u.nombre_usuario LIKE ?)
                ORDER BY p.fecha_prestamo DESC
                LIMIT ?";

        $q = $this->db->prepare($sql);
        $lim = $this->limite;
        $q->bind_param('sssi', $like, $like, $like, $lim);
        $q->execute();
        $res = $q->get_result();

        $lista = [];
        while ($fila = $res->fetch_assoc()) {
            $lista[] = [
                'tipo'      => 'prestamo',
                'id'        => (int) $fila['id_prestamo'],
                'titulo'    => 'Préstamo #' . $fila['id_prestamo'] . ' · ' . ($fila['libro'] ?? ''),
                'subtitulo' => ($fila['usuario'] ?? '') . ' · ' . ($fila['estado'] ?? ''),
                'url'       => '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php',
            ];
        }
        $q->close();
        return $lista;
    }

    // Comentarios (admin)
    public function comentarios($texto)
    {
        $like = '%' . $texto . '%';
        $sql = "SELECT id_comentario, nombre, tipo_comentario, comentario, correo
                FROM comentarios
                WHERE comentario LIKE ?
                   OR nombre LIKE ?
                   OR correo LIKE ?
                   OR tipo_comentario LIKE ?
                ORDER BY id_comentario DESC
                LIMIT ?";

        $q = $this->db->prepare($sql);
        $lim = $this->limite;
        $q->bind_param('ssssi', $like, $like, $like, $like, $lim);
        $q->execute();
        $res = $q->get_result();

        $lista = [];
        while ($fila = $res->fetch_assoc()) {
            $preview = mb_substr(trim($fila['comentario'] ?? ''), 0, 60);
            $lista[] = [
                'tipo'      => 'comentario',
                'id'        => (int) $fila['id_comentario'],
                'titulo'    => $preview !== '' ? $preview : 'Comentario #' . $fila['id_comentario'],
                'subtitulo' => trim(($fila['nombre'] ?? 'Anónimo') . ' · ' . ($fila['tipo_comentario'] ?? '')),
                'url'       => '/Appjoteca/dashboards/Administrador/comentarios/comentarios.php',
            ];
        }
        $q->close();
        return $lista;
    }
}