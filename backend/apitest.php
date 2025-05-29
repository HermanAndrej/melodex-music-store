<?php
// Minimal test to isolate issues
echo "=== Minimal API Test ===\n";

// Test 1: Basic server response
echo "1. Testing basic server response...\n";
$response = @file_get_contents('http://localhost:8000');
if ($response === false) {
    $error = error_get_last();
    echo "❌ Server not responding: " . $error['message'] . "\n";
    echo "Make sure you're running: php -S localhost:8000\n";
    exit(1);
} else {
    echo "✅ Server is responding\n";
}

// Test 2: Check if it's actually PHP
echo "\n2. Testing PHP execution...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if (strpos($response, 'X-Powered-By: PHP') !== false) {
    echo "✅ PHP is running\n";
} else {
    echo "⚠️  PHP header not detected\n";
}

// Test 3: Direct auth route test
echo "\n3. Testing auth route directly...\n";
$postData = json_encode([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123'
]);

$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => $postData
    ]
]);

$result = @file_get_contents('http://localhost:8000/auth/register', false, $context);
if ($result === false) {
    echo "❌ Auth route failed\n";
    $headers = get_headers('http://localhost:8000/auth/register');
    echo "Headers: " . print_r($headers, true) . "\n";
} else {
    echo "✅ Auth route responded: $result\n";
}

echo "\n=== Test Complete ===\n";
?>