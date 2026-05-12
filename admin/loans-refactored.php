<?php
/**
 * Admin Loans (Refactored)
 * Using new config-v2.php and LoanService
 */

// Load configuration and database
require_once '../config/config-v2.php';
require_once '../config/db.php';

// Check authorization
Middleware\AdminMiddleware::handle();

// Initialize database and service
$db = new Database();
$loanService = new Services\LoanService($db);

// Handle actions
$action = $_GET['action'] ?? 'list';
$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'list';
    
    if ($action === 'create') {
        $data = [
            'member_id' => $_POST['member_id'] ?? null,
            'loan_amount' => $_POST['loan_amount'] ?? null,
            'tenure_months' => $_POST['tenure_months'] ?? null,
            'interest_rate' => $_POST['interest_rate'] ?? 12.00,
            'purpose' => $_POST['purpose'] ?? null
        ];
        $response = $loanService->create($data);
    } elseif ($action === 'approve') {
        $loan_id = $_POST['loan_id'] ?? null;
        $response = $loanService->approve($loan_id);
    } elseif ($action === 'disburse') {
        $loan_id = $_POST['loan_id'] ?? null;
        $response = $loanService->disburse($loan_id);
    }
}

// Get list of loans
if ($action === 'list' || !$response) {
    $page = $_GET['page'] ?? 1;
    $filters = [
        'status' => $_GET['status'] ?? null
    ];
    $response = $loanService->getAll($page, 50, $filters);
}

// Get members for dropdown
$members = $db->select("
    SELECT m.member_id, m.member_code, u.first_name, u.last_name 
    FROM members m 
    INNER JOIN users u ON m.user_id = u.user_id 
    WHERE m.status = 'active'
    ORDER BY u.first_name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Loans - Bachat Gat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f5f5f5; padding: 20px 0; }
        .container { max-width: 1200px; }
        .page-header { margin-bottom: 30px; }
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #cfe2ff; color: #084298; }
        .status-disbursed { background: #d1e7dd; color: #0f5132; }
        .status-completed { background: #e2e3e5; color: #383d41; }
        .action-buttons { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="bi bi-cash-coin"></i> Manage Loans</h1>
            <p class="text-muted">Create, approve, and disburse loans to members</p>
        </div>

        <!-- Response Messages -->
        <?php if ($response): ?>
            <?php if ($response['success']): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($response['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php else: ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($response['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Create Loan Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Create New Loan</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="col-md-3">
                        <label for="member_id" class="form-label">Member</label>
                        <select class="form-select" id="member_id" name="member_id" required>
                            <option value="">Select Member</option>
                            <?php foreach ($members as $member): ?>
                            <option value="<?php echo $member['member_id']; ?>">
                                <?php echo htmlspecialchars($member['member_code'] . ' - ' . $member['first_name'] . ' ' . $member['last_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="loan_amount" class="form-label">Loan Amount (₹)</label>
                        <input type="number" class="form-control" id="loan_amount" name="loan_amount" required min="1000" step="100">
                    </div>

                    <div class="col-md-2">
                        <label for="tenure_months" class="form-label">Tenure (Months)</label>
                        <input type="number" class="form-control" id="tenure_months" name="tenure_months" required min="1" max="120">
                    </div>

                    <div class="col-md-2">
                        <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                        <input type="number" class="form-control" id="interest_rate" name="interest_rate" value="12.00" min="0" max="50" step="0.01">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus"></i> Create Loan
                        </button>
                    </div>

                    <div class="col-12">
                        <label for="purpose" class="form-label">Purpose</label>
                        <input type="text" class="form-control" id="purpose" name="purpose" placeholder="Loan purpose" required>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo ($_GET['status'] ?? null) === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo ($_GET['status'] ?? null) === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="disbursed" <?php echo ($_GET['status'] ?? null) === 'disbursed' ? 'selected' : ''; ?>>Disbursed</option>
                            <option value="completed" <?php echo ($_GET['status'] ?? null) === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                        <a href="loans.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loans List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Loans List</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Member</th>
                            <th>Amount</th>
                            <th>EMI</th>
                            <th>Tenure</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($response['success'] && !empty($response['data']['data'])): ?>
                            <?php foreach ($response['data']['data'] as $loan): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($loan['member_code']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']); ?></small>
                                </td>
                                <td>₹<?php echo number_format($loan['loan_amount'], 0); ?></td>
                                <td>₹<?php echo number_format($loan['emi_amount'], 0); ?></td>
                                <td><?php echo $loan['tenure_months']; ?> months</td>
                                <td><?php echo date('d-M-Y', strtotime($loan['applied_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($loan['status']); ?>">
                                        <?php echo ucfirst($loan['status']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="loan-details.php?id=<?php echo $loan['loan_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if ($loan['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this loan?')">
                                            <i class="bi bi-check"></i> Approve
                                        </button>
                                    </form>
                                    <?php elseif ($loan['status'] === 'approved'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="disburse">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Disburse this loan?')">
                                            <i class="bi bi-send"></i> Disburse
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> No loans found
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($response['success']): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $response['data']['pages']; $i++): ?>
                <li class="page-item <?php echo $i === $response['data']['page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $_GET['status'] ? '&status=' . $_GET['status'] : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
