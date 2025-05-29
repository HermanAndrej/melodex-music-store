<?php

return function($flight) {
    // Define all route module filenames
    $routeFiles = [
        'user_routes.php',
        'product_routes.php',
        'category_routes.php',
        'order_routes.php',
        'rating_routes.php',
        'auth_routes.php',
        'test_routes.php'
    ];

    // Load each route module
    foreach ($routeFiles as $routeFile) {
        $filePath = __DIR__ . '/' . $routeFile;

        if (!file_exists($filePath)) {
            error_log("⚠️ Route file not found: $filePath");
            continue;
        }

        try {
            $routeFunction = require $filePath;

            if (is_callable($routeFunction)) {
                $routeFunction($flight);
            } else {
                error_log("⚠️ Route file $routeFile does not return a callable function");
            }
        } catch (Exception $e) {
            error_log("❌ Error loading route file $routeFile: " . $e->getMessage());
        }
    }
};
