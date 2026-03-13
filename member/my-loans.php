<?php
require_once '../config/config.php';

// Require member access
requireMember();

// Page variables
$pageTitle = 'My Loans';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'My Loans', 'url' => '']
];

// Get current member
$db = Database::getInstance();
$member = getCurrentMember();

// Get all loans for current member
$loans = $db->select(
    "SELECT * FROM loans WHERE member_id = ? ORDER BY created_at DESC",
    [$member['member_id']]
);
if (!is_array($loans)) $loans = [];

// Get active loans statistics
$activeLoanStats = $db->selectOne(
    "SELECT 
        COUNT(*) as active_count,
        COALESCE(SUM(total_amount), 0) as total_borrowed,
        COALESCE(SUM(amount_paid), 0) as total_paid,
        COALESCE(SUM(amount_remaining), 0) as total_outstanding
    FROM loans 
    WHERE member_id = ? AND status IN ('disbursed','approved')",
    [$member['member_id']]
);

$stats = is_array($activeLoanStats) ? $activeLoanStats : [
    'active_count' => 0,
    'total_borrowed' => 0,
    'total_paid' => 0,
    'total_outstanding' => 0
];
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">View and track your loan repayments</p>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-file-earmark-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Active Loans</h6>
                            <h4 class="mb-0"><?= $stats['active_count'] ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-info bg-opacity-10 text-info">
                                <i class="bi bi-cash-coin fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Borrowed</h6>
                            <h4 class="mb-0"><?= formatIndianCurrency($stats['total_borrowed']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Paid</h6>
                            <h4 class="mb-0"><?= formatIndianCurrency($stats['total_paid']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Outstanding</h6>
                            <h4 class="mb-0"><?= formatIndianCurrency($stats['total_outstanding']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loans List -->
    <?php if (empty($loans)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-cash-coin display-1 text-muted"></i>
                <h4 class="mt-3">No Loans Yet</h4>
                <p class="text-muted">You haven't taken any loans from the Bachat Gat.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($loans as $loan): 
            // Get installments for this loan
            $installments = $db->select(
                "SELECT * FROM installments WHERE loan_id = ? ORDER BY installment_number",
                [$loan['loan_id']]
            );
            if (!is_array($installments)) $installments = [];
            
            $paidInstallments = 0;
            $overdueInstallments = 0;
            $nextDueInstallment = null;
            
            foreach ($installments as $inst) {
                if ($inst['status'] === 'paid') {
                    $paidInstallments++;
                } elseif ($inst['status'] === 'overdue') {
                    $overdueInstallments++;
                }
                if (!$nextDueInstallment && $inst['status'] === 'pending') {
                    $nextDueInstallment = $inst;
                }
            }
            
            $progress = ($loan['total_amount'] > 0) ? (($loan['amount_paid'] / $loan['total_amount']) * 100) : 0;
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">Loan #<?= htmlspecialchars($loan['loan_number']) ?></h5>
                        <small class="text-muted">Disbursed on <?= formatDate($loan['disbursement_date']) ?></small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <?php
                        $statusColors = [
                            'pending'   => 'warning',
                            'approved'  => 'info',
                            'rejected'  => 'danger',
                            'disbursed' => 'success',
                            'completed' => 'primary',
                            'defaulted' => 'dark'
                        ];
                        $statusColor = $statusColors[$loan['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $statusColor ?> fs-6"><?= ucfirst($loan['status']) ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Principal Amount</h6>
                        <h5><?= formatIndianCurrency($loan['loan_amount']) ?></h5>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Interest (<?= number_format($loan['interest_rate'], 2) ?>%)</h6>
                        <h5><?= formatIndianCurrency($loan['total_amount'] - $loan['loan_amount']) ?></h5>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Total Payable</h6>
                        <h5><?= formatIndianCurrency($loan['total_amount']) ?></h5>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Monthly EMI</h6>
                        <h5><?= formatIndianCurrency($loan['monthly_installment']) ?></h5>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Payment Progress:</strong> <?= number_format($progress, 1) ?>%</span>
                        <span><?= $paidInstallments ?>/<?= count($installments) ?> installments paid</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progress ?>%">
                            ₹<?= number_format($loan['amount_paid'], 0) ?>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-cash-stack fs-4 text-success me-2"></i>
                            <div>
                                <small class="text-muted d-block">Amount Paid</small>
                                <strong><?= formatIndianCurrency($loan['amount_paid']) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hourglass-split fs-4 text-warning me-2"></i>
                            <div>
                                <small class="text-muted d-block">Outstanding</small>
                                <strong class="text-danger"><?= formatIndianCurrency($loan['amount_remaining']) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-event fs-4 text-info me-2"></i>
                            <div>
                                <small class="text-muted d-block">Tenure</small>
                                <strong><?= $loan['installment_months'] ?> months</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($nextDueInstallment): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Next Payment Due:</strong> ₹<?= number_format($nextDueInstallment['installment_amount'], 2) ?> 
                    on <?= formatDate($nextDueInstallment['due_date']) ?>
                    (Installment #<?= $nextDueInstallment['installment_number'] ?>)
                </div>
                <?php endif; ?>
                
                <?php if ($overdueInstallments > 0): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> You have <?= $overdueInstallments ?> overdue installment(s)
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong>Purpose:</strong> <?= htmlspecialchars($loan['purpose']) ?>
                </div>
                
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#installments_<?= $loan['loan_id'] ?>">
                    <i class="bi bi-list-ul me-2"></i>View Installment Schedule
                </button>
                
                <div class="collapse mt-3" id="installments_<?= $loan['loan_id'] ?>">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Paid Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($installments as $inst): ?>
                                <tr class="<?= $inst['status'] === 'overdue' ? 'table-danger' : ($inst['status'] === 'paid' ? 'table-success' : '') ?>">
                                    <td><?= $inst['installment_number'] ?></td>
                                    <td><?= formatDate($inst['due_date']) ?></td>
                                    <td>₹<?= number_format($inst['installment_amount'], 2) ?></td>
                                    <td>₹<?= number_format($inst['paid_amount'], 2) ?></td>
                                    <td><?= $inst['payment_date'] ? formatDate($inst['payment_date']) : '-' ?></td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'paid' => 'success',
                                            'pending' => 'warning',
                                            'overdue' => 'danger',
                                            'waived' => 'secondary'
                                        ];
                                        $badge = $statusBadges[$inst['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($inst['status']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
