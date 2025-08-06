<?php
// Deshabilitar la visualización de errores
error_reporting(0);
ini_set('display_errors', 0);

// Asegurarse de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establecer el tipo de contenido como JSON
header('Content-Type: application/json');

try {
    // Verificar si existe una sesión activa
    if (isset($_SESSION['usuario_id'])) {
        echo json_encode([
            'success' => true,
            'autenticado' => true,
            'usuario' => [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'autenticado' => false,
            'mensaje' => 'No hay sesión activa'
        ]);
    }
} catch (Exception $e) {
    // En caso de cualquier error, devolver una respuesta JSON con el error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'autenticado' => false,
        'error' => 'Error interno del servidor',
        'mensaje' => $e->getMessage()
    ]);
}