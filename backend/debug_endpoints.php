<?php
// === DEBUGGING HELPER SCRIPT FOR PRODUCTS & ORDERS ===

function debugEndpoint($method, $url, $data = null, $token = null) {
    echo "\n=== Testing $method $url ===\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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

// Base URL
$baseUrl = 'http://localhost:8000';

// === LOGIN FIRST ===
$loginResult = debugEndpoint('POST', "$baseUrl/auth/login", [
    'email' => 'test@example.com',
    'password' => 'password123'
]);

if (!isset($loginResult['data']['token'])) {
    exit("❌ Login failed. Cannot proceed.\n");
}

$token = $loginResult['data']['token'];
$user = $loginResult['data']['user'];
$userId = $user['UserID'];

// === CREATE A PRODUCT ===
$productData = [
    'Name' => 'Test Product ' . rand(100, 999),
    'Price' => 19.99,
    'Stock' => 50,
    'CategoryID' => 1,
    'Brand' => 'TestBrand',
    'Description' => 'This is a test product',
    'ImageURL' => 'https://example.com/image.jpg'
];
$createdProduct = debugEndpoint('POST', "$baseUrl/api/products", $productData, $token);

// === FETCH ALL PRODUCTS ===
$products = debugEndpoint('GET', "$baseUrl/api/products", null, $token);
$firstProduct = $products[0] ?? null;

if (!$firstProduct) {
    exit("❌ No product available to create an order.\n");
}

// === CREATE AN ORDER ===
$orderData = [
    'UserID' => $userId,
    'items' => [
        [
            'ProductID' => $firstProduct['ProductID'],
            'Quantity' => 2
        ]
    ]
];
$createdOrder = debugEndpoint('POST', "$baseUrl/api/orders", $orderData, $token);

// === FETCH ALL ORDERS ===
$orders = debugEndpoint('GET', "$baseUrl/api/orders", null, $token);

?>
