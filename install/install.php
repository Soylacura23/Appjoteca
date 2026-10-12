<?php
session_start();

if (file_exists(__DIR__ . '/../install.lock')) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../backend/config/env.php';

$step = $_GET['step'] ?? 'composer';
$error = '';

if ($step === 'composer') {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        header('Location: ?step=env');
        exit;
    }
?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Instalación - Composer</title>

        <?php include_once '/Appjoteca/shared/layouts/anti-fouc.php'; ?>

        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; }
            pre { background: #f4f4f4; padding: 12px; border-radius: 4px; }
            a { display: inline-block; margin-top: 16px; }
        </style>
        <?php 
        include_once __DIR__ . '/../shared/layouts/favicon.php';
        ?>
    </head>
    <body>
        <h1>Paso 1 - Composer</h1>
        <p>No se encontró <code>vendor/autoload.php</code>.</p>
        <p>Ejecuta en la raíz del proyecto:</p>
        <pre>composer install</pre>
        <a href="?step=composer">Reintentar</a>
        <script src="/Appjoteca/shared/js/register-sw.js"></script>
    </body>
    </html>
<?php
    exit;
}

if ($step === 'env') {

    $requiredNotEmpty = ['DB_HOST', 'DB_USER', 'DB_NAME', 'SMTP_PASS', 'SMTP_USER'];

    $requiredCanBeEmpty = ['DB_PASS', 'GOOGLE_BOOKS_API_KEY'];
    
    $missing = [];

    foreach ($requiredNotEmpty as $key) {
        if (!isset($_ENV[$key]) || $_ENV[$key] === '') {
            $missing[] = $key;
        }
    }

    foreach ($requiredCanBeEmpty as $key) {
        if (!array_key_exists($key, $_ENV)) {
            $missing[] = $key;
        }
    }

    if (empty($missing)) {
        header('Location: ?step=connection');
        exit;
    }

?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Instalación - Variables de entorno</title>

        <?php include_once '/Appjoteca/shared/layouts/anti-fouc.php'; ?>

        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; }
            ul { background: #fff3cd; padding: 16px 16px 16px 36px; border-radius: 4px; }
            a { display: inline-block; margin-top: 16px; }
        </style>
        <?php 
        include_once __DIR__ . '/../shared/layouts/favicon.php';
        ?>
    </head>
    <body>
        <h1>Paso 2 - Variables de entorno</h1>
        <p>Faltan estas claves en tus variables de entorno:</p>
        <ul>
            <?php foreach ($missing as $key): ?>
                <li><code><?= htmlspecialchars($key) ?></code></li>
            <?php endforeach; ?>
        </ul>
        <a href="?step=env">Reintentar</a>
        <script src="/Appjoteca/shared/js/register-sw.js"></script>
    </body>
    </html>
<?php
    exit;
}

if ($step === 'connection') {
    require_once __DIR__ . '/../backend/Database/conexion.php';

    try {
        $connection = db_connect(false); 
        $connection->close();
        header('Location: ?step=database');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Instalación - Conexión</title>

        <?php include_once '/Appjoteca/shared/layouts/anti-fouc.php'; ?>

        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; }
            .error { color: #c0392b; background: #fdecea; padding: 12px; border-radius: 4px; }
            a { display: inline-block; margin-top: 16px; }
        </style>
        <?php 
        include_once __DIR__ . '/../shared/layouts/favicon.php';
        ?>
    </head>
    <body>
        <h1>Paso 3 - Conexión</h1>
        <p class="error"><?= htmlspecialchars($error) ?></p>
        <p>Revisa <code>DB_HOST</code>, <code>DB_USER</code> y <code>DB_PASS</code>.</p>
        <a href="?step=connection">Reintentar</a>
        <script src="/Appjoteca/shared/js/register-sw.js"></script>
    </body>
    </html>
    <?php
    exit;
}

if ($step === 'database') {
    require_once __DIR__ . '/../backend/Database/conexion.php';

    try {
        $connection = db_connect(false);
        $dbName = $connection->real_escape_string($_ENV['DB_NAME']);
        $result = $connection->query("SHOW DATABASES LIKE '$dbName'");
        $exists = $result && $result->num_rows > 0;
        $connection->close();

        if ($exists) {
            header('Location: create-admin.php');
            exit;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $connection = db_connect(false);
            $dbName = $connection->real_escape_string($_ENV['DB_NAME']);

            $connection->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $connection->select_db($_ENV['DB_NAME']);

            $sqlPath = __DIR__ . '/../sql/schema.sql';
            if (!file_exists($sqlPath)) {
                throw new Exception('No se encontró el archivo sql/schema.sql');
            }

            $sql = file_get_contents($sqlPath);
            if ($sql === false) {
                throw new Exception('No se pudo leer sql/schema.sql');
            }

            if (!$connection->multi_query($sql)) {
                throw new Exception($connection->error);
            }

            while ($connection->more_results() && $connection->next_result()) {
                // Limpiar resultados
            }

            $connection->close();
            header('Location: create-admin.php');
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        
        <title>Instalación - Base de datos</title>

        <?php include_once '/Appjoteca/shared/layouts/anti-fouc.php'; ?>
        
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; }
            .error { color: #c0392b; background: #fdecea; padding: 12px; border-radius: 4px; }
            button { padding: 10px 18px; font-size: 16px; cursor: pointer; }
        </style>
        <?php 
        include_once __DIR__ . '/../shared/layouts/favicon.php';
        ?>
    </head>
    <body>
        <h1>Paso 4 - Base de datos</h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <p>La base de datos <strong><?= htmlspecialchars($_ENV['DB_NAME']) ?></strong> no existe.</p>

        <form method="post">
            <button type="submit">Crear base de datos y ejecutar schema.sql</button>
        </form>

        <p style="margin-top: 20px; color: #666;">
            O ejecuta manualmente <code>/Appjoteca/sql/schema.sql</code> y recarga esta página.
        </p>
        <script src="/Appjoteca/shared/js/register-sw.js"></script>
    </body>
    </html>
    <?php
    exit;
}

http_response_code(404);
exit('Paso no válido.');
?>