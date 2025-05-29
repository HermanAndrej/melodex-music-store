<?php
return function($flight) {
    // Simple test route
    $flight->route('GET /test', function() use ($flight) {
        $flight->json([
            'success' => true,
            'message' => 'Test route working',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => 'GET'
        ]);
    });
    
    // Test POST route
    $flight->route('POST /test', function() use ($flight) {
        $input = json_decode(file_get_contents('php://input'), true);
        $flight->json([
            'success' => true,
            'message' => 'Test POST route working',
            'received_data' => $input,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    });
    
    // Debug info route
    $flight->route('GET /debug', function() use ($flight) {
        $flight->json([
            'success' => true,
            'server_info' => [
                'php_version' => PHP_VERSION,
                'flight_loaded' => class_exists('Flight\\Engine') ? 'yes' : 'no',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'env_loaded' => isset($_ENV['JWT_SECRET']) ? 'yes' : 'no'
            ]
        ]);
    });
};