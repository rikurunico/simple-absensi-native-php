<?php
// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Get salary ID
$id = $_GET['id'] ?? 0;

// Get salary data
$stmt = $conn->prepare("SELECT * FROM gaji WHERE id = ?");
$stmt->execute([$id]);
$salary = $stmt->fetch();

// Check if salary exists
if (!$salary) {
    $_SESSION['flash_message'] = 'Data gaji tidak ditemukan';
    $_SESSION['flash_type'] = 'danger';
    redirect('index.php?page=gaji');
}

// Check if salary is finalized
if ($salary['status'] === 'final') {
    $_SESSION['flash_message'] = 'Gaji yang sudah difinalisasi tidak dapat dihapus';
    $_SESSION['flash_type'] = 'warning';
    redirect('index.php?page=gaji');
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Delete salary
    $stmt = $conn->prepare("DELETE FROM gaji WHERE id = ?");
    $stmt->execute([$id]);
    
    // Commit transaction
    $conn->commit();
    
    // Set flash message
    $_SESSION['flash_message'] = 'Data gaji berhasil dihapus';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    // Rollback transaction
    $conn->rollBack();
    
    // Set error message
    $_SESSION['flash_message'] = 'Terjadi kesalahan: ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

// Redirect to salary page
redirect('index.php?page=gaji');
