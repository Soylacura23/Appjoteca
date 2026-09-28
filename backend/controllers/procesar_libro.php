<?php

require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../config/env.php';  
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../models/libro.php';

header('Content-Type: application/json; charset=utf-8');

define('RUTA_PORTADAS_FISICA',  __DIR__ . '/../../uploads/books/portadas/');
define('RUTA_PORTADAS_PUBLICA', 'uploads/books/portadas/');
define('PORTADA_DEFAULT',       'assets/images/books/default-cover.jpg');

$modelo = new libro($connection);
$action = $_REQUEST['action'] ?? '';

function responder($status, $mensaje = '', $data = null)
{
    echo json_encode(['status' => $status, 'mensaje' => $mensaje, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function subirPortada()
{
    if (!isset($_FILES['portada']) || $_FILES['portada']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $ext = strtolower(pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        return null;
    }

    if (!is_dir(RUTA_PORTADAS_FISICA)) {
        mkdir(RUTA_PORTADAS_FISICA, 0755, true);
    }

    $nombre  = uniqid('portada_', true) . '.' . $ext;
    $destino = RUTA_PORTADAS_FISICA . $nombre;

    if (!move_uploaded_file($_FILES['portada']['tmp_name'], $destino)) {
        return null;
    }

    return RUTA_PORTADAS_PUBLICA . $nombre;
}

function descargarPortadaUrl($url)
{
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_USERAGENT      => 'AppJoteca/1.0',
    ]);
    $contenido = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($contenido === false || $httpCode !== 200) {
        return null;
    }

    if (!is_dir(RUTA_PORTADAS_FISICA)) {
        mkdir(RUTA_PORTADAS_FISICA, 0755, true);
    }

    $nombre  = uniqid('portada_', true) . '.jpg';
    $destino = RUTA_PORTADAS_FISICA . $nombre;

    if (file_put_contents($destino, $contenido) === false) {
        return null;
    }

    return RUTA_PORTADAS_PUBLICA . $nombre;
}

function autoresDesdePost()
{
    $autores = $_POST['autores'] ?? [];
    if (!is_array($autores)) {
        $autores = [$autores];
    }
    return array_values(array_filter(array_map('trim', $autores)));
}

function datosLibroDesdePost($rutaPortada, $modelo)
{
    // Idioma: puede venir por id o por texto libre
    $idiomaId = null;
    if (!empty($_POST['idioma_id'])) {
        $idiomaId = (int) $_POST['idioma_id'];
    } elseif (!empty($_POST['idioma_texto'])) {
        $idiomaId = $modelo->obtenerOCrearIdioma(trim($_POST['idioma_texto']));
    }

    // Editorial: puede venir por id o por texto libre
    $editorialId = null;
    if (!empty($_POST['id_editorial'])) {
        $editorialId = (int) $_POST['id_editorial'];
    } elseif (!empty($_POST['editorial_texto'])) {
        $editorialId = $modelo->obtenerOCrearEditorial(trim($_POST['editorial_texto']));
    }

    return [
        'titulo'           => trim($_POST['titulo'] ?? ''),
        'isbn'             => trim($_POST['isbn'] ?? ''),
        'id_editorial'     => $editorialId,
        'id_materia'       => (int)($_POST['id_materia'] ?? 0) ?: null,
        'id_tipo_material' => (int)($_POST['id_tipo_material'] ?? 0) ?: null,
        'edicion'          => trim($_POST['edicion'] ?? ''),
        'ciudad'           => trim($_POST['ciudad'] ?? ''),
        'publicacion_year' => trim($_POST['publicacion_year'] ?? '') ?: null,
        'serie'            => trim($_POST['serie'] ?? ''),
        'volumen'          => (int)($_POST['volumen'] ?? 0) ?: null,
        'sinopsis'         => trim($_POST['sinopsis'] ?? ''),
        'portada'          => $rutaPortada,   // null = no tocar la portada actual
        'idioma'           => $idiomaId,
        'numero_paginas'   => (int)($_POST['numero_paginas'] ?? 0) ?: null,
    ];
}

function httpGet($url, $timeout = 12)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_USERAGENT      => 'AppJoteca/1.0',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $code !== 200) {
        return null;
    }
    return $body;
}

switch ($action) {

    /* ---------- LISTAR (paginado) ---------- */
    case 'listar':
        $pagina      = max(1, (int)($_GET['pagina'] ?? 1));
        $porPagina   = 30;
        $filtroEstado  = $_GET['estado']  ?? '';
        $filtroMateria = (int)($_GET['materia'] ?? 0);
        $orden         = $_GET['orden']   ?? 'title-asc';
        $busqueda      = trim($_GET['q']  ?? '');

        $resultado = $modelo->listarLibrosPaginado(
            $pagina, $porPagina, $filtroEstado, $filtroMateria, $orden, $busqueda
        );
        responder('success', '', $resultado);
        break;

    /* ---------- OBTENER UNO ---------- */
    case 'obtener':
        $id = (int)($_GET['id'] ?? 0);
        $libro = $modelo->obtenerLibro($id);
        if ($libro) {
            responder('success', '', $libro);
        }
        responder('error', 'Libro no encontrado');
        break;

    /* ---------- CATÁLOGOS ---------- */
    case 'catalogos':
        responder('success', '', $modelo->obtenerCatalogos());
        break;

    /* ---------- GUARDAR (nuevo) ---------- */
    case 'guardar':
        $portada = subirPortada();
        if (!$portada && !empty($_POST['portada_url'])) {
            $portada = descargarPortadaUrl(trim($_POST['portada_url']));
        }
        // Solo si no hay ninguna portada usamos la por defecto
        if (!$portada) {
            $portada = PORTADA_DEFAULT;
        }

        $datos = datosLibroDesdePost($portada, $modelo);

        // Validaciones obligatorias
        $errores = [];
        if ($datos['titulo'] === '')          $errores[] = 'El título es obligatorio';
        if (empty(autoresDesdePost()))        $errores[] = 'El autor principal es obligatorio';
        if ($datos['isbn'] === '')            $errores[] = 'El ISBN es obligatorio';
        if (!$datos['id_materia'])            $errores[] = 'La materia es obligatoria';
        if (empty($_POST['id_coleccion']))    $errores[] = 'La colección es obligatoria';

        if ($errores) {
            responder('error', implode('. ', $errores));
        }

        $cantidad    = max(1, (int)($_POST['cantidad_ejemplares'] ?? 1));
        $idColeccion = (int)$_POST['id_coleccion'];
        $estado      = 'Disponible';

        $id = $modelo->registrarLibro($datos, autoresDesdePost(), $cantidad, $idColeccion, $estado);

        if ($id) {
            responder('success', 'Libro guardado correctamente', ['id_libro' => $id]);
        }
        responder('error', 'Error al guardar el libro');
        break;

    /* ---------- ACTUALIZAR ---------- */
    case 'actualizar':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            responder('error', 'ID inválido');
        }

        // Solo subimos/descargamos portada si el usuario envió algo nuevo
        $portada = subirPortada();
        if (!$portada && !empty($_POST['portada_url'])) {
            $portada = descargarPortadaUrl(trim($_POST['portada_url']));
        }
        // Si $portada queda null → el modelo NO toca la columna portada

        $datos = datosLibroDesdePost($portada, $modelo);

        $errores = [];
        if ($datos['titulo'] === '')   $errores[] = 'El título es obligatorio';
        if (empty(autoresDesdePost())) $errores[] = 'El autor principal es obligatorio';
        if ($datos['isbn'] === '')     $errores[] = 'El ISBN es obligatorio';
        if (!$datos['id_materia'])     $errores[] = 'La materia es obligatoria';

        if ($errores) {
            responder('error', implode('. ', $errores));
        }

        $ok = $modelo->actualizarLibro($id, $datos, autoresDesdePost());

        if ($ok) {
            responder('success', 'Cambios guardados correctamente');
        }
        responder('error', 'Error al actualizar el libro');
        break;

    /* ---------- ELIMINAR (soft) ---------- */
    case 'eliminar':
        $id = (int)($_POST['id'] ?? 0);
        if ($modelo->eliminarLibro($id)) {
            responder('success', 'Libro eliminado correctamente');
        }
        responder('error', 'No se pudo eliminar el libro');
        break;

    /* ---------- BUSCAR PORTADAS (varias) ---------- */
    case 'buscar_portada':
        $titulo = trim($_GET['titulo'] ?? '');
        if ($titulo === '') {
            responder('error', 'Título vacío');
        }

        // Pedimos más resultados para que el usuario elija
        $url  = 'https://openlibrary.org/search.json?title=' . urlencode($titulo) . '&limit=8';
        $json = httpGet($url);

        if (!$json) {
            responder('error', 'No se pudo consultar Open Library');
        }

        $data = json_decode($json, true);
        $docs = $data['docs'] ?? [];

        $portadas = [];
        foreach ($docs as $doc) {
            if (empty($doc['cover_i'])) continue;

            $portadas[] = [
                'url'    => 'https://covers.openlibrary.org/b/id/' . $doc['cover_i'] . '-L.jpg',
                'titulo' => $doc['title'] ?? '',
                'autor'  => isset($doc['author_name']) ? implode(', ', $doc['author_name']) : '',
                'anio'   => $doc['first_publish_year'] ?? null,
            ];
        }

        if (empty($portadas)) {
            responder('error', 'No se encontraron portadas');
        }

        responder('success', '', ['portadas' => $portadas]);
        break;

    /* ---------- GENERAR SINOPSIS (Google Books + API key) ---------- */
    case 'generar_sinopsis':
        $titulo = trim($_GET['titulo'] ?? '');
        $idioma = trim($_GET['idioma'] ?? 'es');

        if ($titulo === '') {
            responder('error', 'Título vacío');
        }

        $apiKey = $_ENV['GOOGLE_BOOKS_API_KEY'] ?? getenv('GOOGLE_BOOKS_API_KEY') ?? '';
        if ($apiKey === '') {
            responder('error', 'Falta la clave de Google Books en el .env (GOOGLE_BOOKS_API_KEY)');
        }

        // Primera intento con idioma, si falla se reintenta sin langRestrict
        $q = urlencode($titulo);
        $urls = [
            "https://www.googleapis.com/books/v1/volumes?q=intitle:{$q}&langRestrict={$idioma}&maxResults=3&key={$apiKey}",
            "https://www.googleapis.com/books/v1/volumes?q=intitle:{$q}&maxResults=3&key={$apiKey}",
        ];

        $desc = null;
        foreach ($urls as $url) {
            $json = httpGet($url);
            if (!$json) continue;

            $data = json_decode($json, true);
            if (!empty($data['items'][0]['volumeInfo']['description'])) {
                $desc = $data['items'][0]['volumeInfo']['description'];
                break;
            }
        }

        if (!$desc) {
            responder('error', 'No se encontró sinopsis para este título');
        }

        responder('success', '', ['sinopsis' => strip_tags($desc)]);
        break;

    default:
        responder('error', 'Acción no válida');
}