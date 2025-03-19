<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

// Get current user data
$user_id = getCurrentUserId();
$today = date('Y-m-d');
$now = date('H:i:s');

// Get connection
$conn = getConnection();

// Check if already checked in today
$stmt = $conn->prepare("SELECT * FROM absensi WHERE user_id = ? AND tanggal = ?");
$stmt->execute([$user_id, $today]);
$attendance = $stmt->fetch();

// Process check-out
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$attendance) {
            throw new Exception('Anda belum melakukan absen masuk hari ini');
        }
        
        if (!$attendance['jam_masuk']) {
            throw new Exception('Anda belum melakukan absen masuk hari ini');
        }
        
        if ($attendance['jam_keluar']) {
            throw new Exception('Anda sudah melakukan absen keluar hari ini');
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Update attendance
        $stmt = $conn->prepare("
            UPDATE absensi 
            SET jam_keluar = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$now, $attendance['id']]);
        
        // Commit transaction
        $conn->commit();
        
        // Set flash message
        $_SESSION['flash_message'] = 'Absen keluar berhasil pada ' . formatTime($now);
        $_SESSION['flash_type'] = 'success';
        
        // Redirect to dashboard
        ob_end_clean(); // Clear any output before redirect
        redirect('index.php?page=dashboard');
        exit;
    } catch (Exception $e) {
        // Rollback transaction if started
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        // Set error message
        $error = $e->getMessage();
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Absen Keluar</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="text-center mb-4">
                    <div class="time-display" id="live-clock">--:--:--</div>
                    <div class="date-display" id="live-date">Loading...</div>
                </div>
                
                <?php if (!$attendance || !$attendance['jam_masuk']): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Anda belum melakukan absen masuk hari ini
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="index.php?page=absensi&action=checkin" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> Absen Masuk Sekarang
                        </a>
                        
                        <a href="index.php?page=dashboard" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
                        </a>
                    </div>
                <?php elseif ($attendance['jam_keluar']): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Anda sudah melakukan absen keluar hari ini pada pukul <?= formatTime($attendance['jam_keluar']) ?>
                    </div>
                    
                    <div class="d-grid">
                        <a href="index.php?page=dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="row text-center mb-4">
                            <div class="col-md-6">
                                <h6>Jam Masuk</h6>
                                <div class="display-6"><?= formatTime($attendance['jam_masuk']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <h6>Durasi Kerja</h6>
                                <div class="display-6" id="work-duration">--:--:--</div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Silakan klik tombol di bawah untuk melakukan absen keluar
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-sign-out-alt me-2"></i> Absen Keluar Sekarang
                            </button>
                            
                            <a href="index.php?page=dashboard" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> Batal
                            </a>
                        </div>
                    </form>
                    
                    <script>
                        // Calculate work duration
                        document.addEventListener('DOMContentLoaded', function() {
                            function updateWorkDuration() {
                                const startTime = new Date('<?= date('Y-m-d') ?> <?= $attendance['jam_masuk'] ?>');
                                const now = new Date();
                                
                                const diff = Math.floor((now - startTime) / 1000);
                                const hours = Math.floor(diff / 3600);
                                const minutes = Math.floor((diff % 3600) / 60);
                                const seconds = diff % 60;
                                
                                document.getElementById('work-duration').textContent = 
                                    String(hours).padStart(2, '0') + ':' + 
                                    String(minutes).padStart(2, '0') + ':' + 
                                    String(seconds).padStart(2, '0');
                            }
                            
                            // Update immediately and then every second
                            updateWorkDuration();
                            setInterval(updateWorkDuration, 1000);
                        });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
