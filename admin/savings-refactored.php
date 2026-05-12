<?php
/**
 * Admin Savings (Refactored)
 * Using new config-v2.php and SavingsService
 */

// Load configuration and database
require_once '../config/config-v2.php';
require_once '../config/db.php';

// Check authorization
Middleware\AdminMiddleware::handle();

// Initialize database and service
$db = new Database();
$savingsService = new Services\SavingsService($db);

// Handle actions
$action = $_GET['action'] ?? 'list';
$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'list';
    
    if ($action === 'deposit') {
        $data = [
            'member_id' => $_POST['member_id'] ?? null,
            'amount' => $_POST['amount'] ?? null,
            'deposit_date' => $_POST['deposit_date'] ?? date('Y-m-d')
        ];
        $response = $savingsService->deposit($data);
    } elseif ($action === 'withdraw') {
        $data = [
            'member_id' => $_POST['member_id'] ?? null,
            'amount' => $_POST['amount'] ?? null,
            'withdrawal_date' => $_POST['withdrawal_date'] ?? date('Y-m-d')
        ];
        $response = $savingsService->withdraw($data);
    }
}

// Get list of savings
if ($action === 'list' || !$response) {
    $page = $_GET['page'] ?? 1;
    $response = $savingsService->getAll($page, 50);
}

// Get members for dropdown
$members = $db->select("
    SELECT m.member_id, m.member_code, u.first_name, u.last_name, s.total_savings
    FROM members m 
    INNER JOIN users u ON m.user_id = u.user_id 
    LEFT JOIN savings s ON m.member_id = s.member_id
    WHERE m.status = 'active'
    ORDER BY u.first_name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Savings - Bachat Gat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f5f5f5; padding: 20px 0; }
        .container { max-width: 1200px; }
        .page-header { margin-bottom: 30px; }
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="bi bi-piggy-bank"></i> Manage Savings</h1>
            <p class="text-muted">Record deposits, withdrawals, and view member savings</p>
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

        <!-- Deposit & Withdrawal Forms -->
        <div class="row mb-4">
            <!-- Deposit Form -->
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-arrow-down-circle"></i> Record Deposit</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="deposit">
                            
                            <div class="mb-3">
                                <label for="deposit_member" class="form-label">Member</label>
                                <select class="form-select" id="deposit_member" name="member_id" required>
                                    <option value="">Select Member</option>
                                    <?php foreach ($members as $member): ?>
                                    <option value="<?php echo $member['member_id']; ?>">
                                        <?php echo htmlspecialchars($member['member_code'] . ' - ' . $member['first_name'] . ' ' . $member['last_name']); ?> 
                                        (Current: ₹<?php echo number_format($member['total_savings'] ?? 0, 0); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="deposit_amount" class="form-label">Amount (₹)</label>
                                <input type="number" class="form-control" id="deposit_amount" name="amount" required min="0.01" step="0.01">
                            </div>

                            <div class="mb-3">
                                <label for="deposit_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="deposit_date" name="deposit_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-plus"></i> Record Deposit
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Withdrawal Form -->
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bi bi-arrow-up-circle"></i> Record Withdrawal</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="withdraw">
                            
                            <div class="mb-3">
                                <label for="withdraw_member" class="form-label">Member</label>
                                <select class="form-select" id="withdraw_member" name="member_id" required>
                                    <option value="">Select Member</option>
                                    <?php foreach ($members as $member): ?>
                                    <option value="<?php echo $member['member_id']; ?>">
                                        <?php echo htmlspecialchars($member['member_code'] . ' - ' . $member['first_name'] . ' ' . $member['last_name']); ?> 
                                        (Available: ₹<?php echo number_format($member['total_savings'] ?? 0, 0); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="withdraw_amount" class="form-label">Amount (₹)</label>
                                <input type="number" class="form-control" id="withdraw_amount" name="amount" required min="0.01" step="0.01">
                            </div>

                            <div class="mb-3">
                                <label for="withdraw_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="withdraw_date" name="withdrawal_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-arrow-up"></i> Record Withdrawal
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Savings List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Members Savings Summary</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Member</th>
                            <th>Email</th>
                            <th>Total Savings</th>
                            <th>Interest Earned</th>
                            <th>Last Deposit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($response['success'] && !empty($response['data']['data'])): ?>
                            <?php foreach ($response['data']['data'] as $saving): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($saving['member_code']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($saving['first_name'] . ' ' . $saving['last_name']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($saving['email']); ?></td>
                                <td>
                                    <strong>₹<?php echo number_format($saving['total_savings'], 0); ?></strong>
                                </td>
                                <td>
                                    <small class="text-success">₹<?php echo number_format($saving['interest_earned'], 0); ?></small>
                                </td>
                                <td><?php echo date('d-M-Y', strtotime($saving['last_deposit_date'])); ?></td>
                                <td>
                                    <a href="member-savings.php?id=<?php echo $saving['savings_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> No savings records found
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
                    <a class="page-link" href="?page=<?php echo $i; ?>">
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
