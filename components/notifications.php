<?php
// Get current user ID
$user_id = getCurrentUserId();

// Get unread notifications count
$unread_count = countUnreadNotifications($user_id);

// Get latest notifications
$notifications = getNotifications($user_id, 5);
?>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <?php if ($unread_count > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unread_count > 99 ? '99+' : $unread_count ?>
                <span class="visually-hidden">unread notifications</span>
            </span>
        <?php endif; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-end notification-dropdown">
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifikasi</span>
            <?php if ($unread_count > 0): ?>
                <a href="index.php?page=notifications/mark-all-read" class="text-decoration-none small">Tandai semua dibaca</a>
            <?php endif; ?>
        </div>
        
        <div class="notification-list">
            <?php if (empty($notifications)): ?>
                <div class="dropdown-item text-center text-muted">
                    <i class="fas fa-info-circle me-2"></i> Tidak ada notifikasi
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <a href="index.php?page=notifications/read&id=<?= $notification['id'] ?>&redirect=<?= urlencode($notification['link'] ?: 'index.php?page=notifications') ?>" 
                       class="dropdown-item notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                        <div class="d-flex">
                            <div class="notification-icon bg-<?= $notification['type'] ?> text-white">
                                <?php if ($notification['type'] === 'info'): ?>
                                    <i class="fas fa-info"></i>
                                <?php elseif ($notification['type'] === 'success'): ?>
                                    <i class="fas fa-check"></i>
                                <?php elseif ($notification['type'] === 'warning'): ?>
                                    <i class="fas fa-exclamation"></i>
                                <?php elseif ($notification['type'] === 'danger'): ?>
                                    <i class="fas fa-times"></i>
                                <?php endif; ?>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title"><?= $notification['title'] ?></div>
                                <div class="notification-message"><?= $notification['message'] ?></div>
                                <div class="notification-time">
                                    <small class="text-muted"><?= timeAgo($notification['created_at']) ?></small>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="dropdown-footer text-center">
            <a href="index.php?page=notifications" class="text-decoration-none">Lihat semua notifikasi</a>
        </div>
    </div>
</li>
