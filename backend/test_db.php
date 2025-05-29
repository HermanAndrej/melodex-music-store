<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
    // Test database connection
    $conn = Database::connect();
    
    // Test query
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result && $result['test'] == 1) {
        echo json_encode(['success' => true, 'message' => 'Database connection successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database test query failed']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
}
?>
