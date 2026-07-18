<?php
/**
 * Admin Dashboard
 * Bachat Gat Smart Management System
 */

// Load configuration
define('BASE_PATH', dirname(__DIR__));
require_once '../config/config.php';
require_once '../config/constants.php';

// Require admin access
requireAdmin();

// Page configuration
$pageTitle = 'Dashboard';
$breadcrumbs = [
    'Home' => BASE_URL . 'admin/dashboard.php',
    'Dashboard' => null
];
$includeCharts = true; // Include Chart.js for dashboard stats

// Include header
require_once ROOT_PATH . 'includes/header.php';
?>

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm hover-lift" style="background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 1) 0%, rgba(139, 92, 246, 1) 90%) !important; color: white; border-radius: 16px;">
            <div class="card-body p-4">
                <h3 class="mb-2">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>! 👋</h3>
                <p class="mb-0 opacity-75">Here's what's happening with your Bachat Gat today.</p>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <?php
    // Get database instance
    $db = Database::getInstance();
    
    // Get stats
    $totalMembers = $db->selectValue("SELECT COUNT(*) FROM members WHERE status = 'active'");
    $totalSavings = $db->selectValue("SELECT COALESCE(SUM(total_savings), 0) FROM members");
    $activeLoans = $db->selectValue("SELECT COUNT(*) FROM loans WHERE status = 'active'");
    $pendingLoans = $db->selectValue("SELECT COUNT(*) FROM loans WHERE status = 'pending'");
    
    // Stats array
    $stats = [
        [
            'title' => 'Total Members',
            'value' => $totalMembers,
            'icon' => 'bi-people',
            'color' => 'primary',
            'change' => '+5 this month'
        ],
        [
            'title' => 'Total Savings',
            'value' => formatCurrency($totalSavings),
            'icon' => 'bi-wallet2',
            'color' => 'success',
            'change' => '+12% from last month'
        ],
        [
            'title' => 'Active Loans',
            'value' => $activeLoans,
            'icon' => 'bi-cash-coin',
            'color' => 'info',
            'change' => $activeLoans . ' ongoing'
        ],
        [
            'title' => 'Pending Approvals',
            'value' => $pendingLoans,
            'icon' => 'bi-hourglass-split',
            'color' => 'warning',
            'change' => 'Needs attention'
        ]
    ];
    
    foreach ($stats as $stat):
    ?>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small"><?= $stat['title'] ?></p>
                        <h3 class="mb-0 fw-bold"><?= $stat['value'] ?></h3>
                    </div>
                    <div class="icon-shape bg-<?= $stat['color'] ?> bg-opacity-10 text-<?= $stat['color'] ?> rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi <?= $stat['icon'] ?> fs-4"></i>
                    </div>
                </div>
                <small class="text-muted"><i class="bi bi-arrow-up-right"></i> <?= $stat['change'] ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Recent Activity & Quick Actions -->
<div class="row g-4">
    <!-- Recent Transactions -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3">
                <h5 class="mb-0 fw-bold">Recent Transactions</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-transparent border-bottom border-light">
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recentTransactions = $db->select(
                                "SELECT t.*, u.full_name 
                                 FROM transactions t
                                 JOIN members m ON t.member_id = m.member_id
                                 JOIN users u ON m.user_id = u.user_id
                                 ORDER BY t.created_at DESC
                                 LIMIT 5"
                            );
                            
                            if ($recentTransactions):
                                foreach ($recentTransactions as $txn):
                            ?>
                            <tr>
                                <td><?= formatDate($txn['transaction_date']) ?></td>
                                <td><?= htmlspecialchars($txn['full_name']) ?></td>
                                <td>
                                    <?php
                                    $typeColors = [
                                        'saving_deposit' => 'success',
                                        'loan_disbursement' => 'info',
                                        'installment_payment' => 'primary',
                                        'penalty' => 'warning',
                                        'credit' => 'success',
                                        'debit' => 'danger'
                                    ];
                                    $badgeColor = $typeColors[$txn['transaction_type']] ?? 'secondary';
                                    $typeText = ucwords(str_replace('_', ' ', $txn['transaction_type']));
                                    ?>
                                    <span class="badge bg-<?= $badgeColor ?> bg-opacity-10 text-<?= $badgeColor ?> border border-<?= $badgeColor ?> border-opacity-10 px-3 py-2 rounded-pill fw-medium">
                                        <?= $typeText ?>
                                    </span>
                                </td>
                                <td><?= formatCurrency($txn['amount']) ?></td>
                                <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-3 py-2 rounded-pill fw-medium">Completed</span></td>
                            </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No transactions yet</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm hover-lift">
            <div class="card-header bg-transparent border-0 py-3">
                <h5 class="mb-0 fw-bold">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>admin/members.php?action=add" class="btn border-0 d-flex justify-content-start align-items-center p-3 mb-2 bg-primary bg-opacity-10 text-primary" style="border-radius: 12px;">
                        <i class="bi bi-person-plus fs-5 me-3"></i><span class="fw-medium">Add New Member</span>
                    </a>
                    <a href="<?= BASE_URL ?>admin/savings.php?action=add" class="btn border-0 d-flex justify-content-start align-items-center p-3 mb-2 bg-success bg-opacity-10 text-success" style="border-radius: 12px;">
                        <i class="bi bi-wallet2 fs-5 me-3"></i><span class="fw-medium">Record Savings</span>
                    </a>
                    <a href="<?= BASE_URL ?>admin/loans.php?action=add" class="btn border-0 d-flex justify-content-start align-items-center p-3 mb-2 bg-info bg-opacity-10 text-info" style="border-radius: 12px;">
                        <i class="bi bi-cash-coin fs-5 me-3"></i><span class="fw-medium">Approve Loan</span>
                    </a>
                    <a href="<?= BASE_URL ?>admin/reports.php" class="btn border-0 d-flex justify-content-start align-items-center p-3 bg-secondary bg-opacity-10 text-secondary" style="border-radius: 12px;">
                        <i class="bi bi-file-earmark-bar-graph fs-5 me-3"></i><span class="fw-medium">Generate Report</span>
                    </a>
                </div>
                
                <hr class="my-4">
                
                <h6 class="fw-bold mb-3">System Status</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Database</span>
                    <span class="badge bg-success">Connected</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Backups</span>
                    <span class="badge bg-success">Up to date</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Version</span>
                    <span class="badge bg-info">1.0.0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-shape {
        transition: transform 0.3s ease;
    }
    .card:hover .icon-shape {
        transform: scale(1.1);
    }
</style>

<?php
// Include footer
require_once ROOT_PATH . 'includes/footer.php';
?>
