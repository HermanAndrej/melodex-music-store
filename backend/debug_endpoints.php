<?php
// === DEBUGGING HELPER SCRIPT ===
// Create a separate debug_endpoints.php file to test your API

function debugEndpoint($method, $url, $data = null) {
    echo "\n=== Testing $method $url ===\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        echo "Sending data: " . json_encode($data) . "\n";
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    echo "HTTP Code: $httpCode\n";
    echo "Content-Type: $contentType\n";
    echo "Response: $response\n";
    
    curl_close($ch);
    
    return json_decode($response, true);
}

// Test the endpoints
$baseUrl = 'http://localhost:8000';

// Test register
$registerResult = debugEndpoint('POST', "$baseUrl/auth/register", [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123'
]);

// Test login
$loginResult = debugEndpoint('POST', "$baseUrl/auth/login", [
    'email' => 'test@example.com',
    'password' => 'password123'
]);

// If login successful, test protected endpoint
if (isset($loginResult['data']['token'])) {
    $token = $loginResult['data']['token'];
    echo "\n=== Testing Protected Endpoint ===\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/api/orders");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer $token"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Protected endpoint HTTP Code: $httpCode\n";
    echo "Protected endpoint Response: $response\n";
    
    curl_close($ch);
}

?>