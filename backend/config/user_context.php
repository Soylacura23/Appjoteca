<?php
require_once __DIR__ . '/../Database/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$diccionario_roles = [
    1 => 'Estudiante',
    2 => 'Docente',
    3 => 'Bibliotecario',
];
define('BASE_URL', '/Appjoteca/');


$mi_id       = $_SESSION['usuario_id'] ?? null;
$mi_nombre   = $_SESSION['nombre'] ?? '';
$mi_usuario  = $_SESSION['usuario'] ?? '';
$mi_rol_num  = $_SESSION['rol'] ?? null;
$mi_rol      = $diccionario_roles[$mi_rol_num] ?? 'Usuario';
$mi_documento= $_SESSION['documento'];



$mi_foto = !empty($_SESSION['foto_perfil'])
    ? BASE_URL . $_SESSION['foto_perfil']
    : BASE_URL .'assets/images/default-avatar.png';

if (!isset($_SESSION['resumen_libros']) && $mi_id) {

    $_SESSION['resumen_libros'] =[
        'prestados' => 0,
        'reservados' => 0
      ];
    
        $sql = "SELECT 'prestado' AS tipo, COUNT(*) AS cantidad 
                FROM prestamos p
                INNER JOIN reservas r ON p.id_reserva = r.id_reserva
                WHERE r.fk_id_usuario_reserva = ? AND p.estado = 'activo'
                
                UNION ALL
                
                SELECT 'reservado' AS tipo, COUNT(*) AS cantidad 
                FROM reservas 
                WHERE fk_id_usuario_reserva = ? AND estado = 'activo'";

        if ($query = $connection->prepare($sql)) {

            $query->bind_param("ii", $mi_id, $mi_id);

            $query->execute();

            $resultado = $query->get_result();

            while ($fila = $resultado->fetch_assoc()) {

                if ($fila['tipo'] === 'prestado')  $_SESSION['resumen_libros']['prestados'] = $fila['cantidad'];

                if ($fila['tipo'] === 'reservado') $_SESSION['resumen_libros']['reservados'] = $fila['cantidad'];
            }
            $query->close();
        }
    }

?>

<script>
    window.AppUser = <?php echo json_encode([
        'id' => $mi_id,
        'nombre' => $mi_nombre,
        'usuario' => $mi_usuario,
        'rol' => $mi_rol,
        'foto' => $mi_foto,
        'libros' => $_SESSION['resumen_libros'] ?? null
    ]); ?>;
</script>