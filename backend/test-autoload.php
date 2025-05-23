<?php
// test-autoload.php - Save this in your backend folder
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Current directory: " . __DIR__ . "<br>";
echo "Looking for autoload at: " . __DIR__ . '/../vendor/autoload.php' . "<br>";

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "Autoload file exists!<br>";
    try {
        require __DIR__ . '/../vendor/autoload.php';
        echo "Autoload loaded successfully!<br>";
        
        // Test if Flight is available
        if (class_exists('Flight')) {
            echo "Flight class is available!<br>";
        } else {
            echo "Flight class NOT available!<br>";
        }
        
    } catch (Exception $e) {
        echo "Error loading autoload: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Autoload file does NOT exist!<br>";
    echo "Files in parent directory:<br>";
    $parentDir = dirname(__DIR__);
    if (is_dir($parentDir)) {
        foreach (scandir($parentDir) as $file) {
            echo "- $file<br>";
        }
    }
}
?>