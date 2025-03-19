<?php
// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Define backup directory
$backup_dir = __DIR__ . '/../../backups';

// Create backup directory if it doesn't exist
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Process backup request
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    try {
        // Generate backup filename
        $backup_file = $backup_dir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Get database tables
        $stmt = $conn->prepare("SHOW TABLES");
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Start output buffering
        ob_start();
        
        // Add header
        echo "-- Backup of database '" . DB_NAME . "'\n";
        echo "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";
        
        // Add SET statements
        echo "SET FOREIGN_KEY_CHECKS=0;\n";
        echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        echo "SET time_zone = \"+00:00\";\n\n";
        
        // Process each table
        foreach ($tables as $table) {
            // Get create table statement
            $stmt = $conn->prepare("SHOW CREATE TABLE `$table`");
            $stmt->execute();
            $create_table = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "-- Table structure for table `$table`\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";
            echo $create_table['Create Table'] . ";\n\n";
            
            // Get table data
            $stmt = $conn->prepare("SELECT * FROM `$table`");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                echo "-- Dumping data for table `$table`\n";
                
                // Get column names
                $columns = array_keys($rows[0]);
                $column_list = '`' . implode('`, `', $columns) . '`';
                
                // Start insert statement
                echo "INSERT INTO `$table` ($column_list) VALUES\n";
                
                // Add rows
                $row_values = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $row_values[] = '(' . implode(', ', $values) . ')';
                }
                
                echo implode(",\n", $row_values) . ";\n\n";
            }
        }
        
        // Add footer
        echo "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // Get buffer contents
        $backup_content = ob_get_clean();
        
        // Write to file
        file_put_contents($backup_file, $backup_content);
        
        // Set flash message
        $_SESSION['flash_message'] = 'Backup berhasil dibuat: ' . basename($backup_file);
        $_SESSION['flash_type'] = 'success';
        
        // Redirect to backup page
        redirect('index.php?page=backup');
    } catch (Exception $e) {
        // Set error message
        $_SESSION['flash_message'] = 'Terjadi kesalahan: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'danger';
        
        // Redirect to backup page
        redirect('index.php?page=backup');
    }
}

// Process download request
if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $file_path = $backup_dir . '/' . $file;
    
    if (file_exists($file_path)) {
        // Set headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        
        // Clear output buffer
        ob_clean();
        flush();
        
        // Read file
        readfile($file_path);
        exit;
    } else {
        // Set error message
        $_SESSION['flash_message'] = 'File tidak ditemukan';
        $_SESSION['flash_type'] = 'danger';
        
        // Redirect to backup page
        redirect('index.php?page=backup');
    }
}

// Process delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $file_path = $backup_dir . '/' . $file;
    
    if (file_exists($file_path)) {
        // Delete file
        unlink($file_path);
        
        // Set flash message
        $_SESSION['flash_message'] = 'File berhasil dihapus';
        $_SESSION['flash_type'] = 'success';
    } else {
        // Set error message
        $_SESSION['flash_message'] = 'File tidak ditemukan';
        $_SESSION['flash_type'] = 'danger';
    }
    
    // Redirect to backup page
    redirect('index.php?page=backup');
}

// Process restore request
if (isset($_POST['action']) && $_POST['action'] === 'restore') {
    try {
        // Check if file is uploaded
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File backup tidak valid');
        }
        
        // Get file content
        $backup_content = file_get_contents($_FILES['backup_file']['tmp_name']);
        
        // Split into queries
        $queries = explode(';', $backup_content);
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Execute queries
        foreach ($queries as $query) {
            $query = trim($query);
            
            if (!empty($query)) {
                $conn->exec($query);
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set flash message
        $_SESSION['flash_message'] = 'Database berhasil direstore';
        $_SESSION['flash_type'] = 'success';
    } catch (Exception $e) {
        // Rollback transaction
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        // Set error message
        $_SESSION['flash_message'] = 'Terjadi kesalahan: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'danger';
    }
    
    // Redirect to backup page
    redirect('index.php?page=backup');
}

// Get backup files
$backup_files = [];
if (file_exists($backup_dir)) {
    $files = scandir($backup_dir);
    
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $file_path = $backup_dir . '/' . $file;
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($file_path),
                'date' => filemtime($file_path)
            ];
        }
    }
    
    // Sort by date (newest first)
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">Backup & Restore Database</h2>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Buat Backup</h5>
                    </div>
                    <div class="card-body">
                        <p>Buat backup database untuk menyimpan data Anda. Backup akan disimpan di server.</p>
                        
                        <div class="d-grid">
                            <a href="index.php?page=backup&action=backup" class="btn btn-primary">
                                <i class="fas fa-download me-2"></i> Buat Backup Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Restore Database</h5>
                    </div>
                    <div class="card-body">
                        <p>Restore database dari file backup yang telah dibuat sebelumnya.</p>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="restore">
                            
                            <div class="mb-3">
                                <label for="backup_file" class="form-label">File Backup</label>
                                <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i> Perhatian! Restore database akan menimpa semua data yang ada. Pastikan Anda telah membuat backup terlebih dahulu.
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success" onclick="return confirm('Anda yakin ingin merestore database? Semua data yang ada akan ditimpa.');">
                                    <i class="fas fa-upload me-2"></i> Restore Database
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daftar File Backup</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($backup_files)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Belum ada file backup. Silakan buat backup terlebih dahulu.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama File</th>
                                            <th>Ukuran</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backup_files as $file): ?>
                                            <tr>
                                                <td><?= $file['name'] ?></td>
                                                <td><?= formatFileSize($file['size']) ?></td>
                                                <td><?= date('d M Y H:i', $file['date']) ?></td>
                                                <td>
                                                    <a href="index.php?page=backup&action=download&file=<?= $file['name'] ?>" class="btn btn-sm btn-primary btn-action">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    
                                                    <a href="index.php?page=backup&action=delete&file=<?= $file['name'] ?>" class="btn btn-sm btn-danger btn-action btn-delete" onclick="return confirm('Anda yakin ingin menghapus file ini?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Format file size
 * 
 * @param int $size File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    
    return round($size, 2) . ' ' . $units[$i];
}
?>
