<?php
/**
 * Modelo Prestamo — Appjoteca
 * Gestión de préstamos y devoluciones
 */

class Prestamo
{
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    /**
     * Registra la devolución de un préstamo activo.
     */
    public function registrarDevolucion($id_prestamo, $observaciones = null)
    {
        return is_array($this->registrarDevolucionesRelacionadas($id_prestamo, 1, $observaciones));
    }

    /**
     * Devuelve varios ejemplares del mismo libro y usuario en una operación atómica.
     */
    public function registrarDevolucionesRelacionadas($id_prestamo, $cantidad, $observaciones = null)
    {
        $cantidad = (int) $cantidad;
        if ($cantidad < 1) {
            error_log('Error en registrarDevolucionesRelacionadas: cantidad inválida.');
            return false;
        }

        try {
            $this->db->begin_transaction();

            $sqlBuscar = "SELECT e.fk_id_libro_ejemplar AS id_libro,
                                  p.id_reserva
                           FROM prestamos p
                           INNER JOIN reservas r ON r.id_reserva = p.id_reserva
                           INNER JOIN ejemplares e ON e.id_ejemplar = p.id_ejemplar
                           WHERE p.id_prestamo = ? AND p.estado IN ('activo', 'vencido')
                           FOR UPDATE";
            $qBuscar = $this->db->prepare($sqlBuscar);
            $qBuscar->bind_param("i", $id_prestamo);
            $qBuscar->execute();
            $referencia = $qBuscar->get_result()->fetch_assoc();
            $qBuscar->close();

            if (!$referencia) {
                throw new Exception("Préstamo no encontrado o ya devuelto.");
            }

            $idLibro = (int) $referencia['id_libro'];
            $idReserva = (int) $referencia['id_reserva'];

            $sqlCandidatos = "SELECT p.id_prestamo, p.id_ejemplar
                              FROM prestamos p
                              INNER JOIN reservas r ON r.id_reserva = p.id_reserva
                              INNER JOIN ejemplares e ON e.id_ejemplar = p.id_ejemplar
                              WHERE p.id_reserva = ?
                                AND e.fk_id_libro_ejemplar = ?
                                AND p.estado IN ('activo', 'vencido')
                              ORDER BY p.fecha_devolucion_prevista ASC, p.id_prestamo ASC
                              LIMIT ? FOR UPDATE";
            $qCandidatos = $this->db->prepare($sqlCandidatos);
            $qCandidatos->bind_param('iii', $idReserva, $idLibro, $cantidad);
            $qCandidatos->execute();
            $resultado = $qCandidatos->get_result();
            $prestamos = [];
            while ($fila = $resultado->fetch_assoc()) {
                $prestamos[] = $fila;
            }
            $qCandidatos->close();

            if (count($prestamos) !== $cantidad) {
                throw new Exception('La cantidad solicitada excede los ejemplares activos de esta reserva.');
            }

            $sqlPrestamo = "UPDATE prestamos
                            SET estado = 'devuelto', fecha_devolucion_real = CURDATE(), observaciones = ?
                            WHERE id_prestamo = ? AND estado IN ('activo', 'vencido')";
            $qPrestamo = $this->db->prepare($sqlPrestamo);
            $sqlEjemplar = "UPDATE ejemplares SET estado = 'Disponible' WHERE id_ejemplar = ?";
            $qEjemplar = $this->db->prepare($sqlEjemplar);
            $idsDevueltos = [];
            foreach ($prestamos as $prestamo) {
                $idPrestamoActual = (int) $prestamo['id_prestamo'];
                $idEjemplar = (int) $prestamo['id_ejemplar'];
                $qPrestamo->bind_param('si', $observaciones, $idPrestamoActual);
                $qPrestamo->execute();
                if ($qPrestamo->affected_rows !== 1) {
                    throw new Exception('No se pudo actualizar uno de los préstamos.');
                }
                $qEjemplar->bind_param('i', $idEjemplar);
                $qEjemplar->execute();
                if ($qEjemplar->affected_rows !== 1) {
                    throw new Exception('No se pudo liberar uno de los ejemplares.');
                }
                $idsDevueltos[] = $idPrestamoActual;
            }
            $qPrestamo->close();
            $qEjemplar->close();

            $this->db->commit();
            return $idsDevueltos;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en registrarDevolucionesRelacionadas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca como vencidos los préstamos activos cuya fecha prevista ya pasó.
     */
    public function actualizarVencidos()
    {
        $sql = "UPDATE prestamos
                SET estado = 'vencido'
                WHERE estado = 'activo' AND fecha_devolucion_prevista < CURDATE()";

        return $this->db->query($sql);
    }

    /**
     * Lista cada reserva como un préstamo lógico, aunque sus ejemplares
     * continúen almacenados como registros individuales.
     */
    public function listarActivos($offset = 0, $limit = 20)
    {
        // Primero actualizar vencidos
        $this->actualizarVencidos();

        $sql = "SELECT
                    MIN(p.id_prestamo) AS id_prestamo,
                    r.id_reserva,
                    MIN(p.fecha_prestamo) AS fecha_prestamo,
                    MIN(p.fecha_devolucion_prevista) AS fecha_devolucion_prevista,
                    CASE
                        WHEN SUM(CASE WHEN p.estado = 'vencido' THEN 1 ELSE 0 END) > 0 THEN 'vencido'
                        ELSE 'activo'
                    END AS estado,
                    MIN(e.id_ejemplar) AS id_ejemplar,
                    l.id_libro,
                    l.titulo AS libro,
                    l.portada,
                    l.edicion,
                    u.id_usuario,
                    u.nombre_apellido AS usuario,
                    COUNT(DISTINCT p.id_prestamo) AS cantidad_devolver,
                    COALESCE(
                        (SELECT GROUP_CONCAT(a.nombre SEPARATOR ', ')
                         FROM libro_autor la
                         JOIN autores a ON la.id_autor = a.id_autor
                         WHERE la.id_libro = l.id_libro),
                        'Autor desconocido'
                    ) AS autor,
                    DATEDIFF(MIN(p.fecha_devolucion_prevista), CURDATE()) AS dias_restantes
                FROM prestamos p
                JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                JOIN libros l ON e.fk_id_libro_ejemplar = l.id_libro
                JOIN reservas r ON p.id_reserva = r.id_reserva
                JOIN usuarios u ON r.fk_id_usuario_reserva = u.id_usuario
                WHERE p.estado IN ('activo', 'vencido')
                GROUP BY r.id_reserva
                ORDER BY MIN(p.fecha_devolucion_prevista) ASC
                LIMIT ? OFFSET ?";

        $query = $this->db->prepare($sql);
        $query->bind_param("ii", $limit, $offset);
        $query->execute();
        $resultado = $query->get_result();

        $prestamos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $prestamos[] = $fila;
        }
        $query->close();

        return $prestamos;
    }

    /**
     * Cuenta préstamos activos + vencidos.
     */
    public function contarActivos()
    {
        $this->actualizarVencidos();
        $sql = "SELECT COUNT(DISTINCT id_reserva) AS total FROM prestamos WHERE estado IN ('activo', 'vencido')";
        $resultado = $this->db->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int) ($fila['total'] ?? 0);
    }

    /**
     * Cuenta solo vencidos (atrasados).
     */
    public function contarVencidos()
    {
        $this->actualizarVencidos();
        $sql = "SELECT COUNT(DISTINCT id_reserva) AS total FROM prestamos WHERE estado = 'vencido'";
        $resultado = $this->db->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int) ($fila['total'] ?? 0);
    }

    /**
     * Cuenta los que vencen hoy.
     */
    public function contarVencenHoy()
    {
        $sql = "SELECT COUNT(DISTINCT id_reserva) AS total FROM prestamos
                WHERE estado = 'activo' AND fecha_devolucion_prevista = CURDATE()";
        $resultado = $this->db->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int) ($fila['total'] ?? 0);
    }

    /**
     * Agrupa los ejemplares de cada reserva y estado en un único movimiento
     * lógico para el historial. El tipo se deriva del estado.
     */
    public function listarMovimientos($offset = 0, $limit = 50)
    {
        $this->actualizarVencidos();

        $sql = "SELECT
                    MIN(p.id_prestamo) AS id_prestamo,
                    r.id_reserva,
                    MIN(p.fecha_prestamo) AS fecha_prestamo,
                    MAX(p.fecha_devolucion_prevista) AS fecha_devolucion_prevista,
                    MAX(p.fecha_devolucion_real) AS fecha_devolucion_real,
                    p.estado,
                    MIN(e.id_ejemplar) AS id_ejemplar,
                    l.titulo AS libro,
                    l.id_libro,
                    MAX(u.nombre_apellido) AS usuario,
                    MAX(u.id_usuario) AS id_usuario,
                    COUNT(DISTINCT p.id_prestamo) AS cantidad,
                    COALESCE(
                        (SELECT GROUP_CONCAT(a.nombre SEPARATOR ', ')
                         FROM libro_autor la
                         JOIN autores a ON la.id_autor = a.id_autor
                         WHERE la.id_libro = l.id_libro),
                        'Autor desconocido'
                    ) AS autor
                FROM prestamos p
                JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                JOIN libros l ON e.fk_id_libro_ejemplar = l.id_libro
                JOIN reservas r ON p.id_reserva = r.id_reserva
                JOIN usuarios u ON r.fk_id_usuario_reserva = u.id_usuario
                GROUP BY r.id_reserva, p.estado
                ORDER BY MAX(COALESCE(p.fecha_devolucion_real, p.fecha_prestamo)) DESC
                LIMIT ? OFFSET ?";

        $query = $this->db->prepare($sql);
        $query->bind_param("ii", $limit, $offset);
        $query->execute();
        $resultado = $query->get_result();

        $movimientos = [];
        while ($fila = $resultado->fetch_assoc()) {
            // Derivar tipo legible
            switch ($fila['estado']) {
                case 'devuelto':
                    $fila['tipo'] = 'return';
                    $fila['tipo_label'] = 'Devolución Completada';
                    break;
                case 'vencido':
                    $fila['tipo'] = 'overdue';
                    $fila['tipo_label'] = 'Marcado como Atrasado';
                    break;
                case 'activo':
                default:
                    $fila['tipo'] = 'loan';
                    $fila['tipo_label'] = 'Préstamo Emitido';
                    break;
            }
            $movimientos[] = $fila;
        }
        $query->close();

        return $movimientos;
    }

    /**
     * Cuenta total de movimientos (para paginación).
     */
    public function contarMovimientos()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM (
                    SELECT r.id_reserva, p.estado
                    FROM prestamos p
                    JOIN reservas r ON r.id_reserva = p.id_reserva
                    GROUP BY r.id_reserva, p.estado
                ) AS movimientos";
        $resultado = $this->db->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int) ($fila['total'] ?? 0);
    }

    /**
     * Obtiene un préstamo completo por id (para overlay).
     */
    public function obtenerPorId($id_prestamo)
    {
        $sql = "SELECT
                    p.id_prestamo,
                    p.fecha_prestamo,
                    p.fecha_devolucion_prevista,
                    p.fecha_devolucion_real,
                    p.estado,
                    p.observaciones,
                    e.id_ejemplar,
                    l.id_libro,
                    l.titulo AS libro,
                    l.portada,
                    l.edicion,
                    l.isbn,
                    u.id_usuario,
                    u.nombre_apellido AS usuario,
                    u.correo_institucional,
                    (SELECT COUNT(*)
                     FROM prestamos p2
                     WHERE p2.id_reserva = p.id_reserva
                       AND p2.estado IN ('activo', 'vencido')) AS cantidad_devolver,
                    COALESCE(
                        (SELECT GROUP_CONCAT(a.nombre SEPARATOR ', ')
                         FROM libro_autor la
                         JOIN autores a ON la.id_autor = a.id_autor
                         WHERE la.id_libro = l.id_libro),
                        'Autor desconocido'
                    ) AS autor,
                    DATEDIFF(p.fecha_devolucion_prevista, CURDATE()) AS dias_restantes
                FROM prestamos p
                JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                JOIN libros l ON e.fk_id_libro_ejemplar = l.id_libro
                JOIN reservas r ON p.id_reserva = r.id_reserva
                JOIN usuarios u ON r.fk_id_usuario_reserva = u.id_usuario
                WHERE p.id_prestamo = ?";

        $query = $this->db->prepare($sql);
        $query->bind_param("i", $id_prestamo);
        $query->execute();
        $fila = $query->get_result()->fetch_assoc();
        $query->close();

        return $fila ?: null;
    }
}