<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/verify-csrf.php';
require_once __DIR__ . '/../config/env.php';  
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../models/libro.php';

header('Content-Type: application/json; charset=utf-8');

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

define('RUTA_PORTADAS_FISICA',  __DIR__ . '/../../uploads/books/portadas/');
define('RUTA_PORTADAS_PUBLICA', 'uploads/books/portadas/');
define('PORTADA_DEFAULT',       'assets/images/books/default-cover.jpg');

$modelo = new libro($connection);
requiereRol([3]);
$action = $_SERVER['REQUEST_METHOD'] === 'GET'
    ? ($_GET['action'] ?? '')
    : ($_POST['action'] ?? '');
$accionesLectura = ['listar', 'obtener', 'catalogos', 'buscar_portada', 'generar_sinopsis', 'historial'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !in_array($action, $accionesLectura, true)) {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

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

    if ($_FILES['portada']['size'] > 10 * 1024 * 1024) {
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($_FILES['portada']['tmp_name']);
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowedTypes[$mimeType])) {
        return null;
    }

    if (!is_dir(RUTA_PORTADAS_FISICA)) {
        mkdir(RUTA_PORTADAS_FISICA, 0755, true);
    }

    $nombre  = 'portada_' . bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
    $destino = RUTA_PORTADAS_FISICA . $nombre;

    if (!move_uploaded_file($_FILES['portada']['tmp_name'], $destino)) {
        return null;
    }

    return RUTA_PORTADAS_PUBLICA . $nombre;
}

function descargarPortadaUrl($url)
{
    if (!is_string($url)) {
        return null;
    }

    $parsedUrl = parse_url($url);
    if (!filter_var($url, FILTER_VALIDATE_URL)
        || ($parsedUrl['scheme'] ?? '') !== 'https'
        || strtolower($parsedUrl['host'] ?? '') !== 'covers.openlibrary.org') {
        return null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_MAXFILESIZE    => 5 * 1024 * 1024,
        CURLOPT_USERAGENT      => 'AppJoteca/1.0',
    ]);
    $contenido = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $urlFinal  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    $hostFinal = strtolower(parse_url($urlFinal, PHP_URL_HOST) ?: '');

    if ($contenido === false
        || $httpCode !== 200
        || $hostFinal !== 'covers.openlibrary.org'
        || strlen($contenido) > 5 * 1024 * 1024) {
        return null;
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->buffer($contenido);
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowedTypes[$mimeType])) {
        return null;
    }

    if (!is_dir(RUTA_PORTADAS_FISICA)) {
        mkdir(RUTA_PORTADAS_FISICA, 0755, true);
    }

    $nombre  = 'portada_' . bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
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

function httpGet($url, $timeout = 7)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(3, $timeout),
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

    /* LISTAR (paginado) */
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

    /* OBTENER UNO */
    case 'obtener':
        $id = (int)($_GET['id'] ?? 0);
        $libro = $modelo->obtenerLibro($id);
        if ($libro) {
            responder('success', '', $libro);
        }
        responder('error', 'Libro no encontrado');
        break;

    /* CATÁLOGOS */
    case 'catalogos':
        responder('success', '', $modelo->obtenerCatalogos());
        break;

    case 'historial':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            responder('error', 'ID de libro inválido');
        }
        responder('success', '', $modelo->obtenerHistorial($id));
        break;

    /* GUARDAR (nuevo) */
    case 'guardar':
        $portada = subirPortada();
        if (!$portada && !empty($_POST['portada_url'])) {
            $portada = descargarPortadaUrl(trim($_POST['portada_url']));
            if (!$portada) {
                responder('error', 'No se pudo guardar la portada seleccionada. Intenta elegir otra o subir una imagen local.');
            }
        }
        if (!$portada) {
            $portada = PORTADA_DEFAULT;
        }

        $datos = datosLibroDesdePost($portada, $modelo);

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

    /* ACTUALIZAR */
    case 'actualizar':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            responder('error', 'ID inválido');
        }

        $portada = subirPortada();
        if (!$portada && !empty($_POST['portada_url'])) {
            $portada = descargarPortadaUrl(trim($_POST['portada_url']));
            if (!$portada) {
                responder('error', 'No se pudo guardar la portada seleccionada. Intenta elegir otra o subir una imagen local.');
            }
        }

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

    /* ELIMINAR (soft) */
    case 'eliminar':
        $id = (int)($_POST['id'] ?? 0);
        if ($modelo->eliminarLibro($id)) {
            responder('success', 'Libro eliminado correctamente');
        }
        responder('error', 'No se pudo eliminar el libro');
        break;

    /* BUSCAR PORTADAS (varias) */
    case 'buscar_portada':
        $titulo = trim($_GET['titulo'] ?? '');
        $isbn = preg_replace('/[^0-9Xx]/', '', $_GET['isbn'] ?? '');
        $autor = trim($_GET['autor'] ?? '');
        if ($titulo === '' && $isbn === '') {
            responder('error', 'Indica un título o ISBN');
        }

        $consultas = [];
        if ($isbn !== '') {
            $consultas[] = 'https://openlibrary.org/search.json?isbn=' . urlencode($isbn) . '&limit=8';
        }
        if ($titulo !== '') {
            $parametros = ['title' => $titulo, 'limit' => 8];
            if ($autor !== '') $parametros['author'] = $autor;
            $consultas[] = 'https://openlibrary.org/search.json?' . http_build_query($parametros);
        }

        $portadas = [];
        $consultasCorrectas = false;
        foreach ($consultas as $url) {
            $json = httpGet($url, 4);
            if (!$json) continue;
            $data = json_decode($json, true);
            if (!is_array($data)) continue;
            $consultasCorrectas = true;

            foreach ($data['docs'] ?? [] as $doc) {
                if (empty($doc['cover_i'])) continue;
                $portadas[(string)$doc['cover_i']] = [
                    'url'    => 'https://covers.openlibrary.org/b/id/' . $doc['cover_i'] . '-M.jpg',
                    'titulo' => $doc['title'] ?? '',
                    'autor'  => isset($doc['author_name']) ? implode(', ', $doc['author_name']) : '',
                    'anio'   => $doc['first_publish_year'] ?? null,
                ];
                if (count($portadas) >= 8) break;
            }
            if (count($portadas) >= 8) break;
        }

        if (empty($portadas)) {
            if (!$consultasCorrectas) {
                responder('error', 'No se pudo consultar Open Library');
            }
            responder('error', 'No se encontraron portadas');
        }

        responder('success', '', ['portadas' => array_values($portadas)]);
        break;

    case 'generar_sinopsis':
        $titulo = trim($_GET['titulo'] ?? '');
        $idioma = trim($_GET['idioma'] ?? 'es');
        $autor = trim($_GET['autor'] ?? '');
        $isbn = preg_replace('/[^0-9Xx]/', '', $_GET['isbn'] ?? '');

        if ($titulo === '' && $isbn === '') {
            responder('error', 'Indica un título o ISBN');
        }

        $apiKey = $_ENV['GOOGLE_BOOKS_API_KEY'] ?? getenv('GOOGLE_BOOKS_API_KEY') ?? '';

        $consultas = [];
        if ($isbn !== '') $consultas[] = 'isbn:' . $isbn;
        if ($titulo !== '') {
            $consultaTitulo = 'intitle:' . $titulo;
            if ($autor !== '') $consultaTitulo .= ' inauthor:' . $autor;
            $consultas[] = $consultaTitulo;
            if ($autor !== '') $consultas[] = 'intitle:' . $titulo;
        }

        $candidatos = [];
        foreach (array_unique($consultas) as $consulta) {
            foreach ([$idioma, ''] as $restriccionIdioma) {
                $parametros = [
                    'q' => $consulta,
                    'maxResults' => 10,
                ];
                if ($apiKey !== '') $parametros['key'] = $apiKey;
                if ($restriccionIdioma !== '') $parametros['langRestrict'] = $restriccionIdioma;
                $url = 'https://www.googleapis.com/books/v1/volumes?' . http_build_query($parametros);
                $json = httpGet($url, 5);
                if (!$json) continue;

                $data = json_decode($json, true);
                foreach ($data['items'] ?? [] as $item) {
                    $info = $item['volumeInfo'] ?? [];
                    if (empty($info['description'])) continue;
                    $candidatos[] = [
                        'titulo' => $info['title'] ?? '',
                        'autores' => $info['authors'] ?? [],
                        'descripcion' => $info['description'],
                        'puntaje' => 0,
                    ];
                }
                if ($candidatos) break;
            }
            if ($candidatos) break;
        }

        if (!$candidatos) {
            if ($apiKey === '') {
                responder('error', 'Google Books no devolvió una sinopsis; configura GOOGLE_BOOKS_API_KEY para disponer de una cuota estable.');
            }
            responder('error', 'No se encontró sinopsis para este título');
        }

        $normalizar = static function ($texto) {
            $texto = function_exists('mb_strtolower')
                ? mb_strtolower($texto, 'UTF-8')
                : strtolower($texto);
            return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $texto));
        };
        $tituloBuscado = $normalizar($titulo);
        $autorBuscado = $normalizar($autor);
        foreach ($candidatos as &$candidato) {
            if ($isbn !== '') $candidato['puntaje'] += 100;
            $tituloResultado = $normalizar($candidato['titulo']);
            if ($tituloBuscado !== '' && $tituloResultado !== '') {
                similar_text($tituloBuscado, $tituloResultado, $coincidencia);
                $candidato['puntaje'] += $coincidencia;
            }
            if ($autorBuscado !== '') {
                foreach ($candidato['autores'] as $autorResultado) {
                    if (strpos($normalizar($autorResultado), $autorBuscado) !== false) {
                        $candidato['puntaje'] += 30;
                        break;
                    }
                }
            }
        }
        unset($candidato);
        usort($candidatos, static fn($a, $b) => $b['puntaje'] <=> $a['puntaje']);
        $descripcionHtml = preg_replace('/<(?:br|\/p|\/div|\/li)\b[^>]*>/i', ' ', $candidatos[0]['descripcion']);
        $descripcion = html_entity_decode(
            preg_replace('/\s+/u', ' ', strip_tags($descripcionHtml)),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
        $descripcion = function_exists('mb_substr')
            ? mb_substr(trim($descripcion), 0, 2000, 'UTF-8')
            : substr(trim($descripcion), 0, 2000);
        responder('success', '', ['sinopsis' => trim($descripcion)]);
        break;

    default:
        responder('error', 'Acción no válida');
}