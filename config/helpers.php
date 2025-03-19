<?php
/**
 * Helper functions for the application
 */

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    // If headers are already sent, use JavaScript for redirection
    if (headers_sent()) {
        echo "<script>location.href='$url';</script>";
        exit;
    }
    
    // Otherwise use standard header redirect
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user ID
 * 
 * @return int Current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 0;
}

/**
 * Get current user role
 * 
 * @return string|null User role or null if not logged in
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Format date to Indonesian format
 * 
 * @param string $date Date to format
 * @param bool $withDay Include day name
 * @return string Formatted date
 */
function formatDate($date, $withDay = false) {
    if (empty($date)) {
        return '-';
    }
    
    $timestamp = strtotime($date);
    $day = '';
    
    if ($withDay) {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $day = $days[date('w', $timestamp)] . ', ';
    }
    
    return $day . date('d F Y', $timestamp);
}

/**
 * Format date and time to Indonesian format
 * 
 * @param string $datetime Date and time to format
 * @return string Formatted date and time
 */
function formatDateTime($datetime) {
    if (empty($datetime)) {
        return '-';
    }
    
    $timestamp = strtotime($datetime);
    return date('d F Y H:i', $timestamp);
}

/**
 * Format time
 * 
 * @param string $time Time to format
 * @return string Formatted time
 */
function formatTime($time) {
    if (empty($time)) {
        return '-';
    }
    
    $timestamp = strtotime($time);
    return date('H:i', $timestamp);
}

/**
 * Format number to rupiah
 * 
 * @param float $number Number to format
 * @return string Formatted number
 */
function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Get current date
 * 
 * @return string Current date in Y-m-d format
 */
function getCurrentDate() {
    return date('Y-m-d');
}

/**
 * Get current time
 * 
 * @return string Current time in H:i:s format
 */
function getCurrentTime() {
    return date('H:i:s');
}

/**
 * Get current datetime
 * 
 * @return string Current datetime in Y-m-d H:i:s format
 */
function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

/**
 * Calculate duration between two times
 * 
 * @param string $start Start time
 * @param string $end End time
 * @return float Duration in hours
 */
function calculateDuration($start, $end) {
    if (empty($start) || empty($end)) {
        return 0;
    }
    
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    
    // Calculate duration in seconds
    $duration_seconds = $end_time - $start_time;
    
    // Convert to hours
    $duration_hours = $duration_seconds / 3600;
    
    return round($duration_hours, 2);
}

/**
 * Calculate salary based on hours worked and tariff
 * 
 * @param float $hours Hours worked
 * @param float $tariff Tariff per hour
 * @return float Salary
 */
function calculateSalary($hours, $tariff) {
    return $hours * $tariff;
}

/**
 * Generate random string
 * 
 * @param int $length Length of random string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * Check if date is weekend
 * 
 * @param string $date Date to check
 * @return bool True if date is weekend, false otherwise
 */
function isWeekend($date) {
    $day = date('w', strtotime($date));
    return ($day == 0 || $day == 6); // 0 = Sunday, 6 = Saturday
}

/**
 * Get month name
 * 
 * @param int $month Month number
 * @return string Month name
 */
function getMonthName($month) {
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
    
    return $months[$month] ?? '';
}

/**
 * Get day name
 * 
 * @param string $date Date to get day name from
 * @return string Day name
 */
function getDayName($date) {
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $day = date('w', strtotime($date));
    
    return $days[$day];
}

/**
 * Sanitize input
 * 
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate date format
 * 
 * @param string $date Date to validate
 * @param string $format Format to validate against
 * @return bool True if date is valid, false otherwise
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Set flash message
 * 
 * @param string $message Message to set
 * @param string $type Message type (success, danger, warning, info)
 * @return void
 */
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get flash message
 * 
 * @return array Flash message and type
 */
function getFlashMessage() {
    $message = $_SESSION['flash_message'] ?? '';
    $type = $_SESSION['flash_type'] ?? 'success';
    
    // Clear flash message
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    
    return [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Check if flash message exists
 * 
 * @return bool True if flash message exists, false otherwise
 */
function hasFlashMessage() {
    return isset($_SESSION['flash_message']) && !empty($_SESSION['flash_message']);
}
