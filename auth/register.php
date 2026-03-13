<?php
// Load configuration
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once '../config/config.php';
require_once '../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bachat Gat Smart Management</title>
    
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
            padding: 40px 20px;
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
        
        /* Register Container */
        .register-container {
            position: relative;
            z-index: 1;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 45px rgba(10, 132, 255, 0.22);
            padding: 3rem;
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
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }
        
        .logo-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .logo-text {
            color: white;
            font-size: 1.5rem;
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
            font-size: 0.9rem;
        }
        
        .required {
            color: #ff6b6b;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.25rem;
        }
        
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            pointer-events: none;
            z-index: 2;
        }
        
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.15);
            outline: none;
        }
        
        .form-select {
            color: white;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='white' fill-opacity='0.7' d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.85rem center;
            background-size: 14px 10px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 2.5rem;
        }
        
        .form-select option {
            background: var(--gradient-end);
            color: white;
        }
        
        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s ease;
            z-index: 2;
        }
        
        .password-toggle:hover {
            color: white;
        }
        
        /* Password Strength */
        .password-strength {
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak { width: 33%; background: #ff6b6b; }
        .strength-medium { width: 66%; background: #ffd93d; }
        .strength-strong { width: 100%; background: #6bcf7f; }
        
        .password-hint {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 0.5rem;
        }
        
        /* Grid Layout */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        /* Submit Button */
        .btn-register {
            width: 100%;
            padding: 0.85rem;
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
            margin-top: 1rem;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-register:active {
            transform: translateY(0);
        }
        
        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        /* Login Link */
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: white;
            font-size: 0.95rem;
        }
        
        .login-link a {
            color: white;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 2px solid white;
            transition: opacity 0.3s ease;
        }
        
        .login-link a:hover {
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
        
        /* Invalid Feedback */
        .invalid-feedback {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        .is-invalid {
            border-color: #ff6b6b !important;
        }
        
        /* Spinner */
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
            body {
                padding: 20px 15px;
            }
            
            .glass-card {
                padding: 2rem 1.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
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
    
    <!-- Register Container -->
    <div class="register-container">
        <div class="glass-card">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="bi bi-piggy-bank-fill"></i>
                </div>
                <h1 class="logo-text">Create Account</h1>
                <p class="logo-subtitle">Join Bachat Gat Today</p>
            </div>
            
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <!-- Registration Form -->
            <form id="registerForm" method="POST" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" id="csrf_token">
                
                <!-- Full Name -->
                <div class="mb-3">
                    <label for="fullName" class="form-label">Full Name <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="bi bi-person-fill input-icon"></i>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="fullName" 
                            name="full_name" 
                            placeholder="Enter your full name"
                            required
                        >
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                
                <!-- Email & Phone -->
                <div class="form-row">
                    <div>
                        <label for="email" class="form-label">Email <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="bi bi-envelope-fill input-icon"></i>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                placeholder="your@email.com"
                                required
                            >
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div>
                        <label for="phone" class="form-label">Phone <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="bi bi-phone-fill input-icon"></i>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="phone" 
                                name="phone" 
                                placeholder="10-digit number"
                                maxlength="10"
                                required
                            >
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <!-- Password & Confirm Password -->
                <div class="form-row">
                    <div>
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Create password"
                                required
                            >
                            <i class="bi bi-eye-fill password-toggle" data-target="password"></i>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="password-hint">Min 8 chars, 1 uppercase, 1 number</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div>
                        <label for="confirmPassword" class="form-label">Confirm Password <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirmPassword" 
                                name="confirm_password" 
                                placeholder="Confirm password"
                                required
                            >
                            <i class="bi bi-eye-fill password-toggle" data-target="confirmPassword"></i>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <!-- Address -->
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <div class="input-group">
                        <i class="bi bi-house-fill input-icon"></i>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="address" 
                            name="address" 
                            placeholder="Enter your address"
                        >
                    </div>
                </div>
                
                <!-- City, State & Pincode -->
                <div class="form-row">
                    <div>
                        <label for="city" class="form-label">City</label>
                        <div class="input-group">
                            <i class="bi bi-geo-alt-fill input-icon"></i>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="city" 
                                name="city" 
                                placeholder="City"
                            >
                        </div>
                    </div>
                    
                    <div>
                        <label for="pincode" class="form-label">Pincode</label>
                        <div class="input-group">
                            <i class="bi bi-mailbox input-icon"></i>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="pincode" 
                                name="pincode" 
                                placeholder="6-digit code"
                                maxlength="6"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- State -->
                <div class="mb-3">
                    <label for="state" class="form-label">State</label>
                    <div class="input-group">
                        <i class="bi bi-map-fill input-icon"></i>
                        <select class="form-select" id="state" name="state">
                            <option value="">Select State</option>
                            <option value="Maharashtra">Maharashtra</option>
                            <option value="Gujarat">Gujarat</option>
                            <option value="Karnataka">Karnataka</option>
                            <option value="Tamil Nadu">Tamil Nadu</option>
                            <option value="Delhi">Delhi</option>
                            <option value="Uttar Pradesh">Uttar Pradesh</option>
                            <option value="West Bengal">West Bengal</option>
                            <option value="Rajasthan">Rajasthan</option>
                            <option value="Bihar">Bihar</option>
                            <option value="Madhya Pradesh">Madhya Pradesh</option>
                            <option value="Punjab">Punjab</option>
                            <option value="Haryana">Haryana</option>
                            <option value="Kerala">Kerala</option>
                            <option value="Andhra Pradesh">Andhra Pradesh</option>
                            <option value="Telangana">Telangana</option>
                        </select>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-register" id="submitBtn">
                    <span id="btnText">Create Account</span>
                </button>
                
                <!-- Login Link -->
                <div class="login-link">
                    Already have an account? <a href="<?= BASE_URL ?>auth/login.php">Login Here</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Get CSRF token on page load
        window.addEventListener('DOMContentLoaded', function() {
            document.getElementById('csrf_token').value = generateToken();
        });
        
        // Generate random token
        function generateToken() {
            return Array.from({length: 32}, () => Math.random().toString(36)[2]).join('');
        }
        
        // Password Toggle
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const target = document.getElementById(this.dataset.target);
                const type = target.getAttribute('type') === 'password' ? 'text' : 'password';
                target.setAttribute('type', type);
                this.classList.toggle('bi-eye-fill');
                this.classList.toggle('bi-eye-slash-fill');
            });
        });
        
        // Password Strength Checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength-bar';
            if (strength === 1 || strength === 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength === 3) {
                strengthBar.classList.add('strength-medium');
            } else if (strength === 4) {
                strengthBar.classList.add('strength-strong');
            }
        });
        
        // Phone Number Validation
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        
        // Pincode Validation
        const pincodeInput = document.getElementById('pincode');
        pincodeInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        
        // Form Validation & Submit
        const registerForm = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const alertContainer = document.getElementById('alertContainer');
        
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear previous alerts and errors
            alertContainer.innerHTML = '';
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
                el.nextElementSibling.textContent = '';
            });
            
            // Get form data
            const formData = new FormData(registerForm);
            
            // Client-side validation
            let isValid = true;
            
            // Full Name
            const fullName = formData.get('full_name');
            if (!fullName || fullName.trim().length < 3) {
                showFieldError('fullName', 'Full name must be at least 3 characters');
                isValid = false;
            }
            
            // Email
            const email = formData.get('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                showFieldError('email', 'Please enter a valid email address');
                isValid = false;
            }
            
            // Phone
            const phone = formData.get('phone');
            const phoneRegex = /^[6-9]\d{9}$/;
            if (!phone || !phoneRegex.test(phone)) {
                showFieldError('phone', 'Enter valid 10-digit Indian mobile number');
                isValid = false;
            }
            
            // Password
            const password = formData.get('password');
            if (!password || password.length < 8) {
                showFieldError('password', 'Password must be at least 8 characters');
                isValid = false;
            } else if (!/[A-Z]/.test(password)) {
                showFieldError('password', 'Password must contain uppercase letter');
                isValid = false;
            } else if (!/\d/.test(password)) {
                showFieldError('password', 'Password must contain a number');
                isValid = false;
            }
            
            // Confirm Password
            const confirmPassword = formData.get('confirm_password');
            if (password !== confirmPassword) {
                showFieldError('confirmPassword', 'Passwords do not match');
                isValid = false;
            }
            
            if (!isValid) {
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.innerHTML = 'Creating Account<span class="spinner"></span>';
            
            try {
                // Submit form
                const response = await fetch('register-process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = '<?= BASE_URL ?>auth/login.php?registered=1';
                    }, 2000);
                } else {
                    showAlert(result.message, 'danger');
                    submitBtn.disabled = false;
                    btnText.textContent = 'Create Account';
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'danger');
                submitBtn.disabled = false;
                btnText.textContent = 'Create Account';
            }
        });
        
        // Show Field Error
        function showFieldError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const feedback = field.parentElement.nextElementSibling;
            field.classList.add('is-invalid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = message;
            }
        }
        
        // Show Alert Function
        function showAlert(message, type) {
            const iconMap = {
                success: 'bi-check-circle-fill',
                danger: 'bi-exclamation-triangle-fill',
                warning: 'bi-exclamation-circle-fill'
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
    </script>
</body>
</html>
