<?php
header('Content-Type: application/json; charset=utf-8');
require("../Database/conexion.php");

$respuesta = [
    'libros' => [],
    'recomendado' => null
];

$sqlLibros = "SELECT l.id_libro, l.titulo_libro, l.portada, m.nombre_materia AS categoria, 
              CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS autor
              FROM libros l
              LEFT JOIN materias m ON l.id_materia = m.id_materia
              LEFT JOIN autores_libros al ON l.id_libro = al.id_libro
              LEFT JOIN autores a ON al.id_autor = a.id_autor
              GROUP BY l.id_libro
              LIMIT 6";

$queryLibros = $connection->prepare($sqlLibros);

if ($queryLibros && $queryLibros->execute()) {

    $result = $queryLibros->get_result();

    while ($fila = $result->fetch_assoc()) {

        $respuesta['libros'][] = $fila;
    }
}

$sqlRecomendado = "SELECT l.id_libro, l.titulo_libro, l.portada, l.resumen,
                   CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS autor,
                   COUNT(p.id_prestamo) AS total_prestamos
                   FROM prestamos p
                   INNER JOIN libros l ON p.id_libro = l.id_libro
                   LEFT JOIN autores_libros al ON l.id_libro = al.id_libro
                   LEFT JOIN autores a ON al.id_autor = a.id_autor
                   GROUP BY l.id_libro
                   ORDER BY total_prestamos DESC
                   LIMIT 1";

$queryRec = $connection->prepare($sqlRecomendado);

if ($queryRec && $queryRec->execute()) {

    $resultRec = $queryRec->get_result();

    if ($filaRec = $resultRec->fetch_assoc()) {
        
        $respuesta['recomendado'] = $filaRec;
    }
}

echo json_encode($respuesta);