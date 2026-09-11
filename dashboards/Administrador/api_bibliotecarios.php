<?php
// Activar errores temporalmente para diagnosticar
error_reporting(E_ALL);
ini_set('display_errors', 0); // Lo dejamos en 0 para que no imprima HTML puro y rompa el JSON

header('Content-Type: application/json');

try {
    // 1. Incluir conexión
    require_once __DIR__ . '/../../backend/Database/conexion.php';

    // SI TU VARIABLE DE CONEXIÓN NO ES $conn, CÁMBIALA AQUÍ:
    $conexion_db = $conn; // <-- Cambia $conn por $conexion o $mysqli según tu archivo conexion.php

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // LISTAR
    if ($action === 'listar') {
        // Ajusta los nombres de las columnas a como los tienes en appjoteca.sql
        $sql = "SELECT id, documento, nombres, apellidos, correo FROM usuarios";
        $result = $conexion_db->query($sql);
        
        if (!$result) {
            throw new Exception("Error SQL al listar: " . $conexion_db->error);
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
    }

    // CREAR
    if ($action === 'crear') {
        $documento = trim($_POST['documento'] ?? '');
        $nombres   = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $correo    = trim($_POST['correo'] ?? '');
        $password  = $_POST['password'] ?? '';

        if (!$documento || !$nombres || !$correo || !$password) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
            exit;
        }

        $passHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conexion_db->prepare("INSERT INTO usuarios (documento, nombres, apellidos, correo, password, rol) VALUES (?, ?, ?, ?, ?, 'bibliotecario')");
        
        if (!$stmt) {
             throw new Exception("Error preparando consulta: " . $conexion_db->error);
        }

        $stmt->bind_param("sssss", $documento, $nombres, $apellidos, $correo, $passHash);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Bibliotecario registrado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar: ' . $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    // ELIMINAR
    if ($action === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conexion_db->prepare("DELETE FROM usuarios WHERE id = ?");
        
        if (!$stmt) {
             throw new Exception("Error preparando eliminación: " . $conexion_db->error);
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Bibliotecario eliminado.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar: ' . $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida.']);

} catch (Exception $e) {
    // Si hay un error fatal, lo capturamos y lo enviamos como JSON válido para que SweetAlert lo muestre
    echo json_encode(['status' => 'error', 'message' => 'Excepción PHP: ' . $e->getMessage()]);
}
?>