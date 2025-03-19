<?php
/**
 * Get PDO database connection
 * 
 * @return PDO
 */
function getConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $host = 'localhost';
        $dbname = 'absensi';
        $username = 'root';
        $password = '';
        
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $conn;
}

/**
 * Get current user ID from session
 * 
 * @return int|null
 */
function getCurrentUserId() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current user role from session
 * 
 * @return string|null
 */
function getCurrentUserRole() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Redirect to a URL
 * 
 * @param string $url
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Format date to Indonesian format
 * 
 * @param string $date
 * @return string
 */
function formatDate($date) {
    if (!$date) return '-';
    
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Format time to HH:MM format
 * 
 * @param string $time
 * @return string
 */
function formatTime($time) {
    if (!$time) return '-';
    
    $timestamp = strtotime($time);
    return date('H:i', $timestamp);
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has admin role
 * 
 * @return bool
 */
function isAdmin() {
    return getCurrentUserRole() === 'admin';
}
?>
