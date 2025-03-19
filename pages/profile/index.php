<?php
// Get current user data
$user_id = getCurrentUserId();

// Get connection
$conn = getConnection();

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $nama_lengkap = $_POST['nama_lengkap'] ?? '';
        $username = $_POST['username'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate data
        if (empty($nama_lengkap) || empty($username)) {
            throw new Exception('Nama lengkap dan username harus diisi');
        }
        
        // Check if username already exists (except for current user)
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user_id]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            throw new Exception('Username sudah digunakan');
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Update user
        if (!empty($current_password) && !empty($new_password)) {
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception('Password saat ini tidak sesuai');
            }
            
            // Validate new password
            if (strlen($new_password) < 6) {
                throw new Exception('Password baru minimal 6 karakter');
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception('Password baru dan konfirmasi password tidak sama');
            }
            
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                UPDATE users 
                SET nama_lengkap = ?, username = ?, password = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$nama_lengkap, $username, $hashed_password, $user_id]);
        } else {
            // Without password change
            $stmt = $conn->prepare("
                UPDATE users 
                SET nama_lengkap = ?, username = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$nama_lengkap, $username, $user_id]);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Update session data
        $_SESSION['username'] = $username;
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        
        // Set flash message
        $_SESSION['flash_message'] = 'Profil berhasil diupdate';
        $_SESSION['flash_type'] = 'success';
        
        // Redirect to refresh page
        redirect('index.php?page=profile');
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
    <div class="col-md-8 mx-auto">
        <h2 class="mb-4">Profil Saya</h2>
        
        <div class="card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama_lengkap']) ?>&size=150&background=007bff&color=fff" class="profile-img" alt="Profile Image">
                        <h5><?= $user['nama_lengkap'] ?></h5>
                        <p class="text-muted">
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge bg-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-primary">Karyawan</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="col-md-8">
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= $_POST['nama_lengkap'] ?? $user['nama_lengkap'] ?>" required>
                                <div class="invalid-feedback">
                                    Nama lengkap harus diisi
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= $_POST['username'] ?? $user['username'] ?>" required>
                                <div class="invalid-feedback">
                                    Username harus diisi
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h5>Ubah Password</h5>
                            <p class="text-muted small">Kosongkan jika tidak ingin mengubah password</p>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
