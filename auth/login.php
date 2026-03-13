<?php
// Load configuration
define('BASE_PATH', dirname(__DIR__));
require_once '../config/config.php';
require_once '../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bachat Gat Smart Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Global iPhone Font -->
    <link rel="stylesheet" href="../assets/css/ios-font.css">
    
    <style>
        :root {
            --primary-color: #0a84ff;
            --secondary-color: #5e5ce6;
            --gradient-start: #0a84ff;
            --gradient-end: #5e5ce6;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --font-stack: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-stack);
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
        
        /* Login Container */
        .login-container {
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
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 45px rgba(10, 132, 255, 0.22);
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
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 40px rgba(99, 102, 241, 0.4);
            }
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
            padding: 0.85rem 1rem 0.85rem 3rem;
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
        
        .form-control:-webkit-autofill,
        .form-control:-webkit-autofill:hover,
        .form-control:-webkit-autofill:focus {
            -webkit-text-fill-color: white;
            -webkit-box-shadow: 0 0 0 1000px rgba(255, 255, 255, 0.2) inset;
            transition: background-color 5000s ease-in-out 0s;
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
        
        /* Remember Me & Forgot Password */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .form-check {
            color: white;
        }
        
        .form-check-input {
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.15);
        }
        
        .form-check-label {
            font-size: 0.9rem;
            cursor: pointer;
        }
        
        .forgot-link {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            transition: opacity 0.3s ease;
        }
        
        .forgot-link:hover {
            opacity: 0.8;
        }
        
        /* Submit Button */
        .btn-login {
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        /* Register Link */
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: white;
            font-size: 0.95rem;
        }
        
        .register-link a {
            color: white;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 2px solid white;
            transition: opacity 0.3s ease;
        }
        
        .register-link a:hover {
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
        
        .alert-warning {
            background: rgba(255, 193, 7, 0.2);
            border-color: rgba(255, 193, 7, 0.3);
        }

        .field-error {
            color: #ffd4d4;
            font-size: 0.82rem;
            margin-top: 0.35rem;
            display: none;
        }

        .form-control.is-invalid {
            border-color: rgba(255, 99, 132, 0.8);
        }

        .form-control.is-valid {
            border-color: rgba(40, 167, 69, 0.75);
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
    
    <!-- Login Container -->
    <div class="login-container">
        <div class="glass-card">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="bi bi-piggy-bank-fill"></i>
                </div>
                <h1 class="logo-text">Bachat Gat</h1>
                <p class="logo-subtitle">Smart Management System</p>
            </div>
            
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <!-- Login Form -->
            <form id="loginForm" method="POST" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" id="csrf_token">
                
                <!-- Email Input -->
                <div class="mb-3">
                    <label for="email" class="form-label">Username / Email</label>
                    <div class="input-group">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email"
                            required
                            autocomplete="email"
                        >
                    </div>
                    <div id="emailError" class="field-error"></div>
                </div>
                
                <!-- Password Input -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <i class="bi bi-eye-fill password-toggle" id="togglePassword"></i>
                    </div>
                    <div id="passwordError" class="field-error"></div>
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me" value="1">
                        <label class="form-check-label" for="rememberMe">
                            Remember me
                        </label>
                    </div>
                    <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-login" id="submitBtn">
                    <span id="btnText">Login</span>
                </button>
                
                <!-- Register Link -->
                <div class="register-link">
                    Don't have an account? <a href="<?= BASE_URL ?>auth/register.php">Register Now</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Get CSRF token on page load
        window.addEventListener('DOMContentLoaded', function() {
            // In production, this should come from the server
            // For now, we'll generate it client-side (should be server-generated)
            document.getElementById('csrf_token').value = generateToken();
        });
        
        // Generate random token (temporary - should be server-generated)
        function generateToken() {
            return Array.from({length: 32}, () => Math.random().toString(36)[2]).join('');
        }
        
        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye-fill');
            this.classList.toggle('bi-eye-slash-fill');
        });
        
        // Form Validation & Submit
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const alertContainer = document.getElementById('alertContainer');

        function setFieldValidation(inputEl, errorElId, message) {
            const errorEl = document.getElementById(errorElId);
            if (message) {
                inputEl.classList.remove('is-valid');
                inputEl.classList.add('is-invalid');
                errorEl.textContent = message;
                errorEl.style.display = 'block';
                return false;
            }

            inputEl.classList.remove('is-invalid');
            inputEl.classList.add('is-valid');
            errorEl.textContent = '';
            errorEl.style.display = 'none';
            return true;
        }

        function validateEmailField() {
            const email = emailInput.value.trim();
            if (!email) {
                return setFieldValidation(emailInput, 'emailError', 'Username/Email is required.');
            }
            if (email.length > 100) {
                return setFieldValidation(emailInput, 'emailError', 'Username/Email must be at most 100 characters.');
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                return setFieldValidation(emailInput, 'emailError', 'Please enter a valid email address.');
            }
            emailInput.value = email;
            return setFieldValidation(emailInput, 'emailError', '');
        }

        function validatePasswordField() {
            const password = passwordInput.value;
            if (!password) {
                return setFieldValidation(passwordInput, 'passwordError', 'Password is required.');
            }
            if (password.length < 6) {
                return setFieldValidation(passwordInput, 'passwordError', 'Password must be at least 6 characters.');
            }
            if (password.length > 128) {
                return setFieldValidation(passwordInput, 'passwordError', 'Password is too long.');
            }
            return setFieldValidation(passwordInput, 'passwordError', '');
        }

        function updateSubmitState() {
            const emailOk = validateEmailField();
            const passOk = validatePasswordField();
            submitBtn.disabled = !(emailOk && passOk);
        }

        emailInput.addEventListener('input', updateSubmitState);
        emailInput.addEventListener('blur', validateEmailField);
        passwordInput.addEventListener('input', updateSubmitState);
        passwordInput.addEventListener('blur', validatePasswordField);
        
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear previous alerts
            alertContainer.innerHTML = '';
            
            // Get form data
            const formData = new FormData(loginForm);
            // Field validation
            const emailValid = validateEmailField();
            const passwordValid = validatePasswordField();
            if (!emailValid || !passwordValid) {
                showAlert('Please fix the validation errors and try again.', 'danger');
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.innerHTML = 'Logging in<span class="spinner"></span>';
            
            try {
                // Submit form
                const response = await fetch('login-process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = result.data.redirect || result.redirect;
                    }, 1000);
                } else {
                    showAlert(result.message, 'danger');
                    submitBtn.disabled = false;
                    btnText.textContent = 'Login';
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'danger');
                submitBtn.disabled = false;
                btnText.textContent = 'Login';
            }
        });

        updateSubmitState();
        
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
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                alert.style.animation = 'slideUp 0.4s ease-out reverse';
                setTimeout(() => alert.remove(), 400);
            }, 5000);
        }
        
        // Check for URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('timeout') === '1') {
            showAlert('Your session has expired. Please login again.', 'warning');
        }
        if (urlParams.get('registered') === '1') {
            showAlert('Registration successful! Please login to continue.', 'success');
        }
        if (urlParams.get('reset') === 'success') {
            showAlert('Password reset successful! Please login with your new password.', 'success');
        }
    </script>
</body>
</html>
