<?php
// Get connection
$conn = getConnection();

// Get filter parameters
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$user_id = $_GET['user_id'] ?? '';

// Get users for filter
$stmt = $conn->prepare("SELECT id, nama_lengkap FROM users ORDER BY nama_lengkap");
$stmt->execute();
$users = $stmt->fetchAll();

// Build query
$query = "
    SELECT a.*, u.nama_lengkap, u.username
    FROM absensi a
    JOIN users u ON a.user_id = u.id
    WHERE MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
";
$params = [$month, $year];

// Add user filter if specified
if (!empty($user_id)) {
    $query .= " AND a.user_id = ?";
    $params[] = $user_id;
}

// Add order by
$query .= " ORDER BY a.tanggal DESC, a.jam_masuk ASC";

// Get attendance data
$stmt = $conn->prepare($query);
$stmt->execute($params);
$attendances = $stmt->fetchAll();

// Calculate statistics
$total_days = count($attendances);
$total_hours = 0;
$total_overtime = 0;

foreach ($attendances as $attendance) {
    if (!empty($attendance['jam_masuk']) && !empty($attendance['jam_keluar'])) {
        $jam_masuk = new DateTime($attendance['jam_masuk']);
        $jam_keluar = new DateTime($attendance['jam_keluar']);
        $interval = $jam_masuk->diff($jam_keluar);
        
        // Calculate hours worked
        $hours = $interval->h + ($interval->i / 60);
        $total_hours += $hours;
        
        // Get current tariff
        $stmt = $conn->prepare("SELECT * FROM tarif ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $tariff = $stmt->fetch();
        
        // Calculate overtime
        $overtime = max(0, $hours - $tariff['jam_normal']);
        $total_overtime += $overtime;
    }
}

// Generate month options
$months = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

// Generate year options (last 5 years)
$current_year = date('Y');
$years = range($current_year - 4, $current_year);
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Filter Laporan Absensi</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="type" value="attendance">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="month" class="form-label">Bulan</label>
                        <select class="form-select" id="month" name="month">
                            <?php foreach ($months as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $month == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="year" class="form-label">Tahun</label>
                        <select class="form-select" id="year" name="year">
                            <?php foreach ($years as $year_option): ?>
                                <option value="<?= $year_option ?>" <?= $year == $year_option ? 'selected' : '' ?>><?= $year_option ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Karyawan</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">Semua Karyawan</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $user_id == $user['id'] ? 'selected' : '' ?>><?= $user['nama_lengkap'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i> Filter
                </button>
                
                <div>
                    <a href="index.php?page=reports/export&type=attendance&month=<?= $month ?>&year=<?= $year ?>&user_id=<?= $user_id ?>&format=csv" class="btn btn-success me-2">
                        <i class="fas fa-file-csv me-2"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-info" onclick="printReport()">
                        <i class="fas fa-print me-2"></i> Cetak Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card" id="printable-report">
    <div class="card-header">
        <h5 class="mb-0">Laporan Absensi - <?= $months[$month] ?> <?= $year ?></h5>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Hari Kerja</h6>
                        <h2 class="mb-0"><?= $total_days ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Jam Kerja</h6>
                        <h2 class="mb-0"><?= number_format($total_hours, 1) ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Jam Lembur</h6>
                        <h2 class="mb-0"><?= number_format($total_overtime, 1) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Durasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendances)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data absensi</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendances as $attendance): ?>
                            <tr>
                                <td><?= formatDate($attendance['tanggal']) ?></td>
                                <td><?= $attendance['nama_lengkap'] ?></td>
                                <td><?= $attendance['jam_masuk'] ? formatTime($attendance['jam_masuk']) : '-' ?></td>
                                <td><?= $attendance['jam_keluar'] ? formatTime($attendance['jam_keluar']) : '-' ?></td>
                                <td>
                                    <?php
                                    if (!empty($attendance['jam_masuk']) && !empty($attendance['jam_keluar'])) {
                                        $jam_masuk = new DateTime($attendance['jam_masuk']);
                                        $jam_keluar = new DateTime($attendance['jam_keluar']);
                                        $interval = $jam_masuk->diff($jam_keluar);
                                        echo $interval->format('%h jam %i menit');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (empty($attendance['jam_keluar'])): ?>
                                        <span class="badge bg-warning">Belum Checkout</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Selesai</span>
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

<script>
function printReport() {
    const printContents = document.getElementById('printable-report').innerHTML;
    const originalContents = document.body.innerHTML;
    
    document.body.innerHTML = `
        <div class="container mt-4">
            <h2 class="text-center mb-4">Laporan Absensi - <?= $months[$month] ?> <?= $year ?></h2>
            ${printContents}
        </div>
    `;
    
    window.print();
    document.body.innerHTML = originalContents;
}
</script>
