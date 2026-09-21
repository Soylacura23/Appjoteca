<?php

class libro {
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    private function obtenerOCrearAutor($nombre_autor) {

        $sqlBuscar = "SELECT id_autor FROM autores WHERE nombre = ?";

        $queryBuscar = $this->db->prepare($sqlBuscar);

        $queryBuscar->bind_param("s", $nombre_autor);

        $queryBuscar->execute();

        $resultado = $queryBuscar->get_result();

        if ($fila = $resultado->fetch_assoc()) {

            $queryBuscar->close();

            return $fila['id_autor']; 
        }

        $queryBuscar->close();

        $sqlCrear = "INSERT INTO autores (nombre) VALUES (?)";
        $queryCrear = $this->db->prepare($sqlCrear);
        $queryCrear->bind_param("s", $nombre_autor);
        $queryCrear->execute();
        
        $nuevo_id = $this->db->insert_id;
        $queryCrear->close();

        return $nuevo_id;
    }

    public function registrarLibro($datos_libro, $autores_nombres, $cantidad_ejemplar, $id_coleccion){
        try {
            $this->db->begin_transaction();

            $sqlLibro = "INSERT INTO libros (id_editorial, id_materia, id_tipo_material, isbn, titulo, edicion, ciudad, publicacion_year, serie, volumen, portada) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $query = $this->db->prepare($sqlLibro);

            // "iiissssssis" -> 's' adicional al final correspondiente a la portada
            $query->bind_param(
                "iiissssssis", 
                $datos_libro['id_editorial'], 
                $datos_libro['id_materia'], 
                $datos_libro['id_tipo_material'],
                $datos_libro['isbn'], 
                $datos_libro['titulo'], 
                $datos_libro['edicion'], 
                $datos_libro['ciudad'],
                $datos_libro['publicacion_year'], 
                $datos_libro['serie'], 
                $datos_libro['volumen'],
                $datos_libro['portada']
            );
            $query->execute();

            $id_libro_creado = $this->db->insert_id;
            $query->close();

            if (!is_array($autores_nombres)) {
                $autores_nombres = explode(',', $autores_nombres);
            }

            $sqlAutor = "INSERT INTO libro_autor (id_libro, id_autor) VALUES (?, ?)";
            $queryAutor = $this->db->prepare($sqlAutor);
            
            $id_autor_actual = 0;

            $queryAutor->bind_param("ii", $id_libro_creado, $id_autor_actual);

            foreach ($autores_nombres as $nombre) {

                $nombre_limpio = trim($nombre); 

                if (!empty($nombre_limpio)) {
                    $id_autor_actual = $this->obtenerOCrearAutor($nombre_limpio);

                    $queryAutor->execute();
                }
            }
            $queryAutor->close();

            // Crear ejemplares automáticamente
            $sqlEjemplar = "INSERT INTO ejemplares (fk_id_libro_ejemplar, estado, id_coleccion) VALUES (?, ?, ?)";
            $queryEjemplar = $this->db->prepare($sqlEjemplar);
            $estado_inicial = 'Disponible';
            
            $queryEjemplar->bind_param("isi", $id_libro_creado, $estado_inicial, $id_coleccion);

            for ($i = 0; $i < $cantidad_ejemplar; $i++) {

                $queryEjemplar->execute();

            }

            $queryEjemplar->close();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            
            error_log("Error en registrarLibro: " . $e->getMessage()); 
            return false;
        }
    }
}

?>