<?php
// Start output buffering for the entire application to prevent "headers already sent" errors
ob_start();

// Check if system is installed
if (!file_exists('.env') || !file_exists('installed.txt')) {
    header('Location: install.php');
    exit;
}

// Include required files
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'functions/auth_functions.php';
require_once 'functions/notification_functions.php';

// Simple routing
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Authentication check
$public_pages = ['login', 'logout', 'register'];
if (!isLoggedIn() && !in_array($page, $public_pages)) {
    redirect('index.php?page=login');
}

// Admin-only pages
$admin_pages = ['users', 'tarif', 'laporan'];
if (isLoggedIn() && !isAdmin() && in_array($page, $admin_pages)) {
    redirect('index.php?page=dashboard');
}

// Include header
include 'templates/header.php';

// Load requested page
$file_path = "pages/{$page}/{$action}.php";
if (file_exists($file_path)) {
    include $file_path;
} else {
    include 'pages/error/404.php';
}

// Include footer
include 'templates/footer.php';

// End and flush the output buffer
ob_end_flush();
