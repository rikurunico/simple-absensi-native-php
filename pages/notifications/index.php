<?php
// Get current user ID
$user_id = getCurrentUserId();

// Get all notifications for the user
$notifications = getNotifications($user_id, 50, false);
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell me-2"></i> Notifikasi</h2>
                <div>
                    <?php if (!empty($notifications)): ?>
                        <a href="index.php?page=notifications/mark-all-read" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-check-double me-1"></i> Tandai semua dibaca
                        </a>
                        <a href="index.php?page=notifications/delete-all" class="btn btn-sm btn-outline-danger" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus semua notifikasi?')">
                            <i class="fas fa-trash me-1"></i> Hapus semua
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada notifikasi</h5>
                            <p class="text-muted">Anda akan menerima notifikasi untuk aktivitas penting</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="list-group-item list-group-item-action <?= $notification['is_read'] ? '' : 'bg-light' ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div class="d-flex">
                                            <div class="notification-icon bg-<?= $notification['type'] ?> text-white me-3">
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
                                            <div>
                                                <h5 class="mb-1"><?= $notification['title'] ?></h5>
                                                <p class="mb-1"><?= $notification['message'] ?></p>
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i> <?= formatDateTime($notification['created_at']) ?>
                                                    <?php if ($notification['is_read']): ?>
                                                        <span class="ms-2">
                                                            <i class="fas fa-check-double me-1"></i> Dibaca pada <?= formatDateTime($notification['read_at']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="d-flex">
                                            <?php if (!$notification['is_read']): ?>
                                                <a href="index.php?page=notifications/read&id=<?= $notification['id'] ?>&redirect=<?= urlencode('index.php?page=notifications') ?>" 
                                                   class="btn btn-sm btn-outline-primary me-2" title="Tandai dibaca">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($notification['link']): ?>
                                                <a href="<?= $notification['link'] ?>" class="btn btn-sm btn-outline-secondary me-2" title="Lihat detail">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="index.php?page=notifications/delete&id=<?= $notification['id'] ?>&redirect=<?= urlencode('index.php?page=notifications') ?>" 
                                               class="btn btn-sm btn-outline-danger" title="Hapus"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
