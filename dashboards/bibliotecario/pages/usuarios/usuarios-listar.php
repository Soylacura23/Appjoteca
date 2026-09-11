<?php
require_once __DIR__ . '/../../../../backend/config/auth.php';
require_once __DIR__ . '/../../../../backend/Database/conexion.php';

header('Content-Type: application/json; charset=utf-8');

define('BASE_URL', '/Appjoteca/');


$usuarios = [];

$diccionario_roles = [
    1 => 'Estudiante',
    2 => 'Docente',
    3 => 'Bibliotecario',
];

$resultado = mysqli_query(
    $connection,
    "SELECT id_usuario, id_rol, nombre_apellido, nombre_usuario, correo_institucional, documento, foto_perfil, foto_documento, biografia, estado, fecha_registro
     FROM usuarios
     WHERE id_rol IN (1, 2)"
);

if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $foto = !empty($fila['foto_perfil']) 
            ? BASE_URL . $fila['foto_perfil'] 
            : BASE_URL . 'assets/images/default-avatar.png';
        $usuarios[] = [
            'id'             => $fila['id_usuario'],
            'rol'            => $diccionario_roles[$fila['id_rol']] ?? 'Sin rol',
            'nombre'         => $fila['nombre_apellido'],
            'usuario'        => $fila['nombre_usuario'],
            'correo'         => $fila['correo_institucional'],
            'documento'      => $fila['documento'],
            'foto_perfil'    => $foto,
            'foto_documento' => $fila['foto_documento'],
            'biografia'      => $fila['biografia'],
            'estado'         => $fila['estado'] == 1 ? 'activo' : 'inactivo',
            'fecha_creacion' => $fila['fecha_registro']
        ];
    }
    mysqli_free_result($resultado);
    mysqli_close($connection);

    echo json_encode(['ok' => true, 'usuarios' => $usuarios], JSON_UNESCAPED_UNICODE);
} else{
    $error_msg = mysqli_error($connection);
    mysqli_close($connection);

    echo json_encode(['ok' => false, 'error' => 'Error en la base de datos: ' . $error_msg], JSON_UNESCAPED_UNICODE);
}



