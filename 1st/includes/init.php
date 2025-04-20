<?php
/**
 * Application Initialization
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Autoloader
spl_autoload_register(function ($class) {
    $file = INCLUDES_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load configuration
$config = require_once ROOT_PATH . '/config/database.php';

// Initialize database connection
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Set up session handling
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize core classes
require_once INCLUDES_PATH . '/Process.php';
require_once INCLUDES_PATH . '/Scheduler.php';
require_once INCLUDES_PATH . '/Queue.php';
require_once INCLUDES_PATH . '/Simulation.php'; 