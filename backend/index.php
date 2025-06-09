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
    error_log("Starting application...");

    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // ✅ Check if JWT_SECRET is defined
    if (empty($_ENV['JWT_SECRET'])) {
        throw new Exception("JWT_SECRET not set in .env file. Please define it.");
    }

    // Load config
    require 'config.php';

    // Create Flight engine instance
    $flight = new Engine();
    error_log("Flight engine created");

    // Load all DAO files
    foreach (glob(__DIR__ . '/dao/*.php') as $file) {
        require_once $file;
        error_log("Loaded DAO file: $file");
    }

    // Load all service files
    foreach (glob(__DIR__ . '/services/*.php') as $file) {
        require_once $file;
        error_log("Loaded service file: $file");
    }

    // Register DAOs
    if (class_exists('UserDao')) {
        $flight->set('userDao', new UserDao());
        error_log("Registered UserDao");
    }
    if (class_exists('ProductDao')) {
        $flight->set('productDao', new ProductDao());
        error_log("Registered ProductDao");
    }
    if (class_exists('CategoryDao')) {
        $flight->set('categoryDao', new CategoryDao());
        error_log("Registered CategoryDao");
    }
    if (class_exists('OrderDao')) {
        $flight->set('orderDao', new OrderDao());
        error_log("Registered OrderDao");
    }
    if (class_exists('RatingDao')) {
        $flight->set('ratingDao', new RatingDao());
        error_log("Registered RatingDao");
    }

    // Register core services
    if (class_exists('UserService')) {
        $flight->set('userService', new UserService($flight->get('userDao')));
        error_log("Registered UserService");
    }
    if (class_exists('ProductService')) {
        $flight->set('productService', new ProductService($flight->get('productDao')));
        error_log("Registered ProductService");
    }
    if (class_exists('CategoryService')) {
        $flight->set('categoryService', new CategoryService($flight->get('categoryDao')));
        error_log("Registered CategoryService");
    }
    if (class_exists('AuthService')) {
        $flight->set('authService', new AuthService());
        error_log("Registered AuthService");
    }

    // Register composite services
    if (class_exists('OrderService')) {
        $flight->set('orderService', new OrderService(
            $flight->get('orderDao'),
            $flight->get('productService')
        ));
        error_log("Registered OrderService");
    }
    if (class_exists('RatingService')) {
        $flight->set('ratingService', new RatingService(
            $flight->get('ratingDao'),
            $flight->get('productService')
        ));
        error_log("Registered RatingService");
    }

    // ✅ Load all route modules from routes/index.php
    error_log("Loading route modules...");
    $loadRoutes = require __DIR__ . '/routes/index.php';
    $loadRoutes($flight);
    error_log("Route modules loaded");

    // CORS handler - must be first
    $flight->before('start', function () use ($flight) {
        $allowedOrigins = [
            'http://127.0.0.1:5500',
            'http://localhost:5500',
            'http://127.0.0.1:3000',
            'http://localhost:3000'
        ];
        
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // 24 hours
        }

        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            if (in_array($origin, $allowedOrigins)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Max-Age: 86400'); // 24 hours
            }
            $flight->halt(200);
            exit;
        }
    });

    // Auth middleware
    $flight->before('start', function () use ($flight) {
        // Skip auth check for preflight requests
        if ($flight->request()->method === 'OPTIONS') {
            return;
        }

        $fullUrl = $flight->request()->url;
        $path = parse_url($fullUrl, PHP_URL_PATH);
        error_log("Auth Middleware: path is $path");

        $publicPaths = [
            '/api/auth/login',
            '/api/auth/register',
            '/api/test',
            '/api/debug',
            '/api/login',
            '/api/register',
        ];

        foreach ($publicPaths as $prefix) {
            if (str_starts_with($path, $prefix)) {
                error_log("Auth Middleware: Skipping auth for $path");
                return; // skip auth check
            }
        }

        // 🔒 Run JWT auth here
        try {
            $authHeader = $flight->request()->getHeader("Authorization")
                ?: $flight->request()->getHeader("Authentication");
            error_log("Auth Middleware: Authorization header is " . var_export($authHeader, true));

            if (!$authHeader) {
                $flight->halt(401, json_encode(['success' => false, 'message' => 'Missing authorization header']));
            }

            $token = str_starts_with($authHeader, 'Bearer ') ? substr($authHeader, 7) : $authHeader;

            if (!$token) {
                $flight->halt(401, json_encode(['success' => false, 'message' => 'Invalid authorization header format']));
            }

            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($_ENV['JWT_SECRET'], 'HS256'));
            $flight->set('user', $decoded->user);
            $flight->set('jwt_token', $token);

        } catch (\Exception $e) {
            $flight->halt(401, json_encode(['success' => false, 'message' => 'Invalid token: ' . $e->getMessage()]));
        }
    });

    // Start the app
    $flight->start();

} catch (Exception $e) {
    error_log("Fatal error in index.php: " . $e->getMessage());
    echo "Fatal error: " . $e->getMessage();
    echo "\nStack trace: " . $e->getTraceAsString();
}
