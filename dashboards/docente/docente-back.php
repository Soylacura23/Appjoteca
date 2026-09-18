<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

require_once __DIR__ . '/../../backend/Database/conexion.php';

if (!isset($connection) || $connection->connect_error) {
    echo json_encode(['error' => 'Error en la conexión a la base de datos']);
    exit;
}

// Consulta uniendo libros con la tabla de autores y libro_autor
$sql = "SELECT l.id_libro, l.titulo, 
               COALESCE(a.nombre, 'Autor desconocido') AS autor
        FROM libros l
        LEFT JOIN libro_autor la ON l.id_libro = la.id_libro
        LEFT JOIN autores a ON la.id_autor = a.id_autor
        GROUP BY l.id_libro
        ORDER BY l.id_libro DESC 
        LIMIT 6";

$result = $connection->query($sql);

$libros = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $libros[] = [
            'id'          => $row['id_libro'],
            'titulo'      => $row['titulo'],
            'autor'       => $row['autor'],
            'portada_url' => '../../shared/images/default-book.png'
        ];
    }
}

echo json_encode($libros, JSON_UNESCAPED_UNICODE);
$connection->close();
?>