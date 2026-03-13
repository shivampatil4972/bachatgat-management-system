<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

// Page variables
$pageTitle = 'Loans Management';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Loans', 'url' => '']
];
$includeDataTables = true;

// Get all loans with member information
$db = Database::getInstance();

$query = "
    SELECT 
        l.*,
        m.member_code,
        u.full_name,
        u.email,
        u.phone,
        (SELECT COUNT(*) FROM installments WHERE loan_id = l.loan_id AND status = 'paid') as paid_installments,
        (SELECT COUNT(*) FROM installments WHERE loan_id = l.loan_id) as total_installments
    FROM loans l
    JOIN members m ON l.member_id = m.member_id
    JOIN users u ON m.user_id = u.user_id
    ORDER BY l.application_date DESC
";

$loans = $db->select($query) ?: [];

// Get all active members for dropdown
$members = $db->select("
    SELECT m.member_id, m.member_code, u.full_name,
        COALESCE((SELECT SUM(amount) FROM savings WHERE member_id = m.member_id AND transaction_type='deposit'),0) AS total_savings
    FROM members m
    JOIN users u ON m.user_id = u.user_id
    WHERE m.status = 'active' ORDER BY u.full_name
") ?: [];

// Get loan settings
$loanSettings = $db->selectOne("SELECT * FROM loan_settings WHERE setting_id = 1") ?: [
    'setting_id'            => 1,
    'interest_rate'         => 12.00,
    'min_loan_amount'       => 5000,
    'max_loan_amount'       => 100000,
    'max_installment_months'=> 24,
    'min_installment_months'=> 3,
];

// Get summary statistics
$stats = $db->selectOne("
    SELECT 
        COUNT(*) as total_loans,
        SUM(CASE WHEN status = 'disbursed' THEN 1 ELSE 0 END) as active_loans,
        COALESCE(SUM(CASE WHEN status = 'disbursed' THEN loan_amount ELSE 0 END),0) as total_disbursed,
        COALESCE(SUM(CASE WHEN status = 'disbursed' THEN amount_paid ELSE 0 END),0) as total_collected,
        COALESCE(SUM(CASE WHEN status = 'disbursed' THEN amount_remaining ELSE 0 END),0) as total_outstanding
    FROM loans
") ?: ['total_loans'=>0,'active_loans'=>0,'total_disbursed'=>0,'total_collected'=>0,'total_outstanding'=>0];
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Manage member loans and repayments</p>
        </div>
        <div class="col-lg-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                <i class="bi bi-plus-circle me-2"></i>New Loan
            </button>
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
                                <i class="bi bi-cash-coin fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Disbursed</h6>
                            <h4 class="mb-0"><?= formatIndianCurrency($stats['total_disbursed']) ?></h4>
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
                            <h6 class="mb-1 text-muted">Total Collected</h6>
                            <h4 class="mb-0"><?= formatIndianCurrency($stats['total_collected']) ?></h4>
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
                                <i class="bi bi-hourglass-split fs-4"></i>
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
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-info bg-opacity-10 text-info">
                                <i class="bi bi-file-earmark-check fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Active Loans</h6>
                            <h4 class="mb-0"><?= number_format((int)($stats['active_loans'] ?? 0)) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loans Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="loansTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Loan #</th>
                            <th>Member</th>
                            <th>Amount</th>
                            <th>Interest</th>
                            <th>Total Payable</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Tenure</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $loan): ?>
                        <tr>
                            <td>
                                <strong class="text-primary"><?= htmlspecialchars($loan['loan_number']) ?></strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center me-2">
                                        <?= strtoupper(substr($loan['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div><?= htmlspecialchars($loan['full_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($loan['member_code']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= formatIndianCurrency($loan['loan_amount']) ?></td>
                            <td><?= number_format($loan['interest_rate'], 2) ?>%</td>
                            <td><?= formatIndianCurrency($loan['total_amount']) ?></td>
                            <td><?= formatIndianCurrency($loan['amount_paid']) ?></td>
                            <td>
                                <strong class="<?= $loan['amount_remaining'] > 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= formatIndianCurrency($loan['amount_remaining']) ?>
                                </strong>
                            </td>
                            <td>
                                <?= $loan['installment_months'] ?> months<br>
                                <small class="text-muted"><?= $loan['paid_installments'] ?>/<?= $loan['total_installments'] ?> paid</small>
                            </td>
                            <td>
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
                                <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($loan['status']) ?></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewLoan(<?= $loan['loan_id'] ?>)" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="addPayment(<?= $loan['loan_id'] ?>)" title="Add Payment">
                                        <i class="bi bi-cash"></i>
                                    </button>
                                    <?php if ($loan['status'] === 'disbursed'): ?>
                                    <button class="btn btn-outline-warning" onclick="closeLoan(<?= $loan['loan_id'] ?>, '<?= htmlspecialchars($loan['loan_number']) ?>')" title="Close Loan">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Loan Modal -->
<div class="modal fade" id="addLoanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>New Loan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addLoanForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Loan Settings:</strong> Min: <?= formatIndianCurrency($loanSettings['min_loan_amount']) ?> | 
                        Max: <?= formatIndianCurrency($loanSettings['max_loan_amount']) ?> | 
                        Interest: <?= number_format($loanSettings['interest_rate'], 2) ?>% | 
                        Max Tenure: <?= $loanSettings['max_installment_months'] ?> months
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="member_id" class="form-label">Member <span class="text-danger">*</span></label>
                            <select class="form-select" id="member_id" name="member_id" required>
                                <option value="">Select Member...</option>
                                <?php foreach ($members as $member): ?>
                                <option value="<?= $member['member_id'] ?>" data-savings="<?= $member['total_savings'] ?>">
                                    <?= htmlspecialchars($member['full_name']) ?> - <?= htmlspecialchars($member['member_code']) ?> 
                                    (Savings: <?= formatIndianCurrency($member['total_savings']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="principal_amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="principal_amount" name="loan_amount" 
                                    min="<?= $loanSettings['min_loan_amount'] ?>" 
                                    max="<?= $loanSettings['max_loan_amount'] ?>" 
                                    step="1000" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="interest_rate" class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="interest_rate" name="interest_rate" 
                                value="<?= $loanSettings['interest_rate'] ?>" 
                                min="0" max="30" step="0.1" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="tenure_months" class="form-label">Tenure (Months) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="tenure_months" name="installment_months" 
                                min="1" max="<?= $loanSettings['max_installment_months'] ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="disbursement_date" class="form-label">Disbursement Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="disbursement_date" name="disbursement_date" 
                                value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="col-12">
                            <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="2" required></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        
                        <div class="col-12" id="loanSummary" style="display: none;">
                            <div class="alert alert-success">
                                <h6>Loan Summary:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Principal Amount:</strong> ₹<span id="summary_principal">0</span><br>
                                        <strong>Interest Amount:</strong> ₹<span id="summary_interest">0</span><br>
                                        <strong>Total Payable:</strong> ₹<span id="summary_total">0</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Monthly EMI:</strong> ₹<span id="summary_emi">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addLoanBtn">
                        <i class="bi bi-save me-2"></i>Approve Loan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Loan Modal -->
<div class="modal fade" id="viewLoanModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text me-2"></i>Loan Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewLoanContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cash me-2"></i>Add Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm">
                <input type="hidden" id="payment_loan_id" name="loan_id">
                <div class="modal-body" id="addPaymentContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="addPaymentBtn">
                        <i class="bi bi-check-circle me-2"></i>Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$customJS = <<<JS
<script>
    const processingFeePercentage = 0;
    
    // Initialize DataTable
    $(document).ready(function() {
        $('#loansTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search loans..."
            }
        });
    });
    
    // Calculate loan summary
    function calculateLoanSummary() {
        const principal = parseFloat(document.getElementById('principal_amount').value) || 0;
        const rate = parseFloat(document.getElementById('interest_rate').value) || 0;
        const tenure = parseInt(document.getElementById('tenure_months').value) || 0;
        
        if (principal > 0 && rate > 0 && tenure > 0) {
            const monthlyRate = rate / 12 / 100;
            const interestAmount = (principal * rate * tenure) / (12 * 100);
            const totalAmount = principal + interestAmount;
            const emi = totalAmount / tenure;
            const processingFee = (principal * processingFeePercentage) / 100;
            
            document.getElementById('summary_principal').textContent = principal.toLocaleString('en-IN', {maximumFractionDigits: 2});
            document.getElementById('summary_interest').textContent = interestAmount.toLocaleString('en-IN', {maximumFractionDigits: 2});
            document.getElementById('summary_total').textContent = totalAmount.toLocaleString('en-IN', {maximumFractionDigits: 2});
            document.getElementById('summary_emi').textContent = emi.toLocaleString('en-IN', {maximumFractionDigits: 2});
            // processingFee element removed (not in schema)
            
            document.getElementById('loanSummary').style.display = 'block';
        } else {
            document.getElementById('loanSummary').style.display = 'none';
        }
    }
    
    // Listen for input changes
    ['principal_amount', 'interest_rate', 'tenure_months'].forEach(id => {
        document.getElementById(id).addEventListener('input', calculateLoanSummary);
    });
    
    // Continue in next comment due to length...
</script>
JS;

// Continue JavaScript
$customJS .= <<<'JS'
<script>
    // Add Loan Form Submission
    document.getElementById('addLoanForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('addLoanBtn');
        
        showLoading(submitBtn, 'Processing...');
        
        fetch('loans-process.php?action=add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addLoanModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('An error occurred. Please try again.');
            console.error('Error:', error);
        })
        .finally(() => {
            hideLoading(submitBtn);
        });
    });
    
    // View Loan Details
    function viewLoan(loanId) {
        const modal = new bootstrap.Modal(document.getElementById('viewLoanModal'));
        const content = document.getElementById('viewLoanContent');
        
        modal.show();
        
        fetch('loans-process.php?action=view&id=' + loanId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.innerHTML = generateLoanViewHTML(data.loan, data.installments);
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Failed to load loan details</div>';
                }
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-danger">An error occurred</div>';
            });
    }
    
    // Add Payment
    function addPayment(loanId) {
        const modal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
        const content = document.getElementById('addPaymentContent');
        
        document.getElementById('payment_loan_id').value = loanId;
        modal.show();
        
        fetch('loans-process.php?action=get_payment_info&id=' + loanId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.innerHTML = generatePaymentHTML(data.loan, data.pending_installment);
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Failed to load payment information</div>';
                }
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-danger">An error occurred</div>';
            });
    }
    
    // Add Payment Form Submission
    document.getElementById('addPaymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('addPaymentBtn');
        
        showLoading(submitBtn, 'Processing...');
        
        fetch('loans-process.php?action=add_payment', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addPaymentModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('An error occurred. Please try again.');
            console.error('Error:', error);
        })
        .finally(() => {
            hideLoading(submitBtn);
        });
    });
    
    // Close Loan
    function closeLoan(loanId, loanNumber) {
        if (confirm('Are you sure you want to close loan ' + loanNumber + '? This will mark it as completed.')) {
            fetch('loans-process.php?action=close&id=' + loanId, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
            });
        }
    }
    
    // Generate Loan View HTML
    function generateLoanViewHTML(loan, installments) {
        let installmentRows = '';
        installments.forEach(inst => {
            const statusBadge = inst.status === 'paid' ? 'success' : (inst.status === 'pending' ? 'warning' : 'danger');
            installmentRows += `
                <tr>
                    <td>${inst.installment_number}</td>
                    <td>${formatDate(inst.due_date)}</td>
                    <td>₹${parseFloat(inst.installment_amount).toLocaleString('en-IN')}</td>
                    <td>₹${parseFloat(inst.paid_amount).toLocaleString('en-IN')}</td>
                    <td>${inst.payment_date ? formatDate(inst.payment_date) : '-'}</td>
                    <td><span class="badge bg-${statusBadge}">${inst.status.charAt(0).toUpperCase() + inst.status.slice(1)}</span></td>
                </tr>
            `;
        });
        
        const progress = (loan.amount_paid / loan.total_amount) * 100;
        
        return `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted">Loan Number</h6>
                    <h5>${loan.loan_number}</h5>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Member</h6>
                    <h5>${loan.full_name} (${loan.member_code})</h5>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-muted mb-1">Principal</h6>
                        <h4>₹${parseFloat(loan.loan_amount).toLocaleString('en-IN')}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-muted mb-1">Interest (${loan.interest_rate}%)</h6>
                        <h4>₹${(parseFloat(loan.total_amount) - parseFloat(loan.loan_amount)).toLocaleString('en-IN')}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-muted mb-1">Total Payable</h6>
                        <h4>₹${parseFloat(loan.total_amount).toLocaleString('en-IN')}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <h6 class="text-muted mb-1">Outstanding</h6>
                        <h4 class="text-danger">₹${(parseFloat(loan.total_amount) - parseFloat(loan.amount_paid)).toLocaleString('en-IN')}</h4>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <h6>Payment Progress</h6>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: ${progress}%">
                        ${progress.toFixed(1)}%
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <strong>Tenure:</strong> ${loan.installment_months} months
                </div>
                <div class="col-md-4">
                    <strong>Disbursement Date:</strong> ${formatDate(loan.disbursement_date)}
                </div>
                <div class="col-md-4">
                    <strong>Status:</strong> <span class="badge bg-${{'pending':'warning','approved':'info','rejected':'danger','disbursed':'success','completed':'primary','defaulted':'dark'}[loan.status] || 'secondary'}">${loan.status.charAt(0).toUpperCase() + loan.status.slice(1)}</span>
                </div>
            </div>
            
            <div class="mb-4">
                <strong>Purpose:</strong><br>
                ${loan.purpose}
            </div>
            
            <h5 class="mb-3">Installment Schedule</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Paid Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${installmentRows}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    // Generate Payment HTML
    function generatePaymentHTML(loan, pendingInstallment) {
        return `
            <div class="alert alert-info">
                <strong>Loan:</strong> ${loan.loan_number} | 
                <strong>Outstanding:</strong> ₹${(parseFloat(loan.total_amount) - parseFloat(loan.amount_paid)).toLocaleString('en-IN')}
            </div>
            
            ${pendingInstallment ? `
            <div class="alert alert-warning">
                <strong>Next Installment ${pendingInstallment.installment_number}:</strong> 
                ₹${parseFloat(pendingInstallment.installment_amount).toLocaleString('en-IN')} 
                (Due: ${formatDate(pendingInstallment.due_date)})
            </div>
            ` : ''}
            
            <div class="row g-3">
                <div class="col-12">
                    <label for="payment_amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="payment_amount" name="amount" 
                            value="${pendingInstallment ? pendingInstallment.amount : ''}" 
                            min="1" step="0.01" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="payment_date" name="payment_date" 
                        value="${new Date().toISOString().split('T')[0]}" required>
                </div>
                
                <div class="col-md-6">
                    <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select class="form-select" id="payment_method" name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="upi">UPI</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <label for="payment_notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="payment_notes" name="notes" rows="2"></textarea>
                </div>
            </div>
        `;
    }
    
    // Helper function to format dates
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric' });
    }
</script>
JS;

include '../includes/footer.php';
?>
