<?php
// Prevent caching during installation
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

session_start();

// Check if already installed
if (file_exists('.env') && filesize('.env') > 0 && file_exists('installed.txt')) {
    // Check if force install
    if (!isset($_GET['force']) || $_GET['force'] !== 'true') {
        header('Location: index.php');
        exit;
    }
}

// Function to test database connection
function testDbConnection($host, $name, $user, $pass)
{
    try {
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, $user, $pass, $options);

        // Check if database exists, create if not
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$name'");
        if (!$stmt->fetch()) {
            $pdo->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        // Connect to the specific database
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, $options);

        return [true, $pdo];
    } catch (PDOException $e) {
        return [false, $e->getMessage()];
    }
}

// Define installation steps
$steps = [
    1 => 'Database Configuration',
    2 => 'Admin Account Setup',
    3 => 'Installation Complete'
];

// Get current step
$current_step = isset($_GET['step']) ? (int) $_GET['step'] : 1;

// Process form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Database Configuration
    if ($current_step === 1) {
        $db_host = $_POST['db_host'] ?? '';
        $db_name = $_POST['db_name'] ?? '';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';

        // Validate inputs
        if (empty($db_host) || empty($db_name) || empty($db_user)) {
            $error = 'All fields are required except password (if not needed)';
        } else {
            // Test database connection
            list($connected, $result) = testDbConnection($db_host, $db_name, $db_user, $db_pass);

            if ($connected) {
                $pdo = $result;

                // Create .env file
                $env_content = "# Database Configuration\n";
                $env_content .= "DB_HOST=$db_host\n";
                $env_content .= "DB_NAME=$db_name\n";
                $env_content .= "DB_USER=$db_user\n";
                $env_content .= "DB_PASS=$db_pass\n\n";
                $env_content .= "# Application Configuration\n";
                $env_content .= "APP_NAME=\"Sistem Absensi dan Penggajian\"\n";
                $env_content .= "APP_URL=http://localhost\n";
                $env_content .= "TIMEZONE=Asia/Jakarta\n";

                if (file_put_contents('.env', $env_content) === false) {
                    $error = 'Failed to write .env file. Please check file permissions.';
                } else {
                    // Import database schema
                    try {
                        $sql = file_get_contents('database.sql');

                        // Select the database first
                        $pdo->exec("USE `$db_name`");

                        // Split SQL commands
                        $commands = explode(';', $sql);
                        foreach ($commands as $command) {
                            $command = trim($command);
                            if (!empty($command)) {
                                $pdo->exec($command);
                            }
                        }

                        // Store database info in session for next step
                        $_SESSION['install_db'] = [
                            'host' => $db_host,
                            'name' => $db_name,
                            'user' => $db_user,
                            'pass' => $db_pass
                        ];

                        // Redirect to next step
                        header('Location: install.php?step=2');
                        exit;
                    } catch (PDOException $e) {
                        $error = 'Error importing database schema: ' . $e->getMessage();
                    }
                }
            } else {
                $error = 'Database connection failed: ' . $result;
            }
        }
    }

    // Step 2: Admin Account Setup
    elseif ($current_step === 2) {
        $nama_lengkap = $_POST['nama_lengkap'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate inputs
        if (empty($nama_lengkap) || empty($username) || empty($password) || empty($confirm_password)) {
            $error = 'All fields are required';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            try {
                // Get database info from session
                if (!isset($_SESSION['install_db'])) {
                    $error = 'Database information not found. Please return to Step 1.';
                } else {
                    $db = $_SESSION['install_db'];

                    // Connect to database
                    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ];

                    $pdo = new PDO($dsn, $db['user'], $db['pass'], $options);

                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Check if admin user already exists
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                    $stmt->execute();
                    $count = $stmt->fetch()['count'];

                    if ($count > 0) {
                        // Update admin user
                        $stmt = $pdo->prepare("
                            UPDATE users 
                            SET nama_lengkap = ?, username = ?, password = ? 
                            WHERE role = 'admin' 
                            LIMIT 1
                        ");
                        $stmt->execute([$nama_lengkap, $username, $hashed_password]);
                    } else {
                        // Insert admin user
                        $stmt = $pdo->prepare("
                            INSERT INTO users (nama_lengkap, username, password, role) 
                            VALUES (?, ?, ?, 'admin')
                        ");
                        $stmt->execute([$nama_lengkap, $username, $hashed_password]);
                    }

                    // Create installation complete file
                    file_put_contents('installed.txt', date('Y-m-d H:i:s'));

                    // Clear installation session data
                    unset($_SESSION['install_db']);

                    // Redirect to next step
                    header('Location: install.php?step=3');
                    exit;
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Sistem Absensi dan Penggajian</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .install-container {
            max-width: 800px;
            margin: 50px auto;
        }

        .step-indicator {
            display: flex;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            position: relative;
        }

        .step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 50%;
            right: 0;
            width: 100%;
            height: 2px;
            background-color: #dee2e6;
            z-index: -1;
        }

        .step-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            background-color: #dee2e6;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .step.active .step-number {
            background-color: #007bff;
            color: white;
        }

        .step.completed .step-number {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container install-container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Sistem Absensi dan Penggajian - Installation</h3>
            </div>

            <div class="card-body">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <?php foreach ($steps as $step_num => $step_name): ?>
                        <div
                            class="step <?= $step_num < $current_step ? 'completed' : ($step_num === $current_step ? 'active' : '') ?>">
                            <div class="step-number">
                                <?php if ($step_num < $current_step): ?>
                                    <i class="fas fa-check"></i>
                                <?php else: ?>
                                    <?= $step_num ?>
                                <?php endif; ?>
                            </div>
                            <div class="step-name"><?= $step_name ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <!-- Step 1: Database Configuration -->
                <?php if ($current_step === 1): ?>
                    <h4 class="mb-4">Database Configuration</h4>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="db_host" class="form-label">Database Host</label>
                            <input type="text" class="form-control" id="db_host" name="db_host"
                                value="<?= $_POST['db_host'] ?? 'localhost' ?>" required>
                            <div class="form-text">Usually 'localhost' or '127.0.0.1'</div>
                        </div>

                        <div class="mb-3">
                            <label for="db_name" class="form-label">Database Name</label>
                            <input type="text" class="form-control" id="db_name" name="db_name"
                                value="<?= $_POST['db_name'] ?? 'absensi_db' ?>" required>
                            <div class="form-text">If the database doesn't exist, we'll create it for you</div>
                        </div>

                        <div class="mb-3">
                            <label for="db_user" class="form-label">Database User</label>
                            <input type="text" class="form-control" id="db_user" name="db_user"
                                value="<?= $_POST['db_user'] ?? 'root' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="db_pass" class="form-label">Database Password</label>
                            <input type="password" class="form-control" id="db_pass" name="db_pass"
                                value="<?= $_POST['db_pass'] ?? '' ?>">
                            <div class="form-text">Leave empty if no password is required</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-database me-2"></i> Test Connection & Continue
                            </button>
                        </div>
                    </form>

                    <!-- Step 2: Admin Account Setup -->
                <?php elseif ($current_step === 2): ?>
                    <h4 class="mb-4">Admin Account Setup</h4>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                value="<?= $_POST['nama_lengkap'] ?? 'Administrator' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?= $_POST['username'] ?? 'admin' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Minimum 6 characters</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-shield me-2"></i> Create Admin Account & Continue
                            </button>
                        </div>
                    </form>

                    <!-- Step 3: Installation Complete -->
                <?php elseif ($current_step === 3): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                        <h4 class="mt-4 mb-3">Installation Complete!</h4>
                        <p class="lead">Your Attendance and Payroll System has been successfully installed.</p>

                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> For security reasons, please delete or rename the
                            <code>install.php</code> file.
                        </div>

                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Go to Login
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>