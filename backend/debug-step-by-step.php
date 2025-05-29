<?php
// debug-step-by-step.php - Save this in your backend folder
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h3>Debugging Index.php Step by Step</h3>";

try {
    echo "1. Loading autoload...<br>";
    require __DIR__ . '/../vendor/autoload.php';
    echo "✓ Autoload OK<br><br>";
} catch (Exception $e) {
    echo "✗ Autoload failed: " . $e->getMessage() . "<br>";
    exit;
}

try {
    echo "2. Loading .env...<br>";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✓ .env loaded<br><br>";
} catch (Exception $e) {
    echo "✗ .env failed: " . $e->getMessage() . "<br>";
    echo "Continuing without .env...<br><br>";
}

try {
    echo "3. Loading config.php...<br>";
    if (file_exists('config.php')) {
        require 'config.php';
        echo "✓ Config loaded<br><br>";
    } else {
        echo "✗ config.php file not found<br><br>";
    }
} catch (Exception $e) {
    echo "✗ Config failed: " . $e->getMessage() . "<br><br>";
}

echo "4. Checking service files...<br>";
$serviceFiles = [
    'services/BaseService.php',
    'services/UserService.php',
    'services/ProductService.php',
    'services/CategoryService.php',
    'services/OrderService.php',
    'services/RatingService.php',
    'services/AuthService.php'
];

foreach ($serviceFiles as $file) {
    if (file_exists($file)) {
        try {
            require $file;
            echo "✓ $file loaded<br>";
        } catch (Exception $e) {
            echo "✗ $file failed: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "✗ $file not found<br>";
    }
}
echo "<br>";

echo "5. Testing Flight registration...<br>";
try {
    // Test registration with Flight::set()
    Flight::set('testService', function() {
        return 'test';
    });
    echo "✓ Flight service registration works<br><br>";
} catch (Exception $e) {
    echo "✗ Flight registration failed: " . $e->getMessage() . "<br><br>";
}


echo "6. Checking route files...<br>";
$routeFiles = [
    'routes/index.php',
    'routes/test_routes.php',
    'routes/auth_routes.php',
    'routes/product_routes.php',
    'routes/category_routes.php',
];

foreach ($routeFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists<br>";
        // Don't require route files yet as they might have Flight::route calls
    } else {
        echo "✗ $file not found<br>";
    }
}

echo "<br>7. All checks complete! If you see this, the basic setup is working.<br>";
echo "The issue might be in the route files or Flight::start().<br>";
?>