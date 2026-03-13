<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Bachat Gat Smart Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Global iPhone Font -->
    <link rel="stylesheet" href="../assets/css/ios-font.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
        }
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 5rem 0 3rem;
            color: white;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .section {
            padding: 4rem 0;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--dark-color);
        }
        
        .mission-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        
        .mission-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.75rem;
            margin-bottom: 1.25rem;
        }
        
        .team-card {
            text-align: center;
            padding: 1.5rem;
        }
        
        .team-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 3rem 0 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-piggy-bank-fill"></i> Bachat Gat
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>About Bachat Gat</h1>
            <p class="lead">Empowering Self-Help Groups with Modern Technology</p>
        </div>
    </div>
    
    <!-- About Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="section-title">Our Story</h2>
                    <p class="lead">
                        Bachat Gat Smart Management System was born out of a simple observation: Self-Help Groups across India were still managing their finances using traditional paper-based methods, leading to errors, inefficiency, and lack of transparency.
                    </p>
                    <p>
                        We built this platform to bridge the gap between traditional SHG operations and modern digital solutions. Our mission is to empower every Bachat Gat with tools that make financial management simple, transparent, and efficient.
                    </p>
                    <p>
                        Today, we're proud to serve over 500 active groups, managing more than ₹10 Crore in collective savings, and helping thousands of members achieve their financial goals.
                    </p>
                </div>
                <div class="col-lg-6">
                    <img src="https://via.placeholder.com/600x400/667eea/ffffff?text=Our+Mission" alt="Our Mission" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Mission & Values -->
    <section class="section">
        <div class="container">
            <h2 class="section-title text-center mb-5">Our Mission & Values</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <h4>Mission</h4>
                        <p>To digitize and simplify financial management for Self-Help Groups, making it accessible to everyone regardless of technical expertise.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="bi bi-eye"></i>
                        </div>
                        <h4>Vision</h4>
                        <p>A world where every Bachat Gat operates with complete transparency, efficiency, and digital empowerment.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h4>Values</h4>
                        <p>Transparency, Security, Simplicity, and Empowerment guide everything we do for our users.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Why Choose Us -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Why Choose Us?</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="p-3">
                        <i class="bi bi-shield-check display-4 text-primary mb-3"></i>
                        <h5>Secure & Safe</h5>
                        <p class="text-muted">Bank-level security for your data</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="p-3">
                        <i class="bi bi-phone display-4 text-primary mb-3"></i>
                        <h5>Easy to Use</h5>
                        <p class="text-muted">Simple interface, no training needed</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="p-3">
                        <i class="bi bi-headset display-4 text-primary mb-3"></i>
                        <h5>24/7 Support</h5>
                        <p class="text-muted">Always here to help you</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="p-3">
                        <i class="bi bi-cash-coin display-4 text-primary mb-3"></i>
                        <h5>Affordable</h5>
                        <p class="text-muted">Free to start, pay as you grow</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA -->
    <section class="section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4">Join hundreds of successful Bachat Gats today</p>
            <a href="../auth/register.php" class="btn btn-light btn-lg">Create Free Account</a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?= date('Y') ?> Bachat Gat Smart Management System. All rights reserved.</p>
            <div class="mt-2">
                <a href="privacy.php" class="text-white-50 me-3">Privacy Policy</a>
                <a href="terms.php" class="text-white-50 me-3">Terms of Service</a>
                <a href="contact.php" class="text-white-50">Contact Us</a>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
