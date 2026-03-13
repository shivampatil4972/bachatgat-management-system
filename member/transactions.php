<?php
require_once '../config/config.php';

// Require member access
requireMember();

// Page variables
$pageTitle = 'Transaction History';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Transactions', 'url' => '']
];
$includeDataTables = true;

// Get current member
$db = Database::getInstance();
$user = getCurrentUser();
$member = getCurrentMember();

// Get filter parameters
$filterType = $_GET['type'] ?? 'all';
$filterCategory = $_GET['category'] ?? 'all';
$filterDateFrom = $_GET['date_from'] ?? '';
$filterDateTo = $_GET['date_to'] ?? '';

// Build query
$query = "SELECT * FROM transactions WHERE member_id = ?";
$params = [$member['member_id']];

if ($filterType !== 'all') {
    $query .= " AND transaction_type = ?";
    $params[] = $filterType;
}

if (!empty($filterDateFrom)) {
    $query .= " AND transaction_date >= ?";
    $params[] = $filterDateFrom;
}

if (!empty($filterDateTo)) {
    $query .= " AND transaction_date <= ?";
    $params[] = $filterDateTo;
}

$query .= " ORDER BY transaction_date DESC, created_at DESC";

$transactions = $db->select($query, $params);
if (!is_array($transactions)) $transactions = [];

// Get summary statistics
$statsResult = $db->selectOne(
    "SELECT 
        COUNT(*) as total_transactions,
        COALESCE(SUM(CASE WHEN transaction_type IN ('saving_deposit','loan_disbursement') THEN amount ELSE 0 END), 0) as total_credit,
        COALESCE(SUM(CASE WHEN transaction_type IN ('saving_withdrawal','installment_payment') THEN amount ELSE 0 END), 0) as total_debit
    FROM transactions 
    WHERE member_id = ?",
    [$member['member_id']]
);

$stats = is_array($statsResult) ? $statsResult : [
    'total_transactions' => 0,
    'total_credit' => 0,
    'total_debit' => 0
];
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">View all your financial transactions</p>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-arrow-repeat fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Transactions</h6>
                            <h4 class="mb-0"><?= number_format($stats['total_transactions']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-down-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Credits</h6>
                            <h4 class="mb-0 text-success"><?= formatCurrency($stats['total_credit']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-arrow-up-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Debits</h6>
                            <h4 class="mb-0 text-danger"><?= formatCurrency($stats['total_debit']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All Types</option>
                        <option value="saving_deposit" <?= $filterType === 'saving_deposit' ? 'selected' : '' ?>>Saving Deposit</option>
                        <option value="saving_withdrawal" <?= $filterType === 'saving_withdrawal' ? 'selected' : '' ?>>Saving Withdrawal</option>
                        <option value="loan_disbursement" <?= $filterType === 'loan_disbursement' ? 'selected' : '' ?>>Loan Disbursement</option>
                        <option value="installment_payment" <?= $filterType === 'installment_payment' ? 'selected' : '' ?>>Installment Payment</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($filterDateFrom) ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($filterDateTo) ?>">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                    <a href="transactions.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-receipt display-1 text-muted"></i>
                    <h4 class="mt-3">No Transactions Found</h4>
                    <p class="text-muted">Your transaction history will appear here.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="transactionsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><?= formatDate($txn['transaction_date']) ?></td>
                                <td>
                                    <?php
                                    $isCredit = in_array($txn['transaction_type'], ['saving_deposit', 'loan_disbursement']);
                                    $typeLabels = [
                                        'saving_deposit'    => 'Saving Deposit',
                                        'saving_withdrawal' => 'Saving Withdrawal',
                                        'loan_disbursement' => 'Loan Disbursement',
                                        'installment_payment' => 'Installment Payment',
                                    ];
                                    $typeColors = [
                                        'saving_deposit'    => 'success',
                                        'saving_withdrawal' => 'danger',
                                        'loan_disbursement' => 'info',
                                        'installment_payment' => 'warning',
                                    ];
                                    $label = $typeLabels[$txn['transaction_type']] ?? ucwords(str_replace('_', ' ', $txn['transaction_type']));
                                    $color = $typeColors[$txn['transaction_type']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                </td>
                                <td>
                                    <strong class="<?= $isCredit ? 'text-success' : 'text-danger' ?>">
                                        <?= $isCredit ? '+' : '-' ?><?= formatCurrency($txn['amount']) ?>
                                    </strong>
                                </td>
                                <td><?= htmlspecialchars($txn['description'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$customJS = <<<'JS'
<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            order: [[0, 'desc']], // Sort by date
            pageLength: 25,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search transactions..."
            }
        });
    });
</script>
JS;

include '../includes/footer.php';
?>
