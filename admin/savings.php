<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

// Page variables
$pageTitle = 'Savings Management';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Savings', 'url' => '']
];
$includeDataTables = true;

// Get all savings with member information
$db = Database::getInstance();

$query = "
    SELECT 
        s.*,
        m.member_code,
        u.full_name,
        u.email,
        u.phone
    FROM savings s
    JOIN members m ON s.member_id = m.member_id
    JOIN users u ON m.user_id = u.user_id
    ORDER BY s.deposit_date DESC, s.saving_id DESC
";

$savings = $db->select($query) ?: [];

// Get all active members for dropdown
$members = $db->select("SELECT m.member_id, m.member_code, u.full_name FROM members m JOIN users u ON m.user_id = u.user_id WHERE m.status = 'active' ORDER BY u.full_name") ?: [];

// Get summary statistics
$stats = $db->selectOne("
    SELECT 
        COUNT(*) as total_transactions,
        COALESCE(SUM(CASE WHEN transaction_type='deposit' THEN amount ELSE 0 END),0) as total_amount,
        COUNT(DISTINCT member_id) as active_members
    FROM savings
");
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Track and manage member savings</p>
        </div>
        <div class="col-lg-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSavingModal">
                <i class="bi bi-plus-circle me-2"></i>Add Savings
            </button>
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
                                <i class="bi bi-wallet2 fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Savings</h6>
                            <h3 class="mb-0"><?= formatIndianCurrency($stats['total_amount'] ?? 0) ?></h3>
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
                                <i class="bi bi-arrow-repeat fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Transactions</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_transactions']) ?></h3>
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
                            <div class="avatar-sm rounded bg-info bg-opacity-10 text-info">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Active Savers</h6>
                            <h3 class="mb-0"><?= number_format($stats['active_members']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Savings Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="savingsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Member Code</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Mode</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($savings as $saving): ?>
                        <tr>
                            <td><?= htmlspecialchars($saving['deposit_date']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center me-2">
                                        <?= strtoupper(substr($saving['full_name'], 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($saving['full_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($saving['member_code']) ?></span>
                            </td>
                            <td>
                                <strong class="text-<?= $saving['transaction_type'] === 'deposit' ? 'success' : 'danger' ?>">
                                    <?= $saving['transaction_type'] === 'deposit' ? '+' : '-' ?><?= formatIndianCurrency($saving['amount']) ?>
                                </strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= $saving['transaction_type'] === 'deposit' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($saving['transaction_type']) ?>
                                </span>
                            </td>
                            <td><?= ucfirst($saving['transaction_mode']) ?></td>
                            <td><?= htmlspecialchars($saving['remarks'] ?? '-') ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-success" onclick="editSaving(<?= $saving['saving_id'] ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteSaving(<?= $saving['saving_id'] ?>, <?= $saving['amount'] ?>)" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
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

<!-- Add Saving Modal -->
<div class="modal fade" id="addSavingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-wallet2 me-2"></i>Add Savings
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSavingForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="member_id" class="form-label">Member <span class="text-danger">*</span></label>
                            <select class="form-select" id="member_id" name="member_id" required>
                                <option value="">Select Member...</option>
                                <?php foreach ($members as $member): ?>
                                <option value="<?= $member['member_id'] ?>">
                                    <?= htmlspecialchars($member['full_name']) ?> - <?= htmlspecialchars($member['member_code']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="amount" name="amount" min="1" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="deposit_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="deposit_date" name="deposit_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="transaction_type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="transaction_type" name="transaction_type" required>
                                <option value="">Select Type...</option>
                                <option value="deposit">Deposit</option>
                                <option value="withdrawal">Withdrawal</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="transaction_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-select" id="transaction_mode" name="transaction_mode" required>
                                <option value="">Select Mode...</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online / UPI</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addSavingBtn">
                        <i class="bi bi-save me-2"></i>Add Savings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Saving Modal -->
<div class="modal fade" id="editSavingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Savings
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSavingForm">
                <input type="hidden" id="edit_saving_id" name="saving_id">
                <div class="modal-body" id="editSavingContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="editSavingBtn">
                        <i class="bi bi-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$customJS = <<<'JS'
<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#savingsTable').DataTable({
            order: [[0, 'desc']], // Sort by date
            pageLength: 25,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search savings..."
            }
        });
    });
    
    // Add Saving Form Submission
    document.getElementById('addSavingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('addSavingBtn');
        
        showLoading(submitBtn, 'Adding...');
        
        fetch('savings-process.php?action=add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addSavingModal')).hide();
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
    
    // Edit Saving
    function editSaving(savingId) {
        const modal = new bootstrap.Modal(document.getElementById('editSavingModal'));
        const content = document.getElementById('editSavingContent');
        
        modal.show();
        
        fetch('savings-process.php?action=get&id=' + savingId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.innerHTML = generateEditHTML(data.saving, data.members);
                    document.getElementById('edit_saving_id').value = data.saving.saving_id;
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Failed to load savings details</div>';
                }
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-danger">An error occurred</div>';
            });
    }
    
    // Edit Saving Form Submission
    document.getElementById('editSavingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('editSavingBtn');
        
        showLoading(submitBtn, 'Saving...');
        
        fetch('savings-process.php?action=edit', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editSavingModal')).hide();
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
    
    // Delete Saving
    function deleteSaving(savingId, amount) {
        if (confirmDelete('Are you sure you want to delete this savings transaction of ₹' + amount.toLocaleString('en-IN') + '?')) {
            fetch('savings-process.php?action=delete&id=' + savingId, {
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
    
    // Generate Edit HTML
    function generateEditHTML(saving, members) {
        let memberOptions = '<option value="">Select Member...</option>';
        members.forEach(member => {
            const selected = member.member_id == saving.member_id ? 'selected' : '';
            memberOptions += `<option value="${member.member_id}" ${selected}>${member.full_name} - ${member.member_code}</option>`;
        });
        
        return `
            <div class="row g-3">
                <div class="col-12">
                    <label for="edit_member_id" class="form-label">Member <span class="text-danger">*</span></label>
                    <select class="form-select" id="edit_member_id" name="member_id" required>
                        ${memberOptions}
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="edit_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="edit_amount" name="amount" value="${saving.amount}" min="1" step="0.01" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="edit_deposit_date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="edit_deposit_date" name="deposit_date" value="${saving.deposit_date}" required>
                </div>
                
                <div class="col-md-6">
                    <label for="edit_transaction_type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="edit_transaction_type" name="transaction_type" required>
                        <option value="">Select Type...</option>
                        <option value="deposit" ${saving.transaction_type === 'deposit' ? 'selected' : ''}>Deposit</option>
                        <option value="withdrawal" ${saving.transaction_type === 'withdrawal' ? 'selected' : ''}>Withdrawal</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="edit_transaction_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select class="form-select" id="edit_transaction_mode" name="transaction_mode" required>
                        <option value="">Select Mode...</option>
                        <option value="cash" ${saving.transaction_mode === 'cash' ? 'selected' : ''}>Cash</option>
                        <option value="online" ${saving.transaction_mode === 'online' ? 'selected' : ''}>Online / UPI</option>
                        <option value="cheque" ${saving.transaction_mode === 'cheque' ? 'selected' : ''}>Cheque</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <label for="edit_remarks" class="form-label">Remarks</label>
                    <textarea class="form-control" id="edit_remarks" name="remarks" rows="2">${saving.remarks || ''}</textarea>
                </div>
            </div>
        `;
    }
</script>
JS;

include '../includes/footer.php';
?>
