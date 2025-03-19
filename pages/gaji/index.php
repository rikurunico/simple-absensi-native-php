<?php
// Start output buffering
ob_start();

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Get current user role
$role = getCurrentUserRole();
$user_id = getCurrentUserId();

// Get connection
$conn = getConnection();

// Get tarif information
$stmt = $conn->prepare("SELECT * FROM tarif ORDER BY id DESC LIMIT 1");
$stmt->execute();
$tarif = $stmt->fetch();

// If no tarif data exists, create a default one
if (!$tarif) {
    $tarif = [
        'tarif_normal' => 50000,
        'tarif_lembur' => 75000,
        'jam_normal' => 8
    ];
}

// Get filter parameters
$filter_month = $_GET['month'] ?? date('m');
$filter_year = $_GET['year'] ?? date('Y');
$filter_user_id = $_GET['user_id'] ?? ($role === 'admin' ? null : $user_id);

// Get users for filter
$stmt = $conn->prepare("SELECT id, nama_lengkap FROM users WHERE role = 'karyawan' ORDER BY nama_lengkap");
$stmt->execute();
$users = $stmt->fetchAll();

// Get months for filter
$months = [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
];

// Build query
$query = "
    SELECT g.*, u.nama_lengkap 
    FROM gaji g
    JOIN users u ON g.user_id = u.id
    WHERE 1=1
";
$params = [];

// Add filters
if (!empty($filter_month)) {
    $query .= " AND g.bulan = ?";
    $params[] = $filter_month;
}

if (!empty($filter_year)) {
    $query .= " AND g.tahun = ?";
    $params[] = $filter_year;
}

if (!empty($filter_user_id)) {
    $query .= " AND g.user_id = ?";
    $params[] = $filter_user_id;
}

// Add order by
$query .= " ORDER BY g.tahun DESC, g.bulan DESC, u.nama_lengkap ASC";

// Get salary data
$stmt = $conn->prepare($query);
$stmt->execute($params);
$salaries = $stmt->fetchAll();

// Calculate totals
$total_salaries = 0;
$total_normal_hours = 0;
$total_overtime_hours = 0;
$total_salary = 0;

foreach ($salaries as $salary) {
    $total_salaries++;
    $total_normal_hours += $salary['total_jam_normal'];
    $total_overtime_hours += $salary['total_jam_lembur'];
    $total_salary += $salary['total_gaji'];
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Data Gaji</h2>
            
            <?php if ($role === 'admin'): ?>
            <a href="index.php?page=gaji&action=calculate" class="btn btn-primary">
                <i class="fas fa-calculator"></i> Hitung Gaji
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="page" value="gaji">
                    
                    <div class="col-md-6">
                        <label for="year" class="form-label">Tahun</label>
                        <select class="form-select" id="year" name="year">
                            <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                                <option value="<?= $i ?>" <?= $filter_year == $i ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <?php if ($role === 'admin'): ?>
                    <div class="col-md-6">
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
                        <h5 class="card-title">Total Jam Normal</h5>
                        <h2 class="mb-0"><?= number_format($total_normal_hours, 2) ?> jam</h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Jam Lembur</h5>
                        <h2 class="mb-0"><?= number_format($total_overtime_hours, 2) ?> jam</h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Gaji</h5>
                        <h2 class="mb-0"><?= formatRupiah($total_salary) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Salary Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <?php if ($role === 'admin' && !$filter_user_id): ?>
                                <th>Nama</th>
                                <?php endif; ?>
                                <th>Jam Normal</th>
                                <th>Jam Lembur</th>
                                <th>Gaji Normal</th>
                                <th>Gaji Lembur</th>
                                <th>Total Gaji</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($salaries)): ?>
                                <tr>
                                    <td colspan="<?= ($role === 'admin' && !$filter_user_id) ? 9 : 8 ?>" class="text-center">
                                        Tidak ada data gaji
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($salaries as $salary): ?>
                                    <tr>
                                        <td><?= date('F Y', mktime(0, 0, 0, $salary['bulan'], 1, $salary['tahun'])) ?></td>
                                        <?php if ($role === 'admin' && !$filter_user_id): ?>
                                        <td><?= $salary['nama_lengkap'] ?></td>
                                        <?php endif; ?>
                                        <td><?= number_format($salary['total_jam_normal'], 2) ?> jam</td>
                                        <td><?= number_format($salary['total_jam_lembur'], 2) ?> jam</td>
                                        <td><?= formatRupiah($salary['total_jam_normal'] * $tarif['tarif_normal']) ?></td>
                                        <td><?= formatRupiah($salary['total_jam_lembur'] * $tarif['tarif_lembur']) ?></td>
                                        <td><?= formatRupiah($salary['total_gaji']) ?></td>
                                        <td>
                                            <?php if ($salary['status'] === 'draft'): ?>
                                                <span class="badge bg-warning">Draft</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Final</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="index.php?page=gaji&action=detail&id=<?= $salary['id'] ?>" class="btn btn-sm btn-info btn-action">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if ($role === 'admin' && $salary['status'] === 'draft'): ?>
                                                <a href="index.php?page=gaji&action=finalize&id=<?= $salary['id'] ?>" class="btn btn-sm btn-success btn-action">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="index.php?page=gaji&action=delete&id=<?= $salary['id'] ?>" class="btn btn-sm btn-danger btn-action btn-delete">
                                                    <i class="fas fa-trash"></i>
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
