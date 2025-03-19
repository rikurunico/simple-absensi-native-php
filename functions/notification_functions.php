<?php
/**
 * Notification related functions
 */

/**
 * Count unread notifications for the current user
 * 
 * @param int|null $user_id Optional user ID (defaults to current logged in user)
 * @return int Number of unread notifications
 */
function countUnreadNotifications($user_id = null) {
    // Use current user ID if no specific ID is provided
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    // Return 0 if no user ID is available
    if (empty($user_id)) {
        return 0;
    }
    
    try {
        // Use getConnection() instead of global $pdo
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM notifications 
            WHERE user_id = ? 
            AND is_read = 0
        ");
        
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error or handle it as appropriate
        error_log("Error counting notifications: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get unread notifications for the current user
 * 
 * @param int|null $user_id Optional user ID (defaults to current logged in user)
 * @param int $limit Maximum number of notifications to return
 * @return array Array of notification data
 */
function getUnreadNotifications($user_id = null, $limit = 5) {
    // Use current user ID if no specific ID is provided
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    // Return empty array if no user ID is available
    if (empty($user_id)) {
        return [];
    }
    
    try {
        // Use getConnection() instead of global $pdo
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            SELECT * 
            FROM notifications 
            WHERE user_id = ? 
            AND is_read = 0
            ORDER BY created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error or handle it as appropriate
        error_log("Error fetching notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Get notifications for the current user
 * 
 * @param int|null $user_id Optional user ID (defaults to current logged in user)
 * @param int $limit Maximum number of notifications to return
 * @return array Array of notification data
 */
function getNotifications($user_id = null, $limit = 5) {
    // Use current user ID if no specific ID is provided
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    // Return empty array if no user ID is available
    if (empty($user_id)) {
        return [];
    }
    
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            SELECT * 
            FROM notifications 
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error or handle it as appropriate
        error_log("Error fetching all notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Mark a notification as read
 * 
 * @param int $notification_id ID of the notification to mark as read
 * @param int|null $user_id Optional user ID for security verification
 * @return bool Success or failure
 */
function markNotificationAsRead($notification_id, $user_id = null) {
    // Use current user ID if no specific ID is provided
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    // Return false if no user ID is available
    if (empty($user_id)) {
        return false;
    }
    
    try {
        // Use getConnection() instead of global $pdo
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = ?
            AND user_id = ?
        ");
        
        $stmt->execute([$notification_id, $user_id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        // Log error or handle it as appropriate
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}
