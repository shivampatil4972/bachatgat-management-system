<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

// Page variables
$pageTitle = 'Members Management';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Members', 'url' => '']
];
$includeDataTables = true;

// Get all members with user information
$db = Database::getInstance();

$query = "
    SELECT 
        m.*,
        u.full_name,
        u.email,
        u.phone,
        u.status AS user_status,
        u.created_at,
        COALESCE((SELECT SUM(amount) FROM savings WHERE member_id = m.member_id AND transaction_type='deposit'),0) AS total_savings,
        (SELECT COUNT(*) FROM savings WHERE member_id = m.member_id) AS savings_count,
        (SELECT COUNT(*) FROM loans WHERE member_id = m.member_id) AS loans_count
    FROM members m
    JOIN users u ON m.user_id = u.user_id
    ORDER BY m.created_at DESC
";

$members = $db->select($query) ?: [];
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Manage all members of your Bachat Gat</p>
        </div>
        <div class="col-lg-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Member
            </button>
        </div>
    </div>
    
    <!-- Members Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="membersTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Member Code</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Total Savings</th>
                            <th>Active Loans</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                        <tr>
                            <td>
                                <strong class="text-primary"><?= htmlspecialchars($member['member_code']) ?></strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center me-2">
                                        <?= strtoupper(substr($member['full_name'], 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($member['full_name']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($member['email']) ?></td>
                            <td><?= htmlspecialchars($member['phone']) ?></td>
                            <td><?= formatIndianCurrency($member['total_savings']) ?></td>
                            <td>
                                <?php 
                                $loanCount = (int)$member['loans_count'];
                                if ($loanCount > 0):
                                ?>
                                    <span class="badge bg-warning"><?= $loanCount ?> Active</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($member['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $member['created_at'] ? date('d-m-Y', strtotime($member['created_at'])) : '-' ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewMember(<?= $member['member_id'] ?>)" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="editMember(<?= $member['member_id'] ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteMember(<?= $member['member_id'] ?>, '<?= htmlspecialchars($member['full_name']) ?>')" title="Delete">
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

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>Add New Member
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMemberForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" maxlength="10" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="aadhar_number" class="form-label">Aadhar Number</label>
                            <input type="text" class="form-control" id="aadhar_number" name="aadhar_number" maxlength="12">
                        </div>
                        
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" maxlength="6">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Min 8 characters with uppercase, lowercase & number</div>
                        </div>

                        <div class="col-md-6">
                            <label for="profile_image" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            <div class="form-text">Allowed: JPG, JPEG, PNG, GIF (Max 5MB)</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addMemberBtn">
                        <i class="bi bi-save me-2"></i>Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Member Modal -->
<div class="modal fade" id="viewMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-circle me-2"></i>Member Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewMemberContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Member
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMemberForm">
                <input type="hidden" id="edit_member_id" name="member_id">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body" id="editMemberContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="editMemberBtn">
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
        $('#membersTable').DataTable({
            order: [[7, 'desc']], // Sort by joined date
            pageLength: 25,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search members..."
            }
        });
    });
    
    // Add Member Form Submission
    document.getElementById('addMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('addMemberBtn');
        
        showLoading(submitBtn, 'Adding...');
        
        fetch('members-process.php?action=add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalEl = document.getElementById('addMemberModal');
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) instance.hide();
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
    
    // View Member
    function viewMember(memberId) {
        const modal = new bootstrap.Modal(document.getElementById('viewMemberModal'));
        const content = document.getElementById('viewMemberContent');
        
        modal.show();
        
        fetch('members-process.php?action=view&id=' + memberId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.innerHTML = generateViewHTML(data.member);
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Failed to load member details</div>';
                }
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-danger">An error occurred</div>';
            });
    }
    
    // Edit Member
    function editMember(memberId) {
        const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
        const content = document.getElementById('editMemberContent');
        
        modal.show();
        
        fetch('members-process.php?action=get&id=' + memberId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.innerHTML = generateEditHTML(data.member);
                    document.getElementById('edit_member_id').value = data.member.member_id;
                    document.getElementById('edit_user_id').value = data.member.user_id;
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Failed to load member details</div>';
                }
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-danger">An error occurred</div>';
            });
    }
    
    // Edit Member Form Submission
    document.getElementById('editMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('editMemberBtn');
        
        showLoading(submitBtn, 'Saving...');
        
        fetch('members-process.php?action=edit', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const editModalEl = document.getElementById('editMemberModal');
                const editInstance = bootstrap.Modal.getInstance(editModalEl);
                if (editInstance) editInstance.hide();
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
    
    // Delete Member
    function deleteMember(memberId, memberName) {
        if (confirmDelete('Are you sure you want to delete ' + memberName + '? This action cannot be undone.')) {
            fetch('members-process.php?action=delete&id=' + memberId, {
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
    
    // Generate View HTML
    function generateViewHTML(member) {
        return `
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="avatar rounded-circle bg-gradient-primary text-white mx-auto mb-3" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                        ${member.full_name.charAt(0).toUpperCase()}
                    </div>
                    <h4>${member.full_name}</h4>
                    <p class="text-muted">${member.member_code}</p>
                    <span class="badge bg-${member.status === 'active' ? 'success' : 'secondary'}">${member.status}</span>
                </div>
                <div class="col-md-8">
                    <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Email:</strong><br>
                            ${member.email}
                        </div>
                        <div class="col-md-6">
                            <strong>Phone:</strong><br>
                            ${member.phone}
                        </div>
                        <div class="col-md-6">
                            <strong>Date of Birth:</strong><br>
                            ${member.date_of_birth || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Gender:</strong><br>
                            ${member.gender || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Aadhar Number:</strong><br>
                            ${member.aadhar_number || 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Joined Date:</strong><br>
                            ${formatDate(member.created_at)}
                        </div>
                    </div>
                    
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Address</h5>
                    <p>${member.address || 'N/A'}</p>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>City:</strong> ${member.city || 'N/A'}
                        </div>
                        <div class="col-md-4">
                            <strong>State:</strong> ${member.state || 'N/A'}
                        </div>
                        <div class="col-md-4">
                            <strong>Pincode:</strong> ${member.pincode || 'N/A'}
                        </div>
                    </div>
                    
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Financial Summary</h5>
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-primary">${formatCurrency(member.total_savings)}</h3>
                            <small class="text-muted">Total Savings</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-warning">${member.loans_count}</h3>
                            <small class="text-muted">Active Loans</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-success">${member.savings_count}</h3>
                            <small class="text-muted">Savings Records</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Generate Edit HTML
    function generateEditHTML(member) {
        return `
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="edit_full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_full_name" name="full_name" value="${member.full_name}" required>
                </div>
                
                <div class="col-md-6">
                    <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="edit_email" name="email" value="${member.email}" required>
                </div>
                
                <div class="col-md-6">
                    <label for="edit_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="edit_phone" name="phone" value="${member.phone}" maxlength="10" required>
                </div>
                
                <div class="col-md-6">
                    <label for="edit_date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth" value="${member.date_of_birth || ''}">
                </div>
                
                <div class="col-md-6">
                    <label for="edit_gender" class="form-label">Gender</label>
                    <select class="form-select" id="edit_gender" name="gender">
                        <option value="">Select...</option>
                        <option value="Male" ${member.gender === 'Male' ? 'selected' : ''}>Male</option>
                        <option value="Female" ${member.gender === 'Female' ? 'selected' : ''}>Female</option>
                        <option value="Other" ${member.gender === 'Other' ? 'selected' : ''}>Other</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="edit_aadhar_number" class="form-label">Aadhar Number</label>
                    <input type="text" class="form-control" id="edit_aadhar_number" name="aadhar_number" value="${member.aadhar_number || ''}" maxlength="12">
                </div>
                
                <div class="col-12">
                    <label for="edit_address" class="form-label">Address</label>
                    <textarea class="form-control" id="edit_address" name="address" rows="2">${member.address || ''}</textarea>
                </div>
                
                <div class="col-md-6">
                    <label for="edit_city" class="form-label">City</label>
                    <input type="text" class="form-control" id="edit_city" name="city" value="${member.city || ''}">
                </div>
                
                <div class="col-md-6">
                    <label for="edit_state" class="form-label">State</label>
                    <input type="text" class="form-control" id="edit_state" name="state" value="${member.state || ''}">
                </div>
                
                <div class="col-md-6">
                    <label for="edit_pincode" class="form-label">Pincode</label>
                    <input type="text" class="form-control" id="edit_pincode" name="pincode" value="${member.pincode || ''}" maxlength="6">
                </div>
                
                <div class="col-md-6">
                    <label for="edit_status" class="form-label">Status</label>
                    <select class="form-select" id="edit_status" name="status">
                        <option value="active" ${member.status === 'active' ? 'selected' : ''}>Active</option>
                        <option value="inactive" ${member.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="edit_profile_image" class="form-label">Profile Picture</label>
                    <input type="file" class="form-control" id="edit_profile_image" name="profile_image" accept="image/*">
                    <div class="form-text">Upload only if you want to replace current picture</div>
                </div>
            </div>
        `;
    }
    
    // Format Date Helper
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
    
    // Phone validation for add form
    document.getElementById('phone').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    
    // Aadhar validation for add form
    document.getElementById('aadhar_number').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    
    // Pincode validation for add form
    document.getElementById('pincode').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
</script>
JS;

include '../includes/footer.php';
?>
