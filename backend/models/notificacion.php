<?php

class Notificacion {
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    /**
 * Devuelve los ids de todos los usuarios con un rol específico.
 * Útil para notificar a todos los bibliotecarios (rol 3) o admins (rol 4).
 */
    public function obtenerUsuariosPorRol($id_rol)
    {
        $sql = "SELECT id_usuario FROM usuarios WHERE id_rol = ? AND estado = '1'";
        $query = $this->db->prepare($sql);
        $query->bind_param("i", $id_rol);
        $query->execute();
        $resultado = $query->get_result();

        $ids = [];
        while ($fila = $resultado->fetch_assoc()) {
            $ids[] = (int)$fila['id_usuario'];
        }
        $query->close();

        return $ids;
    }

    /**
     * Crea una notificación y la asigna a uno o varios usuarios.
     * 
     * @param string $mensaje
     * @param string|null $url_accion
     * @param array|int $usuarios  Un id o array de ids
     * @return int|false  id_notificacion o false
     */
    public function crear($mensaje, $url_accion, $usuarios)
    {
        try {
            $this->db->begin_transaction();

            // Crear notificación
            $sqlNoti = "INSERT INTO notificaciones (mensaje, url_accion) VALUES (?, ?)";
            $qNoti = $this->db->prepare($sqlNoti);
            $qNoti->bind_param("ss", $mensaje, $url_accion);
            $qNoti->execute();
            $id_notificacion = $this->db->insert_id;
            $qNoti->close();

            // Normalizar usuarios
            if (!is_array($usuarios)) {
                $usuarios = [$usuarios];
            }

            // Asignar a cada usuario
            $sqlRel = "INSERT INTO notificacion_usuario (id_notificacion, id_usuario) VALUES (?, ?)";
            $qRel = $this->db->prepare($sqlRel);

            $id_user = 0;
            $qRel->bind_param("ii", $id_notificacion, $id_user);

            foreach ($usuarios as $uid) {
                $id_user = (int)$uid;
                if ($id_user > 0) {
                    $qRel->execute();
                }
            }
            $qRel->close();

            $this->db->commit();
            return $id_notificacion;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en crear notificacion: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerNoLeidas($id_usuario)
    {
        $sql = "SELECT n.id_notificacion, n.mensaje, n.url_accion, n.fecha_creacion
                FROM notificaciones n
                JOIN notificacion_usuario nu ON n.id_notificacion = nu.id_notificacion
                WHERE nu.id_usuario = ? AND n.leida = 0
                ORDER BY n.fecha_creacion DESC";

        $query = $this->db->prepare($sql);
        $query->bind_param("i", $id_usuario);
        $query->execute();
        $resultado = $query->get_result();

        $notificaciones = [];
        while ($fila = $resultado->fetch_assoc()) {
            $notificaciones[] = $fila;
        }
        $query->close();

        return $notificaciones;
    }

    public function marcarLeida($id_notificacion)
    {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param("i", $id_notificacion);
        $query->execute();

        $ok = $query->affected_rows > 0;
        $query->close();
        return $ok;
    }
}

?>