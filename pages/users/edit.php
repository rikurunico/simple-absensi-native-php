<?php
// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Get user ID
$id = $_GET['id'] ?? 0;

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Check if user exists
if (!$user) {
    $_SESSION['flash_message'] = 'Pengguna tidak ditemukan';
    $_SESSION['flash_type'] = 'danger';
    redirect('index.php?page=users');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $nama_lengkap = $_POST['nama_lengkap'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'karyawan';
        
        // Validate data
        if (empty($nama_lengkap) || empty($username)) {
            throw new Exception('Nama lengkap dan username harus diisi');
        }
        
        // Check if username already exists (except for current user)
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            throw new Exception('Username sudah digunakan');
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Update user
        if (!empty($password)) {
            // With password change
            if (strlen($password) < 6) {
                throw new Exception('Password minimal 6 karakter');
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                UPDATE users 
                SET nama_lengkap = ?, username = ?, password = ?, role = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$nama_lengkap, $username, $hashed_password, $role, $id]);
        } else {
            // Without password change
            $stmt = $conn->prepare("
                UPDATE users 
                SET nama_lengkap = ?, username = ?, role = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$nama_lengkap, $username, $role, $id]);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set flash message
        $_SESSION['flash_message'] = 'Pengguna berhasil diupdate';
        $_SESSION['flash_type'] = 'success';
        
        // Redirect to users page
        redirect('index.php?page=users');
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
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Edit Pengguna</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
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
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="karyawan" <?= ($_POST['role'] ?? $user['role']) === 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                            <option value="admin" <?= ($_POST['role'] ?? $user['role']) === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <div class="invalid-feedback">
                            Role harus dipilih
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i> Update
                        </button>
                        
                        <a href="index.php?page=users" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
