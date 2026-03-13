<?php
/**
 * Header Include
 * Bachat Gat Smart Management System
 * 
 * Common header for dashboard pages
 * Include this at the top of every dashboard page
 */

// Check if user is logged in
requireLogin();

// Get current user data
$currentUser = getCurrentUser();
$currentMember = getCurrentMember();

// Get unread notification count
$unreadCount = getUnreadNotificationCount($_SESSION['user_id']);

// Determine active page
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bachat Gat Smart Management System - Manage your savings, loans, and members efficiently">
    <meta name="author" content="Bachat Gat">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Bachat Gat Management</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>images/favicon.svg">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Global iPhone Font -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/ios-font.css">
    
    <!-- Chart.js (if needed) -->
    <?php if (isset($includeCharts) && $includeCharts): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    
    <!-- DataTables (if needed) -->
    <?php if (isset($includeDataTables) && $includeDataTables): ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <?php endif; ?>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0a84ff;
            --secondary-color: #5e5ce6;
            --success-color: #34c759;
            --danger-color: #ff3b30;
            --warning-color: #ff9f0a;
            --info-color: #64d2ff;
            --dark-color: #1c1c1e;
            --light-color: #f2f2f7;
            --surface-color: #ffffff;
            --border-color: #e5e5ea;
            --font-stack: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            --sidebar-width: 260px;
            --topbar-height: 70px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-stack);
            background: var(--light-color);
            overflow-x: hidden;
            color: var(--dark-color);
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #0a84ff 0%, #5e5ce6 100%);
            box-shadow: 0 16px 30px rgba(10, 132, 255, 0.18);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .sidebar-logo {
            width: 52px;
            height: 52px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.45), rgba(255, 255, 255, 0.14));
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.55rem;
            position: relative;
            transform-style: preserve-3d;
            box-shadow:
                0 8px 18px rgba(4, 45, 112, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.55),
                inset 0 -8px 12px rgba(66, 96, 255, 0.22);
            animation: logoFloat 3.2s ease-in-out infinite;
        }

        .sidebar-logo::before {
            content: "";
            position: absolute;
            inset: 5px;
            border-radius: 12px;
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0.02));
            transform: translateZ(14px);
            pointer-events: none;
        }

        .sidebar-logo::after {
            content: "";
            position: absolute;
            width: 42px;
            height: 10px;
            bottom: -11px;
            left: 5px;
            border-radius: 999px;
            background: rgba(2, 24, 79, 0.35);
            filter: blur(6px);
            z-index: -1;
            pointer-events: none;
        }

        .sidebar-logo i {
            position: relative;
            z-index: 1;
            transform: translateZ(28px);
            text-shadow: 0 2px 6px rgba(12, 30, 103, 0.35);
        }

        @keyframes logoFloat {
            0%, 100% {
                transform: translateY(0) rotateX(0deg) rotateY(0deg);
            }
            50% {
                transform: translateY(-3px) rotateX(5deg) rotateY(-4deg);
            }
        }
        
        .sidebar-title {
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }
        
        .sidebar-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.75rem;
            margin: 0;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
            list-style: none;
        }
        
        .menu-item {
            margin: 0.25rem 0.75rem;
        }
        
        .menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .menu-link:hover {
            background: rgba(255, 255, 255, 0.16);
            color: white;
            transform: translateX(3px);
        }
        
        .menu-link.active {
            background: rgba(255, 255, 255, 0.24);
            color: white;
            font-weight: 600;
        }
        
        .menu-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }
        
        .menu-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 1rem 1.5rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        /* Topbar */
        .topbar {
            height: var(--topbar-height);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(28, 28, 30, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .topbar-left h4 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 700;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
            font-size: 0.85rem;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        /* Notification Badge */
        .notification-icon {
            position: relative;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .notification-icon:hover {
            color: var(--primary-color);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: var(--danger-color);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        
        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        
        .user-profile:hover {
            background: var(--light-color);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }
        
        .user-info h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .user-info p {
            margin: 0;
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        /* Dropdown Menu */
        .dropdown-menu {
            border: 1px solid var(--border-color);
            box-shadow: 0 14px 28px rgba(28, 28, 30, 0.12);
            border-radius: 14px;
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .dropdown-item:hover {
            background: var(--light-color);
        }
        
        /* Page Content */
        .page-content {
            padding: 2rem;
        }

        .card {
            border-radius: 16px !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 8px 24px rgba(28, 28, 30, 0.06) !important;
            background: var(--surface-color);
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .topbar {
                padding: 0 1rem;
            }
            
            .page-content {
                padding: 1rem;
            }
            
            .user-info {
                display: none;
            }
        }
    </style>
    
    <?php if (isset($customCSS)): ?>
        <?= $customCSS ?>
    <?php endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="bi bi-piggy-bank-fill"></i>
            </div>
            <div>
                <h5 class="sidebar-title">Bachat Gat</h5>
                <p class="sidebar-subtitle">Smart Management</p>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <?php if (isAdmin()): ?>
                <!-- Admin Menu -->
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>admin/dashboard.php" class="menu-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>admin/members.php" class="menu-link <?= $currentPage === 'members.php' ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        <span>Members</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>admin/savings.php" class="menu-link <?= $currentPage === 'savings.php' ? 'active' : '' ?>">
                        <i class="bi bi-wallet2"></i>
                        <span>Savings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>admin/loans.php" class="menu-link <?= $currentPage === 'loans.php' ? 'active' : '' ?>">
                        <i class="bi bi-cash-coin"></i>
                        <span>Loans</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>admin/transactions.php" class="menu-link <?= $currentPage === 'transactions.php' ? 'active' : '' ?>">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>admin/reports.php" class="menu-link <?= $currentPage === 'reports.php' ? 'active' : '' ?>">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <div class="menu-divider"></div>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>admin/settings.php" class="menu-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>admin/profile.php" class="menu-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                        <i class="bi bi-person"></i>
                        <span>My Profile</span>
                    </a>
                </li>
            <?php else: ?>
                <!-- Member Menu -->
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>member/dashboard.php" class="menu-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>member/my-savings.php" class="menu-link <?= $currentPage === 'my-savings.php' ? 'active' : '' ?>">
                        <i class="bi bi-wallet2"></i>
                        <span>My Savings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>member/my-loans.php" class="menu-link <?= $currentPage === 'my-loans.php' ? 'active' : '' ?>">
                        <i class="bi bi-cash-coin"></i>
                        <span>My Loans</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>member/transactions.php" class="menu-link <?= $currentPage === 'transactions.php' ? 'active' : '' ?>">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <div class="menu-divider"></div>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>member/profile.php" class="menu-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                        <i class="bi bi-person"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= BASE_URL ?>member/notifications.php" class="menu-link <?= $currentPage === 'notifications.php' ? 'active' : '' ?>">
                        <i class="bi bi-bell"></i>
                        <span>Notifications</span>
                        <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger ms-auto"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="menu-item">
                <a href="<?= BASE_URL ?>auth/logout.php" class="menu-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <nav class="topbar">
            <div class="topbar-left">
                <h4><?= isset($pageTitle) ? $pageTitle : 'Dashboard' ?></h4>
                <?php if (isset($breadcrumbs)): ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <?php foreach ($breadcrumbs as $key => $crumb): ?>
                                <?php
                                // Support both formats:
                                // New: [['title' => 'X', 'url' => 'y'], ...]
                                // Old: ['Title' => 'url', 'Title2' => null]
                                if (is_array($crumb)) {
                                    $crumbTitle = $crumb['title'] ?? '';
                                    $crumbUrl   = $crumb['url'] ?? '';
                                } else {
                                    $crumbTitle = $key;
                                    $crumbUrl   = $crumb;
                                }
                                ?>
                                <?php if (!empty($crumbUrl)): ?>
                                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars($crumbUrl) ?>"><?= htmlspecialchars($crumbTitle) ?></a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active"><?= htmlspecialchars($crumbTitle) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                <?php endif; ?>
            </div>
            
            <div class="topbar-right">
                <!-- Notifications -->
                <div class="dropdown">
                    <div class="notification-icon" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notification-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown-menu dropdown-menu-end" style="width: 350px; max-height: 400px; overflow-y: auto;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <strong>Notifications</strong>
                            <?php if ($unreadCount > 0): ?>
                            <a href="<?= BASE_URL . (isAdmin() ? 'admin' : 'member') ?>/notifications.php?mark_all_read=1" class="badge bg-primary text-decoration-none">
                                Mark all read
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-divider"></div>
                        <?php
                        $notifications = getRecentNotifications($_SESSION['user_id'], 5);
                        if ($notifications):
                            foreach ($notifications as $notification):
                        ?>
                            <a href="<?= BASE_URL . (isAdmin() ? 'admin' : 'member') ?>/notifications.php" class="dropdown-item <?= !$notification['is_read'] ? 'bg-light' : '' ?>">
                                <div class="d-flex align-items-start">
                                    <div class="me-2">
                                        <?php
                                        $iconColors = [
                                            'success' => 'success',
                                            'info' => 'info',
                                            'warning' => 'warning',
                                            'error' => 'danger'
                                        ];
                                        $iconColor = $iconColors[$notification['type']] ?? 'secondary';
                                        ?>
                                        <i class="bi bi-bell-fill text-<?= $iconColor ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong class="d-block mb-1"><?= htmlspecialchars($notification['title']) ?></strong>
                                        <small class="text-muted d-block mb-1"><?= htmlspecialchars($notification['message']) ?></small>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= formatDate($notification['created_at']) ?></small>
                                    </div>
                                </div>
                            </a>
                        <?php 
                            endforeach;
                        ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?= BASE_URL . (isAdmin() ? 'admin' : 'member') ?>/notifications.php" class="dropdown-item text-center text-primary">
                            <strong>View All Notifications</strong>
                        </a>
                        <?php 
                        else:
                        ?>
                            <div class="dropdown-item text-muted text-center">
                                <i class="bi bi-bell-slash d-block mb-2" style="font-size: 2rem;"></i>
                                No notifications
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- User Profile -->
                <div class="dropdown">
                    <div class="user-profile" data-bs-toggle="dropdown">
                        <img 
                            src="<?= !empty($currentUser['profile_image']) ? UPLOADS_URL . 'profiles/' . $currentUser['profile_image'] : ASSETS_URL . 'images/default-avatar.svg' ?>" 
                            alt="Profile" 
                            class="user-avatar"
                        >
                        <div class="user-info">
                            <h6><?= htmlspecialchars($currentUser['full_name']) ?></h6>
                            <p><?= ucfirst($currentUser['role']) ?></p>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL . (isAdmin() ? 'admin' : 'member') ?>/profile.php">
                                <i class="bi bi-person"></i> My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL . (isAdmin() ? 'admin' : 'member') ?>/settings.php">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="page-content">
            <?php displayFlashMessage(); ?>
