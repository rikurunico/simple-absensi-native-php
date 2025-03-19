<?php
// Get connection
$conn = getConnection();

// Get user role
$role = getCurrentUserRole();
$user_id = getCurrentUserId();

// Get filter parameters
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$filter_user_id = $_GET['user_id'] ?? ($role === 'admin' ? null : $user_id);

// Get users for filter (admin only)
$users = [];
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT id, nama_lengkap FROM users ORDER BY nama_lengkap");
    $stmt->execute();
    $users = $stmt->fetchAll();
}

// Build query
$query = "
    SELECT a.*, u.nama_lengkap 
    FROM absensi a 
    JOIN users u ON a.user_id = u.id 
    WHERE MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
";
$params = [$month, $year];

if ($filter_user_id) {
    $query .= " AND a.user_id = ?";
    $params[] = $filter_user_id;
}

$query .= " ORDER BY a.tanggal DESC, a.jam_masuk DESC";

// Execute query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$attendances = $stmt->fetchAll();

// Calculate statistics
$total_days = 0;
$total_hours = 0;
$total_overtime = 0;

// Get tarif
$stmt = $conn->prepare("SELECT * FROM tarif ORDER BY id DESC LIMIT 1");
$stmt->execute();
$tarif = $stmt->fetch();
$jam_normal = $tarif['jam_normal'];

foreach ($attendances as $attendance) {
    if ($attendance['status'] === 'hadir' && $attendance['jam_masuk'] && $attendance['jam_keluar']) {
        $total_days++;
        
        $masuk = strtotime($attendance['jam_masuk']);
        $keluar = strtotime($attendance['jam_keluar']);
        $diff_hours = round(($keluar - $masuk) / 3600, 2);
        
        $total_hours += min($diff_hours, $jam_normal);
        $total_overtime += max(0, $diff_hours - $jam_normal);
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Data Absensi</h2>
            
            <?php if ($role !== 'admin'): ?>
            <a href="index.php?page=absensi&action=checkin" class="btn btn-primary">
                <i class="fas fa-clock"></i> Absen Sekarang
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="page" value="absensi">
                    
                    <div class="col-md-4">
                        <label for="month" class="form-label">Bulan</label>
                        <select class="form-select" id="month" name="month">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= sprintf('%02d', $i) ?>" <?= $month == sprintf('%02d', $i) ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="year" class="form-label">Tahun</label>
                        <select class="form-select" id="year" name="year">
                            <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                                <option value="<?= $i ?>" <?= $year == $i ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <?php if ($role === 'admin'): ?>
                    <div class="col-md-4">
                        <label for="user_id" class="form-label">Karyawan</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">Semua Karyawan</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $filter_user_id == $user['id'] ? 'selected' : '' ?>>
                                    <?= $user['nama_lengkap'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        
                        <?php if ($role === 'admin'): ?>
                        <a href="index.php?page=absensi&action=create" class="btn btn-success ms-2">
                            <i class="fas fa-plus"></i> Tambah Absensi
                        </a>
                        <?php endif; ?>
                        
                        <button type="button" class="btn btn-outline-secondary ms-2 btn-print">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Hari Kerja</h5>
                        <h2 class="mb-0"><?= $total_days ?> hari</h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Jam Kerja</h5>
                        <h2 class="mb-0"><?= number_format($total_hours, 2) ?> jam</h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Jam Lembur</h5>
                        <h2 class="mb-0"><?= number_format($total_overtime, 2) ?> jam</h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Attendance Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <?php if ($role === 'admin' && !$filter_user_id): ?>
                                <th>Nama</th>
                                <?php endif; ?>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                                <th>Durasi</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendances)): ?>
                                <tr>
                                    <td colspan="<?= ($role === 'admin' && !$filter_user_id) ? 8 : 7 ?>" class="text-center">
                                        Tidak ada data absensi
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attendances as $attendance): ?>
                                    <tr>
                                        <td><?= formatDate($attendance['tanggal']) ?></td>
                                        <?php if ($role === 'admin' && !$filter_user_id): ?>
                                        <td><?= $attendance['nama_lengkap'] ?></td>
                                        <?php endif; ?>
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
                                                
                                                $normal_hours = min($diff_hours, $jam_normal);
                                                $overtime_hours = max(0, $diff_hours - $jam_normal);
                                                
                                                echo $normal_hours . ' jam';
                                                
                                                if ($overtime_hours > 0) {
                                                    echo ' <span class="text-success">(+' . $overtime_hours . ' jam lembur)</span>';
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?= $attendance['keterangan'] ?: '-' ?></td>
                                        <td>
                                            <?php if ($role === 'admin'): ?>
                                                <a href="index.php?page=absensi&action=edit&id=<?= $attendance['id'] ?>" class="btn btn-sm btn-warning btn-action">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?page=absensi&action=delete&id=<?= $attendance['id'] ?>" class="btn btn-sm btn-danger btn-action btn-delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="index.php?page=absensi&action=detail&id=<?= $attendance['id'] ?>" class="btn btn-sm btn-info btn-action">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
