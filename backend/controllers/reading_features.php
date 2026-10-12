<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../Database/conexion.php';
require_once __DIR__ . '/../config/verify-csrf.php';

$role = (int) ($_SESSION['rol'] ?? 0);
$userId = (int) ($_SESSION['usuario_id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (!in_array($role, [1, 2, 4], true) || !$connection) {
    http_response_code($connection ? 403 : 503);
    echo json_encode(['ok' => false, 'error' => 'No se pudo acceder a estas funciones.']);
    exit;
}

function featureString(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function featureLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function featureDate(?string $date): string|false|null
{
    if ($date === null || $date === '') return null;
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date ? $date : false;
}

try {
    if ($action === 'notes_list') {
        requiereRol([2]);
        $query = $connection->prepare(
            'SELECT id, titulo, contenido, fecha_recordatorio, es_importante, fecha_creacion
             FROM notas_docente WHERE id_usuario = ?
             ORDER BY es_importante DESC, fecha_recordatorio IS NULL, fecha_recordatorio ASC, fecha_creacion DESC
             LIMIT 100'
        );
        $query->bind_param('i', $userId);
        $query->execute();
        $result = $query->get_result();
        $notes = [];
        while ($row = $result->fetch_assoc()) $notes[] = $row;
        $query->close();
        echo json_encode(['ok' => true, 'notes' => $notes], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'note_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        requiereRol([2]);
        $id = (int) ($_POST['id'] ?? 0);
        $title = featureString('titulo');
        $content = featureString('contenido');
        $reminderInput = $_POST['fecha_recordatorio'] ?? '';
        $reminder = featureDate(is_string($reminderInput) ? $reminderInput : null);
        $important = (int) ($_POST['es_importante'] ?? 0) === 1 ? 1 : 0;
        if ($title === '' || featureLength($title) > 120 || $content === '' || featureLength($content) > 10000 || $reminder === false
            || !is_string($reminderInput)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Revisa el título, el contenido y la fecha.']);
            exit;
        }
        if ($id > 0) {
            $query = $connection->prepare(
                'UPDATE notas_docente SET titulo = ?, contenido = ?, fecha_recordatorio = ?,
                 es_importante = ?, recordatorio_enviado = 0 WHERE id = ? AND id_usuario = ?'
            );
            $query->bind_param('sssiii', $title, $content, $reminder, $important, $id, $userId);
        } else {
            $query = $connection->prepare(
                'INSERT INTO notas_docente (id_usuario, titulo, contenido, fecha_recordatorio, es_importante)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $query->bind_param('isssi', $userId, $title, $content, $reminder, $important);
        }
        $query->execute();
        $saved = $id === 0 ? $query->insert_id > 0 : $query->affected_rows > 0;
        $query->close();
        if (!$saved && $id > 0) {
            $exists = $connection->prepare('SELECT id FROM notas_docente WHERE id = ? AND id_usuario = ? LIMIT 1');
            $exists->bind_param('ii', $id, $userId);
            $exists->execute();
            $saved = (bool) $exists->get_result()->fetch_assoc();
            $exists->close();
        }
        echo json_encode(['ok' => $saved, 'error' => $saved ? null : 'No se pudo guardar la nota.']);
        exit;
    }

    if ($action === 'note_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        requiereRol([2]);
        $id = (int) ($_POST['id'] ?? 0);
        $query = $connection->prepare('DELETE FROM notas_docente WHERE id = ? AND id_usuario = ?');
        $query->bind_param('ii', $id, $userId);
        $query->execute();
        $deleted = $query->affected_rows === 1;
        $query->close();
        echo json_encode(['ok' => $deleted, 'error' => $deleted ? null : 'No se encontró la nota.']);
        exit;
    }

    if ($action === 'wishlist_list') {
        $query = $connection->prepare(
            "SELECT l.id_libro, l.titulo, l.portada, w.fecha_agregado,
                    COALESCE(GROUP_CONCAT(DISTINCT a.nombre SEPARATOR ', '), 'Autor desconocido') AS autores
             FROM lista_deseos w
             JOIN libros l ON l.id_libro = w.id_libro
             LEFT JOIN libro_autor la ON la.id_libro = l.id_libro
             LEFT JOIN autores a ON a.id_autor = la.id_autor
             WHERE w.id_usuario = ? AND l.fecha_eliminacion_libro IS NULL
             GROUP BY l.id_libro, w.fecha_agregado ORDER BY w.fecha_agregado DESC LIMIT 100"
        );
        $query->bind_param('i', $userId);
        $query->execute();
        $result = $query->get_result();
        $books = [];
        while ($row = $result->fetch_assoc()) $books[] = $row;
        $query->close();
        echo json_encode(['ok' => true, 'books' => $books], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'wishlist_toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $bookId = filter_var($_POST['id_libro'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($bookId === false) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'El libro no es válido.']);
            exit;
        }
        $book = $connection->prepare('SELECT id_libro FROM libros WHERE id_libro = ? AND fecha_eliminacion_libro IS NULL');
        $book->bind_param('i', $bookId);
        $book->execute();
        $existsBook = (bool) $book->get_result()->fetch_assoc();
        $book->close();
        if (!$existsBook) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'El libro ya no está disponible en el catálogo.']);
            exit;
        }
        $connection->begin_transaction();
        $exists = $connection->prepare('SELECT id FROM lista_deseos WHERE id_usuario = ? AND id_libro = ? FOR UPDATE');
        $exists->bind_param('ii', $userId, $bookId);
        $exists->execute();
        $found = $exists->get_result()->fetch_assoc();
        $exists->close();
        if ($found) {
            $query = $connection->prepare('DELETE FROM lista_deseos WHERE id_usuario = ? AND id_libro = ?');
            $query->bind_param('ii', $userId, $bookId);
            $query->execute();
            $saved = false;
        } else {
            $query = $connection->prepare('INSERT INTO lista_deseos (id_usuario, id_libro) VALUES (?, ?)');
            $query->bind_param('ii', $userId, $bookId);
            $query->execute();
            $saved = true;
        }
        $query->close();
        $connection->commit();
        echo json_encode(['ok' => true, 'saved' => $saved]);
        exit;
    }

    if ($action === 'goals_list') {
        $query = $connection->prepare(
            'SELECT id, titulo, cantidad_objetivo, cantidad_actual, fecha_limite, completada
             FROM metas WHERE id_usuario = ? ORDER BY completada ASC, fecha_creacion DESC LIMIT 50'
        );
        $query->bind_param('i', $userId);
        $query->execute();
        $result = $query->get_result();
        $goals = [];
        while ($row = $result->fetch_assoc()) $goals[] = $row;
        $query->close();

        $query = $connection->prepare(
            'SELECT i.codigo, i.nombre, i.descripcion, i.icono, ui.fecha_obtencion
             FROM usuario_insignias ui JOIN insignias i ON i.id = ui.id_insignia
             WHERE ui.id_usuario = ? ORDER BY ui.fecha_obtencion DESC'
        );
        $query->bind_param('i', $userId);
        $query->execute();
        $result = $query->get_result();
        $badges = [];
        while ($row = $result->fetch_assoc()) $badges[] = $row;
        $query->close();
        echo json_encode(['ok' => true, 'goals' => $goals, 'badges' => $badges], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'goal_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = featureString('titulo');
        $targetInput = $_POST['cantidad_objetivo'] ?? null;
        $target = is_string($targetInput) || is_int($targetInput)
            ? filter_var($targetInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]])
            : false;
        $deadlineInput = $_POST['fecha_limite'] ?? '';
        $deadline = featureDate(is_string($deadlineInput) ? $deadlineInput : null);
        if ($title === '' || featureLength($title) > 120 || $target === false || $deadline === false || !is_string($deadlineInput)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Revisa el título, la cantidad y la fecha límite.']);
            exit;
        }
        $query = $connection->prepare(
            'INSERT INTO metas (id_usuario, titulo, cantidad_objetivo, fecha_limite)
             VALUES (?, ?, ?, ?)'
        );
        $query->bind_param('isis', $userId, $title, $target, $deadline);
        $query->execute();
        $created = $query->insert_id > 0;
        $query->close();
        echo json_encode(['ok' => $created, 'error' => $created ? null : 'No se pudo crear la meta.']);
        exit;
    }

    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Acción no encontrada.']);
} catch (Throwable $error) {
    try { $connection->rollback(); } catch (Throwable) {}
    error_log('Error en funciones de lectura: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo completar la solicitud.']);
}
