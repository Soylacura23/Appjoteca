<?php
require_once __DIR__ . '/../../backend/config/security.php';

$baseUrl = '/Appjoteca/'; 
$currentUri = $_SERVER['REQUEST_URI'];

$userRole = isset($_SESSION['rol']) ? (string)$_SESSION['rol'] : null;

$menusByRole = [
    'guest' => [ 
        ['text' => 'Catálogo', 'url' => 'pages/biblioteca-digital/index.php', 'nav' => 'catalogo'],
        
    ],
    '1' => [ // Estudiante
        ['text' => 'Catálogo',     'url' => 'pages/biblioteca-digital/index.php', 'nav' => 'catalogo'],
        ['text' => 'Mi historial', 'url' => 'pages/history/historial.php',           'nav' => 'historial'],
        ['text' => 'Panel',         'url' => 'dashboards/estudiante/index.php',            'nav' => 'panel']
    ],
    '2' => [ // Docente
        ['text' => 'Catálogo',     'url' => 'pages/biblioteca-digital/index.php', 'nav' => 'catalogo'],
        ['text' => 'Mi historial', 'url' => 'pages/history/historial.php',        'nav' => 'historial'],
        ['text' => 'Panel',         'url' => 'dashboards/docente/index.php',        'nav' => 'panel']
    ],
    '3' => [ // Bibliotecario
        ['text' => 'Gestión Libros', 'url' => 'pages/admin/libros.php',           'nav' => 'gestion-libros'],
        ['text' => 'Préstamos',      'url' => 'pages/admin/prestamos.php',        'nav' => 'prestamos'],
        ['text' => 'Panel Control',  'url' => 'pages/admin/dashboard.php',        'nav' => 'panel']
    ],
    '4' => [ // Administrador
        ['text' => 'Gestión Comentarios', 'url' => 'dashboards/Administrador/comentarios/comentarios.php', 'nav' => 'comentarios'],
        ['text' => 'Panel Control',  'url' => 'dashboards/Administrador/index.php',        'nav' => 'panel']
    ]
];

$rolesValidos = ['1', '2', '3', '4'];
if ($userRole !== null && !in_array($userRole, $rolesValidos, true)) {
    header('Location: /Appjoteca/backend/auth/logout.php');
    exit;
}

$navigationMenu = $menusByRole[$userRole] ?? $menusByRole['guest'];

function isNavActive($urlTarget, $currentUri) {
    $needle = ltrim($urlTarget, '/');
    return (strpos($currentUri, $needle) !== false) ? 'active' : '';
}
?>