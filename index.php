<?php
/**
 * Home Page - Bachat Gat Smart Management System
 * Main landing page for visitors
 */

// Load configuration files
define('BASE_PATH', __DIR__);
require_once 'config/config.php';
require_once 'config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bachat Gat Smart Management System - Manage your Self-Help Group (SHG) savings, loans, and members efficiently with our modern digital platform.">
    <meta name="keywords" content="bachat gat, self help group, SHG management, savings, loans, financial management">
    <meta name="author" content="Bachat Gat">
    <title>Bachat Gat - Smart Self-Help Group Management System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?? 'assets/' ?>images/favicon.svg">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Global iPhone Font -->
    <link rel="stylesheet" href="assets/css/ios-font.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        
        /* Navigation */
        .navbar {
            padding: 1rem 0;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .navbar.scrolled {
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .navbar-brand i {
            font-size: 1.75rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--dark-color);
            margin: 0 0.5rem;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
        }
        
        .btn-primary-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }
        
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        
        .hero-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }
        
        .hero-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            animation: float 20s infinite;
        }
        
        .hero-shape:nth-child(1) {
            width: 400px;
            height: 400px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }
        
        .hero-shape:nth-child(2) {
            width: 300px;
            height: 300px;
            top: 50%;
            right: 10%;
            animation-delay: 5s;
        }
        
        .hero-shape:nth-child(3) {
            width: 200px;
            height: 200px;
            bottom: 15%;
            left: 40%;
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
        
        .hero-content {
            position: relative;
            z-index: 1;
            color: white;
            padding: 4rem 0;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            font-weight: 400;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            line-height: 1.6;
        }
        
        .hero-buttons .btn {
            margin: 0.5rem;
            padding: 0.9rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            font-size: 1.1rem;
        }
        
        .btn-white {
            background: white;
            color: var(--primary-color);
            border: none;
        }
        
        .btn-white:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
            color: var(--primary-color);
        }
        
        .btn-outline-white {
            border: 2px solid white;
            color: white;
            background: transparent;
        }
        
        .btn-outline-white:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        .hero-stats {
            display: flex;
            gap: 3rem;
            margin-top: 3rem;
        }
        
        .stat-item h3 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }
        
        .stat-item p {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        /* Features Section */
        .features-section {
            padding: 6rem 0;
            background: var(--light-color);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .section-title p {
            font-size: 1.1rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }
        
        .feature-card h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }
        
        .feature-card p {
            color: #6b7280;
            line-height: 1.6;
        }
        
        /* How It Works */
        .how-it-works-section {
            padding: 6rem 0;
            background: white;
        }
        
        .timeline {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .timeline-item {
            display: flex;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .timeline-item:nth-child(even) {
            flex-direction: row-reverse;
        }
        
        .timeline-number {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }
        
        .timeline-content {
            flex: 1;
            padding: 0 2rem;
        }
        
        .timeline-content h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--dark-color);
        }
        
        .timeline-content p {
            color: #6b7280;
            line-height: 1.6;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            color: white;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }
        
        .cta-section p {
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
        }
        
        /* Footer */
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 4rem 0 2rem;
        }
        
        .footer h5 {
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .footer ul {
            list-style: none;
            padding: 0;
        }
        
        .footer ul li {
            margin-bottom: 0.75rem;
        }
        
        .footer ul li a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer ul li a:hover {
            color: white;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 3rem;
            padding-top: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .social-icons {
            display: flex;
            gap: 1rem;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .timeline-item,
            .timeline-item:nth-child(even) {
                flex-direction: column;
                text-align: center;
            }
            
            .timeline-content {
                padding: 1.5rem 0 0;
            }
            
            .cta-section h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-piggy-bank-fill"></i> Bachat Gat
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="auth/register.php" class="btn btn-primary-gradient ms-2">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-shapes">
            <div class="hero-shape"></div>
            <div class="hero-shape"></div>
            <div class="hero-shape"></div>
        </div>
        
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content" data-aos="fade-right">
                    <h1 class="hero-title">Manage Your Self-Help Group with Confidence</h1>
                    <p class="hero-subtitle">
                        Streamline savings, loans, and member management with our modern, secure, and easy-to-use digital platform. Built specifically for Bachat Gat communities.
                    </p>
                    <div class="hero-buttons">
                        <a href="auth/register.php" class="btn btn-white">
                            <i class="bi bi-rocket-takeoff me-2"></i>Start Free Today
                        </a>
                        <a href="#features" class="btn btn-outline-white">
                            <i class="bi bi-play-circle me-2"></i>Learn More
                        </a>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                            <h3>500+</h3>
                            <p>Active Groups</p>
                        </div>
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                            <h3>₹10Cr+</h3>
                            <p>Total Savings</p>
                        </div>
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                            <h3>99.9%</h3>
                            <p>Uptime</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left">
                    <img src="https://via.placeholder.com/600x500/667eea/ffffff?text=Dashboard+Preview" alt="Dashboard Preview" class="img-fluid rounded" style="box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Powerful Features for Your Group</h2>
                <p>Everything you need to manage your Bachat Gat efficiently and transparently</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h4>Member Management</h4>
                        <p>Easily add, manage, and track all your group members with detailed profiles, join dates, and activity history.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <h4>Savings Tracking</h4>
                        <p>Record and monitor monthly savings contributions with automated calculations and detailed transaction history.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <h4>Loan Management</h4>
                        <p>Process loan applications, track installments, calculate interest, and manage approvals seamlessly.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h4>Reports & Analytics</h4>
                        <p>Generate comprehensive reports with beautiful charts and insights for better decision making.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>Secure & Safe</h4>
                        <p>Bank-level security with encrypted data, role-based access control, and activity logging.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h4>Mobile Friendly</h4>
                        <p>Access your group data anytime, anywhere from any device with our responsive design.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- How It Works Section -->
    <section class="how-it-works-section" id="how-it-works">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>How It Works</h2>
                <p>Get started in just 4 simple steps</p>
            </div>
            
            <div class="timeline">
                <div class="timeline-item" data-aos="fade-right">
                    <div class="timeline-number">1</div>
                    <div class="timeline-content">
                        <h4>Create Your Account</h4>
                        <p>Register with your details and get instant access to your dashboard. No credit card required.</p>
                    </div>
                </div>
                
                <div class="timeline-item" data-aos="fade-left">
                    <div class="timeline-number">2</div>
                    <div class="timeline-content">
                        <h4>Add Members</h4>
                        <p>Invite your group members and set up their profiles with all necessary information.</p>
                    </div>
                </div>
                
                <div class="timeline-item" data-aos="fade-right">
                    <div class="timeline-number">3</div>
                    <div class="timeline-content">
                        <h4>Track Savings & Loans</h4>
                        <p>Start recording savings contributions and processing loan applications with ease.</p>
                    </div>
                </div>
                
                <div class="timeline-item" data-aos="fade-left">
                    <div class="timeline-number">4</div>
                    <div class="timeline-content">
                        <h4>Monitor & Grow</h4>
                        <p>Use reports and analytics to track progress and make informed decisions for your group.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container" data-aos="zoom-in">
            <h2>Ready to Transform Your Bachat Gat?</h2>
            <p>Join hundreds of groups already managing their finances efficiently</p>
            <div class="hero-buttons">
                <a href="auth/register.php" class="btn btn-white btn-lg">
                    <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
                </a>
                <a href="pages/contact.php" class="btn btn-outline-white btn-lg">
                    <i class="bi bi-envelope me-2"></i>Contact Sales
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="bi bi-piggy-bank-fill"></i> Bachat Gat</h5>
                    <p class="text-white-50">
                        Modern digital platform for Self-Help Group management. Simplify savings, loans, and member tracking.
                    </p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="pages/about.php">About Us</a></li>
                        <li><a href="pages/contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Resources</h5>
                    <ul>
                        <li><a href="pages/help.php">Help Center</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">API</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Legal</h5>
                    <ul>
                        <li><a href="pages/privacy.php">Privacy Policy</a></li>
                        <li><a href="pages/terms.php">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Disclaimer</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Account</h5>
                    <ul>
                        <li><a href="auth/login.php">Login</a></li>
                        <li><a href="auth/register.php">Register</a></li>
                        <li><a href="#">Forgot Password</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Bachat Gat Smart Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
