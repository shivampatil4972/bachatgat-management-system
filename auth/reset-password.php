<?php
/**
 * Reset Password Page
 * Allows users to set a new password using a valid reset token
 */

// Load configuration
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once '../config/config.php';

$error = '';
$tokenValid = false;
$userData = null;

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid or missing reset token. Please request a new password reset.';
} else {
    try {
        // Get database instance
        $db = Database::getInstance();
        
        // Validate token
        $tokenData = $db->selectOne(
            "SELECT ut.token_id, ut.user_id, ut.expires_at, u.full_name, u.email, u.role 
             FROM user_tokens ut
             INNER JOIN users u ON ut.user_id = u.user_id
             WHERE ut.token = ? AND ut.type = 'password_reset'",
            [$token]
        );
        
        if (!$tokenData) {
            $error = 'Invalid reset token. This link may have already been used or does not exist.';
        } elseif (strtotime($tokenData['expires_at']) < time()) {
            $error = 'This reset link has expired. Please request a new password reset.';
        } else {
            $tokenValid = true;
            $userData = $tokenData;
        }
        
    } catch (Exception $e) {
        $error = 'An error occurred while validating your reset link. Please try again.';
        error_log("Reset Password Token Validation Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Bachat Gat Smart Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Global iPhone Font -->
    <link rel="stylesheet" href="../assets/css/ios-font.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background Shapes */
        .bg-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            overflow: hidden;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            animation: float 20s infinite;
        }
        
        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 60%;
            right: 10%;
            animation-delay: 5s;
        }
        
        .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 50%;
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(30px, 30px) rotate(90deg);
            }
            50% {
                transform: translate(-20px, 50px) rotate(180deg);
            }
            75% {
                transform: translate(-40px, -30px) rotate(270deg);
            }
        }
        
        /* Main Container */
        .main-container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        /* Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }
        
        .logo-icon i {
            font-size: 2.5rem;
            color: white;
        }
        
        .logo-text {
            color: white;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .logo-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            font-weight: 400;
            margin-top: 0.25rem;
        }
        
        .page-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .user-info {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 1.5rem;
            color: white;
        }
        
        .user-info strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .badge-admin {
            background: rgba(220, 53, 69, 0.3);
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
        
        .badge-member {
            background: rgba(25, 135, 84, 0.3);
            border: 1px solid rgba(25, 135, 84, 0.5);
        }
        
        /* Form Styles */
        .form-label {
            color: white;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            pointer-events: none;
            z-index: 2;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 0.85rem 3rem 0.85rem 3rem;
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.15);
            outline: none;
        }
        
        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 2;
        }
        
        .password-toggle:hover {
            color: white;
        }
        
        /* Password Strength Indicator */
        .password-strength {
            margin-top: -1rem;
            margin-bottom: 1rem;
        }
        
        .strength-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }
        
        .strength-bar-fill {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0%;
        }
        
        .strength-text {
            color: white;
            font-size: 0.85rem;
            text-align: center;
        }
        
        .strength-weak { background-color: #dc3545; width: 33%; }
        .strength-medium { background-color: #ffc107; width: 66%; }
        .strength-strong { background-color: #28a745; width: 100%; }
        
        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #fff, #f0f0f0);
            color: var(--primary-color);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        /* Back to Login Link */
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
            color: white;
            font-size: 0.95rem;
        }
        
        .back-link a {
            color: white;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 2px solid white;
            transition: opacity 0.3s ease;
        }
        
        .back-link a:hover {
            opacity: 0.8;
        }
        
        /* Alert Messages */
        .alert {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            color: white;
            margin-bottom: 1.5rem;
            animation: slideDown 0.4s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
        }
        
        .alert-success {
            background: rgba(25, 135, 84, 0.2);
            border-color: rgba(25, 135, 84, 0.3);
        }
        
        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 0.8s linear infinite;
            margin-left: 0.5rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .glass-card {
                padding: 2rem 1.5rem;
            }
            
            .logo-text {
                font-size: 1.5rem;
            }
            
            .logo-icon {
                width: 70px;
                height: 70px;
            }
            
            .logo-icon i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background Animated Shapes -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <div class="glass-card">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h1 class="logo-text">Reset Password</h1>
                <p class="logo-subtitle">Create a New Secure Password</p>
            </div>
            
            <!-- Alert Messages -->
            <div id="alertContainer">
                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($tokenValid && $userData): ?>
                <!-- User Info -->
                <div class="user-info">
                    <strong><i class="bi bi-person-fill"></i> Resetting password for:</strong>
                    <div><?php echo htmlspecialchars($userData['full_name']); ?></div>
                    <div><?php echo htmlspecialchars($userData['email']); ?></div>
                    <span class="badge badge-<?php echo $userData['role']; ?>">
                        <?php echo ucfirst($userData['role']); ?>
                    </span>
                </div>
                
                <!-- Reset Password Form -->
                <form id="resetPasswordForm" method="POST" novalidate>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <!-- New Password Input -->
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="new_password" 
                                name="new_password" 
                                placeholder="Enter new password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <i class="bi bi-eye-fill password-toggle" id="toggleNewPassword"></i>
                        </div>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                    
                    <!-- Confirm Password Input -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="Confirm new password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <i class="bi bi-eye-fill password-toggle" id="toggleConfirmPassword"></i>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span id="btnText">Reset Password</span>
                    </button>
                </form>
            <?php endif; ?>
            
            <!-- Back to Login Link -->
            <div class="back-link">
                <a href="login.php"><i class="bi bi-arrow-left"></i> Back to Login</a>
            </div>
        </div>
    </div>
    
    <script>
        <?php if ($tokenValid && $userData): ?>
        // Password toggle for new password
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const newPasswordInput = document.getElementById('new_password');
        
        toggleNewPassword.addEventListener('click', function() {
            const type = newPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            newPasswordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye-fill');
            this.classList.toggle('bi-eye-slash-fill');
        });
        
        // Password toggle for confirm password
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye-fill');
            this.classList.toggle('bi-eye-slash-fill');
        });
        
        // Password strength checker
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const strengthContainer = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthContainer.style.display = 'none';
                return;
            }
            
            strengthContainer.style.display = 'block';
            
            const strength = calculatePasswordStrength(password);
            
            strengthBar.className = 'strength-bar-fill';
            
            if (strength.score === 1) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
            } else if (strength.score === 2) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Medium strength';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
            }
        });
        
        function calculatePasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
            if (/\d/.test(password)) score++;
            if (/[^a-zA-Z\d]/.test(password)) score++;
            
            return {
                score: Math.min(3, Math.floor(score / 2) + 1),
                value: score
            };
        }
        
        // Form submission
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const alertContainer = document.getElementById('alertContainer');
        
        resetPasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear existing alerts (except error alert)
            const existingAlerts = alertContainer.querySelectorAll('.alert:not(.alert-danger)');
            existingAlerts.forEach(alert => alert.remove());
            
            // Get form data
            const formData = new FormData(resetPasswordForm);
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('confirm_password');
            
            // Validation
            if (!newPassword || !confirmPassword) {
                showAlert('Please fill in all fields.', 'danger');
                return;
            }
            
            if (newPassword.length < 8) {
                showAlert('Password must be at least 8 characters long.', 'danger');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showAlert('Passwords do not match.', 'danger');
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.innerHTML = 'Resetting<span class="spinner"></span>';
            
            try {
                // Submit form
                const response = await fetch('reset-password-process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    resetPasswordForm.reset();
                    
                    // Redirect to login after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'login.php?reset=success';
                    }, 3000);
                } else {
                    showAlert(result.message, 'danger');
                    submitBtn.disabled = false;
                    btnText.textContent = 'Reset Password';
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'danger');
                submitBtn.disabled = false;
                btnText.textContent = 'Reset Password';
            }
        });
        
        // Show Alert Function
        function showAlert(message, type) {
            const iconMap = {
                success: 'bi-check-circle-fill',
                danger: 'bi-exclamation-triangle-fill',
                warning: 'bi-exclamation-circle-fill',
                info: 'bi-info-circle-fill'
            };
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} d-flex align-items-center`;
            alert.innerHTML = `
                <i class="bi ${iconMap[type]} me-2"></i>
                <div>${message}</div>
            `;
            
            alertContainer.appendChild(alert);
            
            // Auto dismiss after 8 seconds
            setTimeout(() => {
                alert.style.animation = 'slideUp 0.4s ease-out reverse';
                setTimeout(() => alert.remove(), 400);
            }, 8000);
        }
        <?php endif; ?>
    </script>
</body>
</html>
