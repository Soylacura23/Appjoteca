<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../../backend/config/auth.php';
    require_once __DIR__ . '/../../../backend/Database/conexion.php';
    require_once __DIR__ . '/../../../backend/models/libro.php';

    $mi_id = $_SESSION['usuario_id'] ?? null;

    if (!$mi_id) {
        echo json_encode([
            'exito'   => false,
            'mensaje' => 'Sesión no autorizada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!isset($connection) || !$connection) {
        throw new Exception('No se pudo establecer la conexión a la base de datos');
    }

    $modelo = new libro($connection);

    // Parámetros
    $page      = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $materiaId = isset($_GET['materia']) ? (int)$_GET['materia'] : 0;
    $busqueda  = trim($_GET['query'] ?? '');
    $estado    = trim($_GET['availability'] ?? '');
    $orden     = trim($_GET['sort'] ?? 'recommended');
    $idAutor   = max(0, (int) ($_GET['autor'] ?? 0));
    $limite    = min(30, max(1, (int) ($_GET['limit'] ?? 10)));

    // Listado paginado del modelo
    $resultado = $modelo->listarLibrosPaginado(
        $page,
        $limite,
        $estado,
        $materiaId,
        $orden,
        $busqueda,
        $idAutor
    );

    // Materias (categorías)
    $catalogos = $modelo->obtenerCatalogos();
    $materias  = $catalogos['materias'] ?? [];

    // Formatear libros para el frontend
    $librosFormateados = [];
    foreach ($resultado['libros'] as $libro) {
        $librosFormateados[] = [
            'id'          => (int) $libro['id_libro'],
            'titulo'      => $libro['titulo'] ?? '',
            'autor'       => $libro['autores'] ?? 'Sin autor',
            'portada'     => $libro['portada'] ?? '',
            'categoria'   => $libro['materia'] ?? 'Sin materia',
            'isbn'        => $libro['isbn'] ?? '',
            'disponibles' => (int) ($libro['disponibles'] ?? 0),
            'reservados'  => (int) ($libro['reservados'] ?? 0),
            'prestados'   => (int) ($libro['prestados'] ?? 0),
            'total'       => (int) ($libro['total_ejemplares'] ?? 0),
            'sinopsis'    => $libro['sinopsis'] ?? '',
        ];
    }

    echo json_encode([
        'exito'        => true,
        'paginaActual' => $resultado['pagina'],
        'totalPaginas' => $resultado['total_paginas'],
        'totalLibros'  => $resultado['total'],
        'libros'       => $librosFormateados,
        'materias'     => $materias
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    error_log('catalogo/back: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'No se pudo cargar el catálogo. Inténtalo de nuevo.'
    ], JSON_UNESCAPED_UNICODE);
}