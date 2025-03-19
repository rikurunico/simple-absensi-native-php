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

// Process check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        if ($attendance && $attendance['jam_masuk']) {
            throw new Exception('Anda sudah melakukan absen masuk hari ini');
        }
        
        if ($attendance) {
            // Update existing attendance
            $stmt = $conn->prepare("
                UPDATE absensi 
                SET jam_masuk = ?, status = 'hadir', updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$now, $attendance['id']]);
        } else {
            // Create new attendance
            $stmt = $conn->prepare("
                INSERT INTO absensi (user_id, tanggal, jam_masuk, status) 
                VALUES (?, ?, ?, 'hadir')
            ");
            $stmt->execute([$user_id, $today, $now]);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set flash message
        $_SESSION['flash_message'] = 'Absen masuk berhasil pada ' . formatTime($now);
        $_SESSION['flash_type'] = 'success';
        
        // Redirect to dashboard - using a safer approach
        ob_end_clean(); // Clear any output before redirect
        redirect('index.php?page=dashboard');
        exit; // Ensure script stops here after redirect
    } catch (PDOException $e) {
        // Rollback transaction
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        // Set error message
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    } catch (Exception $e) {
        // Rollback transaction
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
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Absen Masuk</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="text-center mb-4">
                    <div class="time-display" id="live-clock">--:--:--</div>
                    <div class="date-display" id="live-date">Loading...</div>
                </div>
                
                <?php if ($attendance && $attendance['jam_masuk']): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Anda sudah melakukan absen masuk hari ini pada pukul <?= formatTime($attendance['jam_masuk']) ?>
                    </div>
                    
                    <div class="d-grid">
                        <a href="index.php?page=dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Silakan klik tombol di bawah untuk melakukan absen masuk
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Absen Masuk Sekarang
                            </button>
                            
                            <a href="index.php?page=dashboard" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> Batal
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
