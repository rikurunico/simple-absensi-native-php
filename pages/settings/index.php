<?php
// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Load current settings
$settings = [];
$stmt = $conn->prepare("SELECT * FROM settings");
$stmt->execute();
$result = $stmt->fetchAll();

foreach ($result as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $app_name = $_POST['app_name'] ?? '';
        $company_name = $_POST['company_name'] ?? '';
        $company_address = $_POST['company_address'] ?? '';
        $company_phone = $_POST['company_phone'] ?? '';
        $company_email = $_POST['company_email'] ?? '';
        $work_start_time = $_POST['work_start_time'] ?? '08:00';
        $work_end_time = $_POST['work_end_time'] ?? '17:00';
        $allow_weekend = isset($_POST['allow_weekend']) ? 1 : 0;
        
        // Validate data
        if (empty($app_name) || empty($company_name)) {
            throw new Exception('Nama aplikasi dan nama perusahaan harus diisi');
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Update or insert settings
        $settings_to_update = [
            'app_name' => $app_name,
            'company_name' => $company_name,
            'company_address' => $company_address,
            'company_phone' => $company_phone,
            'company_email' => $company_email,
            'work_start_time' => $work_start_time,
            'work_end_time' => $work_end_time,
            'allow_weekend' => $allow_weekend
        ];
        
        foreach ($settings_to_update as $key => $value) {
            // Check if setting exists
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $count = $stmt->fetch()['count'];
            
            if ($count > 0) {
                // Update setting
                $stmt = $conn->prepare("
                    UPDATE settings 
                    SET setting_value = ?, updated_at = NOW() 
                    WHERE setting_key = ?
                ");
                $stmt->execute([$value, $key]);
            } else {
                // Insert setting
                $stmt = $conn->prepare("
                    INSERT INTO settings (setting_key, setting_value) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$key, $value]);
            }
        }
        
        // Update .env file
        $env_file = __DIR__ . '/../../.env';
        $env_content = file_get_contents($env_file);
        
        // Update APP_NAME
        $env_content = preg_replace('/APP_NAME=".*"/', 'APP_NAME="' . $app_name . '"', $env_content);
        
        // Write to .env file
        file_put_contents($env_file, $env_content);
        
        // Commit transaction
        $conn->commit();
        
        // Set flash message
        $_SESSION['flash_message'] = 'Pengaturan berhasil disimpan';
        $_SESSION['flash_type'] = 'success';
        
        // Redirect to refresh page
        redirect('index.php?page=settings');
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
    <div class="col-md-12">
        <h2 class="mb-4">Pengaturan Aplikasi</h2>
        
        <div class="card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Pengaturan Umum</h5>
                            
                            <div class="mb-3">
                                <label for="app_name" class="form-label">Nama Aplikasi</label>
                                <input type="text" class="form-control" id="app_name" name="app_name" value="<?= $_POST['app_name'] ?? $settings['app_name'] ?? APP_NAME ?>" required>
                                <div class="invalid-feedback">
                                    Nama aplikasi harus diisi
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Nama Perusahaan</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="<?= $_POST['company_name'] ?? $settings['company_name'] ?? '' ?>" required>
                                <div class="invalid-feedback">
                                    Nama perusahaan harus diisi
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="company_address" class="form-label">Alamat Perusahaan</label>
                                <textarea class="form-control" id="company_address" name="company_address" rows="3"><?= $_POST['company_address'] ?? $settings['company_address'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="company_phone" class="form-label">Telepon Perusahaan</label>
                                <input type="text" class="form-control" id="company_phone" name="company_phone" value="<?= $_POST['company_phone'] ?? $settings['company_phone'] ?? '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="company_email" class="form-label">Email Perusahaan</label>
                                <input type="email" class="form-control" id="company_email" name="company_email" value="<?= $_POST['company_email'] ?? $settings['company_email'] ?? '' ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Pengaturan Absensi</h5>
                            
                            <div class="mb-3">
                                <label for="work_start_time" class="form-label">Jam Mulai Kerja</label>
                                <input type="time" class="form-control" id="work_start_time" name="work_start_time" value="<?= $_POST['work_start_time'] ?? $settings['work_start_time'] ?? '08:00' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="work_end_time" class="form-label">Jam Selesai Kerja</label>
                                <input type="time" class="form-control" id="work_end_time" name="work_end_time" value="<?= $_POST['work_end_time'] ?? $settings['work_end_time'] ?? '17:00' ?>">
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="allow_weekend" name="allow_weekend" <?= (isset($_POST['allow_weekend']) || ($settings['allow_weekend'] ?? 0) == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="allow_weekend">Izinkan Absensi di Akhir Pekan</label>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Pengaturan ini akan mempengaruhi cara sistem menghitung jam kerja dan gaji.
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
