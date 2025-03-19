<?php
// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Get export parameters
$type = $_GET['type'] ?? 'attendance';
$format = $_GET['format'] ?? 'csv';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$user_id = $_GET['user_id'] ?? '';

// Set filename
$filename = $type . '_report_' . $year . '-' . $month;
if (!empty($user_id)) {
    // Get user name
    $stmt = $conn->prepare("SELECT nama_lengkap FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $filename .= '_' . str_replace(' ', '_', strtolower($user['nama_lengkap'] ?? 'unknown'));
}
$filename .= '.' . $format;

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Export attendance data
if ($type === 'attendance') {
    // Add headers
    fputcsv($output, [
        'Tanggal',
        'Nama Lengkap',
        'Username',
        'Jam Masuk',
        'Jam Keluar',
        'Durasi (Jam)',
        'Status'
    ]);
    
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
    $query .= " ORDER BY a.tanggal ASC, u.nama_lengkap ASC";
    
    // Get attendance data
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    // Export data
    while ($row = $stmt->fetch()) {
        // Calculate duration
        $duration = '';
        if (!empty($row['jam_masuk']) && !empty($row['jam_keluar'])) {
            $jam_masuk = new DateTime($row['jam_masuk']);
            $jam_keluar = new DateTime($row['jam_keluar']);
            $interval = $jam_masuk->diff($jam_keluar);
            $duration = $interval->h + ($interval->i / 60);
        }
        
        // Determine status
        $status = empty($row['jam_keluar']) ? 'Belum Checkout' : 'Selesai';
        
        // Format date
        $tanggal = date('d/m/Y', strtotime($row['tanggal']));
        
        // Format times
        $jam_masuk = !empty($row['jam_masuk']) ? date('H:i', strtotime($row['jam_masuk'])) : '';
        $jam_keluar = !empty($row['jam_keluar']) ? date('H:i', strtotime($row['jam_keluar'])) : '';
        
        // Add row
        fputcsv($output, [
            $tanggal,
            $row['nama_lengkap'],
            $row['username'],
            $jam_masuk,
            $jam_keluar,
            $duration,
            $status
        ]);
    }
}
// Export salary data
else if ($type === 'salary') {
    // Add headers
    fputcsv($output, [
        'Periode',
        'Nama Lengkap',
        'Username',
        'Jam Normal',
        'Jam Lembur',
        'Tarif Normal',
        'Tarif Lembur',
        'Gaji Normal',
        'Gaji Lembur',
        'Total Gaji',
        'Status'
    ]);
    
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
    
    // Add order by
    $query .= " ORDER BY g.periode_awal ASC, u.nama_lengkap ASC";
    
    // Get salary data
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    // Export data
    while ($row = $stmt->fetch()) {
        // Format dates
        $periode_awal = date('d/m/Y', strtotime($row['periode_awal']));
        $periode_akhir = date('d/m/Y', strtotime($row['periode_akhir']));
        $periode = $periode_awal . ' - ' . $periode_akhir;
        
        // Add row
        fputcsv($output, [
            $periode,
            $row['nama_lengkap'],
            $row['username'],
            $row['total_jam_normal'],
            $row['total_jam_lembur'],
            $row['tarif_normal'],
            $row['tarif_lembur'],
            $row['gaji_normal'],
            $row['gaji_lembur'],
            $row['total_gaji'],
            $row['status']
        ]);
    }
}

// Close output stream
fclose($output);
exit;
