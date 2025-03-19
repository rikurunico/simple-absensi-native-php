<?php
// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Skip lines without equals sign
        if (strpos($line, '=') === false) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes if present
        if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        }
        
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
    
    return true;
}

// Load environment variables
$rootPath = dirname(__DIR__);
$envLoaded = loadEnv($rootPath . '/.env');

// If .env was loaded successfully, use those values directly
// otherwise fallback to defaults
if ($envLoaded) {
    // Set timezone
    date_default_timezone_set($_ENV['TIMEZONE'] ?? 'Asia/Jakarta');

    // Database configuration
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'absensi_db');
    define('DB_USER', $_ENV['DB_USER'] ?? 'root');
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');

    // Application configuration
    define('APP_NAME', $_ENV['APP_NAME'] ?? 'Sistem Absensi dan Penggajian');
    define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
} else {
    // Default values if no .env file
    date_default_timezone_set('Asia/Jakarta');
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'absensi_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('APP_NAME', 'Sistem Absensi dan Penggajian');
    define('APP_URL', 'http://localhost');
}

// Include helper functions
require_once __DIR__ . '/helpers.php';

/**
 * Get database connection
 * 
 * @return PDO Database connection
 */
function getConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Start session
session_start();