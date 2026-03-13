<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Status - Bachat Gat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/ios-font.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .status-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        .status-item {
            padding: 15px;
            border-left: 4px solid #dee2e6;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .status-item.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .status-item.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .status-item.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .badge-custom {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
        h1 {
            color: #764ba2;
            margin-bottom: 30px;
        }
        .btn-action {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-card">
            <h1><i class="bi bi-shield-check"></i> System Status Check</h1>
            
            <?php
            define('BASE_PATH', __DIR__);
            
            $checks = [];
            
            // Check 1: MySQL Connection
            try {
                $conn = new PDO('mysql:host=localhost', 'root', '');
                $checks[] = [
                    'name' => 'MySQL Server Connection',
                    'status' => 'success',
                    'message' => 'MySQL server is running and accessible',
                    'icon' => 'bi-check-circle-fill'
                ];
                
                // Check 2: Database Exists
                $result = $conn->query("SHOW DATABASES LIKE 'bachat_gat_db'")->fetch();
                if ($result) {
                    $checks[] = [
                        'name' => 'Database Exists',
                        'status' => 'success',
                        'message' => 'Database "bachat_gat_db" found',
                        'icon' => 'bi-check-circle-fill'
                    ];
                    
                    // Check 3: Tables Exist
                    $conn->exec('USE bachat_gat_db');
                    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    $requiredTables = ['users', 'members', 'notifications', 'activity_logs', 'savings', 'loans'];
                    $missingTables = array_diff($requiredTables, $tables);
                    
                    if (empty($missingTables)) {
                        $checks[] = [
                            'name' => 'Required Tables',
                            'status' => 'success',
                            'message' => 'All required tables exist (' . count($tables) . ' tables total)',
                            'icon' => 'bi-check-circle-fill'
                        ];
                    } else {
                        $checks[] = [
                            'name' => 'Required Tables',
                            'status' => 'error',
                            'message' => 'Missing tables: ' . implode(', ', $missingTables),
                            'icon' => 'bi-x-circle-fill',
                            'action' => 'Import database/schema.sql via phpMyAdmin'
                        ];
                    }
                    
                    // Check 4: Admin User
                    $adminCount = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
                    if ($adminCount > 0) {
                        $checks[] = [
                            'name' => 'Admin Account',
                            'status' => 'success',
                            'message' => 'Admin account exists',
                            'icon' => 'bi-check-circle-fill'
                        ];
                    } else {
                        $checks[] = [
                            'name' => 'Admin Account',
                            'status' => 'warning',
                            'message' => 'No admin account found',
                            'icon' => 'bi-exclamation-triangle-fill',
                            'action' => 'Use reset-admin.php to create admin account'
                        ];
                    }
                    
                } else {
                    $checks[] = [
                        'name' => 'Database Exists',
                        'status' => 'error',
                        'message' => 'Database "bachat_gat_db" not found',
                        'icon' => 'bi-x-circle-fill',
                        'action' => 'Import database/schema.sql via phpMyAdmin'
                    ];
                }
                
            } catch (PDOException $e) {
                $checks[] = [
                    'name' => 'MySQL Server Connection',
                    'status' => 'error',
                    'message' => 'Cannot connect to MySQL: ' . $e->getMessage(),
                    'icon' => 'bi-x-circle-fill',
                    'action' => 'Start MySQL service in XAMPP Control Panel'
                ];
            }
            
            // Check 5: PHP Extensions
            $required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl'];
            $missing_extensions = [];
            foreach ($required_extensions as $ext) {
                if (!extension_loaded($ext)) {
                    $missing_extensions[] = $ext;
                }
            }
            
            if (empty($missing_extensions)) {
                $checks[] = [
                    'name' => 'PHP Extensions',
                    'status' => 'success',
                    'message' => 'All required PHP extensions are loaded',
                    'icon' => 'bi-check-circle-fill'
                ];
            } else {
                $checks[] = [
                    'name' => 'PHP Extensions',
                    'status' => 'error',
                    'message' => 'Missing extensions: ' . implode(', ', $missing_extensions),
                    'icon' => 'bi-x-circle-fill',
                    'action' => 'Enable extensions in php.ini'
                ];
            }
            
            // Check 6: File Permissions
            $writable_dirs = ['logs', 'assets/uploads/profiles', 'assets/uploads/documents'];
            $permission_issues = [];
            foreach ($writable_dirs as $dir) {
                if (!is_writable(__DIR__ . '/' . $dir)) {
                    $permission_issues[] = $dir;
                }
            }
            
            if (empty($permission_issues)) {
                $checks[] = [
                    'name' => 'Directory Permissions',
                    'status' => 'success',
                    'message' => 'All required directories are writable',
                    'icon' => 'bi-check-circle-fill'
                ];
            } else {
                $checks[] = [
                    'name' => 'Directory Permissions',
                    'status' => 'warning',
                    'message' => 'Not writable: ' . implode(', ', $permission_issues),
                    'icon' => 'bi-exclamation-triangle-fill'
                ];
            }
            
            // Display all checks
            $allPassed = true;
            foreach ($checks as $check) {
                if ($check['status'] === 'error') {
                    $allPassed = false;
                }
                
                $statusClass = $check['status'];
                $icon = $check['icon'];
                echo "<div class='status-item {$statusClass}'>";
                echo "<h5><i class='bi {$icon}'></i> {$check['name']}</h5>";
                echo "<p class='mb-0'>{$check['message']}</p>";
                if (isset($check['action'])) {
                    echo "<p class='mb-0 mt-2'><strong>Action:</strong> {$check['action']}</p>";
                }
                echo "</div>";
            }
            
            // Overall Status
            echo "<div class='mt-4 p-3 rounded " . ($allPassed ? "bg-success" : "bg-warning") . " text-white'>";
            if ($allPassed) {
                echo "<h4><i class='bi bi-check-circle-fill'></i> System Ready!</h4>";
                echo "<p class='mb-0'>All systems are operational. You can now use the application.</p>";
            } else {
                echo "<h4><i class='bi bi-exclamation-triangle-fill'></i> Action Required</h4>";
                echo "<p class='mb-0'>Please fix the issues above before using the application.</p>";
            }
            echo "</div>";
            ?>
            
            <div class="btn-action d-flex gap-2">
                <a href="test-connection.php" class="btn btn-primary">
                    <i class="bi bi-database"></i> Detailed DB Test
                </a>
                <a href="auth/register.php" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> Register
                </a>
                <a href="auth/login.php" class="btn btn-info">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
                <a href="http://localhost/phpmyadmin" target="_blank" class="btn btn-secondary">
                    <i class="bi bi-database-gear"></i> phpMyAdmin
                </a>
            </div>
            
            <div class="mt-3 text-muted small">
                <i class="bi bi-info-circle"></i> Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?> | 
                PHP: <?= PHP_VERSION ?> | 
                Time: <?= date('Y-m-d H:i:s') ?>
            </div>
        </div>
    </div>
</body>
</html>
