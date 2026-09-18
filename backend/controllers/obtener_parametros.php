<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../Database/conexion.php';

requiereRol([3]);

$tabla = $_GET['tabla'] ?? '';

$tablasPermitidas = ['bibliotecas', 'dewey', 'tipos_materiales', 'colecciones', 'materias'];

if (!in_array($tabla, $tablasPermitidas, true)) {
    echo json_encode([]);
    exit();
}

$resultado = [];

switch ($tabla) {
    case 'bibliotecas':
        $sql = "SELECT id_biblioteca, nombre FROM bibliotecas ORDER BY id_biblioteca ASC";
        break;

    case 'dewey':
        $sql = "SELECT id_dewey, codigo, nombre FROM dewey ORDER BY codigo ASC";
        break;

    case 'tipos_materiales':
        $sql = "SELECT id_tipo_material, nombre FROM tipos_materiales ORDER BY id_tipo_material ASC";
        break;

    case 'colecciones':
        $sql = "SELECT c.id_coleccion, c.nombre, c.id_biblioteca, b.nombre AS biblioteca 
                FROM colecciones c
                INNER JOIN bibliotecas b ON c.id_biblioteca = b.id_biblioteca
                ORDER BY c.id_coleccion ASC";
        break;

    case 'materias':
        $sql = "SELECT m.id_materia, m.nombre, m.id_dewey, CONCAT(d.codigo, ' · ', d.nombre) AS dewey 
                FROM materias m
                INNER JOIN dewey d ON m.id_dewey = d.id_dewey
                ORDER BY m.id_materia ASC";
        break;
}

$query = $connection->query($sql);

if ($query) {
    while ($row = $query->fetch_assoc()) {
        $resultado[] = $row;
    }
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

$connection->close();
?>