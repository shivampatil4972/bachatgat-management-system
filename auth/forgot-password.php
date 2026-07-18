<?php
// Load configuration
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Bachat Gat Smart Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Global iPhone Font -->
    <link rel="stylesheet" href="../assets/css/ios-font.css">
    
    <!-- iOS Theme System -->
    <link rel="stylesheet" href="../assets/css/theme.css">
    <script src="../assets/js/theme-switcher.js" defer></script>
    
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
            position: relative;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
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
        
        /* Login Container */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        /* Card override */
        .auth-card {
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
            color: var(--text-primary);
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }
        
        .logo-subtitle {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 400;
            margin-top: 0.25rem;
        }
        
        .page-title {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .page-description {
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        
        /* Form Styles */
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
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
            color: var(--text-secondary);
            font-size: 1.1rem;
            pointer-events: none;
            z-index: 2;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 0.85rem 1rem 0.85rem 3rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
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
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .back-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
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
        
        .alert-info {
            background: rgba(13, 202, 240, 0.2);
            border-color: rgba(13, 202, 240, 0.3);
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
    
    <!-- Theme Toggle -->
    <button class="theme-toggle-btn" aria-label="Toggle Theme" style="position: absolute; top: 20px; right: 20px; z-index: 10;">
        <i class="bi bi-moon-fill"></i>
    </button>

    <!-- Main Container -->
    <div class="login-container">
        <div class="card auth-card">
            <div class="card-body">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="bi bi-key-fill"></i>
                </div>
                <h1 class="logo-text">Forgot Password</h1>
                <p class="logo-subtitle">Reset Your Account Password</p>
            </div>
            
            <!-- Description -->
            <p class="page-description">
                Enter your registered email address and we'll send you a link to reset your password.
            </p>
            
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <!-- Forgot Password Form -->
            <form id="forgotPasswordForm" method="POST" novalidate>
                <!-- Email Input -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your registered email"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="submitBtn">
                    <span id="btnText">Send Reset Link</span>
                </button>
                
                <!-- Back to Login Link -->
                <div class="back-link">
                    <a href="login.php"><i class="bi bi-arrow-left"></i> Back to Login</a>
                </div>
            </form>
            </div>
        </div>
    </div>
    
    <script>
        // Form elements
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const alertContainer = document.getElementById('alertContainer');
        
        // Form Submit Handler
        forgotPasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear previous alerts
            alertContainer.innerHTML = '';
            
            // Get form data
            const formData = new FormData(forgotPasswordForm);
            const email = formData.get('email');
            
            // Basic validation
            if (!email) {
                showAlert('Please enter your email address.', 'danger');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address.', 'danger');
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.innerHTML = 'Sending<span class="spinner"></span>';
            
            try {
                // Submit form
                const response = await fetch('forgot-password-process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    forgotPasswordForm.reset();
                    
                    // Development mode: show direct reset link (email not configured)
                    if (result.data && result.data.reset_link) {
                        const devAlert = document.createElement('div');
                        devAlert.className = 'alert alert-info d-flex flex-column';
                        devAlert.innerHTML = `
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <strong>Dev Mode: Email not configured</strong>
                            </div>
                            <div style="font-size:0.85rem; word-break:break-all;">
                                Click below to reset your password:
                            </div>
                            <a href="${result.data.reset_link}" 
                               style="margin-top:8px; color:white; font-weight:600; word-break:break-all;">
                                Click here to reset password &rarr;
                            </a>
                        `;
                        alertContainer.appendChild(devAlert);
                    } else {
                        // Redirect to login after 5 seconds
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 5000);
                    }
                } else {
                    showAlert(result.message, 'danger');
                    submitBtn.disabled = false;
                    btnText.textContent = 'Send Reset Link';
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showAlert('Server error. Please check that Apache/MySQL is running and try again.', 'danger');
                submitBtn.disabled = false;
                btnText.textContent = 'Send Reset Link';
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
            
            // Auto dismiss after 10 seconds
            setTimeout(() => {
                alert.style.animation = 'slideUp 0.4s ease-out reverse';
                setTimeout(() => alert.remove(), 400);
            }, 10000);
        }
    </script>
</body>
</html>
