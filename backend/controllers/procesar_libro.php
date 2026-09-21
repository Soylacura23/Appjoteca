<?php

header('Content-Type: application/json');

require_once __DIR__ . '../Database/conexion.php'; 
require_once __DIR__ . '../models/libro.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $modeloLibro = new libro($connection);

    $rutaPortada = null;

    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = $_FILES['portada']['name'];
        $tipoArchivo   = $_FILES['portada']['type'];
        $rutaTemporal  = $_FILES['portada']['tmp_name'];

        // Validar extensiones permitidas
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        if (in_array($ext, $extensionesPermitidas)) {
            $directorioSubida = BASE_URL . 'uploads/books/portadas/';

            
            if (!file_exists($directorioSubida)) {
                mkdir($directorioSubida, 0777, true);
            }

            $nuevoNombreImg = uniqid('portada_', true) . '.' . $ext;
            $destinoFinal   = $directorioSubida . $nuevoNombreImg;

            if (move_uploaded_file($rutaTemporal, $destinoFinal)) {
                
                $rutaPortada = 'uploads/portadas/' . $nuevoNombreImg;
            }
        }
    }

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
        'volumen'          => (int)($_POST['volumen'] ?? 0),
        'portada'          => $rutaPortada 
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