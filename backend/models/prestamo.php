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
        try {
            $this->db->begin_transaction();

            $sqlBuscar = "SELECT id_ejemplar FROM prestamos
                          WHERE id_prestamo = ? AND estado IN ('activo', 'vencido')";
            $qBuscar = $this->db->prepare($sqlBuscar);
            $qBuscar->bind_param("i", $id_prestamo);
            $qBuscar->execute();
            $prestamo = $qBuscar->get_result()->fetch_assoc();
            $qBuscar->close();

            if (!$prestamo) {
                throw new Exception("Préstamo no encontrado o ya devuelto.");
            }

            $id_ejemplar = $prestamo['id_ejemplar'];

            // Actualizar préstamo
            $sqlUpd = "UPDATE prestamos
                       SET estado = 'devuelto', fecha_devolucion_real = CURDATE(), observaciones = ?
                       WHERE id_prestamo = ?";
            $qUpd = $this->db->prepare($sqlUpd);
            $qUpd->bind_param("si", $observaciones, $id_prestamo);
            $qUpd->execute();
            $qUpd->close();

            // Liberar ejemplar
            $sqlEj = "UPDATE ejemplares SET estado = 'Disponible' WHERE id_ejemplar = ?";
            $qEj = $this->db->prepare($sqlEj);
            $qEj->bind_param("i", $id_ejemplar);
            $qEj->execute();
            $qEj->close();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en registrarDevolucion: " . $e->getMessage());
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
     * Lista préstamos activos (o vencidos) con datos enriquecidos.
     * Incluye días restantes (negativo si atrasado).
     */
    public function listarActivos($offset = 0, $limit = 20)
    {
        // Primero actualizar vencidos
        $this->actualizarVencidos();

        $sql = "SELECT
                    p.id_prestamo,
                    p.fecha_prestamo,
                    p.fecha_devolucion_prevista,
                    p.estado,
                    e.id_ejemplar,
                    l.id_libro,
                    l.titulo AS libro,
                    l.portada,
                    l.edicion,
                    u.id_usuario,
                    u.nombre_apellido AS usuario,
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
                WHERE p.estado IN ('activo', 'vencido')
                ORDER BY p.fecha_devolucion_prevista ASC
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
        $sql = "SELECT COUNT(*) AS total FROM prestamos WHERE estado IN ('activo', 'vencido')";
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
        $sql = "SELECT COUNT(*) AS total FROM prestamos WHERE estado = 'vencido'";
        $resultado = $this->db->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int) ($fila['total'] ?? 0);
    }

    /**
     * Cuenta los que vencen hoy.
     */
    public function contarVencenHoy()
    {
        $sql = "SELECT COUNT(*) AS total FROM prestamos
                WHERE estado = 'activo' AND fecha_devolucion_prevista = CURDATE()";
        $resultado = $this->db->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int) ($fila['total'] ?? 0);
    }

    /**
     * Lista todos los movimientos (préstamos de cualquier estado) para el historial.
     * Tipo se deriva del estado.
     */
    public function listarMovimientos($offset = 0, $limit = 50)
    {
        $this->actualizarVencidos();

        $sql = "SELECT
                    p.id_prestamo,
                    p.fecha_prestamo,
                    p.fecha_devolucion_prevista,
                    p.fecha_devolucion_real,
                    p.estado,
                    e.id_ejemplar,
                    l.titulo AS libro,
                    l.id_libro,
                    u.nombre_apellido AS usuario,
                    u.id_usuario,
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
                ORDER BY COALESCE(p.fecha_devolucion_real, p.fecha_prestamo) DESC
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
        $sql = "SELECT COUNT(*) AS total FROM prestamos";
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