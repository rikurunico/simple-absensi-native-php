<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?= APP_NAME ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'absensi' ? 'active' : '' ?>" href="index.php?page=absensi">
                            <i class="fas fa-clock"></i> Absensi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'gaji' ? 'active' : '' ?>" href="index.php?page=gaji">
                            <i class="fas fa-money-bill-wave"></i> Gaji
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($page, ['users', 'tarif', 'settings', 'backup']) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs"></i> Pengaturan
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="index.php?page=users">
                                    <i class="fas fa-users"></i> Kelola Pengguna
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="index.php?page=tarif">
                                    <i class="fas fa-dollar-sign"></i> Kelola Tarif
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="index.php?page=settings">
                                    <i class="fas fa-sliders-h"></i> Pengaturan Aplikasi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="index.php?page=backup">
                                    <i class="fas fa-database"></i> Backup & Restore
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'reports' ? 'active' : '' ?>" href="index.php?page=reports">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'help' ? 'active' : '' ?>" href="index.php?page=help">
                            <i class="fas fa-question-circle"></i> Bantuan
                        </a>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php include 'components/notifications.php'; ?>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= $_SESSION['nama_lengkap'] ?? 'User' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="index.php?page=profile">
                                    <i class="fas fa-user"></i> Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="index.php?page=logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container py-4">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
                <?= $_SESSION['flash_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>
