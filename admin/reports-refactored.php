<?php
/**
 * Admin Reports (Refactored)
 * Using new config-v2.php and ReportService
 */

// Load configuration and database
require_once '../config/config-v2.php';
require_once '../config/db.php';

// Check authorization
Middleware\AdminMiddleware::handle();

// Initialize database and service
$db = new Database();
$reportService = new Services\ReportService($db);

// Get date filters
$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$reportType = $_GET['report'] ?? 'summary';

// Get reports based on type
$savingsReport = $reportService->getSavingsReport($startDate, $endDate);
$loanReport = $reportService->getLoanReport($startDate, $endDate);
$memberReport = $reportService->getMemberReport($startDate, $endDate);
$financialSummary = $reportService->getFinancialSummary();
$defaulters = $reportService->getDefaultersList();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Bachat Gat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f5f5f5; padding: 20px 0; }
        .container { max-width: 1200px; }
        .page-header { margin-bottom: 30px; }
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; }
        .stat-box h6 { font-weight: 600; opacity: 0.9; margin-bottom: 5px; }
        .stat-box .value { font-size: 24px; font-weight: bold; }
        .tabs-nav { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="bi bi-bar-chart"></i> Financial Reports</h1>
            <p class="text-muted">Comprehensive financial analysis and reporting</p>
        </div>

        <!-- Date Filter -->
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter"></i> Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Overall Financial Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Overall Financial Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($financialSummary['success']): ?>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h6><i class="bi bi-piggy-bank"></i> Total Savings</h6>
                                <div class="value">₹<?php echo number_format($financialSummary['data']['total_savings'] ?? 0, 0); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h6><i class="bi bi-cash-coin"></i> Outstanding Loans</h6>
                                <div class="value">₹<?php echo number_format($financialSummary['data']['outstanding_loans'] ?? 0, 0); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h6><i class="bi bi-graph-up"></i> Interest Earned</h6>
                                <div class="value">₹<?php echo number_format($financialSummary['data']['total_interest_earned'] ?? 0, 0); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h6><i class="bi bi-people"></i> Active Members</h6>
                                <div class="value"><?php echo $financialSummary['data']['active_members'] ?? 0; ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Savings Report -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-piggy-bank"></i> Savings Report (<?php echo date('M d, Y', strtotime($startDate)); ?> - <?php echo date('M d, Y', strtotime($endDate)); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if ($savingsReport['success']): ?>
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-muted">Active Savers</h6>
                        <h4><?php echo $savingsReport['data']['active_savers'] ?? 0; ?></h4>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Total Deposits</h6>
                        <h4>₹<?php echo number_format($savingsReport['data']['total_deposits'] ?? 0, 0); ?></h4>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Total Withdrawals</h6>
                        <h4>₹<?php echo number_format($savingsReport['data']['total_withdrawals'] ?? 0, 0); ?></h4>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Total Savings</h6>
                        <h4>₹<?php echo number_format($savingsReport['data']['total_savings'] ?? 0, 0); ?></h4>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Interest Earned</h6>
                        <h4>₹<?php echo number_format($savingsReport['data']['total_interest_earned'] ?? 0, 0); ?></h4>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Average per Member</h6>
                        <h4>₹<?php echo number_format($savingsReport['data']['average_savings_per_member'] ?? 0, 0); ?></h4>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Loan Report -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Loan Report (<?php echo date('M d, Y', strtotime($startDate)); ?> - <?php echo date('M d, Y', strtotime($endDate)); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if ($loanReport['success']): ?>
                <div class="row">
                    <div class="col-md-3">
                        <h6 class="text-muted">Total Loans</h6>
                        <h4><?php echo $loanReport['data']['total_loans'] ?? 0; ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Pending Approval</h6>
                        <h4><?php echo $loanReport['data']['pending_loans'] ?? 0; ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Active Loans</h6>
                        <h4><?php echo $loanReport['data']['active_loans'] ?? 0; ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Completed Loans</h6>
                        <h4><?php echo $loanReport['data']['completed_loans'] ?? 0; ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Total Disbursed</h6>
                        <h4>₹<?php echo number_format($loanReport['data']['total_disbursed'] ?? 0, 0); ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Outstanding Principal</h6>
                        <h4>₹<?php echo number_format($loanReport['data']['outstanding_principal'] ?? 0, 0); ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Est. Interest Income</h6>
                        <h4>₹<?php echo number_format($loanReport['data']['estimated_interest'] ?? 0, 0); ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Payment Recovery</h6>
                        <h4><?php echo $loanReport['data']['payment_recovery_rate'] ?? 0; ?>%</h4>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Member Report -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Member Report</h5>
            </div>
            <div class="card-body">
                <?php if ($memberReport['success']): ?>
                <div class="row">
                    <div class="col-md-3">
                        <h6 class="text-muted">Total Members</h6>
                        <h4><?php echo $memberReport['data']['total_members'] ?? 0; ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Active Members</h6>
                        <h4><?php echo $memberReport['data']['active_members'] ?? 0; ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Inactive Members</h6>
                        <h4><?php echo $memberReport['data']['inactive_members'] ?? 0; ?></h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted">Engaged Members</h6>
                        <h4><?php echo $memberReport['data']['members_engaged'] ?? 0; ?></h4>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Defaulters List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Members with Pending Payments</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Member Code</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Pending Installments</th>
                            <th>Total Due</th>
                            <th>Last Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($defaulters['success'] && !empty($defaulters['data'])): ?>
                            <?php foreach ($defaulters['data'] as $defaulter): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($defaulter['member_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($defaulter['first_name'] . ' ' . $defaulter['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($defaulter['phone']); ?></td>
                                <td><span class="badge bg-warning"><?php echo $defaulter['pending_installments']; ?></span></td>
                                <td>₹<?php echo number_format($defaulter['total_due'], 0); ?></td>
                                <td><?php echo date('d-M-Y', strtotime($defaulter['last_due_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-check-circle"></i> No pending payments
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
