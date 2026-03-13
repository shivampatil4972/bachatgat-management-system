<?php
require_once '../config/config.php';

requireAdmin();

$pageTitle = 'Transactions';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Transactions', 'url' => '']
];
$includeDataTables = true;

$db = Database::getInstance();

// Filters
$filterType   = $_GET['type']   ?? '';
$filterMember = $_GET['member'] ?? '';
$filterFrom   = $_GET['from']   ?? '';
$filterTo     = $_GET['to']     ?? '';

// Build query
$where  = ['1=1'];
$params = [];

if ($filterType) {
    $where[]  = 't.transaction_type = ?';
    $params[] = $filterType;
}
if ($filterMember) {
    $where[]  = 't.member_id = ?';
    $params[] = (int)$filterMember;
}
if ($filterFrom) {
    $where[]  = 't.transaction_date >= ?';
    $params[] = $filterFrom;
}
if ($filterTo) {
    $where[]  = 't.transaction_date <= ?';
    $params[] = $filterTo;
}

$whereClause = implode(' AND ', $where);

$transactions = $db->select("
    SELECT
        t.*,
        m.member_code,
        u.full_name,
        u.phone,
        ru.full_name AS recorded_by_name
    FROM transactions t
    JOIN members m  ON t.member_id   = m.member_id
    JOIN users u    ON m.user_id     = u.user_id
    LEFT JOIN users ru ON t.recorded_by = ru.user_id
    WHERE $whereClause
    ORDER BY t.transaction_date DESC, t.transaction_id DESC
", $params) ?: [];

// Summary stats (unfiltered)
$stats = $db->selectOne("
    SELECT
        COUNT(*)                                                               AS total_count,
        COALESCE(SUM(CASE WHEN transaction_type IN ('saving_deposit','loan_disbursement')  THEN amount ELSE 0 END), 0) AS total_credit,
        COALESCE(SUM(CASE WHEN transaction_type IN ('saving_withdrawal','installment_payment') THEN amount ELSE 0 END), 0) AS total_debit
    FROM transactions
") ?: ['total_count' => 0, 'total_credit' => 0, 'total_debit' => 0];

// Members list for filter dropdown
$members = $db->select("
    SELECT m.member_id, m.member_code, u.full_name
    FROM members m JOIN users u ON m.user_id = u.user_id
    WHERE m.status = 'active' ORDER BY u.full_name
") ?: [];

// Type labels
$typeLabels = [
    'saving_deposit'     => ['label' => 'Saving Deposit',     'badge' => 'success'],
    'saving_withdrawal'  => ['label' => 'Saving Withdrawal',  'badge' => 'warning'],
    'loan_disbursement'  => ['label' => 'Loan Disbursement',  'badge' => 'primary'],
    'installment_payment'=> ['label' => 'Installment Payment','badge' => 'info'],
];
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">View all financial transactions</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar-sm rounded bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-arrow-left-right fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Total Transactions</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_count']) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar-sm rounded bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-arrow-down-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Total Credits</h6>
                        <h3 class="mb-0 text-success"><?= formatIndianCurrency($stats['total_credit']) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar-sm rounded bg-danger bg-opacity-10 text-danger me-3">
                        <i class="bi bi-arrow-up-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Total Debits</h6>
                        <h3 class="mb-0 text-danger"><?= formatIndianCurrency($stats['total_debit']) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Transaction Type</label>
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <?php foreach ($typeLabels as $val => $info): ?>
                        <option value="<?= $val ?>" <?= $filterType === $val ? 'selected' : '' ?>><?= $info['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Member</label>
                    <select class="form-select" name="member">
                        <option value="">All Members</option>
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m['member_id'] ?>" <?= $filterMember == $m['member_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['full_name']) ?> (<?= $m['member_code'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($filterFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($filterTo) ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="transactions.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($transactions)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                No transactions found.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table id="transactionsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx):
                            $typeInfo = $typeLabels[$tx['transaction_type']] ?? ['label' => ucfirst($tx['transaction_type']), 'badge' => 'secondary'];
                            $isCredit = in_array($tx['transaction_type'], ['saving_deposit', 'loan_disbursement']);
                        ?>
                        <tr>
                            <td><small class="text-muted">#<?= $tx['transaction_id'] ?></small></td>
                            <td><?= date('d-m-Y', strtotime($tx['transaction_date'])) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;font-size:.85rem">
                                        <?= strtoupper(substr($tx['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?= htmlspecialchars($tx['full_name']) ?></div>
                                        <small class="text-muted"><?= $tx['member_code'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $typeInfo['badge'] ?>"><?= $typeInfo['label'] ?></span>
                            </td>
                            <td>
                                <strong class="text-<?= $isCredit ? 'success' : 'danger' ?>">
                                    <?= $isCredit ? '+' : '-' ?><?= formatIndianCurrency($tx['amount']) ?>
                                </strong>
                            </td>
                            <td><small><?= htmlspecialchars($tx['description'] ?? '-') ?></small></td>
                            <td><small class="text-muted"><?= htmlspecialchars($tx['recorded_by_name'] ?? '-') ?></small></td>
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
$(document).ready(function() {
    $('#transactionsTable').DataTable({
        order: [[0, 'desc']],
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
