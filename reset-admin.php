<?php
/**
 * Admin Password Reset Tool
 * DELETE THIS FILE AFTER USE FOR SECURITY!
 */

define('BASE_PATH', __DIR__);
require_once 'config/config.php';

$message = '';
$messageType = '';
$adminInfo = null;

// Get current admin info
try {
    $db = Database::getInstance();
    $adminInfo = $db->selectOne("SELECT user_id, full_name, email, role FROM users WHERE role = 'admin' LIMIT 1");
} catch (Exception $e) {
    $message = "Database connection error: " . $e->getMessage();
    $messageType = "danger";
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($newPassword)) {
        $message = "Password cannot be empty!";
        $messageType = "danger";
    } elseif (strlen($newPassword) < 8) {
        $message = "Password must be at least 8 characters long!";
        $messageType = "danger";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match!";
        $messageType = "danger";
    } else {
        try {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            if ($adminInfo) {
                // Update existing admin
                $db->update(
                    "UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?",
                    [$hash, $adminInfo['user_id']]
                );
                $message = "Admin password has been reset successfully for: " . $adminInfo['email'];
            } else {
                // Create new admin if none exists
                $email = $_POST['admin_email'] ?? 'admin@bachatgat.com';
                $fullName = $_POST['admin_name'] ?? 'Admin User';
                
                $userId = $db->insert(
                    "INSERT INTO users (full_name, email, phone, password, role, status, created_at) 
                     VALUES (?, ?, '9999999999', ?, 'admin', 'active', NOW())",
                    [$fullName, $email, $hash]
                );
                
                $message = "New admin account created successfully!<br>Email: " . $email;
                $adminInfo = ['email' => $email, 'full_name' => $fullName];
            }
            
            $messageType = "success";
            
        } catch (Exception $e) {
            $message = "Error resetting password: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password - Bachat Gat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/ios-font.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .reset-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            margin: 0 auto;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .danger-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background: #d1ecf1;
            border-left: 4px solid #0dcaf0;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        h1 {
            color: #764ba2;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-card">
            <h1><i class="bi bi-shield-lock"></i> Admin Password Reset</h1>
            
            <div class="danger-box">
                <h5><i class="bi bi-exclamation-triangle-fill"></i> Security Warning</h5>
                <p class="mb-0">This tool should only be used in emergency situations. <strong>DELETE THIS FILE</strong> immediately after resetting the password!</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= $messageType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($adminInfo): ?>
                <div class="info-box">
                    <h6><i class="bi bi-info-circle-fill"></i> Current Admin Account</h6>
                    <p class="mb-0">
                        <strong>Name:</strong> <?= htmlspecialchars($adminInfo['full_name']) ?><br>
                        <strong>Email:</strong> <?= htmlspecialchars($adminInfo['email']) ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="warning-box">
                    <h6><i class="bi bi-exclamation-triangle-fill"></i> No Admin Account Found</h6>
                    <p class="mb-0">No admin account exists. A new one will be created.</p>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="mt-4">
                <?php if (!$adminInfo): ?>
                    <div class="mb-3">
                        <label for="admin_name" class="form-label">Admin Full Name</label>
                        <input type="text" class="form-control" id="admin_name" name="admin_name" value="Admin User" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Admin Email</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@bachatgat.com" required>
                    </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                    <div class="form-text">Minimum 8 characters, include uppercase, lowercase, and numbers</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="reset_password" class="btn btn-primary btn-lg">
                        <i class="bi bi-key-fill"></i> Reset Password
                    </button>
                </div>
            </form>
            
            <?php if ($messageType === 'success'): ?>
                <div class="mt-4 d-grid gap-2">
                    <a href="auth/login.php" class="btn btn-success btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Go to Login
                    </a>
                </div>
                
                <div class="warning-box mt-3">
                    <h6><i class="bi bi-trash-fill"></i> Next Step</h6>
                    <p class="mb-2">Password has been reset. Now:</p>
                    <ol class="mb-0">
                        <li>Test login with the new password</li>
                        <li><strong>DELETE this file (reset-admin.php)</strong> for security</li>
                    </ol>
                </div>
            <?php endif; ?>
            
            <div class="mt-4 text-center">
                <a href="status.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Status
                </a>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="bi bi-house"></i> Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strength = document.createElement('div');
            strength.className = 'mt-2 small';
            
            let score = 0;
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^a-zA-Z0-9]/.test(password)) score++;
            
            const existing = e.target.parentElement.querySelector('.password-strength');
            if (existing) existing.remove();
            
            if (password.length > 0) {
                const strengthDiv = document.createElement('div');
                strengthDiv.className = 'password-strength mt-2 small';
                const colors = ['text-danger', 'text-warning', 'text-info', 'text-success', 'text-success'];
                const labels = ['Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
                strengthDiv.innerHTML = `<strong>Strength:</strong> <span class="${colors[score - 1]}">${labels[score - 1]}</span>`;
                e.target.parentElement.appendChild(strengthDiv);
            }
        });
        
        // Match passwords validation
        document.getElementById('confirm_password').addEventListener('input', function(e) {
            const password = document.getElementById('new_password').value;
            const confirm = e.target.value;
            
            if (confirm.length > 0) {
                if (password === confirm) {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                } else {
                    e.target.classList.remove('is-valid');
                    e.target.classList.add('is-invalid');
                }
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
    </script>
</body>
</html>
