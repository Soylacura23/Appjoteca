<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = array();

if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros['path'],
        $parametros['domain'],
        $parametros['secure'],
        $parametros['httponly']
    );
}

session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");

header("Location: ../../auth/login/login.php");
exit();
?>