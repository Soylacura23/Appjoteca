<?php

class libro
{
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    /* ── Listado simple (catálogo público) ── */
    public function listarLibros($soloActivos = true)
    {
        $where = $soloActivos ? "WHERE l.fecha_eliminacion_libro IS NULL" : "";

        $sql = "SELECT l.id_libro, l.titulo, l.isbn, l.portada, l.id_materia,
                       l.publicacion_year, l.fecha_registro_libro,
                       m.nombre AS materia,
                       GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores,
                       COUNT(DISTINCT e.id_ejemplar) AS total_ejemplares,
                       SUM(CASE WHEN e.estado = 'Disponible' THEN 1 ELSE 0 END) AS disponibles,
                       SUM(CASE WHEN e.estado = 'Prestado'   THEN 1 ELSE 0 END) AS prestados
                FROM libros l
                LEFT JOIN materias m     ON m.id_materia = l.id_materia
                LEFT JOIN libro_autor la ON la.id_libro  = l.id_libro
                LEFT JOIN autores a      ON a.id_autor   = la.id_autor
                LEFT JOIN ejemplares e   ON e.fk_id_libro_ejemplar = l.id_libro
                $where
                GROUP BY l.id_libro
                ORDER BY l.id_libro DESC";

        $query = $this->db->query($sql);
        $libros = [];
        while ($fila = $query->fetch_assoc()) {
            $libros[] = $fila;
        }
        $query->close();
        return $libros;
    }

    /* ── Listado paginado (inventario) ── */
    public function listarLibrosPaginado($pagina, $porPagina, $estado = '', $idMateria = 0, $orden = 'title-asc', $busqueda = '', $idAutor = 0)
    {
        $where  = ["l.fecha_eliminacion_libro IS NULL"];
        $params = [];
        $types  = '';

        if ($idMateria > 0) {
            $where[]  = "l.id_materia = ?";
            $params[] = $idMateria;
            $types   .= 'i';
        }

        if ($idAutor > 0) {
            $where[] = "EXISTS (
                            SELECT 1 FROM libro_autor la_filtro
                            WHERE la_filtro.id_libro = l.id_libro AND la_filtro.id_autor = ?
                        )";
            $params[] = $idAutor;
            $types   .= 'i';
        }

        if ($busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $where[] = "(l.titulo LIKE ? OR l.isbn LIKE ?
                         OR EXISTS (
                             SELECT 1 FROM materias mc
                             WHERE mc.id_materia = l.id_materia AND mc.nombre LIKE ?
                         )
                         OR EXISTS (
                             SELECT 1
                             FROM libro_autor lac
                             INNER JOIN autores ac ON ac.id_autor = lac.id_autor
                             WHERE lac.id_libro = l.id_libro AND ac.nombre LIKE ?
                         ))";
            array_push($params, $like, $like, $like, $like);
            $types .= 'ssss';
        }

        if (in_array($estado, ['available', 'disponible'], true)) {
            $where[] = "EXISTS (
                            SELECT 1 FROM ejemplares ea
                            WHERE ea.fk_id_libro_ejemplar = l.id_libro AND ea.estado = 'Disponible'
                        )";
        } elseif (in_array($estado, ['borrowed', 'prestado'], true)) {
            $where[] = "EXISTS (
                            SELECT 1 FROM ejemplares ep
                            WHERE ep.fk_id_libro_ejemplar = l.id_libro AND ep.estado = 'Prestado'
                        )";
        } elseif (in_array($estado, ['reserved', 'reservado'], true)) {
            $where[] = "EXISTS (
                            SELECT 1 FROM reservas r
                            WHERE r.fk_id_libro_reserva = l.id_libro AND r.estado = 'activa'
                        )";
        } elseif ($estado === 'out') {
            $where[] = "EXISTS (
                            SELECT 1 FROM ejemplares et
                            WHERE et.fk_id_libro_ejemplar = l.id_libro
                        )
                        AND NOT EXISTS (
                            SELECT 1 FROM ejemplares ea
                            WHERE ea.fk_id_libro_ejemplar = l.id_libro AND ea.estado = 'Disponible'
                        )";
        }

        $orderBy = match ($orden) {
            'title-desc'  => 'l.titulo DESC',
            'author-asc'  => 'autores ASC',
            'recommended' => 'total_prestamos DESC, l.id_libro DESC',
            'recent'      => 'l.fecha_registro_libro DESC, l.id_libro DESC',
            'borrowed'    => 'prestados DESC, total_prestamos DESC, l.id_libro DESC',
            'date-desc'   => 'l.fecha_registro_libro DESC, l.id_libro DESC',
            'copies-desc' => 'total_ejemplares DESC',
            default       => 'l.titulo ASC',
        };

        $whereSql = implode(' AND ', $where);

        // El total respeta los mismos filtros de libro y disponibilidad.
        $sqlCount = "SELECT COUNT(DISTINCT l.id_libro) AS total
                     FROM libros l
                     WHERE $whereSql";

        $qCount = $this->db->prepare($sqlCount);
        if ($types !== '') {
            $qCount->bind_param($types, ...$params);
        }
        $qCount->execute();
        $total = (int) $qCount->get_result()->fetch_assoc()['total'];
        $qCount->close();

        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT l.id_libro, l.titulo, l.isbn, l.portada, l.id_materia,
                       l.sinopsis, l.publicacion_year, l.fecha_registro_libro,
                       m.nombre AS materia,
                       GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores,
                       COUNT(DISTINCT e.id_ejemplar) AS total_ejemplares,
                       COUNT(DISTINCT CASE WHEN e.estado = 'Disponible' THEN e.id_ejemplar END) AS disponibles,
                       COUNT(DISTINCT CASE WHEN e.estado = 'Prestado' THEN e.id_ejemplar END) AS prestados,
                       (SELECT COALESCE(SUM(r.cantidad), 0)
                        FROM reservas r
                        WHERE r.fk_id_libro_reserva = l.id_libro
                          AND r.estado = 'activa') AS reservados,
                       (SELECT COUNT(*)
                        FROM prestamos p
                        INNER JOIN ejemplares ep ON ep.id_ejemplar = p.id_ejemplar
                        WHERE ep.fk_id_libro_ejemplar = l.id_libro) AS total_prestamos
                FROM libros l
                LEFT JOIN materias m     ON m.id_materia = l.id_materia
                LEFT JOIN libro_autor la ON la.id_libro  = l.id_libro
                LEFT JOIN autores a      ON a.id_autor   = la.id_autor
                LEFT JOIN ejemplares e   ON e.fk_id_libro_ejemplar = l.id_libro
                WHERE $whereSql
                GROUP BY l.id_libro
                ORDER BY $orderBy
                LIMIT ? OFFSET ?";

        $params[] = $porPagina;
        $params[] = $offset;
        $types   .= 'ii';

        $q = $this->db->prepare($sql);
        $q->bind_param($types, ...$params);
        $q->execute();
        $res = $q->get_result();

        $libros = [];
        while ($fila = $res->fetch_assoc()) {
            $libros[] = $fila;
        }
        $q->close();

        return [
            'libros'        => $libros,
            'total'         => $total,
            'pagina'        => $pagina,
            'por_pagina'    => $porPagina,
            'total_paginas' => max(1, (int) ceil($total / $porPagina)),
        ];
    }

    /* ── Obtener un libro completo ── */
    public function obtenerLibro($id_libro)
    {
        $query = $this->db->prepare(
            "SELECT l.*, m.nombre AS materia, i.nombre_idioma
             FROM libros l
             LEFT JOIN materias m ON m.id_materia = l.id_materia
             LEFT JOIN idioma i   ON i.id_idioma  = l.idioma
             WHERE l.id_libro = ? AND l.fecha_eliminacion_libro IS NULL"
        );
        $query->bind_param("i", $id_libro);
        $query->execute();
        $libro = $query->get_result()->fetch_assoc();
        $query->close();

        if (!$libro) {
            return null;
        }

        // Autores
        $qA = $this->db->prepare(
            "SELECT a.nombre FROM libro_autor la
             INNER JOIN autores a ON a.id_autor = la.id_autor
             WHERE la.id_libro = ? ORDER BY a.nombre"
        );
        $qA->bind_param("i", $id_libro);
        $qA->execute();
        $resA = $qA->get_result();
        $autores = [];
        while ($f = $resA->fetch_assoc()) {
            $autores[] = $f['nombre'];
        }
        $qA->close();

        $libro['autores_array'] = $autores;
        $libro['autores']       = implode(', ', $autores);

        // Ejemplares
        $qE = $this->db->prepare(
            "SELECT id_ejemplar, estado, id_coleccion
             FROM ejemplares WHERE fk_id_libro_ejemplar = ?
             ORDER BY id_ejemplar"
        );
        $qE->bind_param("i", $id_libro);
        $qE->execute();
        $resE = $qE->get_result();
        $ejemplares = [];
        while ($f = $resE->fetch_assoc()) {
            $ejemplares[] = $f;
        }
        $qE->close();

        $libro['ejemplares'] = $ejemplares;
        return $libro;
    }

    /**
     * Libros de la misma materia o del mismo autor.
     */
    public function obtenerRelacionados($id_libro, $limite = 4)
    {
        $id_libro = (int) $id_libro;
        $limite   = max(1, (int) $limite);

        $sql = "SELECT l.id_libro, l.titulo, l.portada, l.id_materia,
                       m.nombre AS materia,
                       GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores
                FROM libros l
                LEFT JOIN materias m     ON m.id_materia = l.id_materia
                LEFT JOIN libro_autor la ON la.id_libro  = l.id_libro
                LEFT JOIN autores a      ON a.id_autor   = la.id_autor
                WHERE l.fecha_eliminacion_libro IS NULL
                  AND l.id_libro != ?
                  AND (
                        l.id_materia = (SELECT id_materia FROM libros WHERE id_libro = ?)
                        OR l.id_libro IN (
                            SELECT la2.id_libro
                            FROM libro_autor la2
                            WHERE la2.id_autor IN (
                                SELECT la3.id_autor FROM libro_autor la3 WHERE la3.id_libro = ?
                            )
                        )
                  )
                GROUP BY l.id_libro
                ORDER BY (l.id_materia = (SELECT id_materia FROM libros WHERE id_libro = ?)) DESC,
                         l.id_libro DESC
                LIMIT ?";

        $query = $this->db->prepare($sql);
        $query->bind_param("iiiii", $id_libro, $id_libro, $id_libro, $id_libro, $limite);
        $query->execute();
        $res = $query->get_result();

        $libros = [];
        while ($fila = $res->fetch_assoc()) {
            $libros[] = $fila;
        }
        $query->close();

        if (count($libros) >= $limite) {
            return $libros;
        }

        $faltan = $limite - count($libros);
        $excluidos = array_merge([$id_libro], array_column($libros, 'id_libro'));
        $placeholders = implode(',', array_fill(0, count($excluidos), '?'));
        $types = str_repeat('i', count($excluidos)) . 'i';

        $sqlExtra = "SELECT l.id_libro, l.titulo, l.portada, l.id_materia,
                            m.nombre AS materia,
                            GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores
                     FROM libros l
                     LEFT JOIN materias m     ON m.id_materia = l.id_materia
                     LEFT JOIN libro_autor la ON la.id_libro  = l.id_libro
                     LEFT JOIN autores a      ON a.id_autor   = la.id_autor
                     WHERE l.fecha_eliminacion_libro IS NULL
                       AND l.id_libro NOT IN ($placeholders)
                     GROUP BY l.id_libro
                     ORDER BY l.id_libro DESC
                     LIMIT ?";

        $params = array_map('intval', $excluidos);
        $params[] = $faltan;

        $qExtra = $this->db->prepare($sqlExtra);
        $qExtra->bind_param($types, ...$params);
        $qExtra->execute();
        $resExtra = $qExtra->get_result();
        while ($fila = $resExtra->fetch_assoc()) {
            $libros[] = $fila;
        }
        $qExtra->close();

        return $libros;
    }

    /* ── Catálogos ── */
    public function obtenerCatalogos()
    {
        return [
            'materias'    => $this->listarSimple("SELECT id_materia AS id, nombre FROM materias ORDER BY nombre"),
            'editoriales' => $this->listarSimple("SELECT id_editorial AS id, nombre FROM editoriales ORDER BY nombre"),
            'tipos'       => $this->listarSimple("SELECT id_tipo_material AS id, nombre FROM tipos_materiales ORDER BY nombre"),
            'colecciones' => $this->listarSimple("SELECT id_coleccion AS id, nombre FROM colecciones ORDER BY nombre"),
            'idiomas'     => $this->listarSimple("SELECT id_idioma AS id, nombre_idioma AS nombre FROM idioma ORDER BY nombre_idioma"),
        ];
    }

    public function obtenerHistorial($id_libro, $limite = 8)
    {
        $limite = min(12, max(1, (int) $limite));
        $sql = "SELECT actividad, usuario, detalle, cantidad, observacion, fecha
                FROM (
                    SELECT
                        CASE r.estado
                            WHEN 'activa' THEN 'reservado'
                            WHEN 'aceptada' THEN 'reservado'
                            WHEN 'rechazada' THEN 'reserva_rechazada'
                            WHEN 'cancelada' THEN 'reserva_cancelada'
                            ELSE 'reserva'
                        END AS actividad,
                        u.nombre_apellido AS usuario,
                        CONCAT('Reserva #', r.id_reserva) AS detalle,
                        r.cantidad AS cantidad,
                        r.observacion,
                        r.fecha_reserva AS fecha
                    FROM reservas r
                    INNER JOIN usuarios u ON u.id_usuario = r.fk_id_usuario_reserva
                    WHERE r.fk_id_libro_reserva = ?
                      AND r.estado IN ('activa', 'aceptada', 'rechazada', 'cancelada')
                      AND NOT EXISTS (
                          SELECT 1 FROM prestamos p
                          WHERE p.id_reserva = r.id_reserva
                      )

                    UNION ALL

                    SELECT
                        CASE p.estado
                            WHEN 'devuelto' THEN 'devuelto'
                            WHEN 'vencido' THEN 'vencido'
                            ELSE 'prestado'
                        END AS actividad,
                        u.nombre_apellido AS usuario,
                        CONCAT('Reserva #', r.id_reserva) AS detalle,
                        COUNT(*) AS cantidad,
                        NULL AS observacion,
                        CASE WHEN p.estado = 'devuelto' THEN
                            COALESCE(p.fecha_devolucion_real, p.fecha_prestamo)
                        ELSE p.fecha_prestamo END AS fecha
                    FROM prestamos p
                    INNER JOIN ejemplares e ON e.id_ejemplar = p.id_ejemplar
                    INNER JOIN reservas r ON r.id_reserva = p.id_reserva
                    INNER JOIN usuarios u ON u.id_usuario = r.fk_id_usuario_reserva
                    WHERE e.fk_id_libro_ejemplar = ?
                    GROUP BY r.id_reserva, actividad, fecha
                ) AS movimientos
                ORDER BY fecha DESC
                LIMIT ?";

        $query = $this->db->prepare($sql);
        $query->bind_param('iii', $id_libro, $id_libro, $limite);
        $query->execute();
        $resultado = $query->get_result();
        $movimientos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $movimientos[] = $fila;
        }
        $query->close();

        return $movimientos;
    }

    private function listarSimple($sql)
    {
        $query = $this->db->query($sql);
        $datos = [];
        while ($fila = $query->fetch_assoc()) {
            $datos[] = $fila;
        }
        $query->close();
        return $datos;
    }

    /* ── Registrar ── */
    public function registrarLibro($datos, $autores, $cantidad, $id_coleccion, $estado = 'Disponible')
    {
        try {
            $this->db->begin_transaction();

            $sql = "INSERT INTO libros
                    (id_editorial, id_materia, id_tipo_material, isbn, titulo,
                     edicion, ciudad, publicacion_year, serie, volumen, portada,
                     sinopsis, idioma, numero_paginas, fecha_registro_libro)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $q = $this->db->prepare($sql);

            // i i i s s s s s s i s s i i
            $q->bind_param(
                "iiissssssissii",
                $datos['id_editorial'],
                $datos['id_materia'],
                $datos['id_tipo_material'],
                $datos['isbn'],
                $datos['titulo'],
                $datos['edicion'],
                $datos['ciudad'],
                $datos['publicacion_year'],
                $datos['serie'],
                $datos['volumen'],
                $datos['portada'],
                $datos['sinopsis'],
                $datos['idioma'],
                $datos['numero_paginas']
            );
            $q->execute();
            $id_libro = $this->db->insert_id;
            $q->close();

            $this->sincronizarAutores($id_libro, $autores);

            $sqlE = "INSERT INTO ejemplares (fk_id_libro_ejemplar, estado, id_coleccion) VALUES (?, ?, ?)";
            $qE = $this->db->prepare($sqlE);
            $qE->bind_param("isi", $id_libro, $estado, $id_coleccion);

            for ($i = 0; $i < $cantidad; $i++) {
                $qE->execute();
            }
            $qE->close();

            $this->db->commit();
            return $id_libro;

        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            error_log("registrarLibro: " . $e->getMessage());
            return false;
        }
    }

    /* ── Actualizar ── */
    public function actualizarLibro($id_libro, $datos, $autores)
    {
        try {
            $this->db->begin_transaction();

            if (!empty($datos['portada'])) {
                // Hay portada nueva → se actualiza
                $this->eliminarPortadaFisica($id_libro);

                $sql = "UPDATE libros SET
                            id_editorial=?, id_materia=?, id_tipo_material=?,
                            isbn=?, titulo=?, edicion=?, ciudad=?, publicacion_year=?,
                            serie=?, volumen=?, portada=?, sinopsis=?, idioma=?, numero_paginas=?
                        WHERE id_libro=? AND fecha_eliminacion_libro IS NULL";

                $q = $this->db->prepare($sql);
                // i i i s s s s s s i s s i i i
                $q->bind_param(
                    "iiissssssissiii",
                    $datos['id_editorial'],
                    $datos['id_materia'],
                    $datos['id_tipo_material'],
                    $datos['isbn'],
                    $datos['titulo'],
                    $datos['edicion'],
                    $datos['ciudad'],
                    $datos['publicacion_year'],
                    $datos['serie'],
                    $datos['volumen'],
                    $datos['portada'],
                    $datos['sinopsis'],
                    $datos['idioma'],
                    $datos['numero_paginas'],
                    $id_libro
                );
            } else {
                // Sin portada nueva → NO se toca la columna portada
                $sql = "UPDATE libros SET
                            id_editorial=?, id_materia=?, id_tipo_material=?,
                            isbn=?, titulo=?, edicion=?, ciudad=?, publicacion_year=?,
                            serie=?, volumen=?, sinopsis=?, idioma=?, numero_paginas=?
                        WHERE id_libro=? AND fecha_eliminacion_libro IS NULL";

                $q = $this->db->prepare($sql);
                // i i i s s s s s s i s i i i
                $q->bind_param(
                    "iiissssssisiii",
                    $datos['id_editorial'],
                    $datos['id_materia'],
                    $datos['id_tipo_material'],
                    $datos['isbn'],
                    $datos['titulo'],
                    $datos['edicion'],
                    $datos['ciudad'],
                    $datos['publicacion_year'],
                    $datos['serie'],
                    $datos['volumen'],
                    $datos['sinopsis'],
                    $datos['idioma'],
                    $datos['numero_paginas'],
                    $id_libro
                );
            }

            $q->execute();
            $q->close();

            $this->sincronizarAutores($id_libro, $autores);
            $this->db->commit();
            return true;

        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            error_log("actualizarLibro: " . $e->getMessage());
            return false;
        }
    }

    /* ── Soft delete ── */
    public function eliminarLibro($id_libro)
    {
        try {
            $this->db->begin_transaction();

            $q = $this->db->prepare(
                "UPDATE libros SET fecha_eliminacion_libro = NOW()
                 WHERE id_libro = ? AND fecha_eliminacion_libro IS NULL"
            );
            $q->bind_param("i", $id_libro);
            $q->execute();
            $ok = $q->affected_rows > 0;
            $q->close();

            $this->db->commit();
            return $ok;

        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            error_log("eliminarLibro: " . $e->getMessage());
            return false;
        }
    }

    /* ── Helpers ── */
    private function sincronizarAutores($id_libro, $autores)
    {
        $qDel = $this->db->prepare("DELETE FROM libro_autor WHERE id_libro = ?");
        $qDel->bind_param("i", $id_libro);
        $qDel->execute();
        $qDel->close();

        if (empty($autores)) {
            return;
        }

        $qIns = $this->db->prepare("INSERT INTO libro_autor (id_libro, id_autor) VALUES (?, ?)");
        $id_autor = 0;
        $qIns->bind_param("ii", $id_libro, $id_autor);

        foreach ($autores as $nombre) {
            $nombre = trim($nombre);
            if ($nombre === '') {
                continue;
            }
            $id_autor = $this->obtenerOCrearAutor($nombre);
            $qIns->execute();
        }
        $qIns->close();
    }

    private function obtenerOCrearAutor($nombre)
    {
        $q = $this->db->prepare("SELECT id_autor FROM autores WHERE nombre = ?");
        $q->bind_param("s", $nombre);
        $q->execute();
        $res = $q->get_result();
        if ($f = $res->fetch_assoc()) {
            $q->close();
            return (int) $f['id_autor'];
        }
        $q->close();

        $q = $this->db->prepare("INSERT INTO autores (nombre) VALUES (?)");
        $q->bind_param("s", $nombre);
        $q->execute();
        $id = $this->db->insert_id;
        $q->close();
        return $id;
    }

    public function obtenerOCrearEditorial($nombre)
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return null;
        }

        $q = $this->db->prepare("SELECT id_editorial FROM editoriales WHERE nombre = ?");
        $q->bind_param("s", $nombre);
        $q->execute();
        $res = $q->get_result();
        if ($f = $res->fetch_assoc()) {
            $q->close();
            return (int) $f['id_editorial'];
        }
        $q->close();

        $q = $this->db->prepare("INSERT INTO editoriales (nombre) VALUES (?)");
        $q->bind_param("s", $nombre);
        $q->execute();
        $id = $this->db->insert_id;
        $q->close();
        return $id;
    }

    public function obtenerOCrearIdioma($nombre)
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return null;
        }

        $q = $this->db->prepare("SELECT id_idioma FROM idioma WHERE nombre_idioma = ?");
        $q->bind_param("s", $nombre);
        $q->execute();
        $res = $q->get_result();
        if ($f = $res->fetch_assoc()) {
            $q->close();
            return (int) $f['id_idioma'];
        }
        $q->close();

        $q = $this->db->prepare("INSERT INTO idioma (nombre_idioma) VALUES (?)");
        $q->bind_param("s", $nombre);
        $q->execute();
        $id = $this->db->insert_id;
        $q->close();
        return $id;
    }

    private function eliminarPortadaFisica($id_libro)
    {
        $q = $this->db->prepare("SELECT portada FROM libros WHERE id_libro = ?");
        $q->bind_param("i", $id_libro);
        $q->execute();
        $f = $q->get_result()->fetch_assoc();
        $q->close();

        if ($f && !empty($f['portada'])) {
            // Solo borramos si está dentro de nuestra carpeta de uploads
            $ruta = RUTA_PORTADAS_FISICA . basename($f['portada']);
            if (strpos($f['portada'], 'uploads/books/portadas/') !== false && file_exists($ruta)) {
                unlink($ruta);
            }
        }
    }
}