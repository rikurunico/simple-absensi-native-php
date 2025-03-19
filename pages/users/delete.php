<?php
// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Get user ID
$id = $_GET['id'] ?? 0;

// Prevent deleting self
if ($id == getCurrentUserId()) {
    $_SESSION['flash_message'] = 'Anda tidak dapat menghapus akun Anda sendiri';
    $_SESSION['flash_type'] = 'danger';
    redirect('index.php?page=users');
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Check if user exists
if (!$user) {
    $_SESSION['flash_message'] = 'Pengguna tidak ditemukan';
    $_SESSION['flash_type'] = 'danger';
    redirect('index.php?page=users');
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    // Commit transaction
    $conn->commit();
    
    // Set flash message
    $_SESSION['flash_message'] = 'Pengguna berhasil dihapus';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    // Rollback transaction
    $conn->rollBack();
    
    // Set error message
    $_SESSION['flash_message'] = 'Terjadi kesalahan: ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

// Redirect to users page
redirect('index.php?page=users');
