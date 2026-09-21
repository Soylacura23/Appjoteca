<?php

class Historial {
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    /**
     * Registra una entrada en el historial.
     * 
     * @param int $id_usuario
     * @param string $tipo  'prestamo' | 'reserva' | 'devolucion' | 'incidencia'
     * @param string $accion  Ej: 'Préstamo realizado'
     * @param string $detalle Ej: 'Cien años de soledad · Ejemplar EJ-0045'
     * @param string $referencia  Ej: 'Devuelto el 1 sep. 2026 · Estado: Devuelto'
     */
    public function registrar($id_usuario, $tipo, $accion, $detalle, $referencia = null)
    {
        try {
            $sql = "INSERT INTO historial (id_usuario, tipo, accion, detalle, referencia, fecha)
                    VALUES (?, ?, ?, ?, ?, NOW())";

            $query = $this->db->prepare($sql);
            $query->bind_param("issss", $id_usuario, $tipo, $accion, $detalle, $referencia);
            $query->execute();

            $id = $this->db->insert_id;
            $query->close();

            return $id;

        } catch (Exception $e) {
            error_log("Error en registrar historial: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorUsuario($id_usuario, $tipo = null, $limite = 50)
    {
        $sql = "SELECT id_historial, tipo, accion, detalle, referencia, fecha
                FROM historial
                WHERE id_usuario = ?";
        
        if ($tipo !== null) {
            $sql .= " AND tipo = ?";
        }

        $sql .= " ORDER BY fecha DESC LIMIT ?";

        $query = $this->db->prepare($sql);

        if ($tipo !== null) {
            $query->bind_param("isi", $id_usuario, $tipo, $limite);
        } else {
            $query->bind_param("ii", $id_usuario, $limite);
        }

        $query->execute();
        $resultado = $query->get_result();

        $historial = [];
        while ($fila = $resultado->fetch_assoc()) {
            $historial[] = $fila;
        }
        $query->close();

        return $historial;
    }

    /**
     * Últimos movimientos globales (para dashboard de bibliotecario).
     */
    public function obtenerRecientes($limite = 10)
    {
        $sql = "SELECT h.id_historial, h.tipo, h.accion, h.detalle, h.referencia, h.fecha,
                       u.nombre_apellido AS usuario
                FROM historial h
                LEFT JOIN usuarios u ON h.id_usuario = u.id_usuario
                ORDER BY h.fecha DESC
                LIMIT ?";

        $query = $this->db->prepare($sql);
        $query->bind_param("i", $limite);
        $query->execute();
        $resultado = $query->get_result();

        $historial = [];
        while ($fila = $resultado->fetch_assoc()) {
            $historial[] = $fila;
        }
        $query->close();

        return $historial;
    }
}

?>