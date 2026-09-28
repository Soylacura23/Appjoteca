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
    public function listarLibrosPaginado($pagina, $porPagina, $estado = '', $idMateria = 0, $orden = 'title-asc', $busqueda = '')
    {
        $where  = ["l.fecha_eliminacion_libro IS NULL"];
        $params = [];
        $types  = '';

        if ($idMateria > 0) {
            $where[]  = "l.id_materia = ?";
            $params[] = $idMateria;
            $types   .= 'i';
        }

        if ($busqueda !== '') {
            $where[]  = "(l.titulo LIKE ? OR l.isbn LIKE ?)";
            $params[] = '%' . $busqueda . '%';
            $params[] = '%' . $busqueda . '%';
            $types   .= 'ss';
        }

        $having = '';
        if ($estado === 'available') {
            $having = "HAVING disponibles > 0";
        } elseif ($estado === 'borrowed') {
            $having = "HAVING prestados > 0";
        } elseif ($estado === 'out') {
            $having = "HAVING disponibles = 0 AND total_ejemplares > 0";
        }

        $orderBy = match ($orden) {
            'title-desc'  => 'l.titulo DESC',
            'author-asc'  => 'autores ASC',
            'date-desc'   => 'l.id_libro DESC',
            'copies-desc' => 'total_ejemplares DESC',
            default       => 'l.titulo ASC',
        };

        $whereSql = implode(' AND ', $where);

        // Total (sin HAVING para no complicar el count)
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
                WHERE $whereSql
                GROUP BY l.id_libro
                $having
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