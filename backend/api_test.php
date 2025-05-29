<?php
function debugRequest($method, $url, $data = null, $token = null) {
    echo "\n=== Testing $method $url ===\n";
    
    $ch = curl_init();
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose for debugging
    curl_setopt($ch, CURLOPT_STDERR, fopen('curl_debug.log', 'a')); // Log curl details
    
    if ($data !== null) {
        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        echo "Sending JSON: $jsonData\n";
    }
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch) . "\n";
        curl_close($ch);
        return null;
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    
    echo "Effective URL: $effectiveUrl\n";
    echo "HTTP Code: $httpCode\n";
    echo "Content-Type: $contentType\n";
    echo "Raw Response: $response\n";
    
    curl_close($ch);
    
    $decodedBody = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON decode error: " . json_last_error_msg() . "\n";
        $decodedBody = $response;
    }
    
    return ['code' => $httpCode, 'body' => $decodedBody, 'raw' => $response];
}

$baseUrl = 'http://localhost:8000';

// Test basic connectivity
echo "=== Testing Basic Connectivity ===\n";
$basic = debugRequest('GET', $baseUrl);
echo "Basic connectivity result: " . ($basic ? 'OK' : 'FAILED') . "\n";

// Test the simple test route first
echo "\n=== Testing Simple Routes ===\n";
$testGet = debugRequest('GET', "$baseUrl/test");
$testPost = debugRequest('POST', "$baseUrl/test", ['test' => 'data']);

// Only proceed with auth tests if basic routing works
if ($testGet && $testGet['code'] == 200) {
    echo "\n=== Basic routing works, testing auth routes ===\n";
    
    // Test register
    $registerResult = debugRequest('POST', "$baseUrl/auth/register", [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);
    
    // Test login
    $loginResult = debugRequest('POST', "$baseUrl/auth/login", [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);
    
} else {
    echo "\n=== Basic routing failed - check your Flight setup ===\n";
    echo "Make sure:\n";
    echo "1. Flight PHP is properly installed\n";
    echo "2. Your web server is running on port 8000\n";
    echo "3. The document root points to your backend folder\n";
    echo "4. mod_rewrite is enabled (for Apache)\n";
}
?>