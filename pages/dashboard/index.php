<?php
// Get current user data
$user_id = getCurrentUserId();
$role = getCurrentUserRole();

// Get connection
$conn = getConnection();

// Get today's attendance for current user (if not admin)
$today_attendance = null;
if ($role !== 'admin') {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT * FROM absensi WHERE user_id = ? AND tanggal = ?");
    $stmt->execute([$user_id, $today]);
    $today_attendance = $stmt->fetch();
}

// Get current tarif
$stmt = $conn->prepare("SELECT * FROM tarif ORDER BY id DESC LIMIT 1");
$stmt->execute();
$tarif = $stmt->fetch();

// Get statistics for admin
if ($role === 'admin') {
    // Count total users
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $total_users = $stmt->fetch()['total'];
    
    // Count users present today
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND status = 'hadir'");
    $stmt->execute([$today]);
    $present_today = $stmt->fetch()['total'];
    
    // Count total attendance this month
    $month = date('m');
    $year = date('Y');
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM absensi WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
    $stmt->execute([$month, $year]);
    $total_attendance = $stmt->fetch()['total'];
    
    // Get recent attendance
    $stmt = $conn->prepare("
        SELECT a.*, u.nama_lengkap 
        FROM absensi a 
        JOIN users u ON a.user_id = u.id 
        ORDER BY a.tanggal DESC, a.jam_masuk DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_attendance = $stmt->fetchAll();
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">Dashboard</h2>
        
        <?php if ($role === 'admin'): ?>
        <!-- Admin Dashboard -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card card-dashboard bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Karyawan</h6>
                                <h2 class="mb-0"><?= $total_users ?></h2>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card card-dashboard bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Hadir Hari Ini</h6>
                                <h2 class="mb-0"><?= $present_today ?></h2>
                            </div>
                            <i class="fas fa-user-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card card-dashboard bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Absensi Bulan Ini</h6>
                                <h2 class="mb-0"><?= $total_attendance ?></h2>
                            </div>
                            <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card card-dashboard bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Tarif Normal / Jam</h6>
                                <h2 class="mb-0"><?= formatRupiah($tarif['tarif_normal']) ?></h2>
                            </div>
                            <i class="fas fa-money-bill fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Absensi Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                        <th>Status</th>
                                        <th>Durasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_attendance)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data absensi</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_attendance as $attendance): ?>
                                            <tr>
                                                <td><?= formatDate($attendance['tanggal']) ?></td>
                                                <td><?= $attendance['nama_lengkap'] ?></td>
                                                <td><?= $attendance['jam_masuk'] ? formatTime($attendance['jam_masuk']) : '-' ?></td>
                                                <td><?= $attendance['jam_keluar'] ? formatTime($attendance['jam_keluar']) : '-' ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'hadir' => 'success',
                                                        'izin' => 'warning',
                                                        'sakit' => 'info',
                                                        'alpha' => 'danger'
                                                    ];
                                                    $status_text = ucfirst($attendance['status']);
                                                    ?>
                                                    <span class="badge bg-<?= $status_class[$attendance['status']] ?>"><?= $status_text ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($attendance['jam_masuk'] && $attendance['jam_keluar']) {
                                                        $masuk = strtotime($attendance['jam_masuk']);
                                                        $keluar = strtotime($attendance['jam_keluar']);
                                                        $diff_hours = round(($keluar - $masuk) / 3600, 2);
                                                        echo $diff_hours . ' jam';
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="index.php?page=absensi" class="btn btn-primary">
                                <i class="fas fa-list"></i> Lihat Semua Absensi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Employee Dashboard -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card absensi-card">
                    <div class="absensi-header">
                        <h5 class="mb-0">Absensi Hari Ini</h5>
                        <div class="time-display" id="live-clock">--:--:--</div>
                        <div class="date-display" id="live-date">Loading...</div>
                    </div>
                    <div class="absensi-body">
                        <?php if ($today_attendance): ?>
                            <div class="row text-center">
                                <div class="col-md-6 mb-3">
                                    <h6>Jam Masuk</h6>
                                    <div class="display-6"><?= $today_attendance['jam_masuk'] ? formatTime($today_attendance['jam_masuk']) : '-' ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6>Jam Keluar</h6>
                                    <div class="display-6"><?= $today_attendance['jam_keluar'] ? formatTime($today_attendance['jam_keluar']) : '-' ?></div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <?php if (!$today_attendance['jam_masuk']): ?>
                                    <a href="index.php?page=absensi&action=checkin" class="btn btn-primary btn-absensi">
                                        <i class="fas fa-sign-in-alt me-2"></i> Absen Masuk
                                    </a>
                                <?php elseif (!$today_attendance['jam_keluar']): ?>
                                    <a href="index.php?page=absensi&action=checkout" class="btn btn-success btn-absensi">
                                        <i class="fas fa-sign-out-alt me-2"></i> Absen Keluar
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-absensi" disabled>
                                        <i class="fas fa-check-circle me-2"></i> Absensi Selesai
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center mb-4">
                                <p>Anda belum melakukan absensi hari ini</p>
                            </div>
                            <div class="d-grid">
                                <a href="index.php?page=absensi&action=checkin" class="btn btn-primary btn-absensi">
                                    <i class="fas fa-sign-in-alt me-2"></i> Absen Masuk
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Tarif</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Tarif Normal</h6>
                                        <h3 class="mb-0"><?= formatRupiah($tarif['tarif_normal']) ?></h3>
                                        <small class="text-muted">per jam</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Tarif Lembur</h6>
                                        <h3 class="mb-0"><?= formatRupiah($tarif['tarif_lembur']) ?></h3>
                                        <small class="text-muted">per jam</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i> Jam kerja normal adalah <?= $tarif['jam_normal'] ?> jam per hari. Kelebihan jam kerja akan dihitung sebagai lembur.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Salary Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Gaji Terbaru</h5>
            </div>
            <div class="card-body">
                <?php
                // Get recent salary information
                $stmt = $conn->prepare("
                    SELECT * FROM gaji 
                    WHERE user_id = ? 
                    ORDER BY tahun DESC, bulan DESC 
                    LIMIT 3
                ");
                $stmt->execute([$user_id]);
                $recent_salary = $stmt->fetchAll();
                ?>
                
                <?php if (empty($recent_salary)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Belum ada data gaji
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Jam Normal</th>
                                    <th>Jam Lembur</th>
                                    <th>Total Gaji</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_salary as $salary): ?>
                                    <tr>
                                        <td><?= date('F Y', mktime(0, 0, 0, $salary['bulan'], 1, $salary['tahun'])) ?></td>
                                        <td><?= $salary['total_jam_normal'] ?> jam</td>
                                        <td><?= $salary['total_jam_lembur'] ?> jam</td>
                                        <td><?= formatRupiah($salary['total_gaji']) ?></td>
                                        <td>
                                            <?php if ($salary['status'] === 'draft'): ?>
                                                <span class="badge bg-warning">Draft</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Final</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="index.php?page=gaji&action=detail&id=<?= $salary['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <div class="text-end mt-3">
                    <a href="index.php?page=gaji" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lihat Semua Gaji
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
