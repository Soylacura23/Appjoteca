<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../Database/conexion.php';

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/verify-csrf.php';

requiereRol([3]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $accion = $_POST['accion'] ?? '';

    try {
        switch ($accion) {

            // ── CREAR REGISTROS ──
            case 'crear_biblioteca':

                $nombre = trim($_POST['nombre'] ?? '');

                if (empty($nombre)) throw new Exception('El nombre de la biblioteca es obligatorio.');

                $sql = "INSERT INTO bibliotecas (nombre) VALUES (?)";
                $query = $connection->prepare($sql);
                $query->bind_param("s", $nombre);
                $query->execute();
                break;

            case 'crear_dewey':

                $codigo = trim($_POST['codigo'] ?? '');

                $nombre = trim($_POST['nombre'] ?? '');

                if (empty($codigo) || empty($nombre)) throw new Exception('El código y el nombre del área Dewey son obligatorios.');

                $sql = "INSERT INTO dewey (codigo, nombre) VALUES (?, ?)";
                $query = $connection->prepare($sql);
                $query->bind_param("ss", $codigo, $nombre);
                $query->execute();
                break;

            case 'crear_tipo_material':

                $nombre = trim($_POST['nombre'] ?? '');

                if (empty($nombre)) throw new Exception('El nombre del tipo de material es obligatorio.');

                $sql = "INSERT INTO tipos_materiales (nombre) VALUES (?)";
                $query = $connection->prepare($sql);
                $query->bind_param("s", $nombre);
                $query->execute();
                break;

            case 'crear_coleccion':

                $nombre = trim($_POST['nombre'] ?? '');

                $id_biblioteca = (int)($_POST['id_biblioteca'] ?? 0);

                if (empty($nombre) || $id_biblioteca <= 0) throw new Exception('Debes seleccionar una biblioteca válida y asignar un nombre.');

                $sql = "INSERT INTO colecciones (nombre, id_biblioteca) VALUES (?, ?)";
                $query = $connection->prepare($sql);
                $query->bind_param("si", $nombre, $id_biblioteca);
                $query->execute();
                break;

            case 'crear_materia':
                $nombre = trim($_POST['nombre'] ?? '');

                $id_dewey = (int)($_POST['id_dewey'] ?? 0);

                if (empty($nombre) || $id_dewey <= 0) throw new Exception('Debes seleccionar un área Dewey válida y asignar un nombre.');

                $sql = "INSERT INTO materias (nombre, id_dewey) VALUES (?, ?)";
                $query = $connection->prepare($sql);
                $query->bind_param("si", $nombre, $id_dewey);
                $query->execute();
                break;

            case 'eliminar_bibliotecas':
            case 'eliminar_dewey':
            case 'eliminar_tipos_materiales':
            case 'eliminar_colecciones':
            case 'eliminar_materias':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception('Identificador de registro no válido.');

                $mapaTablas = [
                    'eliminar_bibliotecas'      => ['tabla' => 'bibliotecas',      'pk' => 'id_biblioteca'],
                    'eliminar_dewey'            => ['tabla' => 'dewey',            'pk' => 'id_dewey'],
                    'eliminar_tipos_materiales' => ['tabla' => 'tipos_materiales', 'pk' => 'id_tipo_material'],
                    'eliminar_colecciones'      => ['tabla' => 'colecciones',      'pk' => 'id_coleccion'],
                    'eliminar_materias'         => ['tabla' => 'materias',         'pk' => 'id_materia']
                ];

                $info = $mapaTablas[$accion];
                $sql = "DELETE FROM {$info['tabla']} WHERE {$info['pk']} = ?";
                $query = $connection->prepare($sql);
                $query->bind_param("i", $id);
                $query->execute();
                break;

            // ── EDITAR REGISTROS ──
            case 'editar_bibliotecas':

                $id = (int)($_POST['id'] ?? 0);

                $nombre = trim($_POST['nombre'] ?? '');

                if ($id <= 0 || empty($nombre)) throw new Exception('El nombre de la biblioteca es obligatorio.');

                $sql = "UPDATE bibliotecas SET nombre = ? WHERE id_biblioteca = ?";
                $query = $connection->prepare($sql);
                $query->bind_param("si", $nombre, $id);
                $query->execute();
                break;

            case 'editar_dewey':
                $id = (int)($_POST['id'] ?? 0);

                $codigo = trim($_POST['codigo'] ?? '');

                $nombre = trim($_POST['nombre'] ?? '');

                if ($id <= 0 || empty($codigo) || empty($nombre)) throw new Exception('El código y el nombre son obligatorios.');

                $sql = "UPDATE dewey SET codigo = ?, nombre = ? WHERE id_dewey = ?";
                $query = $connection->prepare($sql);
                $query->bind_param("ssi", $codigo, $nombre, $id);
                $query->execute();
                break;

            case 'editar_tipos_materiales':

                $id = (int)($_POST['id'] ?? 0);

                $nombre = trim($_POST['nombre'] ?? '');

                if ($id <= 0 || empty($nombre)) throw new Exception('El nombre del tipo de material es obligatorio.');

                $sql = "UPDATE tipos_materiales SET nombre = ? WHERE id_tipo_material = ?";
                $query = $connection->prepare($sql);
                $query->bind_param("si", $nombre, $id);
                $query->execute();
                break;

            case 'editar_colecciones':

                $id = (int)($_POST['id'] ?? 0);

                $nombre = trim($_POST['nombre'] ?? '');

                if ($id <= 0 || empty($nombre)) throw new Exception('El nombre de la colección es obligatorio.');

                $sql = "UPDATE colecciones SET nombre = ? WHERE id_coleccion = ?";
                $query = $connection->prepare($sql);
                $query->bind_param("si", $nombre, $id);
                $query->execute();
                break;

            case 'editar_materias':

                $id = (int)($_POST['id'] ?? 0);

                $nombre = trim($_POST['nombre'] ?? '');

                if ($id <= 0 || empty($nombre)) throw new Exception('El nombre de la materia es obligatorio.');

                $sql = "UPDATE materias SET nombre = ? WHERE id_materia = ?";
                $query = $connection->prepare($sql);
                $query->bind_param("si", $nombre, $id);
                $query->execute();
                break;

            default:
                throw new Exception('Acción no reconocida.');
        }

        echo json_encode(['status' => 'success']);

    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() === 1062) {

            echo json_encode(['status' => 'error', 'mensaje' => 'Ya existe un registro con esos datos.']);

        } elseif ($e->getCode() === 1451) {

            echo json_encode(['status' => 'error', 'mensaje' => 'No se puede eliminar porque este registro está en uso por colecciones, materias o libros asociados.']);

        } else {

            echo json_encode(['status' => 'error', 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {

        echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
    }

    $connection->close();

} else {
    
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit();
}
?>