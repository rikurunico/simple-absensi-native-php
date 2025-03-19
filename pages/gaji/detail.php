<?php
// Get connection
$conn = getConnection();

// Get salary ID
$id = $_GET['id'] ?? 0;

// Get user role
$role = getCurrentUserRole();
$user_id = getCurrentUserId();

// Get salary data
$stmt = $conn->prepare("
    SELECT g.*, u.nama_lengkap 
    FROM gaji g 
    JOIN users u ON g.user_id = u.id 
    WHERE g.id = ?
");
$stmt->execute([$id]);
$salary = $stmt->fetch();

// Check if salary exists and user has permission
if (!$salary || ($role !== 'admin' && $salary['user_id'] != $user_id)) {
    $_SESSION['flash_message'] = 'Data gaji tidak ditemukan atau Anda tidak memiliki akses';
    $_SESSION['flash_type'] = 'danger';
    redirect('index.php?page=gaji');
}

// Get tarif
$stmt = $conn->prepare("SELECT * FROM tarif ORDER BY id DESC LIMIT 1");
$stmt->execute();
$tarif = $stmt->fetch();

// Get attendance data for the month
$start_date = $salary['tahun'] . '-' . sprintf('%02d', $salary['bulan']) . '-01';
$end_date = date('Y-m-t', strtotime($start_date));

$stmt = $conn->prepare("
    SELECT * FROM absensi 
    WHERE user_id = ? AND tanggal BETWEEN ? AND ? 
    ORDER BY tanggal
");
$stmt->execute([$salary['user_id'], $start_date, $end_date]);
$attendances = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detail Gaji</h2>
            
            <div>
                <button class="btn btn-outline-secondary btn-print">
                    <i class="fas fa-print"></i> Cetak
                </button>
                <a href="index.php?page=gaji" class="btn btn-outline-primary ms-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Informasi Gaji</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Nama Karyawan</th>
                                <td width="60%"><?= $salary['nama_lengkap'] ?></td>
                            </tr>
                            <tr>
                                <th>Periode</th>
                                <td><?= date('F Y', mktime(0, 0, 0, $salary['bulan'], 1, $salary['tahun'])) ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php if ($salary['status'] === 'draft'): ?>
                                        <span class="badge bg-warning">Draft</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Final</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Tanggal Perhitungan</th>
                                <td width="60%"><?= formatDateTime($salary['created_at']) ?></td>
                            </tr>
                            <tr>
                                <th>Tarif Normal</th>
                                <td><?= formatRupiah($tarif['tarif_normal']) ?> / jam</td>
                            </tr>
                            <tr>
                                <th>Tarif Lembur</th>
                                <td><?= formatRupiah($tarif['tarif_lembur']) ?> / jam</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Jam Normal</h6>
                        <h3 class="mb-0"><?= number_format($salary['total_jam_normal'], 2) ?> jam</h3>
                        <p class="text-muted mb-0"><?= formatRupiah($salary['total_jam_normal'] * $tarif['tarif_normal']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Jam Lembur</h6>
                        <h3 class="mb-0"><?= number_format($salary['total_jam_lembur'], 2) ?> jam</h3>
                        <p class="text-muted mb-0"><?= formatRupiah($salary['total_jam_lembur'] * $tarif['tarif_lembur']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Gaji</h6>
                        <h3 class="mb-0"><?= formatRupiah($salary['total_gaji']) ?></h3>
                        <p class="mb-0 opacity-75">Periode <?= date('F Y', mktime(0, 0, 0, $salary['bulan'], 1, $salary['tahun'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Absensi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                                <th>Jam Normal</th>
                                <th>Jam Lembur</th>
                                <th>Gaji Normal</th>
                                <th>Gaji Lembur</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendances)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data absensi</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $total_normal = 0;
                                $total_lembur = 0;
                                $total_gaji_normal = 0;
                                $total_gaji_lembur = 0;
                                $total_gaji = 0;
                                
                                foreach ($attendances as $attendance): 
                                    $normal_hours = 0;
                                    $overtime_hours = 0;
                                    $normal_salary = 0;
                                    $overtime_salary = 0;
                                    $daily_total = 0;
                                    
                                    if ($attendance['status'] === 'hadir' && $attendance['jam_masuk'] && $attendance['jam_keluar']) {
                                        $masuk = strtotime($attendance['jam_masuk']);
                                        $keluar = strtotime($attendance['jam_keluar']);
                                        $diff_hours = round(($keluar - $masuk) / 3600, 2);
                                        
                                        $normal_hours = min($diff_hours, $tarif['jam_normal']);
                                        $overtime_hours = max(0, $diff_hours - $tarif['jam_normal']);
                                        
                                        $normal_salary = $normal_hours * $tarif['tarif_normal'];
                                        $overtime_salary = $overtime_hours * $tarif['tarif_lembur'];
                                        $daily_total = $normal_salary + $overtime_salary;
                                        
                                        $total_normal += $normal_hours;
                                        $total_lembur += $overtime_hours;
                                        $total_gaji_normal += $normal_salary;
                                        $total_gaji_lembur += $overtime_salary;
                                        $total_gaji += $daily_total;
                                    }
                                ?>
                                    <tr>
                                        <td><?= formatDate($attendance['tanggal']) ?></td>
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
                                        <td><?= number_format($normal_hours, 2) ?> jam</td>
                                        <td><?= number_format($overtime_hours, 2) ?> jam</td>
                                        <td><?= formatRupiah($normal_salary) ?></td>
                                        <td><?= formatRupiah($overtime_salary) ?></td>
                                        <td><?= formatRupiah($daily_total) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <tr class="table-primary fw-bold">
                                    <td colspan="4" class="text-end">Total</td>
                                    <td><?= number_format($total_normal, 2) ?> jam</td>
                                    <td><?= number_format($total_lembur, 2) ?> jam</td>
                                    <td><?= formatRupiah($total_gaji_normal) ?></td>
                                    <td><?= formatRupiah($total_gaji_lembur) ?></td>
                                    <td><?= formatRupiah($total_gaji) ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php if ($role === 'admin' && $salary['status'] === 'draft'): ?>
        <div class="d-flex justify-content-end mt-4">
            <a href="index.php?page=gaji&action=finalize&id=<?= $salary['id'] ?>" class="btn btn-success me-2">
                <i class="fas fa-check"></i> Finalisasi Gaji
            </a>
            <a href="index.php?page=gaji&action=delete&id=<?= $salary['id'] ?>" class="btn btn-danger btn-delete">
                <i class="fas fa-trash"></i> Hapus
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
