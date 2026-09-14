<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $headers = getallheaders();
    $clientToken = $headers['X-CSRF-TOKEN'] ?? $headers['X-Csrf-Token'] ?? '';

    if (empty($clientToken)) {
        $clientToken = $_POST['csrf_token'] ?? '';
    }

    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (empty($clientToken) || empty($sessionToken) || !hash_equals($sessionToken, $clientToken)) {
        
        http_response_code(403); 
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            "success" => false, 
            "ok" => false,      
            "message" => "Error de seguridad: Token CSRF inválido o expirado."
        ]);
        
        exit; 
    }
}