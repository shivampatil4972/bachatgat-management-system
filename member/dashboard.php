<?php
/**
 * Member Dashboard
 * Bachat Gat Smart Management System
 */

// Load configuration
define('BASE_PATH', dirname(__DIR__));
require_once '../config/config.php';
require_once '../config/constants.php';

// Require member access
requireMember();

// Page configuration
$pageTitle = 'My Dashboard';
$breadcrumbs = [
    'Home' => BASE_URL . 'member/dashboard.php',
    'Dashboard' => null
];
$includeCharts = true; // Include Chart.js for charts

// Get current member data
$db = Database::getInstance();
$member = $db->selectOne(
    "SELECT * FROM members WHERE user_id = ?",
    [$_SESSION['user_id']]
);

// Get member summary
$summary = $db->selectOne(
    "SELECT * FROM view_member_summary WHERE member_id = ?",
    [$member['member_id']]
);

// Include header
require_once ROOT_PATH . 'includes/header.php';
?>

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-2">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>! 👋</h3>
                        <p class="mb-0 opacity-75">Member Code: <strong><?= $member['member_code'] ?></strong></p>
                    </div>
                    <div class="text-end">
                        <p class="mb-1 opacity-75">Member Since</p>
                        <h5 class="mb-0"><?= formatDate($member['joining_date']) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="row g-4 mb-4">
    <?php
    $stats = [
        [
            'title' => 'Total Savings',
            'value' => formatCurrency($summary['total_savings'] ?? 0),
            'icon' => 'bi-wallet2',
            'color' => 'success',
            'description' => 'Your accumulated savings'
        ],
        [
            'title' => 'Active Loans',
            'value' => $summary['total_loans'] ?? 0,
            'icon' => 'bi-cash-coin',
            'color' => 'info',
            'description' => 'Current loan count'
        ],
        [
            'title' => 'Outstanding Amount',
            'value' => formatCurrency($summary['outstanding_loan_amount'] ?? 0),
            'icon' => 'bi-credit-card',
            'color' => 'warning',
            'description' => 'Amount to be paid'
        ],
        [
            'title' => 'Member Since',
            'value' => formatDate($summary['joining_date'] ?? $member['joining_date']),
            'icon' => 'bi-calendar-check',
            'color' => 'primary',
            'description' => 'Member joining date'
        ]
    ];
    
    foreach ($stats as $stat):
    ?>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1 small"><?= $stat['title'] ?></p>
                        <h3 class="mb-0 fw-bold"><?= $stat['value'] ?></h3>
                    </div>
                    <div class="stat-icon bg-<?= $stat['color'] ?> bg-opacity-10 text-<?= $stat['color'] ?> rounded p-3">
                        <i class="bi <?= $stat['icon'] ?> fs-4"></i>
                    </div>
                </div>
                <small class="text-muted"><?= $stat['description'] ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Recent Activity & Quick Info -->
<div class="row g-4">
    <!-- Recent Transactions -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Transactions</h5>
                <a href="<?= BASE_URL ?>member/transactions.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recentTransactions = $db->select(
                                "SELECT * FROM transactions 
                                 WHERE member_id = ?
                                 ORDER BY transaction_date DESC, created_at DESC
                                 LIMIT 5",
                                [$member['member_id']]
                            );
                            
                            if ($recentTransactions):
                                foreach ($recentTransactions as $txn):
                            ?>
                            <tr>
                                <td><?= formatDate($txn['transaction_date']) ?></td>
                                <td><?= htmlspecialchars($txn['description']) ?></td>
                                <td>
                                    <span class="badge bg-<?= in_array($txn['transaction_type'], ['saving_deposit','loan_disbursement']) ? 'success' : 'danger' ?>">
                                        <?= in_array($txn['transaction_type'], ['saving_deposit','loan_disbursement']) ? '+ Credit' : '- Debit' ?>
                                    </span>
                                </td>
                                <td class="fw-bold"><?= formatCurrency($txn['amount']) ?></td>
                            </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No transactions yet
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loan Summary & Quick Links -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Active Loans</h5>
            </div>
            <div class="card-body">
                <?php
                $activeLoans = $db->select(
                    "SELECT * FROM loans 
                     WHERE member_id = ? AND status IN ('disbursed','active')
                     ORDER BY created_at DESC",
                    [$member['member_id']]
                );
                
                if ($activeLoans):
                    foreach ($activeLoans as $loan):
                    $progress = ($loan['total_amount'] > 0) ? (($loan['amount_paid'] / $loan['total_amount']) * 100) : 0;
                ?>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1"><?= $loan['loan_number'] ?></h6>
                            <small class="text-muted"><?= $loan['purpose'] ?></small>
                        </div>
                        <span class="badge bg-info"><?= $loan['installment_months'] ?> months</span>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Paid: <?= formatCurrency($loan['amount_paid']) ?></span>
                            <span>Total: <?= formatCurrency($loan['total_amount']) ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                        </div>
                    </div>
                    <small class="text-muted">Remaining: <?= formatCurrency($loan['amount_remaining']) ?></small>
                </div>
                <?php 
                    endforeach;
                else:
                ?>
                <div class="text-center text-muted py-3">
                    <i class="bi bi-cash-coin fs-1 d-block mb-2"></i>
                    <p class="mb-0">No active loans</p>
                </div>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>member/my-loans.php" class="btn btn-outline-primary w-100 mt-2">View All Loans</a>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>member/my-savings.php" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-wallet2 me-2"></i>View My Savings
                    </a>
                    <a href="<?= BASE_URL ?>member/my-loans.php" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-cash-coin me-2"></i>My Loans
                    </a>
                    <a href="<?= BASE_URL ?>member/profile.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<?php
// Include footer
require_once ROOT_PATH . 'includes/footer.php';
?>
