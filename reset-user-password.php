<?php
/**
 * User Password Reset Tool
 * Reset password for any user (admin or member)
 * DELETE THIS FILE AFTER USE FOR SECURITY!
 */

define('BASE_PATH', __DIR__);
require_once 'config/config.php';

$message = '';
$messageType = '';
$users = [];
$selectedUser = null;

// Get all users
try {
    $db = Database::getInstance();
    $users = $db->select("SELECT u.user_id, u.full_name, u.email, u.phone, u.role, u.status, 
                           m.member_code 
                           FROM users u 
                           LEFT JOIN members m ON u.user_id = m.user_id 
                           ORDER BY u.role DESC, u.full_name ASC");
} catch (Exception $e) {
    $message = "Database connection error: " . $e->getMessage();
    $messageType = "danger";
}

// Get selected user details
if (isset($_GET['user_id'])) {
    foreach ($users as $user) {
        if ($user['user_id'] == $_GET['user_id']) {
            $selectedUser = $user;
            break;
        }
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $userId = $_POST['user_id'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($userId)) {
        $message = "Please select a user!";
        $messageType = "danger";
    } elseif (empty($newPassword)) {
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
            
            // Get user info
            $user = $db->selectOne("SELECT full_name, email, role FROM users WHERE user_id = ?", [$userId]);
            
            if ($user) {
                // Update password
                $db->update(
                    "UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?",
                    [$hash, $userId]
                );
                
                $message = "Password has been reset successfully for:<br><strong>{$user['full_name']}</strong> ({$user['email']})";
                $messageType = "success";
                
                // Log the password reset
                logActivity($userId, 'Password Reset', 'Password was reset using emergency tool');
                
            } else {
                $message = "User not found!";
                $messageType = "danger";
            }
            
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
    <title>Reset User Password - Bachat Gat</title>
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
            max-width: 800px;
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
        .user-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-card:hover {
            border-color: #6366f1;
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }
        .user-card.selected {
            border-color: #6366f1;
            background: #e8e9ff;
        }
        .badge-role {
            font-size: 0.85rem;
        }
        .user-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .search-box {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-card">
            <h1><i class="bi bi-key-fill"></i> User Password Reset</h1>
            
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
            
            <?php if (empty($users)): ?>
                <div class="warning-box">
                    <h6><i class="bi bi-exclamation-triangle-fill"></i> No Users Found</h6>
                    <p class="mb-0">No users exist in the database. Please check your database setup or use <a href="reset-admin.php">reset-admin.php</a> to create an admin account.</p>
                </div>
            <?php else: ?>
                
                <div class="info-box">
                    <h6><i class="bi bi-people-fill"></i> Found <?= count($users) ?> User(s)</h6>
                    <p class="mb-0">Select a user below to reset their password.</p>
                </div>
                
                <!-- Search Box -->
                <div class="search-box">
                    <input type="text" id="searchUsers" class="form-control" placeholder="🔍 Search by name, email, or member code...">
                </div>
                
                <!-- User List -->
                <div class="user-list" id="userList">
                    <?php foreach ($users as $user): ?>
                        <div class="user-card" data-user-id="<?= $user['user_id'] ?>" 
                             data-search="<?= strtolower($user['full_name'] . ' ' . $user['email'] . ' ' . ($user['member_code'] ?? '')) ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['full_name']) ?>
                                        <span class="badge badge-role <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                            <?= strtoupper($user['role']) ?>
                                        </span>
                                        <?php if ($user['status'] === 'inactive'): ?>
                                            <span class="badge bg-secondary">INACTIVE</span>
                                        <?php endif; ?>
                                    </h6>
                                    <div class="small text-muted">
                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']) ?>
                                        <span class="ms-3"><i class="bi bi-phone"></i> <?= htmlspecialchars($user['phone']) ?></span>
                                        <?php if ($user['member_code']): ?>
                                            <span class="ms-3"><i class="bi bi-credit-card"></i> <?= htmlspecialchars($user['member_code']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <a href="?user_id=<?= $user['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-right"></i> Select
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Password Reset Form -->
                <?php if ($selectedUser): ?>
                    <div class="success-box mt-4">
                        <h6><i class="bi bi-person-check-fill"></i> Selected User</h6>
                        <p class="mb-0">
                            <strong>Name:</strong> <?= htmlspecialchars($selectedUser['full_name']) ?><br>
                            <strong>Email:</strong> <?= htmlspecialchars($selectedUser['email']) ?><br>
                            <strong>Role:</strong> <span class="badge <?= $selectedUser['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>"><?= strtoupper($selectedUser['role']) ?></span>
                            <?php if ($selectedUser['member_code']): ?>
                                <br><strong>Member Code:</strong> <?= htmlspecialchars($selectedUser['member_code']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <form method="POST" class="mt-4">
                        <input type="hidden" name="user_id" value="<?= $selectedUser['user_id'] ?>">
                        
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
                                <i class="bi bi-key-fill"></i> Reset Password for <?= htmlspecialchars($selectedUser['full_name']) ?>
                            </button>
                            <a href="reset-user-password.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Select Different User
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
                
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
                            <li><strong>DELETE this file (reset-user-password.php)</strong> for security</li>
                        </ol>
                    </div>
                <?php endif; ?>
                
            <?php endif; ?>
            
            <div class="mt-4 text-center">
                <a href="reset-admin.php" class="btn btn-outline-danger">
                    <i class="bi bi-shield-lock"></i> Reset Admin
                </a>
                <a href="status.php" class="btn btn-outline-secondary">
                    <i class="bi bi-activity"></i> System Status
                </a>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="bi bi-house"></i> Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchUsers')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const userCards = document.querySelectorAll('.user-card');
            
            userCards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Password strength indicator
        document.getElementById('new_password')?.addEventListener('input', function(e) {
            const password = e.target.value;
            
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
        document.getElementById('confirm_password')?.addEventListener('input', function(e) {
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
