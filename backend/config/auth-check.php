<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache"); 
header("Expires: 0"); 


if (isset($_SESSION['usuario_id'])) { 
    $rol = $_SESSION['rol'] ?? 'estudiante';

    switch ($rol) {
        case '4':
            $url = "/Appjoteca/dashboards/Administrador/index.php";
            break;
        case '3':
            $url = "/Appjoteca/dashboards/bibliotecario/index.php";
            break;
        case '2':
                $url = "/Appjoteca/dashboards/docente/index.php";
                break;
        case '1':
            $url = "/Appjoteca/dashboards/estudiante/index.php";
                break;
                
        default:
            $url = "/Appjoteca/backend/auth/logout.php";
            break;
    }
    echo "<script>window.location.replace('$url');</script>";
    exit();
}
?>