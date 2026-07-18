<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - Bachat Gat Smart Management</title>
    <meta name="description" content="Help Center for Bachat Gat Smart Management System. Find answers to frequently asked questions about savings, loans, member management, reports, and more.">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Global iPhone Font -->
    <link rel="stylesheet" href="../assets/css/ios-font.css">
    
    <!-- iOS Theme System -->
    <link rel="stylesheet" href="../assets/css/theme.css">
    <script src="../assets/js/theme-switcher.js" defer></script>
    
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
            color: var(--dark-color);
        }
        
        .navbar {
            padding: 1rem 0;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.6) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
            color: white;
        }
        
        .page-header {
            background: transparent;
            padding: 8rem 0 3rem;
            color: var(--dark-color);
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .section {
            padding: 4rem 0;
            background: transparent !important;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--dark-color);
        }
        
        .mission-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.8);
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .mission-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.08);
            background: rgba(255, 255, 255, 0.85);
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

        /* Help Page Specific Styles */
        .help-search {
            max-width: 600px;
            margin: 0 auto;
        }

        .help-search .form-control {
            border-radius: 50px;
            padding: 0.85rem 1.5rem;
            border: 2px solid rgba(99, 102, 241, 0.2);
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .help-search .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
        }

        .category-card {
            text-align: center;
            padding: 1.5rem;
            cursor: pointer;
        }

        .category-card .mission-icon {
            margin: 0 auto 1rem;
        }

        .faq-section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            margin-top: 1rem;
        }

        .faq-section-title .section-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .accordion {
            --bs-accordion-border-color: rgba(255, 255, 255, 0.6);
            --bs-accordion-border-radius: 16px;
            --bs-accordion-inner-border-radius: 14px;
            --bs-accordion-btn-padding-x: 1.5rem;
            --bs-accordion-btn-padding-y: 1.15rem;
            --bs-accordion-body-padding-x: 1.5rem;
            --bs-accordion-body-padding-y: 1.25rem;
        }

        .accordion-item {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            margin-bottom: 0.75rem;
            border-radius: 16px !important;
            overflow: hidden;
        }

        .accordion-button {
            font-weight: 600;
            color: var(--dark-color);
            background: transparent;
            font-size: 0.95rem;
        }

        .accordion-button:not(.collapsed) {
            color: var(--primary-color);
            background: rgba(99, 102, 241, 0.05);
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(99, 102, 241, 0.2);
        }

        .accordion-button::after {
            background-size: 1rem;
        }

        .accordion-body {
            color: #4b5563;
            line-height: 1.8;
        }

        .accordion-body ul {
            padding-left: 1.25rem;
            margin-top: 0.5rem;
        }

        .accordion-body ul li {
            margin-bottom: 0.4rem;
        }

        .cta-section {
            padding: 6rem 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            margin: 2rem auto;
            max-width: 1200px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            text-align: center;
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
    <nav class="navbar navbar-expand-lg fixed-top glass-navbar">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-piggy-bank-fill"></i> Bachat Gat
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="../auth/register.php" class="btn btn-primary-gradient ms-2">Get Started</a>
                    </li>
                    <li class="nav-item">
                        <button class="theme-toggle-btn ms-2 mt-1" aria-label="Toggle Theme">
                            <i class="bi bi-moon-fill"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="ios-large-title">Help Center</h1>
            <p class="lead">Find answers to your questions and learn how to make the most of Bachat Gat</p>
            <!-- Search -->
            <div class="help-search mt-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0" style="margin-right: -45px; z-index: 5;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control" id="faqSearch" placeholder="Search for help topics..." style="padding-left: 2.75rem;" autocomplete="off">
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <section class="section" style="padding-top: 1rem;">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#getting-started" class="text-decoration-none">
                        <div class="mission-card category-card">
                            <div class="mission-icon">
                                <i class="bi bi-rocket-takeoff"></i>
                            </div>
                            <h6 class="fw-bold">Getting Started</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#savings" class="text-decoration-none">
                        <div class="mission-card category-card">
                            <div class="mission-icon">
                                <i class="bi bi-wallet2"></i>
                            </div>
                            <h6 class="fw-bold">Savings</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#loans" class="text-decoration-none">
                        <div class="mission-card category-card">
                            <div class="mission-icon">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <h6 class="fw-bold">Loans</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#members" class="text-decoration-none">
                        <div class="mission-card category-card">
                            <div class="mission-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h6 class="fw-bold">Members</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#reports" class="text-decoration-none">
                        <div class="mission-card category-card">
                            <div class="mission-icon">
                                <i class="bi bi-bar-chart-line"></i>
                            </div>
                            <h6 class="fw-bold">Reports</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="#troubleshooting" class="text-decoration-none">
                        <div class="mission-card category-card">
                            <div class="mission-icon">
                                <i class="bi bi-wrench-adjustable"></i>
                            </div>
                            <h6 class="fw-bold">Troubleshooting</h6>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Accordions -->
    <section class="section" style="padding-top: 0;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <!-- Getting Started -->
                    <div id="getting-started" class="mb-5">
                        <div class="faq-section-title">
                            <div class="section-icon"><i class="bi bi-rocket-takeoff"></i></div>
                            <h3 class="section-title mb-0">Getting Started</h3>
                        </div>
                        <div class="accordion" id="accordionGettingStarted">
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#gs1">
                                        What is Bachat Gat Smart Management System?
                                    </button>
                                </h2>
                                <div id="gs1" class="accordion-collapse collapse show" data-bs-parent="#accordionGettingStarted">
                                    <div class="accordion-body">
                                        Bachat Gat Smart Management System is a digital platform designed to help Self-Help Groups (SHGs) and Bachat Gats manage their finances efficiently. It replaces traditional paper-based record-keeping with a modern, easy-to-use web application that handles member management, savings tracking, loan management, financial reporting, and more — all in one place.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs2">
                                        How do I create a new Bachat Gat (group)?
                                    </button>
                                </h2>
                                <div id="gs2" class="accordion-collapse collapse" data-bs-parent="#accordionGettingStarted">
                                    <div class="accordion-body">
                                        To create a new group:
                                        <ul>
                                            <li>Click <strong>"Get Started"</strong> or <strong>"Register"</strong> on the homepage.</li>
                                            <li>Fill in your personal details (name, email, mobile number) to create an administrator account.</li>
                                            <li>After registration, you'll be guided through the group setup wizard where you enter your Bachat Gat name, registration number, formation date, and contribution details.</li>
                                            <li>Once created, you can start adding members and recording transactions immediately.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs3">
                                        Is the platform free to use?
                                    </button>
                                </h2>
                                <div id="gs3" class="accordion-collapse collapse" data-bs-parent="#accordionGettingStarted">
                                    <div class="accordion-body">
                                        Yes! Bachat Gat Smart Management offers a free tier that includes all essential features for managing your group — member management, savings tracking, basic loan management, and standard reports. Premium plans with advanced features like detailed analytics, SMS notifications, and priority support are available for groups that need them.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs4">
                                        Can I access the system from my mobile phone?
                                    </button>
                                </h2>
                                <div id="gs4" class="accordion-collapse collapse" data-bs-parent="#accordionGettingStarted">
                                    <div class="accordion-body">
                                        Absolutely! Our platform is fully responsive and works seamlessly on smartphones, tablets, and desktop computers. You can access all features from any modern web browser on your mobile device — no app download required. Simply open your browser and navigate to the platform URL.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Managing Savings -->
                    <div id="savings" class="mb-5">
                        <div class="faq-section-title">
                            <div class="section-icon"><i class="bi bi-wallet2"></i></div>
                            <h3 class="section-title mb-0">Managing Savings</h3>
                        </div>
                        <div class="accordion" id="accordionSavings">
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sav1">
                                        How do I record monthly savings contributions?
                                    </button>
                                </h2>
                                <div id="sav1" class="accordion-collapse collapse" data-bs-parent="#accordionSavings">
                                    <div class="accordion-body">
                                        To record monthly savings:
                                        <ul>
                                            <li>Navigate to <strong>Dashboard → Savings → Add Contribution</strong>.</li>
                                            <li>Select the month and year for the contribution.</li>
                                            <li>You can record contributions for individual members or use the <strong>bulk entry</strong> feature to record all members' contributions at once.</li>
                                            <li>Enter the amount for each member and click <strong>"Save"</strong>.</li>
                                            <li>The system automatically updates each member's savings balance and the group's total fund.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sav2">
                                        Can members have different contribution amounts?
                                    </button>
                                </h2>
                                <div id="sav2" class="accordion-collapse collapse" data-bs-parent="#accordionSavings">
                                    <div class="accordion-body">
                                        Yes, the system supports flexible contribution structures. While you can set a standard monthly contribution amount for the group, you can also allow variable contributions where each member may contribute a different amount each month. The group administrator can configure this in <strong>Group Settings → Contribution Rules</strong>. You can also set minimum and maximum contribution limits.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sav3">
                                        How do I view a member's savings passbook?
                                    </button>
                                </h2>
                                <div id="sav3" class="accordion-collapse collapse" data-bs-parent="#accordionSavings">
                                    <div class="accordion-body">
                                        Each member has a digital passbook that shows their complete savings history:
                                        <ul>
                                            <li>Go to <strong>Members → Select a Member → View Passbook</strong>.</li>
                                            <li>The passbook displays all contributions, withdrawals, loan deductions, and running balance.</li>
                                            <li>You can filter by date range and export the passbook as a <strong>PDF</strong> or <strong>CSV</strong> file.</li>
                                            <li>Members with login access can also view their own passbook from their dashboard.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sav4">
                                        What happens if a member misses a contribution?
                                    </button>
                                </h2>
                                <div id="sav4" class="accordion-collapse collapse" data-bs-parent="#accordionSavings">
                                    <div class="accordion-body">
                                        When a member misses a contribution, the system marks it as <strong>"Pending"</strong> for that month. The administrator can:
                                        <ul>
                                            <li>Apply a late fee or penalty as defined in the group's bylaws.</li>
                                            <li>Allow the member to make up the contribution in a subsequent month.</li>
                                            <li>Mark the contribution as <strong>"Waived"</strong> if approved by the group.</li>
                                        </ul>
                                        The system tracks all pending and missed contributions and can send reminders to members.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loans -->
                    <div id="loans" class="mb-5">
                        <div class="faq-section-title">
                            <div class="section-icon"><i class="bi bi-cash-coin"></i></div>
                            <h3 class="section-title mb-0">Loans</h3>
                        </div>
                        <div class="accordion" id="accordionLoans">
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#loan1">
                                        How does the loan process work?
                                    </button>
                                </h2>
                                <div id="loan1" class="accordion-collapse collapse" data-bs-parent="#accordionLoans">
                                    <div class="accordion-body">
                                        The loan process in Bachat Gat follows these steps:
                                        <ul>
                                            <li><strong>Application:</strong> A member submits a loan request specifying the amount, purpose, and preferred repayment tenure.</li>
                                            <li><strong>Review:</strong> The group administrator reviews the application, checking the member's savings history, existing loans, and eligibility.</li>
                                            <li><strong>Approval/Rejection:</strong> The administrator approves or rejects the loan. Approved loans are recorded with the interest rate and repayment schedule.</li>
                                            <li><strong>Disbursement:</strong> Once approved, the loan is marked as disbursed and the repayment schedule is automatically generated.</li>
                                            <li><strong>Repayment:</strong> Monthly repayments (EMIs) are tracked through the system until the loan is fully repaid.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#loan2">
                                        How is interest calculated on loans?
                                    </button>
                                </h2>
                                <div id="loan2" class="accordion-collapse collapse" data-bs-parent="#accordionLoans">
                                    <div class="accordion-body">
                                        The system supports multiple interest calculation methods:
                                        <ul>
                                            <li><strong>Simple Interest:</strong> Interest is calculated on the original principal amount for the entire loan tenure.</li>
                                            <li><strong>Reducing Balance:</strong> Interest is calculated on the outstanding principal, which decreases as repayments are made. This is the most common method used by Bachat Gats.</li>
                                            <li><strong>Flat Rate:</strong> A fixed interest amount is charged per month regardless of the outstanding balance.</li>
                                        </ul>
                                        The group administrator can configure the default interest rate and calculation method in <strong>Group Settings → Loan Configuration</strong>. Different rates can also be set for individual loans.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#loan3">
                                        Can a member have multiple active loans?
                                    </button>
                                </h2>
                                <div id="loan3" class="accordion-collapse collapse" data-bs-parent="#accordionLoans">
                                    <div class="accordion-body">
                                        This depends on your group's configuration. The administrator can set rules in <strong>Group Settings → Loan Configuration</strong>:
                                        <ul>
                                            <li><strong>Single loan policy:</strong> Members must fully repay their existing loan before applying for a new one.</li>
                                            <li><strong>Multiple loans allowed:</strong> Members can have more than one active loan simultaneously, subject to a maximum borrowing limit.</li>
                                        </ul>
                                        The system enforces whichever policy is configured and will automatically prevent loan applications that violate the group's rules.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#loan4">
                                        How do I handle loan defaults or late payments?
                                    </button>
                                </h2>
                                <div id="loan4" class="accordion-collapse collapse" data-bs-parent="#accordionLoans">
                                    <div class="accordion-body">
                                        The system provides several tools for managing late payments and defaults:
                                        <ul>
                                            <li><strong>Automatic Alerts:</strong> The system sends payment due reminders before the due date and overdue notices after.</li>
                                            <li><strong>Penalty Charges:</strong> Configure automatic penalty/late fee calculations for overdue payments.</li>
                                            <li><strong>Loan Restructuring:</strong> Administrators can modify repayment schedules, extend tenure, or adjust terms for members facing difficulty.</li>
                                            <li><strong>Default Marking:</strong> Loans that remain unpaid beyond a specified period can be marked as defaulted for record-keeping and reporting.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Member Management -->
                    <div id="members" class="mb-5">
                        <div class="faq-section-title">
                            <div class="section-icon"><i class="bi bi-people"></i></div>
                            <h3 class="section-title mb-0">Member Management</h3>
                        </div>
                        <div class="accordion" id="accordionMembers">
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mem1">
                                        How do I add new members to my group?
                                    </button>
                                </h2>
                                <div id="mem1" class="accordion-collapse collapse" data-bs-parent="#accordionMembers">
                                    <div class="accordion-body">
                                        To add new members:
                                        <ul>
                                            <li>Go to <strong>Dashboard → Members → Add Member</strong>.</li>
                                            <li>Enter the member's details: name, mobile number, email (optional), address, date of birth, and Aadhaar number (optional).</li>
                                            <li>Set the member's joining date and initial contribution amount.</li>
                                            <li>Click <strong>"Add Member"</strong> to save.</li>
                                            <li>Optionally, you can invite the member by email to create their own login for viewing their passbook and applying for loans.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mem2">
                                        How do I remove or deactivate a member?
                                    </button>
                                </h2>
                                <div id="mem2" class="accordion-collapse collapse" data-bs-parent="#accordionMembers">
                                    <div class="accordion-body">
                                        Members can be deactivated (not deleted) to preserve financial history:
                                        <ul>
                                            <li>Go to <strong>Members → Select Member → Edit</strong>.</li>
                                            <li>Change the member's status to <strong>"Inactive"</strong> and set the exit date.</li>
                                            <li>The system will calculate the member's final settlement — total savings, earned interest/dividends, minus any outstanding loans or penalties.</li>
                                            <li>Inactive members' data is preserved for audit and reporting purposes but they will no longer appear in active contribution lists.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mem3">
                                        Can members log in and view their own data?
                                    </button>
                                </h2>
                                <div id="mem3" class="accordion-collapse collapse" data-bs-parent="#accordionMembers">
                                    <div class="accordion-body">
                                        Yes! When you add a member, you can optionally invite them to create their own login credentials. Once logged in, members can:
                                        <ul>
                                            <li>View their savings passbook and contribution history.</li>
                                            <li>Check their loan status, repayment schedule, and outstanding balance.</li>
                                            <li>Submit loan applications to the group administrator.</li>
                                            <li>View group meeting schedules and announcements.</li>
                                            <li>Update their personal profile information.</li>
                                        </ul>
                                        Members have read-only access to their own data and cannot modify financial records.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reports & Analytics -->
                    <div id="reports" class="mb-5">
                        <div class="faq-section-title">
                            <div class="section-icon"><i class="bi bi-bar-chart-line"></i></div>
                            <h3 class="section-title mb-0">Reports & Analytics</h3>
                        </div>
                        <div class="accordion" id="accordionReports">
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rep1">
                                        What reports are available?
                                    </button>
                                </h2>
                                <div id="rep1" class="accordion-collapse collapse" data-bs-parent="#accordionReports">
                                    <div class="accordion-body">
                                        The system provides a comprehensive suite of reports:
                                        <ul>
                                            <li><strong>Savings Report:</strong> Monthly, quarterly, and yearly summary of all member contributions.</li>
                                            <li><strong>Loan Report:</strong> Active loans, repayment progress, overdue payments, and interest earned.</li>
                                            <li><strong>Member Report:</strong> Individual member financial summaries with savings, loans, and net position.</li>
                                            <li><strong>Balance Sheet:</strong> Complete financial position of the group — total assets, liabilities, and net worth.</li>
                                            <li><strong>Income & Expenditure:</strong> Interest income, penalties collected, operational expenses, and net profit.</li>
                                            <li><strong>Attendance Report:</strong> Meeting attendance records and participation rates.</li>
                                            <li><strong>Audit Report:</strong> Detailed audit trail for regulatory compliance.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rep2">
                                        Can I export reports to PDF or Excel?
                                    </button>
                                </h2>
                                <div id="rep2" class="accordion-collapse collapse" data-bs-parent="#accordionReports">
                                    <div class="accordion-body">
                                        Yes! All reports can be exported in multiple formats:
                                        <ul>
                                            <li><strong>PDF:</strong> Professionally formatted reports ready for printing or sharing with group members and auditors.</li>
                                            <li><strong>CSV/Excel:</strong> Spreadsheet format for further analysis or custom reporting.</li>
                                            <li><strong>Print:</strong> Directly print reports from the browser with optimised print layouts.</li>
                                        </ul>
                                        Simply click the <strong>"Export"</strong> button at the top of any report page and select your preferred format.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rep3">
                                        How do I view the group's financial dashboard?
                                    </button>
                                </h2>
                                <div id="rep3" class="accordion-collapse collapse" data-bs-parent="#accordionReports">
                                    <div class="accordion-body">
                                        The Dashboard is your homepage after logging in. It provides a real-time overview of your group's financial health:
                                        <ul>
                                            <li><strong>Summary Cards:</strong> Total savings, total loans outstanding, available fund balance, and total members.</li>
                                            <li><strong>Charts:</strong> Visual graphs showing savings growth trends, loan distribution, and monthly collection patterns.</li>
                                            <li><strong>Recent Activity:</strong> Latest transactions, loan applications, and payment receipts.</li>
                                            <li><strong>Alerts:</strong> Overdue payments, upcoming due dates, and pending approvals.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Settings -->
                    <div id="account-settings" class="mb-5">
                        <div class="faq-section-title">
                            <div class="section-icon"><i class="bi bi-gear"></i></div>
                            <h3 class="section-title mb-0">Account Settings</h3>
                        </div>
                        <div class="accordion" id="accordionSettings">
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#set1">
                                        How do I change my password?
                                    </button>
                                </h2>
                                <div id="set1" class="accordion-collapse collapse" data-bs-parent="#accordionSettings">
                                    <div class="accordion-body">
                                        To change your password:
                                        <ul>
                                            <li>Click your profile icon in the top-right corner of the dashboard.</li>
                                            <li>Select <strong>"Account Settings"</strong> or <strong>"Profile"</strong>.</li>
                                            <li>Click <strong>"Change Password"</strong>.</li>
                                            <li>Enter your current password, then your new password twice to confirm.</li>
                                            <li>Click <strong>"Update Password"</strong>.</li>
                                        </ul>
                                        For security, passwords must be at least 8 characters long and include a mix of uppercase letters, lowercase letters, and numbers.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#set2">
                                        How do I update my group's settings?
                                    </button>
                                </h2>
                                <div id="set2" class="accordion-collapse collapse" data-bs-parent="#accordionSettings">
                                    <div class="accordion-body">
                                        Group administrators can update settings by navigating to <strong>Dashboard → Settings</strong>. Configurable options include:
                                        <ul>
                                            <li><strong>Group Profile:</strong> Name, registration number, address, and formation date.</li>
                                            <li><strong>Contribution Settings:</strong> Monthly contribution amount, due date, and late fee policy.</li>
                                            <li><strong>Loan Settings:</strong> Default interest rate, calculation method, maximum loan amount, and repayment tenure.</li>
                                            <li><strong>Notification Preferences:</strong> Email and SMS alert settings for payment reminders, meeting notices, and loan updates.</li>
                                            <li><strong>Financial Year:</strong> Set the start and end dates for your group's financial year for reporting purposes.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#set3">
                                        I forgot my password. How do I reset it?
                                    </button>
                                </h2>
                                <div id="set3" class="accordion-collapse collapse" data-bs-parent="#accordionSettings">
                                    <div class="accordion-body">
                                        If you've forgotten your password:
                                        <ul>
                                            <li>Go to the <strong>Login page</strong> and click <strong>"Forgot Password?"</strong>.</li>
                                            <li>Enter the email address associated with your account.</li>
                                            <li>Check your email for a password reset link (also check your spam/junk folder).</li>
                                            <li>Click the link and set a new password.</li>
                                            <li>The reset link expires after 60 minutes for security purposes.</li>
                                        </ul>
                                        If you don't receive the email, contact your group administrator or our support team.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Troubleshooting -->
                    <div id="troubleshooting" class="mb-5">
                        <div class="faq-section-title">
                            <div class="section-icon"><i class="bi bi-wrench-adjustable"></i></div>
                            <h3 class="section-title mb-0">Troubleshooting</h3>
                        </div>
                        <div class="accordion" id="accordionTroubleshooting">
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ts1">
                                        The page is loading slowly or not loading at all
                                    </button>
                                </h2>
                                <div id="ts1" class="accordion-collapse collapse" data-bs-parent="#accordionTroubleshooting">
                                    <div class="accordion-body">
                                        If you're experiencing slow loading or connectivity issues, try these steps:
                                        <ul>
                                            <li><strong>Check your internet connection:</strong> Ensure you have a stable internet connection.</li>
                                            <li><strong>Clear browser cache:</strong> Go to your browser settings and clear cached data and cookies.</li>
                                            <li><strong>Try a different browser:</strong> We recommend using the latest version of Chrome, Firefox, Safari, or Edge.</li>
                                            <li><strong>Disable browser extensions:</strong> Some ad blockers or security extensions may interfere with the platform.</li>
                                            <li><strong>Check system status:</strong> Visit our status page or contact support to check if there's a scheduled maintenance or outage.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ts2">
                                        I'm getting an error when adding a transaction
                                    </button>
                                </h2>
                                <div id="ts2" class="accordion-collapse collapse" data-bs-parent="#accordionTroubleshooting">
                                    <div class="accordion-body">
                                        Common causes and solutions for transaction errors:
                                        <ul>
                                            <li><strong>Duplicate entry:</strong> The system prevents duplicate transactions for the same member in the same period. Check if the contribution was already recorded.</li>
                                            <li><strong>Invalid amount:</strong> Ensure the amount is a positive number within the configured limits.</li>
                                            <li><strong>Session expired:</strong> If you've been inactive for a while, your session may have expired. Log in again and retry.</li>
                                            <li><strong>Date conflict:</strong> Make sure the transaction date falls within the current or a valid financial period.</li>
                                            <li><strong>Insufficient permissions:</strong> Only group administrators can add or modify financial transactions.</li>
                                        </ul>
                                        If the error persists, note the error message and contact our support team.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ts3">
                                        My report totals don't match my manual calculations
                                    </button>
                                </h2>
                                <div id="ts3" class="accordion-collapse collapse" data-bs-parent="#accordionTroubleshooting">
                                    <div class="accordion-body">
                                        If report totals seem incorrect, verify the following:
                                        <ul>
                                            <li><strong>Date range:</strong> Ensure the report date range matches the period you're calculating manually.</li>
                                            <li><strong>Pending transactions:</strong> Check if there are any pending or draft transactions that haven't been finalised.</li>
                                            <li><strong>Inactive members:</strong> Some reports may include or exclude inactive members. Check the filter settings.</li>
                                            <li><strong>Interest calculations:</strong> Verify that the interest calculation method (simple vs. reducing balance) matches your expectation.</li>
                                            <li><strong>Rounding:</strong> The system rounds to 2 decimal places. Small rounding differences may accumulate over many transactions.</li>
                                        </ul>
                                        If discrepancies persist, use the <strong>Audit Trail</strong> feature to review all changes made to the records.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item faq-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ts4">
                                        How do I contact customer support?
                                    </button>
                                </h2>
                                <div id="ts4" class="accordion-collapse collapse" data-bs-parent="#accordionTroubleshooting">
                                    <div class="accordion-body">
                                        You can reach our support team through multiple channels:
                                        <ul>
                                            <li><strong>Contact Page:</strong> Visit our <a href="contact.php" style="color: var(--primary-color);">Contact Us</a> page and fill out the support form.</li>
                                            <li><strong>Email:</strong> Send your query to <a href="mailto:support@bachatgat.com" style="color: var(--primary-color);">support@bachatgat.com</a>.</li>
                                            <li><strong>Phone:</strong> Call us at +91 98765 43210 (Mon–Sat, 9 AM – 6 PM IST).</li>
                                        </ul>
                                        When contacting support, please include your group name, account email, and a detailed description of the issue along with any error messages or screenshots.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="mb-4">Still Need Help?</h2>
            <p class="lead mb-4">Our support team is ready to assist you with any questions</p>
            <a href="contact.php" class="btn btn-light btn-lg me-3">Contact Support</a>
            <a href="../auth/register.php" class="btn btn-outline-light btn-lg">Get Started Free</a>
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

    <!-- FAQ Search Script -->
    <script>
        document.getElementById('faqSearch').addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(function(item) {
                const question = item.querySelector('.accordion-button').textContent.toLowerCase();
                const answer = item.querySelector('.accordion-body').textContent.toLowerCase();
                
                if (query === '' || question.includes(query) || answer.includes(query)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
