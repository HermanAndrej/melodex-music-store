<?php
require 'vendor/autoload.php';
require 'config.php';

use flight\Engine;

$app = new Engine();

// Error handling
$app->map('error', function(Exception $ex) {
    $app->json([
        'error' => true,
        'message' => $ex->getMessage()
    ], 500);
});

// CORS middleware
$app->before('start', function() use ($app) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    if ($app->request()->method == 'OPTIONS') {
        $app->stop();
    }
});

// Initialize services
$userService = new UserService($userDao);
$productService = new ProductService($productDao);
$categoryService = new CategoryService($categoryDao);
$orderService = new OrderService($orderDao, $productService);
$ratingService = new RatingService($ratingDao, $productService);

// Authentication middleware
$auth = function() use ($app, $userService) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        $app->json(['error' => 'Unauthorized'], 401);
        $app->stop();
    }
    
    try {
        $decoded = JWT::decode($token, JWT_SECRET, ['HS256']);
        $user = $userService->getById($decoded->userId);
        if (!$user) {
            throw new Exception('User not found');
        }
        $app->set('user', $user);
    } catch (Exception $e) {
        $app->json(['error' => 'Invalid token'], 401);
        $app->stop();
    }
};

// Load routes
require 'routes/index.php';

$app->start(); 