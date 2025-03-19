<?php
// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $nama_lengkap = $_POST['nama_lengkap'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'karyawan';
        
        // Validate data
        if (empty($nama_lengkap) || empty($username) || empty($password)) {
            throw new Exception('Semua field harus diisi');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Password dan konfirmasi password tidak sama');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Password minimal 6 karakter');
        }
        
        // Check if username already exists
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            throw new Exception('Username sudah digunakan');
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $conn->prepare("
            INSERT INTO users (nama_lengkap, username, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$nama_lengkap, $username, $hashed_password, $role]);
        
        // Commit transaction
        $conn->commit();
        
        // Set flash message
        $_SESSION['flash_message'] = 'Pengguna berhasil ditambahkan';
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
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Tambah Pengguna</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= $_POST['nama_lengkap'] ?? '' ?>" required>
                        <div class="invalid-feedback">
                            Nama lengkap harus diisi
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= $_POST['username'] ?? '' ?>" required>
                        <div class="invalid-feedback">
                            Username harus diisi
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">
                            Password harus diisi
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">
                            Konfirmasi password harus diisi
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="karyawan" <?= ($_POST['role'] ?? '') === 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                            <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <div class="invalid-feedback">
                            Role harus dipilih
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan
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
