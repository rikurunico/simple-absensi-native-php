<?php
// Get connection
$conn = getConnection();

// Get filter parameters
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$user_id = $_GET['user_id'] ?? '';
$status = $_GET['status'] ?? '';

// Get users for filter
$stmt = $conn->prepare("SELECT id, nama_lengkap FROM users ORDER BY nama_lengkap");
$stmt->execute();
$users = $stmt->fetchAll();

// Build query
$query = "
    SELECT g.*, u.nama_lengkap, u.username
    FROM gaji g
    JOIN users u ON g.user_id = u.id
    WHERE MONTH(g.periode_awal) = ? AND YEAR(g.periode_awal) = ?
";
$params = [$month, $year];

// Add user filter if specified
if (!empty($user_id)) {
    $query .= " AND g.user_id = ?";
    $params[] = $user_id;
}

// Add status filter if specified
if (!empty($status)) {
    $query .= " AND g.status = ?";
    $params[] = $status;
}

// Add order by
$query .= " ORDER BY g.periode_awal DESC, u.nama_lengkap ASC";

// Get salary data
$stmt = $conn->prepare($query);
$stmt->execute($params);
$salaries = $stmt->fetchAll();

// Calculate statistics
$total_salaries = count($salaries);
$total_amount = 0;
$total_normal_hours = 0;
$total_overtime_hours = 0;

foreach ($salaries as $salary) {
    $total_amount += $salary['total_gaji'];
    $total_normal_hours += $salary['total_jam_normal'];
    $total_overtime_hours += $salary['total_jam_lembur'];
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
        <h5 class="mb-0">Filter Laporan Gaji</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="type" value="salary">
            
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="month" class="form-label">Bulan</label>
                        <select class="form-select" id="month" name="month">
                            <?php foreach ($months as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $month == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="year" class="form-label">Tahun</label>
                        <select class="form-select" id="year" name="year">
                            <?php foreach ($years as $year_option): ?>
                                <option value="<?= $year_option ?>" <?= $year == $year_option ? 'selected' : '' ?>><?= $year_option ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
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
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="final" <?= $status === 'final' ? 'selected' : '' ?>>Final</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i> Filter
                </button>
                
                <div>
                    <a href="index.php?page=reports/export&type=salary&month=<?= $month ?>&year=<?= $year ?>&user_id=<?= $user_id ?>&status=<?= $status ?>&format=csv" class="btn btn-success me-2">
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
        <h5 class="mb-0">Laporan Gaji - <?= $months[$month] ?> <?= $year ?></h5>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Slip Gaji</h6>
                        <h2 class="mb-0"><?= $total_salaries ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Jam Normal</h6>
                        <h2 class="mb-0"><?= number_format($total_normal_hours, 1) ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Jam Lembur</h6>
                        <h2 class="mb-0"><?= number_format($total_overtime_hours, 1) ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Pembayaran</h6>
                        <h2 class="mb-0"><?= formatRupiah($total_amount) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Periode</th>
                        <th>Nama</th>
                        <th>Jam Normal</th>
                        <th>Jam Lembur</th>
                        <th>Gaji Normal</th>
                        <th>Gaji Lembur</th>
                        <th>Total Gaji</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($salaries)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data gaji</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($salaries as $salary): ?>
                            <tr>
                                <td><?= formatDate($salary['periode_awal']) ?> - <?= formatDate($salary['periode_akhir']) ?></td>
                                <td><?= $salary['nama_lengkap'] ?></td>
                                <td><?= number_format($salary['total_jam_normal'], 1) ?> jam</td>
                                <td><?= number_format($salary['total_jam_lembur'], 1) ?> jam</td>
                                <td><?= formatRupiah($salary['gaji_normal']) ?></td>
                                <td><?= formatRupiah($salary['gaji_lembur']) ?></td>
                                <td><?= formatRupiah($salary['total_gaji']) ?></td>
                                <td>
                                    <?php if ($salary['status'] === 'draft'): ?>
                                        <span class="badge bg-warning">Draft</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Final</span>
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
            <h2 class="text-center mb-4">Laporan Gaji - <?= $months[$month] ?> <?= $year ?></h2>
            ${printContents}
        </div>
    `;
    
    window.print();
    document.body.innerHTML = originalContents;
}
</script>
