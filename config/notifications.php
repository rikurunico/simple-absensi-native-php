<?php
/**
 * Notifications helper functions
 */

/**
 * Add a notification
 * 
 * @param int $user_id User ID
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type (info, success, warning, danger)
 * @param string $link Link to redirect when notification is clicked
 * @return bool True if notification was added, false otherwise
 */
function addNotification($user_id, $title, $message, $type = 'info', $link = '') {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, link) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $title, $message, $type, $link]);
        
        return true;
    } catch (Exception $e) {
        error_log('Error adding notification: ' . $e->getMessage());
        return false;
    }
}

/**
 * Add a notification for all users
 * 
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type (info, success, warning, danger)
 * @param string $link Link to redirect when notification is clicked
 * @return bool True if notifications were added, false otherwise
 */
function addNotificationForAll($title, $message, $type = 'info', $link = '') {
    try {
        $conn = getConnection();
        
        // Get all users
        $stmt = $conn->prepare("SELECT id FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        // Add notification for each user
        foreach ($users as $user) {
            addNotification($user['id'], $title, $message, $type, $link);
        }
        
        return true;
    } catch (Exception $e) {
        error_log('Error adding notification for all users: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get notifications for a user
 * 
 * @param int $user_id User ID
 * @param int $limit Limit number of notifications
 * @param bool $unread_only Only get unread notifications
 * @return array Notifications
 */
function getNotifications($user_id, $limit = 10, $unread_only = false) {
    try {
        $conn = getConnection();
        
        $query = "
            SELECT * FROM notifications 
            WHERE user_id = ?
        ";
        
        if ($unread_only) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id, $limit]);
        
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log('Error getting notifications: ' . $e->getMessage());
        return [];
    }
}

/**
 * Count unread notifications for a user
 * 
 * @param int $user_id User ID
 * @return int Number of unread notifications
 */
function countUnreadNotifications($user_id) {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$user_id]);
        
        return $stmt->fetch()['count'];
    } catch (Exception $e) {
        error_log('Error counting unread notifications: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Mark notification as read
 * 
 * @param int $notification_id Notification ID
 * @param int $user_id User ID (for security check)
 * @return bool True if notification was marked as read, false otherwise
 */
function markNotificationAsRead($notification_id, $user_id) {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = 1, read_at = NOW() 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notification_id, $user_id]);
        
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log('Error marking notification as read: ' . $e->getMessage());
        return false;
    }
}

/**
 * Mark all notifications as read for a user
 * 
 * @param int $user_id User ID
 * @return bool True if notifications were marked as read, false otherwise
 */
function markAllNotificationsAsRead($user_id) {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = 1, read_at = NOW() 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$user_id]);
        
        return true;
    } catch (Exception $e) {
        error_log('Error marking all notifications as read: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete a notification
 * 
 * @param int $notification_id Notification ID
 * @param int $user_id User ID (for security check)
 * @return bool True if notification was deleted, false otherwise
 */
function deleteNotification($notification_id, $user_id) {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            DELETE FROM notifications 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notification_id, $user_id]);
        
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log('Error deleting notification: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete all notifications for a user
 * 
 * @param int $user_id User ID
 * @return bool True if notifications were deleted, false otherwise
 */
function deleteAllNotifications($user_id) {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            DELETE FROM notifications 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        
        return true;
    } catch (Exception $e) {
        error_log('Error deleting all notifications: ' . $e->getMessage());
        return false;
    }
}
?>
