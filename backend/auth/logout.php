<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 0,
        'cookie_path'     => '/Appjoteca',
        'cookie_domain'   => '/',
        'cookie_secure'   => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

if (isset($_SESSION['usuario_id'])) {
    require_once(__DIR__ . "/../Database/conexion.php"); 

    $id_usuario = $_SESSION['usuario_id'];
    $sql_logout = "UPDATE usuarios SET remember_token = NULL WHERE id_usuario = ?";
    $query_logout = $connection->prepare($sql_logout);
    $query_logout->bind_param("i", $id_usuario);
    $query_logout->execute();
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
setcookie("remember_me", "", [
    'expires' => time() - 3600,
    'path' => '/Appjoteca',
    'domain' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache"); 
header("Expires: 0"); 

header("Location: ../../auth/login/login.php");
exit();
?>