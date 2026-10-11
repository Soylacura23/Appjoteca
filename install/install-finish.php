<?php
session_start();

if (file_exists(__DIR__ . '/../install.lock')) {
    header('Location: ../index.php');
    exit;
}

file_put_contents('../install.lock', date('c') . "\n");

session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación completa - Appjoteca</title>
    <style>
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
            max-width: 480px;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 12px;
            color: #1a1a1a;
        }

        p {
            color: #555;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .ok {
            color: #1e7e34;
            font-weight: 600;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #2c6bed;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
        }

        a:hover {
            background: #1e54c9;
        }

        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Instalación completa</h1>
        <p class="ok">Se creó el archivo <code>install.lock</code>.</p>
        <p>El instalador y el formulario de administrador quedan deshabilitados.</p>
        <p>Recomendación: elimina o renombra <code>install.php</code> y <code>create-admin.php</code>.</p>
        <a href="/Appjoteca/index.php">Ir a la aplicación</a>
    </div>
</body>
</html>