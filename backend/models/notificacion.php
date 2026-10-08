<?php
class Notificacion
{
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    /**  */
    public function obtenerUsuariosPorRol($id_rol)
    {
        $sql = "SELECT id_usuario FROM usuarios WHERE id_rol = ? AND estado = '1'";
        $q = $this->db->prepare($sql);
        $q->bind_param('i', $id_rol);
        $q->execute();
        $res = $q->get_result();

        $ids = [];
        while ($fila = $res->fetch_assoc()) {
            $ids[] = (int) $fila['id_usuario'];
        }
        $q->close();
        return $ids;
    }

    /**
     * @param string     $mensaje
     * @param string|null $url_accion
     * @param int|array  $usuarios  id o lista de ids
     * @return int|false id_notificacion
     */
    public function crear($mensaje, $url_accion, $usuarios)
    {
        try {
            $this->db->begin_transaction();

            $sqlNoti = "INSERT INTO notificaciones (mensaje, url_accion) VALUES (?, ?)";
            $qNoti = $this->db->prepare($sqlNoti);
            $qNoti->bind_param('ss', $mensaje, $url_accion);
            $qNoti->execute();
            $id_notificacion = (int) $this->db->insert_id;
            $qNoti->close();

            if (!is_array($usuarios)) {
                $usuarios = [$usuarios];
            }

            $sqlRel = "INSERT INTO notificacion_usuario (id_notificacion, id_usuario, leida) VALUES (?, ?, 0)";
            $qRel = $this->db->prepare($sqlRel);

            foreach ($usuarios as $uid) {
                $id_user = (int) $uid;
                if ($id_user <= 0) {
                    continue;
                }
                $qRel->bind_param('ii', $id_notificacion, $id_user);
                $qRel->execute();
            }
            $qRel->close();

            $this->db->commit();
            return $id_notificacion;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Error crear notificacion: ' . $e->getMessage());
            return false;
        }
    }

    
    public function listar($id_usuario, $limite = 5, $offset = 0)
    {
        $limite  = max(1, (int) $limite);
        $offset  = max(0, (int) $offset);
        $id_usuario = (int) $id_usuario;

        $sql = "SELECT n.id_notificacion, n.mensaje, n.url_accion, n.fecha_creacion,
                       nu.leida
                FROM notificaciones n
                INNER JOIN notificacion_usuario nu ON nu.id_notificacion = n.id_notificacion
                WHERE nu.id_usuario = ?
                ORDER BY nu.leida ASC, n.fecha_creacion DESC
                LIMIT ? OFFSET ?";

        $q = $this->db->prepare($sql);
        $q->bind_param('iii', $id_usuario, $limite, $offset);
        $q->execute();
        $res = $q->get_result();

        $lista = [];
        while ($fila = $res->fetch_assoc()) {
            $lista[] = [
                'id'         => (int) $fila['id_notificacion'],
                'mensaje'    => $fila['mensaje'],
                'url'        => $fila['url_accion'],
                'leida'      => (int) $fila['leida'] === 1,
                'fecha'      => $fila['fecha_creacion'],
            ];
        }
        $q->close();
        return $lista;
    }

    public function contar($id_usuario)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM notificacion_usuario
                WHERE id_usuario = ?";
        $q = $this->db->prepare($sql);
        $q->bind_param('i', $id_usuario);
        $q->execute();
        $total = (int) $q->get_result()->fetch_assoc()['total'];
        $q->close();
        return $total;
    }

    public function contarNoLeidas($id_usuario)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM notificacion_usuario
                WHERE id_usuario = ? AND leida = 0";
        $q = $this->db->prepare($sql);
        $q->bind_param('i', $id_usuario);
        $q->execute();
        $total = (int) $q->get_result()->fetch_assoc()['total'];
        $q->close();
        return $total;
    }

    public function marcarLeida($id_notificacion, $id_usuario)
    {
        $sql = "UPDATE notificacion_usuario
                SET leida = 1
                WHERE id_notificacion = ? AND id_usuario = ?";
        $q = $this->db->prepare($sql);
        $q->bind_param('ii', $id_notificacion, $id_usuario);
        $q->execute();
        $ok = $q->affected_rows >= 0;
        $q->close();
        return $ok;
    }

    public function marcarTodasLeidas($id_usuario)
    {
        $sql = "UPDATE notificacion_usuario
                SET leida = 1
                WHERE id_usuario = ? AND leida = 0";
        $q = $this->db->prepare($sql);
        $q->bind_param('i', $id_usuario);
        $q->execute();
        $ok = $q->affected_rows >= 0;
        $q->close();
        return $ok;
    }

    public function eliminar($id_notificacion, $id_usuario)
    {
        $sql = "DELETE FROM notificacion_usuario
                WHERE id_notificacion = ? AND id_usuario = ?";
        $q = $this->db->prepare($sql);
        $q->bind_param('ii', $id_notificacion, $id_usuario);
        $q->execute();
        $ok = $q->affected_rows > 0;
        $q->close();
        return $ok;
    }
}