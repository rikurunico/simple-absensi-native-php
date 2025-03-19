<?php
// Start output buffering
ob_start();

// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Get tarif
$stmt = $conn->prepare("SELECT * FROM tarif ORDER BY id DESC LIMIT 1");
$stmt->execute();
$tarif = $stmt->fetch();

// Get month and year from form
$month = $_POST['month'] ?? date('m');
$year = $_POST['year'] ?? date('Y');
$user_id = $_POST['user_id'] ?? null;

// Get users
$stmt = $conn->prepare("SELECT id, nama_lengkap FROM users WHERE role = 'karyawan' ORDER BY nama_lengkap");
$stmt->execute();
$users = $stmt->fetchAll();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculate'])) {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Get selected users
        $selected_users = $user_id ? [$user_id] : array_column($users, 'id');
        
        // Loop through users
        foreach ($selected_users as $uid) {
            // Check if salary already exists for this month/year/user
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM gaji WHERE user_id = ? AND bulan = ? AND tahun = ?");
            $stmt->execute([$uid, $month, $year]);
            $exists = $stmt->fetch()['count'] > 0;
            
            if ($exists) {
                // Skip if already exists
                continue;
            }
            
            // Calculate total hours and salary
            $stmt = $conn->prepare("
                SELECT * FROM absensi 
                WHERE user_id = ? 
                  AND MONTH(tanggal) = ? 
                  AND YEAR(tanggal) = ?
                  AND jam_masuk IS NOT NULL 
                  AND jam_keluar IS NOT NULL
            ");
            $stmt->execute([$uid, $month, $year]);
            $attendances = $stmt->fetchAll();
            
            $total_normal_hours = 0;
            $total_overtime_hours = 0;
            $total_salary = 0;
            
            // Set period dates for the month
            $period_start = date('Y-m-01', strtotime("$year-$month-01"));
            $period_end = date('Y-m-t', strtotime("$year-$month-01"));
            
            foreach ($attendances as $attendance) {
                // Calculate hours worked
                $jam_masuk = new DateTime($attendance['jam_masuk']);
                $jam_keluar = new DateTime($attendance['jam_keluar']);
                $interval = $jam_masuk->diff($jam_keluar);
                $hours_worked = $interval->h + ($interval->i / 60);
                
                // Calculate normal and overtime hours
                $normal_hours = min($hours_worked, $tarif['jam_normal']);
                $overtime_hours = max(0, $hours_worked - $tarif['jam_normal']);
                
                $total_normal_hours += $normal_hours;
                $total_overtime_hours += $overtime_hours;
                $total_salary += ($normal_hours * $tarif['tarif_normal']) + ($overtime_hours * $tarif['tarif_lembur']);
            }
            
            // Create new salary record
            $stmt = $conn->prepare("
                INSERT INTO gaji (user_id, bulan, tahun, total_jam_normal, total_jam_lembur, total_gaji, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'draft')
            ");
            $stmt->execute([
                $uid,
                $month,
                $year,
                $total_normal_hours,
                $total_overtime_hours,
                $total_salary
            ]);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set flash message
        $_SESSION['flash_message'] = 'Perhitungan gaji berhasil dilakukan';
        $_SESSION['flash_type'] = 'success';
        
        // Redirect to gaji page
        ob_end_clean(); // Clear any output buffer before redirect
        redirect('index.php?page=gaji');
        exit;
    } catch (PDOException $e) {
        // Rollback transaction
        $conn->rollBack();
        
        // Set error message
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">Hitung Gaji</h2>
        
        <div class="card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="month" class="form-label">Bulan</label>
                            <select class="form-select" id="month" name="month" required>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= sprintf('%02d', $i) ?>" <?= $month == sprintf('%02d', $i) ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="year" class="form-label">Tahun</label>
                            <select class="form-select" id="year" name="year" required>
                                <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                                    <option value="<?= $i ?>" <?= $year == $i ? 'selected' : '' ?>>
                                        <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="user_id" class="form-label">Karyawan</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">Semua Karyawan</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $user_id == $user['id'] ? 'selected' : '' ?>>
                                        <?= $user['nama_lengkap'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Tarif Normal</h5>
                                    <h3><?= formatRupiah($tarif['tarif_normal']) ?> <small class="text-muted">per jam</small></h3>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Tarif Lembur</h5>
                                    <h3><?= formatRupiah($tarif['tarif_lembur']) ?> <small class="text-muted">per jam</small></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Perhitungan gaji akan dilakukan berdasarkan data absensi pada bulan dan tahun yang dipilih. Jam kerja normal adalah <?= $tarif['jam_normal'] ?> jam per hari. Kelebihan jam kerja akan dihitung sebagai lembur.
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" name="calculate" class="btn btn-primary">
                            <i class="fas fa-calculator me-2"></i> Hitung Gaji
                        </button>
                        
                        <a href="index.php?page=gaji" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
