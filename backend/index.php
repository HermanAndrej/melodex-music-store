<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Flight\Engine;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Load config
    require 'config.php';

    // Create Flight engine instance
    $flight = new Engine();

    // Load all service files
    foreach (glob(__DIR__ . '/services/*.php') as $file) {
        require_once $file;
    }

    // Register core services (if classes exist)
    if (class_exists('UserService')) {
        $flight->register('userService', 'UserService');
    }
    if (class_exists('ProductService')) {
        $flight->register('productService', 'ProductService');
    }
    if (class_exists('CategoryService')) {
        $flight->register('categoryService', 'CategoryService');
    }
    if (class_exists('AuthService')) {
    $flight->set('authService', new AuthService());
    }

    // Register composite services
    if (class_exists('OrderService')) {
        $flight->set('orderService', new OrderService(
            $flight->get('productService'),
            $flight->get('userService')
        ));
    }
    if (class_exists('RatingService')) {
        $flight->set('ratingService', new RatingService(
            $flight->get('productService'),
            $flight->get('userService')
        ));
    }

    // CORS middleware
    $flight->before('start', function () use ($flight) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($flight->request()->method === 'OPTIONS') {
            $flight->halt(200);
        }
    });

    // Auth middleware
    $flight->before('start', function () use ($flight) {
        $url = $flight->request()->url;

        // Skip auth for login/register and test/debug routes
        if (
            str_starts_with($url, '/auth/login') ||
            str_starts_with($url, '/auth/register') ||
            str_starts_with($url, '/test') ||
            str_starts_with($url, '/debug')
        ) {
            return;
        }

        try {
            $authHeader = $flight->request()->getHeader("Authorization") 
                ?: $flight->request()->getHeader("Authentication");

            if (!$authHeader) {
                $flight->halt(401, json_encode(['success' => false, 'message' => 'Missing authorization header']));
            }

            $token = str_starts_with($authHeader, 'Bearer ') ? substr($authHeader, 7) : $authHeader;

            if (!$token) {
                $flight->halt(401, json_encode(['success' => false, 'message' => 'Invalid authorization header format']));
            }

            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $flight->set('user', $decoded->user);
            $flight->set('jwt_token', $token);

        } catch (\Exception $e) {
            $flight->halt(401, json_encode(['success' => false, 'message' => 'Invalid token: ' . $e->getMessage()]));
        }
    });

    // ✅ Load all route modules from routes/index.php (just once)
    $loadRoutes = require __DIR__ . '/routes/index.php';
    $loadRoutes($flight);

    // Start the app
    $flight->start();

} catch (Exception $e) {
    error_log("Fatal error in index.php: " . $e->getMessage());
    echo "Fatal error: " . $e->getMessage();
    echo "\nStack trace: " . $e->getTraceAsString();
}
