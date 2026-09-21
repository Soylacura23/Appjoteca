<?php

class Prestamo {
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    public function registrarDevolucion($id_prestamo, $observaciones = null)
    {
        try {
            $this->db->begin_transaction();

            $sqlBuscar = "SELECT id_ejemplar FROM prestamos 
                          WHERE id_prestamo = ? AND estado = 'activo'";
            $qBuscar = $this->db->prepare($sqlBuscar);
            $qBuscar->bind_param("i", $id_prestamo);
            $qBuscar->execute();
            $prestamo = $qBuscar->get_result()->fetch_assoc();
            $qBuscar->close();

            if (!$prestamo) {
                throw new Exception("Préstamo no encontrado o ya devuelto.");
            }

            $id_ejemplar = $prestamo['id_ejemplar'];

            // actualizar
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

    public function actualizarVencidos()
    {
        $sql = "UPDATE prestamos 
                SET estado = 'vencido' 
                WHERE estado = 'activo' AND fecha_devolucion_prevista < CURDATE()";
        
        return $this->db->query($sql);
    }

    public function listarActivos()
    {
        $sql = "SELECT p.id_prestamo,
                       p.fecha_prestamo,
                       p.fecha_devolucion_prevista,
                       e.id_ejemplar,
                       l.titulo AS libro,
                       u.nombre_apellido AS usuario
                FROM prestamos p
                JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                JOIN libros l ON e.fk_id_libro_ejemplar = l.id_libro
                JOIN reservas r ON p.id_reserva = r.id_reserva
                JOIN usuarios u ON r.fk_id_usuario_reserva = u.id_usuario
                WHERE p.estado = 'activo'
                ORDER BY p.fecha_devolucion_prevista ASC";

        $resultado = $this->db->query($sql);

        $prestamos = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $prestamos[] = $fila;
            }
        }
        return $prestamos;
    }
}

?>