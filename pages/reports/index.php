<?php
// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Get report type
$report_type = $_GET['type'] ?? 'attendance';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">Laporan</h2>
        
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $report_type === 'attendance' ? 'active' : '' ?>" href="index.php?page=reports&type=attendance">
                    <i class="fas fa-calendar-check me-2"></i> Laporan Absensi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $report_type === 'salary' ? 'active' : '' ?>" href="index.php?page=reports&type=salary">
                    <i class="fas fa-money-bill-wave me-2"></i> Laporan Gaji
                </a>
            </li>
        </ul>
        
        <?php if ($report_type === 'attendance'): ?>
            <?php include 'attendance_report.php'; ?>
        <?php elseif ($report_type === 'salary'): ?>
            <?php include 'salary_report.php'; ?>
        <?php endif; ?>
    </div>
</div>
