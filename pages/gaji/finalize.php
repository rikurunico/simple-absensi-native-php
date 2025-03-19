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

// Check if salary is already finalized
if ($salary['status'] === 'final') {
    $_SESSION['flash_message'] = 'Gaji sudah difinalisasi';
    $_SESSION['flash_type'] = 'warning';
    redirect('index.php?page=gaji');
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Update salary status
    $stmt = $conn->prepare("
        UPDATE gaji 
        SET status = 'final', updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    
    // Commit transaction
    $conn->commit();
    
    // Set flash message
    $_SESSION['flash_message'] = 'Gaji berhasil difinalisasi';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    // Rollback transaction
    $conn->rollBack();
    
    // Set error message
    $_SESSION['flash_message'] = 'Terjadi kesalahan: ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

// Redirect to salary detail page
redirect('index.php?page=gaji&action=detail&id=' . $id);
