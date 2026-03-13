<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

// Page variables
$pageTitle = 'Reports & Analytics';
$breadcrumbs = [
    'Dashboard' => 'dashboard.php',
    'Reports' => ''
];
$includeCharts = true;

// Get database instance
$db = Database::getInstance();

// Get date range filters
$dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$dateTo = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Validate dates
if (strtotime($dateFrom) > strtotime($dateTo)) {
    $temp = $dateFrom;
    $dateFrom = $dateTo;
    $dateTo = $temp;
}

// ============================================
// OVERVIEW STATISTICS
// ============================================

// Total Members
$totalMembers = $db->selectOne("SELECT COUNT(*) as count FROM members")['count'];
$activeMembers = $db->selectOne("SELECT COUNT(*) as count FROM members WHERE status = 'active'")['count'];

// Total Savings
$totalSavingsData = $db->selectOne("SELECT COALESCE(SUM(amount),0) as total FROM savings WHERE deposit_date BETWEEN ? AND ?", [$dateFrom, $dateTo]);
$totalSavings = $totalSavingsData['total'] ?? 0;

// Total Loans
$totalLoansData = $db->selectOne("SELECT COUNT(*) as count, COALESCE(SUM(loan_amount),0) as total FROM loans WHERE disbursement_date BETWEEN ? AND ?", [$dateFrom, $dateTo]);
$totalLoansCount = $totalLoansData['count'];
$totalLoansAmount = $totalLoansData['total'] ?? 0;

// Active Loans
$activeLoansData = $db->selectOne("SELECT COUNT(*) as count, COALESCE(SUM(loan_amount),0) as total FROM loans WHERE status = 'disbursed'");
$activeLoansCount = $activeLoansData['count'];
$activeLoansAmount = $activeLoansData['total'] ?? 0;

// Total Collected (Loan Repayments)
$totalCollected = $db->selectOne("SELECT COALESCE(SUM(paid_amount),0) as total FROM installments WHERE payment_date BETWEEN ? AND ? AND status = 'paid'", [$dateFrom, $dateTo])['total'] ?? 0;

// Outstanding Amount
$outstandingData = $db->selectOne("SELECT COALESCE(SUM(amount_remaining),0) as total FROM loans WHERE status = 'disbursed'");
$outstandingAmount = $outstandingData['total'] ?? 0;

// ============================================
// SAVINGS ANALYTICS
// ============================================

// Monthly Savings Trend (Last 6 months)
$monthlySavings = $db->select("
    SELECT 
        DATE_FORMAT(deposit_date, '%Y-%m') as month,
        SUM(amount) as total
    FROM savings
    WHERE deposit_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(deposit_date, '%Y-%m')
    ORDER BY month ASC
") ?: [];

// Top 10 Savers in date range
$topSavers = $db->select("
    SELECT 
        m.member_code,
        u.full_name,
        SUM(s.amount) as total_savings,
        COUNT(s.saving_id) as deposit_count
    FROM members m
    JOIN users u ON m.user_id = u.user_id
    JOIN savings s ON m.member_id = s.member_id
    WHERE s.deposit_date BETWEEN ? AND ?
    GROUP BY m.member_id
    ORDER BY total_savings DESC
    LIMIT 10
", [$dateFrom, $dateTo]) ?: [];

// Savings by Type
$savingsByType = $db->select("
    SELECT 
        transaction_type,
        COUNT(*) as count,
        SUM(amount) as total
    FROM savings
    WHERE deposit_date BETWEEN ? AND ?
    GROUP BY transaction_type
", [$dateFrom, $dateTo]) ?: [];

// ============================================
// LOANS ANALYTICS
// ============================================

// Monthly Loans Trend (Last 6 months)
$monthlyLoans = $db->select("
    SELECT 
        DATE_FORMAT(disbursement_date, '%Y-%m') as month,
        COUNT(*) as count,
        SUM(loan_amount) as total
    FROM loans
    WHERE disbursement_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(disbursement_date, '%Y-%m')
    ORDER BY month ASC
") ?: [];

// Loan Status Distribution
$loansByStatus = $db->select("
    SELECT 
        status,
        COUNT(*) as count,
        SUM(loan_amount) as total
    FROM loans
    GROUP BY status
") ?: [];

// Overdue Loans
$overdueLoans = $db->select("
    SELECT 
        l.loan_number,
        m.member_code,
        u.full_name,
        l.loan_amount,
        l.amount_paid,
        l.amount_remaining as outstanding,
        COUNT(i.installment_id) as overdue_installments
    FROM loans l
    JOIN members m ON l.member_id = m.member_id
    JOIN users u ON m.user_id = u.user_id
    LEFT JOIN installments i ON l.loan_id = i.loan_id 
        AND i.status = 'overdue'
    WHERE l.status = 'disbursed'
    GROUP BY l.loan_id
    HAVING overdue_installments > 0
    ORDER BY overdue_installments DESC, outstanding DESC
    LIMIT 10
") ?: [];

// Collection Efficiency
$collectionData = $db->selectOne("
    SELECT 
        COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
        COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_count,
        COUNT(*) as total_count,
        COALESCE(SUM(CASE WHEN status = 'paid' THEN paid_amount ELSE 0 END),0) as paid_amount,
        COALESCE(SUM(installment_amount),0) as total_amount
    FROM installments
    WHERE due_date BETWEEN ? AND ?
", [$dateFrom, $dateTo]) ?: ['paid_count'=>0,'overdue_count'=>0,'total_count'=>0,'paid_amount'=>0,'total_amount'=>0];

$collectionEfficiency = $collectionData['total_count'] > 0 
    ? round(($collectionData['paid_count'] / $collectionData['total_count']) * 100, 2) 
    : 0;

// ============================================
// MEMBER ANALYTICS
// ============================================

// Member Growth (Last 6 months)
$memberGrowth = $db->select("
    SELECT 
        DATE_FORMAT(joining_date, '%Y-%m') as month,
        COUNT(*) as count
    FROM members
    WHERE joining_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(joining_date, '%Y-%m')
    ORDER BY month ASC
") ?: [];

// Member Status Distribution
$membersByStatus = $db->select("
    SELECT 
        status,
        COUNT(*) as count
    FROM members
    GROUP BY status
") ?: [];

// Member Engagement (Members with both savings and loans)
$memberEngagement = $db->selectOne("
    SELECT 
        COUNT(DISTINCT CASE WHEN s.member_id IS NOT NULL THEN s.member_id END) as savers,
        COUNT(DISTINCT CASE WHEN l.member_id IS NOT NULL THEN l.member_id END) as borrowers,
        COUNT(DISTINCT CASE WHEN s.member_id IS NOT NULL AND l.member_id IS NOT NULL THEN s.member_id END) as both_count
    FROM members m
    LEFT JOIN savings s ON m.member_id = s.member_id
    LEFT JOIN loans l ON m.member_id = l.member_id
") ?: ['savers'=>0,'borrowers'=>0,'both_count'=>0];

?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Comprehensive analytics and reports for decision making</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <button class="btn btn-primary" onclick="downloadPdfReport()">
                    <i class="bi bi-printer me-2"></i>Print Report
                </button>
                <button class="btn btn-success" onclick="exportToExcel()">
                    <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                </button>
            </div>
        </div>
    </div>
    
    <!-- Date Range Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel me-2"></i>Apply Filter
                    </button>
                    <a href="reports.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Overview Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Active Members</h6>
                            <h4 class="mb-0"><?= number_format($activeMembers) ?></h4>
                            <small class="text-muted">of <?= number_format($totalMembers) ?> total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-success bg-opacity-10 text-success">
                                <i class="bi bi-wallet2 fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Savings</h6>
                            <h4 class="mb-0"><?= formatIndianCurrency($totalSavings) ?></h4>
                            <small class="text-muted">in selected period</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-cash-coin fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Active Loans</h6>
                            <h4 class="mb-0"><?= formatIndianCurrency($activeLoansAmount) ?></h4>
                            <small class="text-muted"><?= $activeLoansCount ?> loans active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                        </div>
                        <div class "flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Outstanding</h6>
                            <h4 class="mb-0"><?= formatIndianCurrency($outstandingAmount) ?></h4>
                            <small class="text-muted">to be collected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 1 -->
    <div class="row g-3 mb-4">
        <!-- Monthly Savings Trend -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Savings Trend (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="savingsTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Savings by Type -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Savings by Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="savingsTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 2 -->
    <div class="row g-3 mb-4">
        <!-- Loan Status Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Loan Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="loanStatusChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Collection Efficiency -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Collection Efficiency</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <canvas id="collectionChart"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted d-block">Paid Installments</small>
                                <strong class="h4 text-success"><?= number_format($collectionData['paid_count']) ?></strong>
                                <span class="text-muted">/ <?= number_format($collectionData['total_count']) ?></span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Amount Collected</small>
                                <strong class="h4 text-success"><?= formatIndianCurrency($collectionData['paid_amount']) ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Overdue Installments</small>
                                <strong class="h4 text-danger"><?= number_format($collectionData['overdue_count']) ?></strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Success Rate</small>
                                <strong class="h4 text-primary"><?= $collectionEfficiency ?>%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Savers Table -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top 10 Savers</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Member</th>
                                    <th>Deposits</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($topSavers)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No data available</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($topSavers as $index => $saver): ?>
                                <tr>
                                    <td>
                                        <?php if ($index < 3): ?>
                                            <span class="badge bg-<?= ['warning', 'secondary', 'dark'][$index] ?>">
                                                #<?= $index + 1 ?>
                                            </span>
                                        <?php else: ?>
                                            #<?= $index + 1 ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($saver['full_name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($saver['member_code']) ?></small>
                                    </td>
                                    <td><?= number_format($saver['deposit_count']) ?></td>
                                    <td class="text-end">
                                        <strong class="text-success"><?= formatIndianCurrency($saver['total_savings']) ?></strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Overdue Loans Table -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Overdue Loans</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Member</th>
                                    <th>Loan #</th>
                                    <th class="text-center">Overdue</th>
                                    <th class="text-end">Outstanding</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($overdueLoans)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-check-circle text-success d-block mb-2" style="font-size: 2rem;"></i>
                                        No overdue loans!
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($overdueLoans as $loan): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($loan['full_name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($loan['member_code']) ?></small>
                                    </td>
                                    <td><span class="badge bg-dark"><?= htmlspecialchars($loan['loan_number']) ?></span></td>
                                    <td class="text-center">
                                        <span class="badge bg-danger"><?= $loan['overdue_installments'] ?> EMI</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-danger"><?= formatIndianCurrency($loan['outstanding']) ?></strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Member Analytics -->
    <div class="row g-3 mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Member Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="memberGrowthChart" height="60"></canvas>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-3">Member Engagement</h6>
                            <div class="mb-3">
                                <small class="text-muted d-block">Members with Savings</small>
                                <strong class="h4"><?= number_format($memberEngagement['savers']) ?></strong>
                                <span class="text-muted">/ <?= $totalMembers ?></span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Members with Loans</small>
                                <strong class="h4"><?= number_format($memberEngagement['borrowers']) ?></strong>
                                <span class="text-muted">/ <?= $totalMembers ?></span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Active in Both</small>
                                <strong class="h4"><?= number_format($memberEngagement['both_count']) ?></strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?= $totalMembers > 0 ? ($memberEngagement['savers'] / $totalMembers * 100) : 0 ?>%"></div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <?= $totalMembers > 0 ? round($memberEngagement['savers'] / $totalMembers * 100, 1) : 0 ?>% participation rate
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.3/dist/jspdf.plugin.autotable.min.js"></script>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default configuration
    Chart.defaults.font.family = 'Inter';
    Chart.defaults.color = '#6b7280';
    
    // Colors
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--success-color').trim();
    const warningColor = getComputedStyle(document.documentElement).getPropertyValue('--warning-color').trim();
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--danger-color').trim();
    const infoColor = getComputedStyle(document.documentElement).getPropertyValue('--info-color').trim();
    
    // ============================================
    // SAVINGS TREND CHART
    // ============================================
    const savingsLabels = <?= json_encode(array_column($monthlySavings, 'month')) ?>;
    const savingsData = <?= json_encode(array_column($monthlySavings, 'total')) ?>;
    
    new Chart(document.getElementById('savingsTrendChart'), {
        type: 'line',
        data: {
            labels: savingsLabels,
            datasets: [{
                label: 'Monthly Savings',
                data: savingsData,
                borderColor: successColor,
                backgroundColor: successColor + '20',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // ============================================
    // SAVINGS BY TYPE CHART
    // ============================================
    const savingsTypeLabels = <?= json_encode(array_column($savingsByType, 'transaction_type')) ?>;
    const savingsTypeData = <?= json_encode(array_column($savingsByType, 'total')) ?>;
    
    new Chart(document.getElementById('savingsTypeChart'), {
        type: 'doughnut',
        data: {
            labels: savingsTypeLabels.map(label => label.replace('_', ' ').toUpperCase()),
            datasets: [{
                data: savingsTypeData,
                backgroundColor: [primaryColor, successColor, warningColor, infoColor, dangerColor]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // ============================================
    // LOAN STATUS CHART
    // ============================================
    const loanStatusLabels = <?= json_encode(array_column($loansByStatus, 'status')) ?>;
    const loanStatusData = <?= json_encode(array_column($loansByStatus, 'count')) ?>;
    const statusColors = {
        'pending': warningColor,
        'approved': infoColor,
        'active': successColor,
        'completed': primaryColor,
        'rejected': dangerColor,
        'closed': '#6b7280'
    };
    
    new Chart(document.getElementById('loanStatusChart'), {
        type: 'doughnut',
        data: {
            labels: loanStatusLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
            datasets: [{
                data: loanStatusData,
                backgroundColor: loanStatusLabels.map(status => statusColors[status] || '#6b7280')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // ============================================
    // COLLECTION EFFICIENCY CHART
    // ============================================
    const collectionEfficiency = <?= $collectionEfficiency ?>;
    
    new Chart(document.getElementById('collectionChart'), {
        type: 'doughnut',
        data: {
            labels: ['Collected', 'Pending'],
            datasets: [{
                data: [collectionEfficiency, 100 - collectionEfficiency],
                backgroundColor: [successColor, '#e5e7eb'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        }
    });
    
    // ============================================
    // MEMBER GROWTH CHART
    // ============================================
    const memberGrowthLabels = <?= json_encode(array_column($memberGrowth, 'month')) ?>;
    const memberGrowthData = <?= json_encode(array_column($memberGrowth, 'count')) ?>;
    
    new Chart(document.getElementById('memberGrowthChart'), {
        type: 'bar',
        data: {
            labels: memberGrowthLabels,
            datasets: [{
                label: 'New Members',
                data: memberGrowthData,
                backgroundColor: primaryColor,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});

const reportMeta = {
    dateFrom: <?= json_encode($dateFrom) ?>,
    dateTo: <?= json_encode($dateTo) ?>,
    generatedAt: <?= json_encode(date('d-m-Y H:i')) ?>
};

const reportData = {
    overview: [
        ['Active Members', <?= json_encode((int)$activeMembers) ?>],
        ['Total Members', <?= json_encode((int)$totalMembers) ?>],
        ['Total Savings', <?= json_encode((float)$totalSavings) ?>],
        ['Active Loans Amount', <?= json_encode((float)$activeLoansAmount) ?>],
        ['Active Loans Count', <?= json_encode((int)$activeLoansCount) ?>],
        ['Outstanding Amount', <?= json_encode((float)$outstandingAmount) ?>],
        ['Collection Efficiency %', <?= json_encode((float)$collectionEfficiency) ?>]
    ],
    topSavers: <?= json_encode($topSavers) ?>,
    overdueLoans: <?= json_encode($overdueLoans) ?>,
    monthlySavings: <?= json_encode($monthlySavings) ?>,
    loansByStatus: <?= json_encode($loansByStatus) ?>,
    memberGrowth: <?= json_encode($memberGrowth) ?>
};

function buildFilename(prefix, ext) {
    return `${prefix}_${reportMeta.dateFrom}_to_${reportMeta.dateTo}.${ext}`;
}

function money(value) {
    const num = Number(value || 0);
    return `Rs ${num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

// Export to Excel function
function exportToExcel() {
    if (typeof XLSX === 'undefined') {
        alert('Excel library failed to load. Please check internet connection and try again.');
        return;
    }

    const wb = XLSX.utils.book_new();

    const summaryRows = [
        ['Bachat Gat Reports Summary'],
        [`Date Range: ${reportMeta.dateFrom} to ${reportMeta.dateTo}`],
        [`Generated At: ${reportMeta.generatedAt}`],
        [],
        ['Metric', 'Value'],
        ...reportData.overview
    ];
    const wsSummary = XLSX.utils.aoa_to_sheet(summaryRows);
    XLSX.utils.book_append_sheet(wb, wsSummary, 'Summary');

    const topSaversRows = [
        ['Member Code', 'Full Name', 'Total Savings', 'Deposit Count'],
        ...reportData.topSavers.map(row => [
            row.member_code || '',
            row.full_name || '',
            Number(row.total_savings || 0),
            Number(row.deposit_count || 0)
        ])
    ];
    const wsTopSavers = XLSX.utils.aoa_to_sheet(topSaversRows);
    XLSX.utils.book_append_sheet(wb, wsTopSavers, 'Top Savers');

    const overdueRows = [
        ['Loan Number', 'Member Code', 'Full Name', 'Loan Amount', 'Paid', 'Outstanding', 'Overdue Installments'],
        ...reportData.overdueLoans.map(row => [
            row.loan_number || '',
            row.member_code || '',
            row.full_name || '',
            Number(row.loan_amount || 0),
            Number(row.amount_paid || 0),
            Number(row.outstanding || 0),
            Number(row.overdue_installments || 0)
        ])
    ];
    const wsOverdue = XLSX.utils.aoa_to_sheet(overdueRows);
    XLSX.utils.book_append_sheet(wb, wsOverdue, 'Overdue Loans');

    const trendRows = [
        ['Month', 'Savings Total', 'New Members'],
        ...reportData.monthlySavings.map((row, index) => [
            row.month || '',
            Number(row.total || 0),
            Number((reportData.memberGrowth[index] && reportData.memberGrowth[index].count) || 0)
        ])
    ];
    const wsTrends = XLSX.utils.aoa_to_sheet(trendRows);
    XLSX.utils.book_append_sheet(wb, wsTrends, 'Trends');

    XLSX.writeFile(wb, buildFilename('Reports', 'xlsx'));
}

function downloadPdfReport() {
    if (!window.jspdf || typeof window.jspdf.jsPDF === 'undefined') {
        alert('PDF library failed to load. Please check internet connection and try again.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4');

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(16);
    doc.text('Bachat Gat Reports', 40, 40);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text(`Date Range: ${reportMeta.dateFrom} to ${reportMeta.dateTo}`, 40, 58);
    doc.text(`Generated At: ${reportMeta.generatedAt}`, 40, 72);

    let y = 90;

    doc.autoTable({
        startY: y,
        head: [['Metric', 'Value']],
        body: reportData.overview.map(item => [item[0], item[1]]),
        styles: { fontSize: 9 },
        headStyles: { fillColor: [67, 97, 238] }
    });

    y = doc.lastAutoTable.finalY + 18;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('Top 10 Savers', 40, y);

    doc.autoTable({
        startY: y + 8,
        head: [['Member Code', 'Full Name', 'Total Savings', 'Deposits']],
        body: (reportData.topSavers || []).map(row => [
            row.member_code || '',
            row.full_name || '',
            money(row.total_savings),
            String(row.deposit_count || 0)
        ]),
        styles: { fontSize: 9 },
        headStyles: { fillColor: [40, 167, 69] }
    });

    y = doc.lastAutoTable.finalY + 18;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('Overdue Loans', 40, y);

    doc.autoTable({
        startY: y + 8,
        head: [['Loan #', 'Member', 'Outstanding', 'Overdue EMI']],
        body: (reportData.overdueLoans || []).map(row => [
            row.loan_number || '',
            row.full_name || '',
            money(row.outstanding),
            String(row.overdue_installments || 0)
        ]),
        styles: { fontSize: 9 },
        headStyles: { fillColor: [220, 53, 69] }
    });

    doc.save(buildFilename('Reports', 'pdf'));
}
</script>

<style>
@media print {
    .sidebar, .topbar .btn-group, .breadcrumb { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .card { break-inside: avoid; }
}
</style>

<?php include '../includes/footer.php'; ?>
