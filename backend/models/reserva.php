<?php

class Reserva {
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    /**
 * Devuelve el id del usuario dueño de la reserva.
 */
public function obtenerUsuarioDeReserva($id_reserva)
{
    $sql = "SELECT fk_id_usuario_reserva FROM reservas WHERE id_reserva = ?";
    $query = $this->db->prepare($sql);
    $query->bind_param("i", $id_reserva);
    $query->execute();
    $fila = $query->get_result()->fetch_assoc();
    $query->close();

    return $fila ? (int)$fila['fk_id_usuario_reserva'] : 0;
}

    public function crearReserva($id_usuario, $id_libro, $cantidad = 1)
    {
        try {
            $sql = "INSERT INTO reservas 
                    (fk_id_usuario_reserva, fk_id_libro_reserva, cantidad, fecha_reserva, fecha_limite, estado)
                    VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'activa')";

            $query = $this->db->prepare($sql);
            $query->bind_param("iii", $id_usuario, $id_libro, $cantidad);
            $query->execute();

            $id_reserva = $this->db->insert_id;
            $query->close();

            return $id_reserva;

        } catch (Exception $e) {
            error_log("Error en crearReserva: " . $e->getMessage());
            return false;
        }
    }

    public function aceptarReserva($id_reserva)
    {
        try {
            $this->db->begin_transaction();

            $sqlReserva = "SELECT fk_id_libro_reserva, fk_id_usuario_reserva 
                           FROM reservas WHERE id_reserva = ? AND estado = 'activa'";
            $qReserva = $this->db->prepare($sqlReserva);
            $qReserva->bind_param("i", $id_reserva);
            $qReserva->execute();
            $res = $qReserva->get_result()->fetch_assoc();
            $qReserva->close();

            if (!$res) {
                throw new Exception("Reserva no encontrada o ya procesada.");
            }

            $id_libro = $res['fk_id_libro_reserva'];

            $sqlEjemplar = "SELECT id_ejemplar FROM ejemplares 
                            WHERE fk_id_libro_ejemplar = ? AND estado = 'Disponible' 
                            LIMIT 1";
            $qEjemplar = $this->db->prepare($sqlEjemplar);
            $qEjemplar->bind_param("i", $id_libro);
            $qEjemplar->execute();
            $ejemplar = $qEjemplar->get_result()->fetch_assoc();
            $qEjemplar->close();

            if (!$ejemplar) {
                throw new Exception("No hay ejemplares disponibles.");
            }

            $id_ejemplar = $ejemplar['id_ejemplar'];

            $sqlUpdEj = "UPDATE ejemplares SET estado = 'Prestado' WHERE id_ejemplar = ?";
            $qUpdEj = $this->db->prepare($sqlUpdEj);
            $qUpdEj->bind_param("i", $id_ejemplar);
            $qUpdEj->execute();
            $qUpdEj->close();

            $sqlUpdRes = "UPDATE reservas SET estado = 'aceptada' WHERE id_reserva = ?";
            $qUpdRes = $this->db->prepare($sqlUpdRes);
            $qUpdRes->bind_param("i", $id_reserva);
            $qUpdRes->execute();
            $qUpdRes->close();

            $sqlPrestamo = "INSERT INTO prestamos 
                            (id_ejemplar, id_reserva, fecha_prestamo, fecha_devolucion_prevista, estado)
                            VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'activo')";
            $qPrestamo = $this->db->prepare($sqlPrestamo);
            $qPrestamo->bind_param("ii", $id_ejemplar, $id_reserva);
            $qPrestamo->execute();
            $id_prestamo = $this->db->insert_id;
            $qPrestamo->close();

            $this->db->commit();
            return $id_prestamo;

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

    public function listarReservasActivas()
    {
        $sql = "SELECT r.id_reserva, 
                       r.fk_id_libro_reserva AS id_libro,
                       u.nombre_apellido AS usuario,
                       l.titulo AS libro,
                       r.fecha_reserva,
                       r.cantidad
                FROM reservas r
                JOIN usuarios u ON r.fk_id_usuario_reserva = u.id_usuario
                JOIN libros l ON r.fk_id_libro_reserva = l.id_libro
                WHERE r.estado = 'activa'
                ORDER BY r.fecha_reserva ASC";

        $resultado = $this->db->query($sql);

        $reservas = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $reservas[] = $fila;
            }
        }
        return $reservas;
    }
}

?>