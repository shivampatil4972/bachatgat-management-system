<?php
/**
 * Admin Dashboard (Refactored)
 * Using new config-v2.php and services
 */

// Load new configuration and auto-loader
require_once '../config/config-v2.php';
require_once '../config/db.php';

// Check authorization (middleware)
Middleware\AdminMiddleware::handle();

// Initialize services
$memberService = new Services\MemberService(new Database());
$loanService = new Services\LoanService(new Database());
$savingsService = new Services\SavingsService(new Database());
$reportService = new Services\ReportService(new Database());

// Get dashboard data
$memberStats = $memberService->getStats();
$loanStats = $loanService->getStats();
$savingsStats = $savingsService->getStats();
$financialSummary = $reportService->getFinancialSummary();
$defaulters = $reportService->getDefaultersList(10);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bachat Gat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px 0; }
        .dashboard-container { max-width: 1200px; margin: 0 auto; }
        .stat-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #667eea; font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 10px; }
        .stat-value { font-size: 32px; font-weight: bold; color: #333; }
        .stat-icon { float: right; font-size: 40px; color: rgba(102, 126, 234, 0.2); }
        .page-header { color: white; margin-bottom: 30px; }
        .page-header h1 { font-size: 32px; margin-bottom: 10px; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .recent-activity { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .activity-item { border-bottom: 1px solid #eee; padding: 15px 0; }
        .activity-item:last-child { border-bottom: none; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-active { background: #d4edda; color: #155724; }
        .status-overdue { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>📊 Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>! Here's your overview.</p>
        </div>

        <!-- Key Statistics -->
        <div class="card-grid">
            <!-- Members Card -->
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <h3>Total Members</h3>
                <div class="stat-value"><?php 
                    if ($memberStats['success']) {
                        echo number_format($memberStats['data']['total'] ?? 0);
                    } else {
                        echo '0';
                    }
                ?></div>
                <small class="text-muted">Active members in system</small>
            </div>

            <!-- Savings Card -->
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-piggy-bank-fill"></i></div>
                <h3>Total Savings</h3>
                <div class="stat-value">₹<?php 
                    if ($financialSummary['success']) {
                        echo number_format($financialSummary['data']['total_savings'] ?? 0, 0);
                    } else {
                        echo '0';
                    }
                ?></div>
                <small class="text-muted">Pooled savings</small>
            </div>

            <!-- Loans Card -->
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                <h3>Outstanding Loans</h3>
                <div class="stat-value">₹<?php 
                    if ($financialSummary['success']) {
                        echo number_format($financialSummary['data']['outstanding_loans'] ?? 0, 0);
                    } else {
                        echo '0';
                    }
                ?></div>
                <small class="text-muted">To be recovered</small>
            </div>

            <!-- Interest Card -->
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <h3>Interest Earned</h3>
                <div class="stat-value">₹<?php 
                    if ($financialSummary['success']) {
                        echo number_format($financialSummary['data']['total_interest_earned'] ?? 0, 0);
                    } else {
                        echo '0';
                    }
                ?></div>
                <small class="text-muted">On member savings</small>
            </div>
        </div>

        <!-- Detailed Statistics Row -->
        <div class="row">
            <!-- Loan Statistics -->
            <div class="col-md-6 mb-4">
                <div class="recent-activity">
                    <h5 class="mb-4"><i class="bi bi-bar-chart"></i> Loan Statistics</h5>
                    <?php if ($loanStats['success']): ?>
                    <table class="table table-sm">
                        <tr>
                            <td>Active Loans:</td>
                            <td><strong><?php echo $loanStats['data']['active_loans'] ?? 0; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Pending Approvals:</td>
                            <td><strong><?php echo $loanStats['data']['pending_loans'] ?? 0; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Completed Loans:</td>
                            <td><strong><?php echo $loanStats['data']['completed_loans'] ?? 0; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Total Disbursed:</td>
                            <td><strong>₹<?php echo number_format($loanStats['data']['total_disbursed'] ?? 0, 0); ?></strong></td>
                        </tr>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Savings Statistics -->
            <div class="col-md-6 mb-4">
                <div class="recent-activity">
                    <h5 class="mb-4"><i class="bi bi-bar-chart"></i> Savings Statistics</h5>
                    <?php if ($savingsStats['success']): ?>
                    <table class="table table-sm">
                        <tr>
                            <td>Active Savers:</td>
                            <td><strong><?php echo $savingsStats['data']['active_members'] ?? 0; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Average Savings:</td>
                            <td><strong>₹<?php echo number_format($savingsStats['data']['average_savings'] ?? 0, 0); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Highest Savings:</td>
                            <td><strong>₹<?php echo number_format($savingsStats['data']['max_savings'] ?? 0, 0); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Total Interest:</td>
                            <td><strong>₹<?php echo number_format($savingsStats['data']['total_interest'] ?? 0, 0); ?></strong></td>
                        </tr>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Defaulters List -->
        <div class="recent-activity mb-4">
            <h5 class="mb-4"><i class="bi bi-exclamation-triangle"></i> Members with Pending Payments</h5>
            <?php if ($defaulters['success'] && !empty($defaulters['data'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Member</th>
                            <th>Contact</th>
                            <th>Pending Installments</th>
                            <th>Total Due</th>
                            <th>Last Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($defaulters['data'] as $defaulter): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($defaulter['member_code'] . ' - ' . $defaulter['first_name'] . ' ' . $defaulter['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($defaulter['phone']); ?></td>
                            <td><span class="badge bg-warning"><?php echo $defaulter['pending_installments']; ?></span></td>
                            <td>₹<?php echo number_format($defaulter['total_due'], 0); ?></td>
                            <td><?php echo date('d-M-Y', strtotime($defaulter['last_due_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> No pending payments!</div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="recent-activity">
            <h5 class="mb-4"><i class="bi bi-lightning"></i> Quick Actions</h5>
            <div class="btn-group-vertical w-100" role="group">
                <a href="members.php" class="btn btn-outline-primary text-start"><i class="bi bi-people"></i> Manage Members</a>
                <a href="loans.php" class="btn btn-outline-primary text-start"><i class="bi bi-cash-coin"></i> Manage Loans</a>
                <a href="savings.php" class="btn btn-outline-primary text-start"><i class="bi bi-piggy-bank"></i> Manage Savings</a>
                <a href="reports.php" class="btn btn-outline-primary text-start"><i class="bi bi-bar-chart"></i> View Reports</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
