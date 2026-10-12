<?php

session_start();

if (file_exists(__DIR__ . '/../install.lock')) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../backend/config/env.php';
require_once __DIR__ . '/../backend/Database/conexion.php';

$error = '';

try {
    $connection = db_connect(true);
} catch (Throwable $e) {
    header('Location: install.php?step=database');
    exit;
}

$result = $connection->query("SELECT COUNT(*) AS total FROM usuarios WHERE id_rol = 4");
$row = $result ? $result->fetch_assoc() : null;
$hasAdmin = $row && (int)$row['total'] > 0;

if ($hasAdmin) {
    header('Location: install-finish.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_apellido      = trim($_POST['nombre_apellido'] ?? '');
    $nombre_usuario       = trim($_POST['nombre_usuario'] ?? '');
    $correo_institucional = trim($_POST['correo_institucional'] ?? '');
    $documento            = trim($_POST['documento'] ?? '');
    $password             = $_POST['password'] ?? '';
    $confirm              = $_POST['confirm'] ?? '';

    // Validaciones
    if ($nombre_apellido === '') {
        $error = 'El nombre completo es obligatorio.';
    } elseif ($nombre_usuario === '') {
        $error = 'El nombre de usuario es obligatorio.';
    } elseif (strlen($nombre_usuario) > 30) {
        $error = 'El nombre de usuario no puede tener más de 30 caracteres.';
    } elseif (!filter_var($correo_institucional, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo institucional no es válido.';
    } elseif ($documento === '') {
        $error = 'El documento es obligatorio.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $check = $connection->prepare(
            "SELECT id_usuario FROM usuarios WHERE correo_institucional = ? OR nombre_usuario = ? LIMIT 1"
        );
        $check->bind_param('ss', $correo_institucional, $nombre_usuario);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $error = 'El correo o el nombre de usuario ya están registrados.';
            $check->close();
        } else {
            $check->close();

            $adminCheck = $connection->query("SELECT COUNT(*) AS total FROM usuarios WHERE id_rol = 4");
            $adminRow = $adminCheck->fetch_assoc();
            if ((int)$adminRow['total'] > 0) {
                header('Location: install-finish.php');
                exit;
            }

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $fecha = date('Y-m-d');
            $id_rol = 4;
            $estado = '1';

            $query = $connection->prepare(
                "INSERT INTO usuarios
                    (id_rol, nombre_apellido, nombre_usuario, correo_institucional, documento, password, estado, fecha_registro)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$query) {
                $error = 'Error al preparar la consulta: ' . $connection->error;
            } else {
                $query->bind_param(
                    'isssssss',
                    $id_rol,
                    $nombre_apellido,
                    $nombre_usuario,
                    $correo_institucional,
                    $documento,
                    $hash,
                    $estado,
                    $fecha
                );

                if ($query->execute()) {
                    $query->close();
                    $connection->close();
                    header('Location: install-finish.php');
                    exit;
                } else {
                    $error = 'No se pudo crear el administrador: ' . $query->error;
                    $query->close();
                }
            }
        }
    }
}

$connection->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear administrador - Appjoteca</title>

    <?php include_once '/Appjoteca/shared/layouts/anti-fouc.php'; ?>
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: #fff;
            width: 100%;
            max-width: 420px;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        h1 {
            font-size: 22px;
            margin-bottom: 8px;
            color: #1a1a1a;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .error {
            background: #fdecea;
            color: #c0392b;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 15px;
            margin-bottom: 16px;
        }

        input:focus {
            outline: none;
            border-color: #2c6bed;
            box-shadow: 0 0 0 2px rgba(44, 107, 237, 0.15);
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2c6bed;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #1e54c9;
        }

        .hint {
            font-size: 12px;
            color: #888;
            margin-top: -10px;
            margin-bottom: 16px;
        }
    </style>
    <?php 
        include_once __DIR__ . '/../shared/layouts/favicon.php';
    ?>
</head>
<body>
    <div class="card">
        <h1>Primer administrador</h1>
        <p class="subtitle">Crea la cuenta que administrará el sistema.</p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off" novalidate>
            <label for="nombre_apellido">Nombre completo</label>
            <input type="text" id="nombre_apellido" name="nombre_apellido" required
                   value="<?= htmlspecialchars($_POST['nombre_apellido'] ?? '') ?>">

            <label for="nombre_usuario">Nombre de usuario</label>
            <input type="text" id="nombre_usuario" name="nombre_usuario" required maxlength="30"
                   value="<?= htmlspecialchars($_POST['nombre_usuario'] ?? '') ?>">

            <label for="correo_institucional">Correo institucional</label>
            <input type="email" id="correo_institucional" name="correo_institucional" required
                   value="<?= htmlspecialchars($_POST['correo_institucional'] ?? '') ?>">

            <label for="documento">Documento</label>
            <input type="text" id="documento" name="documento" required
                   value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>">

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required minlength="8">
            <p class="hint">Mínimo 8 caracteres</p>

            <label for="confirm">Confirmar contraseña</label>
            <input type="password" id="confirm" name="confirm" required minlength="8">

            <button type="submit">Crear administrador</button>
        </form>
    </div>
<script src="/Appjoteca/shared/js/register-sw.js"></script>
</body>
</html>