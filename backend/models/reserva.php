<?php
require_once __DIR__ . '/../helpers/fecha_devolucion.php';

/**
 * Modelo Reserva — Appjoteca
 * Gestión de reservas de libros
 */

class Reserva
{
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    public function obtenerUsuarioDeReserva($id_reserva)
    {
        $sql = "SELECT fk_id_usuario_reserva FROM reservas WHERE id_reserva = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param("i", $id_reserva);
        $query->execute();
        $fila = $query->get_result()->fetch_assoc();
        $query->close();

        return $fila ? (int) $fila['fk_id_usuario_reserva'] : 0;
    }

        /**
     * Detalle de una reserva: usuario, título del libro, cantidad, estado
     */
    public function obtenerDetalleReserva($id_reserva)
    {
        $sql = "SELECT
                    r.id_reserva,
                    r.fk_id_usuario_reserva AS id_usuario,
                    r.fk_id_libro_reserva AS id_libro,
                    r.cantidad,
                    r.estado,
                    r.fecha_reserva,
                    r.fecha_limite,
                    r.observacion,
                    l.titulo AS libro,
                    l.portada,
                    u.nombre_apellido AS usuario,
                    u.foto_perfil,
                    (SELECT COUNT(*)
                     FROM ejemplares e
                     WHERE e.fk_id_libro_ejemplar = l.id_libro
                       AND e.estado = 'Disponible') AS stock_disponible
                FROM reservas r
                JOIN libros l ON r.fk_id_libro_reserva = l.id_libro
                JOIN usuarios u ON r.fk_id_usuario_reserva = u.id_usuario
                WHERE r.id_reserva = ?
                LIMIT 1";

        $query = $this->db->prepare($sql);
        $query->bind_param("i", $id_reserva);
        $query->execute();
        $fila = $query->get_result()->fetch_assoc();
        $query->close();

        return $fila ?: null;
    }

    

    public function crearReserva($id_usuario, $id_libro, $cantidad = 1, $observacion = null, $fecha_devolucion_estimada = null)
    {
        try {
            $sql = "INSERT INTO reservas
                    (fk_id_usuario_reserva, fk_id_libro_reserva, cantidad, fecha_reserva, fecha_limite, fecha_devolucion_estimada, estado, observacion)
                    VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), ?, 'activa', ?)";

            $query = $this->db->prepare($sql);
            $obs = ($observacion !== null && $observacion !== '') ? substr($observacion, 0, 100) : null;
            $query->bind_param("iisss", $id_usuario, $id_libro, $cantidad, $fecha_devolucion_estimada, $obs);
            $query->execute();

            $id_reserva = $this->db->insert_id;
            $query->close();

            return $id_reserva;
        } catch (Exception $e) {
            error_log("Error en crearReserva: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPendienteDeUsuario($id_usuario, $id_libro)
    {
        $sql = "SELECT id_reserva, fecha_reserva, fecha_limite, observacion, estado
                FROM reservas
                WHERE fk_id_usuario_reserva = ?
                  AND fk_id_libro_reserva = ?
                  AND estado = 'activa'
                LIMIT 1";

        $query = $this->db->prepare($sql);
        $query->bind_param("ii", $id_usuario, $id_libro);
        $query->execute();
        $fila = $query->get_result()->fetch_assoc();
        $query->close();

        return $fila ?: null;
    }

    public function cancelarReserva($id_reserva, $id_usuario)
    {
        try {
            $sqlEstado = "SELECT estado
                          FROM reservas
                          WHERE id_reserva = ? AND fk_id_usuario_reserva = ?
                          LIMIT 1";
            $qEstado = $this->db->prepare($sqlEstado);
            $qEstado->bind_param("ii", $id_reserva, $id_usuario);
            $qEstado->execute();
            $reserva = $qEstado->get_result()->fetch_assoc();
            $qEstado->close();

            if (!$reserva) {
                return false;
            }
            if ($reserva['estado'] === 'cancelada') {
                return 'ya_cancelada';
            }
            if ($reserva['estado'] !== 'activa') {
                return false;
            }

            $sql = "UPDATE reservas
                    SET estado = 'cancelada'
                    WHERE id_reserva = ?
                      AND fk_id_usuario_reserva = ?
                      AND estado = 'activa'";

            $query = $this->db->prepare($sql);
            $query->bind_param("ii", $id_reserva, $id_usuario);
            $query->execute();
            $filas = $query->affected_rows;
            $query->close();

            if ($filas > 0) {
                return 'cancelada';
            }

            // Another request may have cancelled it after the initial read.
            $qEstado = $this->db->prepare($sqlEstado);
            $qEstado->bind_param("ii", $id_reserva, $id_usuario);
            $qEstado->execute();
            $reserva = $qEstado->get_result()->fetch_assoc();
            $qEstado->close();

            return ($reserva['estado'] ?? null) === 'cancelada' ? 'ya_cancelada' : false;
        } catch (Exception $e) {
            error_log("Error en cancelarReserva: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Acepta una reserva y genera un préstamo por cada ejemplar solicitado.
     * Usa min(cantidad solicitada, ejemplares disponibles).
     * @return array|false  Lista de id_prestamo generados, o false si falla
     */
    public function aceptarReserva($id_reserva)
    {
        try {
            $this->db->begin_transaction();

            $sqlReserva = "SELECT fk_id_libro_reserva, fk_id_usuario_reserva, cantidad, fecha_devolucion_estimada
                        FROM reservas WHERE id_reserva = ? AND estado = 'activa' FOR UPDATE";
            $qReserva = $this->db->prepare($sqlReserva);
            $qReserva->bind_param("i", $id_reserva);
            $qReserva->execute();
            $res = $qReserva->get_result()->fetch_assoc();
            $qReserva->close();

            if (!$res) {
                throw new Exception("Reserva no encontrada o ya procesada.");
            }

            $id_libro = (int) $res['fk_id_libro_reserva'];
            $cantidad = (int) ($res['cantidad'] ?? 1);
            if ($cantidad < 1) {
                $cantidad = 1;
            }

            $sqlEjemplar = "SELECT id_ejemplar FROM ejemplares
                            WHERE fk_id_libro_ejemplar = ? AND estado = 'Disponible'
                            LIMIT ? FOR UPDATE";
            $qEjemplar = $this->db->prepare($sqlEjemplar);
            $qEjemplar->bind_param("ii", $id_libro, $cantidad);
            $qEjemplar->execute();
            $resultado = $qEjemplar->get_result();
            $ejemplares = [];
            while ($fila = $resultado->fetch_assoc()) {
                $ejemplares[] = (int) $fila['id_ejemplar'];
            }
            $qEjemplar->close();

            if (empty($ejemplares)) {
                throw new Exception("No hay ejemplares disponibles.");
            }

            $sqlUpdEj = "UPDATE ejemplares SET estado = 'Prestado'
                         WHERE id_ejemplar = ? AND estado = 'Disponible'";
            $qUpdEj = $this->db->prepare($sqlUpdEj);

            $fechaDevolucion = $res['fecha_devolucion_estimada'];
            if (!$fechaDevolucion) {
                $fechaDevolucion = fechaDevolucionPredeterminada();
            }
            $sqlPrestamo = "INSERT INTO prestamos
                            (id_ejemplar, id_reserva, fecha_prestamo, fecha_devolucion_prevista, estado)
                            VALUES (?, ?, CURDATE(), ?, 'activo')";
            $qPrestamo = $this->db->prepare($sqlPrestamo);

            $ids_prestamo = [];
            foreach ($ejemplares as $id_ejemplar) {
                $qUpdEj->bind_param("i", $id_ejemplar);
                $qUpdEj->execute();
                if ($qUpdEj->affected_rows !== 1) {
                    throw new Exception("El ejemplar ya no está disponible.");
                }

                $qPrestamo->bind_param("iis", $id_ejemplar, $id_reserva, $fechaDevolucion);
                $qPrestamo->execute();
                $ids_prestamo[] = (int) $this->db->insert_id;
            }
            $qUpdEj->close();
            $qPrestamo->close();

            $sqlUpdRes = "UPDATE reservas SET estado = 'aceptada'
                          WHERE id_reserva = ? AND estado = 'activa'";
            $qUpdRes = $this->db->prepare($sqlUpdRes);
            $qUpdRes->bind_param("i", $id_reserva);
            $qUpdRes->execute();
            if ($qUpdRes->affected_rows !== 1) {
                throw new Exception("La reserva ya fue procesada.");
            }
            $qUpdRes->close();

            $this->db->commit();
            return $ids_prestamo;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en aceptarReserva: " . $e->getMessage());
            return false;
        }
    }
    public function rechazarReserva($id_reserva, $observacion = null)
    {
        try {
            $sql = "UPDATE reservas SET estado = 'rechazada', observacion = ?
                    WHERE id_reserva = ? AND estado = 'activa'";
            $query = $this->db->prepare($sql);
            $query->bind_param("si", $observacion, $id_reserva);
            $query->execute();

            $filas = $query->affected_rows;
            $query->close();

            return $filas > 0;
        } catch (Exception $e) {
            error_log("Error en rechazarReserva: " . $e->getMessage());
            return false;
        }
    }

    public function listarReservasActivas($offset = 0, $limit = 30)
    {
        $sql = "SELECT
                    r.id_reserva,
                    r.fk_id_libro_reserva AS id_libro,
                    r.fk_id_usuario_reserva AS id_usuario,
                    r.cantidad,
                    r.fecha_reserva,
                    r.fecha_limite,
                    r.observacion,
                    u.nombre_apellido AS usuario,
                    u.foto_perfil,
                    l.titulo AS libro,
                    l.edicion,
                    l.portada,
                    (SELECT COUNT(*)
                     FROM ejemplares ea
                     WHERE ea.fk_id_libro_ejemplar = l.id_libro
                       AND ea.estado = 'Disponible') AS stock_disponible,
                    COALESCE(
                        (SELECT GROUP_CONCAT(a.nombre SEPARATOR ', ')
                         FROM libro_autor la
                         JOIN autores a ON la.id_autor = a.id_autor
                         WHERE la.id_libro = l.id_libro),
                        'Autor desconocido'
                    ) AS autor,
                    COALESCE(
                        (SELECT c.nombre
                         FROM ejemplares e
                         JOIN colecciones c ON e.id_coleccion = c.id_coleccion
                         WHERE e.fk_id_libro_ejemplar = l.id_libro
                         LIMIT 1),
                        'General'
                    ) AS coleccion
                FROM reservas r
                JOIN usuarios u ON r.fk_id_usuario_reserva = u.id_usuario
                JOIN libros l ON r.fk_id_libro_reserva = l.id_libro
                WHERE r.estado = 'activa'
                ORDER BY r.fecha_reserva ASC
                LIMIT ? OFFSET ?";

        $query = $this->db->prepare($sql);
        $query->bind_param("ii", $limit, $offset);
        $query->execute();
        $resultado = $query->get_result();

        $reservas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $reservas[] = $fila;
        }
        $query->close();

        return $reservas;
    }

    public function contarReservasActivas()
    {
        $sql = "SELECT COUNT(*) AS total FROM reservas WHERE estado = 'activa'";
        $resultado = $this->db->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int) ($fila['total'] ?? 0);
    }
}