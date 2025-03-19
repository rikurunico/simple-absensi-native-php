<?php
/**
 * Authentication related functions
 * 
 * This file acts as a wrapper that includes the appropriate helper files
 * to avoid function redeclarations.
 */

// Include helpers.php if not already included
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../config/helpers.php';
}

// Add any additional authentication functions that aren't in helpers.php here
