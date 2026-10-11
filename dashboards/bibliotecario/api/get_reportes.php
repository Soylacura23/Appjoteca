<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../backend/config/auth.php';
require_once __DIR__ . '/../../../backend/Database/conexion.php';

requiereRol([3]);

// Parámetros de consulta
$tipo    = $_GET['tipo']    ?? 'todo';
$periodo = $_GET['periodo'] ?? '30';

$periodosValidos = ['7', '30', '90', '365', 'todo'];
if (!in_array($periodo, $periodosValidos, true)) {
    $periodo = '30';
}
      
function unaFila($db, $sql)
{
    $res = $db->query($sql);
    if (!$res) return null;
    $fila = $res->fetch_assoc();
    $res->close();
    return $fila;
}

function todasFilas($db, $sql)
{
    $res = $db->query($sql);
    if (!$res) return [];
    $filas = [];
    while ($f = $res->fetch_assoc()) {
        $filas[] = $f;
    }
    $res->close();
    return $filas;
}

/**
 * Gráfica de reservas por día de la semana según el período.
 */
function obtenerGraficaReservas($db, $periodo)
{
    $mapaDias = [
        1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles',
        5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'
    ];
    $orden = [2, 3, 4, 5, 6, 7, 1]; // Lun → Dom

    if ($periodo === 'todo') {
        $whereFecha = '1=1';
        $etiqueta   = 'Todo el historial';
    } else {
        $dias = (int) $periodo;
        $whereFecha = "fecha_reserva >= DATE_SUB(CURDATE(), INTERVAL $dias DAY)";
        $etiqueta = match ($periodo) {
            '7'   => 'Últimos 7 días',
            '90'  => 'Últimos 90 días',
            '365' => 'Último año',
            default => 'Últimos 30 días',
        };
    }

    $filas = todasFilas($db, "
        SELECT DAYOFWEEK(fecha_reserva) AS dow, COUNT(*) AS total
        FROM reservas
        WHERE $whereFecha
        GROUP BY DAYOFWEEK(fecha_reserva)
    ");

    $conteo = [];
    foreach ($filas as $f) {
        $conteo[(int) $f['dow']] = (int) $f['total'];
    }

    $grafica = [];
    foreach ($orden as $dow) {
        $grafica[] = [
            'dia'   => $mapaDias[$dow],
            'total' => $conteo[$dow] ?? 0
        ];
    }

    return [
        'datos'    => $grafica,
        'periodo'  => $periodo,
        'etiqueta' => $etiqueta
    ];
}

function obtenerFraseSemanal()
{
    $semana = date('o-W');

    if (session_status() === PHP_SESSION_NONE) {
        require_once __DIR__ . '/../../../backend/config/security.php';
    }

    if (
        isset($_SESSION['frase_semana'], $_SESSION['frase_semana_id']) &&
        $_SESSION['frase_semana_id'] === $semana
    ) {
        return $_SESSION['frase_semana'];
    }

    $frase = 'La lectura es a la mente lo que el ejercicio es al cuerpo.';

    $ch = curl_init('https://api.quotable.io/random?maxLength=120');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 6,
        CURLOPT_USERAGENT      => 'AppJoteca/1.0',
    ]);
    $json = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($json && $code === 200) {
        $data    = json_decode($json, true);
        $textoEn = $data['content'] ?? '';
        $autor   = $data['author'] ?? '';

        if ($textoEn !== '') {
            $url = 'https://api.mymemory.translated.net/get?q=' . urlencode($textoEn) . '&langpair=en|es';
            $ch2 = curl_init($url);
            curl_setopt_array($ch2, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 6,
                CURLOPT_USERAGENT      => 'AppJoteca/1.0',
            ]);
            $json2 = curl_exec($ch2);
            $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            curl_close($ch2);

            if ($json2 && $code2 === 200) {
                $data2 = json_decode($json2, true);
                $trad  = trim($data2['responseData']['translatedText'] ?? '');
                if ($trad !== '' && stripos($trad, 'MYMEMORY WARNING') === false) {
                    $frase = $trad;
                    if ($autor !== '') {
                        $frase .= ' — ' . $autor;
                    }
                }
            }
        }
    }

    $_SESSION['frase_semana']    = $frase;
    $_SESSION['frase_semana_id'] = $semana;

    return $frase;
}

try {
    // Endpoints parciales salen antes de ejecutar las consultas del informe completo.
    if ($tipo === 'grafica') {
        $infoGrafica = obtenerGraficaReservas($connection, $periodo);
        echo json_encode([
            'ok'               => true,
            'grafica_reservas' => $infoGrafica['datos'],
            'periodo'          => $infoGrafica['periodo'],
            'etiqueta'         => $infoGrafica['etiqueta']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($tipo === 'frase') {
        echo json_encode([
            'ok' => true,
            'frase' => obtenerFraseSemanal()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Métricas resumidas en una consulta para reducir los viajes a la base de datos.
    $metricasRaw = unaFila($connection, "
        SELECT
            (SELECT COUNT(*) FROM reservas) +
                (SELECT COUNT(*) FROM prestamos) AS trafico_total,
            (SELECT COUNT(*) FROM reservas WHERE fecha_reserva = CURDATE()) AS reservas_diarias,
            (SELECT COUNT(*) FROM usuarios WHERE estado = '1') AS usuarios_activos,
            (SELECT COUNT(*) FROM usuarios WHERE estado IS NULL OR estado != '1') AS usuarios_inactivos,
            (SELECT COUNT(DISTINCT fk_id_usuario_reserva) FROM reservas WHERE estado = 'activa') AS usuarios_con_reserva,
            (SELECT COUNT(*) FROM prestamos WHERE estado IN ('activo', 'vencido')) AS prestamos_activos,
            (SELECT COUNT(*) FROM prestamos WHERE estado = 'devuelto') AS devoluciones
    ");
    $traficoTotal = (int) ($metricasRaw['trafico_total'] ?? 0);
    $reservasDiarias = (int) ($metricasRaw['reservas_diarias'] ?? 0);
    $usuariosActivos = (int) ($metricasRaw['usuarios_activos'] ?? 0);
    $usuariosInactivos = (int) ($metricasRaw['usuarios_inactivos'] ?? 0);
    $usuariosConReserva = (int) ($metricasRaw['usuarios_con_reserva'] ?? 0);
    $prestamosActivos = (int) ($metricasRaw['prestamos_activos'] ?? 0);
    $totalDevoluciones = (int) ($metricasRaw['devoluciones'] ?? 0);

    // Gráfica del informe completo (el selector de período sigue aplicando el mismo filtro).
    $infoGrafica = obtenerGraficaReservas($connection, $periodo);

    // Categoría popular
    $cat = unaFila($connection, "
        SELECT categoria.nombre, categoria.total,
            (SELECT COUNT(*) FROM reservas) AS total_reservas
        FROM (
            SELECT m.id_materia, m.nombre, COUNT(*) AS total
            FROM reservas r
            JOIN libros l ON r.fk_id_libro_reserva = l.id_libro
            JOIN materias m ON l.id_materia = m.id_materia
            GROUP BY m.id_materia, m.nombre
        ) AS categoria
        ORDER BY categoria.total DESC
        LIMIT 1
    ");

    $totalR = max(1, (int) ($cat['total_reservas'] ?? 1));

    $categoriaPopular = null;
    if ($cat) {
        $categoriaPopular = [
            'nombre'     => $cat['nombre'],
            'total'      => (int) $cat['total'],
            'porcentaje' => round(((int) $cat['total'] / $totalR) * 100)
        ];
    }

    // Libro más reservado
    $libroPop = unaFila($connection, "
        SELECT l.id_libro, l.titulo, l.portada, COUNT(*) AS total
        FROM reservas r
        JOIN libros l ON r.fk_id_libro_reserva = l.id_libro
        WHERE l.fecha_eliminacion_libro IS NULL
        GROUP BY l.id_libro
        ORDER BY total DESC
        LIMIT 1
    ");

    $libroPopular = null;
    if ($libroPop) {
        $libroPopular = [
            'id_libro' => (int) $libroPop['id_libro'],
            'titulo'   => $libroPop['titulo'],
            'portada'  => $libroPop['portada'],
            'total'    => (int) $libroPop['total']
        ];
    }

    // Editoriales
    $editoriales = todasFilas($connection, "
        SELECT e.nombre, COUNT(l.id_libro) AS total
        FROM libros l
        JOIN editoriales e ON l.id_editorial = e.id_editorial
        WHERE l.fecha_eliminacion_libro IS NULL
        GROUP BY e.id_editorial
        ORDER BY total DESC
        LIMIT 5
    ");

    // Alertas de inventario
    $alertasRaw = todasFilas($connection, "
        SELECT
            l.id_libro,
            l.titulo,
            l.portada,
            COUNT(e.id_ejemplar) AS total_ejemplares,
            SUM(CASE WHEN e.estado = 'Disponible' THEN 1 ELSE 0 END) AS disponibles,
            SUM(CASE WHEN e.estado = 'Prestado' THEN 1 ELSE 0 END) AS prestados,
            SUM(CASE WHEN LOWER(e.estado) IN ('sin stock', 'no disponible', 'nodisponible') THEN 1 ELSE 0 END) AS problematicos
        FROM libros l
        LEFT JOIN ejemplares e ON e.fk_id_libro_ejemplar = l.id_libro
        WHERE l.fecha_eliminacion_libro IS NULL
        GROUP BY l.id_libro
        HAVING (total_ejemplares > 0 AND disponibles = 0) OR problematicos > 0
        ORDER BY disponibles ASC, total_ejemplares DESC
        LIMIT 12
    ");

    $alertas = [];
    foreach ($alertasRaw as $a) {
        $motivo  = 'Sin stock';
        $detalle = '0 de ' . (int) $a['total_ejemplares'] . ' ejemplares disponibles';
        $tipoA   = 'error';

        if ((int) $a['problematicos'] > 0) {
            $motivo  = 'No disponible';
            $detalle = (int) $a['problematicos'] . ' ejemplar(es) marcados como no disponibles';
            $tipoA   = 'error';
        } elseif ((int) $a['prestados'] > 0 && (int) $a['disponibles'] === 0) {
            $motivo  = 'Máx. préstamos';
            $detalle = 'Todos los ejemplares están prestados';
            $tipoA   = 'warning';
        }

        $alertas[] = [
            'id_libro' => (int) $a['id_libro'],
            'titulo'   => $a['titulo'],
            'portada'  => $a['portada'],
            'motivo'   => $motivo,
            'detalle'  => $detalle,
            'tipo'     => $tipoA
        ];
    }

    $frase = 'La lectura es a la mente lo que el ejercicio es al cuerpo.';

   
    echo json_encode([
        'ok'   => true,
        'logo' => '/shared/images/logo-appjoteca.svg',
        'metricas' => [
            'trafico_total'        => $traficoTotal,
            'reservas_diarias'     => $reservasDiarias,
            'usuarios_activos'     => $usuariosActivos,
            'usuarios_inactivos'   => $usuariosInactivos,
            'usuarios_con_reserva' => $usuariosConReserva,
            'prestamos_activos'    => $prestamosActivos,
            'devoluciones'         => $totalDevoluciones
        ],
        'grafica_reservas'  => $infoGrafica['datos'],
        'periodo'           => $infoGrafica['periodo'],
        'etiqueta'          => $infoGrafica['etiqueta'],
        'categoria_popular' => $categoriaPopular,
        'libro_popular'     => $libroPopular,
        'editoriales'       => $editoriales,
        'alertas'           => $alertas,
        'frase'             => $frase
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('get_reportes: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error al cargar los reportes']);
}