<?php
// Only admin can access this page
if (!isAdmin()) {
    redirect('index.php?page=dashboard');
}

// Get connection
$conn = getConnection();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $tarif_normal = $_POST['tarif_normal'] ?? 0;
        $tarif_lembur = $_POST['tarif_lembur'] ?? 0;
        $jam_normal = $_POST['jam_normal'] ?? 8;
        
        // Validate data
        if ($tarif_normal <= 0 || $tarif_lembur <= 0 || $jam_normal <= 0) {
            throw new Exception('Semua nilai harus lebih dari 0');
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Insert new tarif
        $stmt = $conn->prepare("
            INSERT INTO tarif (tarif_normal, tarif_lembur, jam_normal) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$tarif_normal, $tarif_lembur, $jam_normal]);
        
        // Commit transaction
        $conn->commit();
        
        // Set flash message
        $_SESSION['flash_message'] = 'Tarif berhasil disimpan';
        $_SESSION['flash_type'] = 'success';
        
        // Redirect to refresh page
        redirect('index.php?page=tarif');
    } catch (Exception $e) {
        // Rollback transaction if started
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        // Set error message
        $error = $e->getMessage();
    }
}

// Get tarif history
$stmt = $conn->prepare("SELECT * FROM tarif ORDER BY id DESC");
$stmt->execute();
$tarif_history = $stmt->fetchAll();

// Get current tarif
$current_tarif = $tarif_history[0] ?? null;
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">Kelola Tarif</h2>
        
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tambah/Update Tarif</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="tarif_normal" class="form-label">Tarif Normal (per jam)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="tarif_normal" name="tarif_normal" value="<?= $current_tarif ? $current_tarif['tarif_normal'] : 50000 ?>" required>
                                    <div class="invalid-feedback">
                                        Tarif normal harus diisi
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tarif_lembur" class="form-label">Tarif Lembur (per jam)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="tarif_lembur" name="tarif_lembur" value="<?= $current_tarif ? $current_tarif['tarif_lembur'] : 75000 ?>" required>
                                    <div class="invalid-feedback">
                                        Tarif lembur harus diisi
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="jam_normal" class="form-label">Jam Kerja Normal (per hari)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="jam_normal" name="jam_normal" value="<?= $current_tarif ? $current_tarif['jam_normal'] : 8 ?>" required>
                                    <span class="input-group-text">jam</span>
                                    <div class="invalid-feedback">
                                        Jam kerja normal harus diisi
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Kelebihan jam kerja dari jam normal akan dihitung sebagai lembur.
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Simpan Tarif
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Riwayat Tarif</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal Update</th>
                                        <th>Tarif Normal</th>
                                        <th>Tarif Lembur</th>
                                        <th>Jam Normal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tarif_history)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Belum ada data tarif</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tarif_history as $index => $tarif): ?>
                                            <tr<?= $index === 0 ? ' class="table-primary"' : '' ?>>
                                                <td><?= formatDateTime($tarif['created_at']) ?></td>
                                                <td><?= formatRupiah($tarif['tarif_normal']) ?></td>
                                                <td><?= formatRupiah($tarif['tarif_lembur']) ?></td>
                                                <td><?= $tarif['jam_normal'] ?> jam</td>
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
    </div>
</div>
