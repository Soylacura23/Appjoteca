<?php

header('Content-Type: application/json');

require_once '../Database/conexion.php'; 
require_once '../models/libro.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $modeloLibro = new libro($connection);

    $datos_libro = [
        'titulo'           => $_POST['titulo'] ?? '',
        'isbn'             => $_POST['isbn'] ?? '',
        'id_editorial'     => (int)($_POST['id_editorial'] ?? 0),
        'id_materia'       => (int)($_POST['id_materia'] ?? 0),
        'id_tipo_material' => (int)($_POST['id_tipo_material'] ?? 0),
        'edicion'          => $_POST['edicion'] ?? '',
        'ciudad'           => $_POST['ciudad'] ?? '',
        'publicacion_year' => $_POST['publicacion_year'] ?? '',
        'serie'            => $_POST['serie'] ?? '',
        'volumen'          => (int)($_POST['volumen'] ?? 0)
    ];

    $autores_nombres   = $_POST['autores'] ?? []; 
    $cantidad_ejemplar = (int)($_POST['cantidad_ejemplares'] ?? 1);
    $id_coleccion      = (int)($_POST['id_coleccion'] ?? 0);

    $registroExitoso = $modeloLibro->registrarLibro(
        $datos_libro, 
        $autores_nombres, 
        $cantidad_ejemplar, 
        $id_coleccion
    );

    if ($registroExitoso) {
        echo json_encode(['status' => 'success', 'mensaje' => 'Libro guardado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error al guardar el libro en el sistema']);
    }
    exit();
}

?>