<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/Database/conexion.php';

if (!isset($connection) || $connection->connect_error) {
    echo json_encode(['error' => 'Error en la conexión a la base de datos']);
    exit;
}

$sql = "SELECT l.id_libro, 
               l.titulo, 
               l.portada,
               COALESCE(MIN(a.nombre), 'Autor desconocido') AS autor
        FROM libros l
        LEFT JOIN libro_autor la ON l.id_libro = la.id_libro
        LEFT JOIN autores a ON la.id_autor = a.id_autor
        WHERE l.fecha_eliminacion_libro IS NULL
        GROUP BY l.id_libro, l.titulo, l.portada
        ORDER BY l.id_libro DESC 
        LIMIT 6";

$result = $connection->query($sql);
$libros = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $portada = !empty($row['portada']) 
            ? '/Appjoteca/' . htmlspecialchars($row['portada']) 
            : '../../shared/images/logo-appjoteca.svg';

        $libros[] = [
            'id'          => $row['id_libro'],
            'titulo'      => $row['titulo'],
            'autor'       => $row['autor'],
            'portada_url' => $portada
        ];
    }
}

echo json_encode($libros, JSON_UNESCAPED_UNICODE);
$connection->close();
?>