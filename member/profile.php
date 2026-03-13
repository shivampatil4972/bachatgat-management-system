<?php
require_once '../config/config.php';

// Require member access
requireMember();

// Page variables
$pageTitle = 'My Profile';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'My Profile', 'url' => '']
];

// Get current member with user data
$db = Database::getInstance();
$userId = $_SESSION['user_id'] ?? 0;

// Fetch member and user data with JOIN
$result = $db->selectOne(
    "SELECT m.*, u.full_name, u.email, u.phone, u.created_at, u.profile_image, u.status
     FROM members m 
     JOIN users u ON m.user_id = u.user_id 
     WHERE m.user_id = ?",
    [$userId]
);

// Set both user and member arrays from the joined result
if ($result && is_array($result)) {
    $user = [
        'user_id' => $result['user_id'],
        'full_name' => $result['full_name'],
        'email' => $result['email'],
        'phone' => $result['phone'],
        'created_at' => $result['created_at'],
        'profile_image' => $result['profile_image'] ?? 'default-avatar.png',
        'status' => $result['status'] ?? 'active'
    ];
    $member = $result;
} else {
    // Fallback if JOIN fails - try individual queries
    $user = getCurrentUser();
    $member = getCurrentMember();
    
    // Ensure both are arrays
    if (!is_array($user)) {
        $user = [
            'user_id' => $userId,
            'full_name' => 'Unknown',
            'email' => '',
            'phone' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'profile_image' => 'default-avatar.png',
            'status' => 'active'
        ];
    }
    
    if (!is_array($member)) {
        $member = [
            'user_id' => $userId,
            'member_id' => 0,
            'member_code' => 'N/A',
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'address' => '',
            'city' => '',
            'state' => '',
            'pincode' => '',
            'total_savings' => 0
        ];
    } else {
        // Merge user data into member array
        $member['full_name'] = $user['full_name'];
        $member['email'] = $user['email'];
        $member['phone'] = $user['phone'];
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Manage your personal information</p>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php $profileImage = !empty($user['profile_image']) ? UPLOADS_URL . 'profiles/' . $user['profile_image'] : ASSETS_URL . 'images/default-avatar.svg'; ?>
                    <img src="<?= htmlspecialchars($profileImage) ?>"
                         alt="Profile"
                         class="rounded-circle mb-3 border"
                         style="width: 120px; height: 120px; object-fit: cover;">
                    <h4 class="mb-1"><?= htmlspecialchars($member['full_name']) ?></h4>
                    <p class="text-muted mb-2"><?= htmlspecialchars($member['member_code']) ?></p>
                    <span class="badge bg-success">Active Member</span>
                    
                    <hr class="my-4">
                    
                    <div class="text-start">
                        <h6 class="text-muted mb-3">Quick Info</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-envelope me-2 text-primary"></i>
                            <small><?= htmlspecialchars($user['email']) ?></small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-phone me-2 text-primary"></i>
                            <small><?= htmlspecialchars($user['phone']) ?></small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>
                            <small>Joined <?= formatDate($user['created_at']) ?></small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Financial Summary -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Financial Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Total Savings</small>
                        <h5 class="mb-0"><?= formatCurrency($member['total_savings']) ?></h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Active Loans</small>
                        <?php
                        $activeLoansResult = $db->query(
                            "SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'active'",
                            [$member['member_id']]
                        );
                        $loanCount = (is_array($activeLoansResult) && count($activeLoansResult) > 0) ? $activeLoansResult[0]['count'] : 0;
                        ?>
                        <h5 class="mb-0"><?= $loanCount ?></h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Edit Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="profile_image" class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                <div class="form-text">Allowed: JPG, JPEG, PNG, GIF (Max 5MB)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?= htmlspecialchars($member['full_name']) ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone']) ?>" maxlength="10" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                       value="<?= htmlspecialchars($member['date_of_birth'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">Select...</option>
                                    <option value="Male" <?= ($member['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($member['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= ($member['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="aadhar_number" class="form-label">Aadhar Number</label>
                                <input type="text" class="form-control" id="aadhar_number" name="aadhar_number" 
                                       value="<?= htmlspecialchars($member['aadhar_number'] ?? '') ?>" maxlength="12">
                            </div>
                            
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?= htmlspecialchars($member['city'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?= htmlspecialchars($member['state'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="pincode" name="pincode" 
                                       value="<?= htmlspecialchars($member['pincode'] ?? '') ?>" maxlength="6">
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                    <i class="bi bi-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lock me-2"></i>Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form id="changePasswordForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="text-muted">Min 8 characters with uppercase, lowercase & number</small>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning" id="changePasswordBtn">
                                    <i class="bi bi-key me-2"></i>Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = <<<'JS'
<script>
    // Phone validation
    document.getElementById('phone').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    
    // Aadhar validation
    document.getElementById('aadhar_number').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    
    // Pincode validation
    document.getElementById('pincode').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    
    // Profile Form Submission
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('saveProfileBtn');
        
        // Validate phone
        const phone = formData.get('phone');
        if (!/^[6-9]\d{9}$/.test(phone)) {
            alert('Please enter a valid 10-digit mobile number starting with 6-9');
            return;
        }
        
        // Validate aadhar if provided
        const aadhar = formData.get('aadhar_number');
        if (aadhar && !/^\d{12}$/.test(aadhar)) {
            alert('Aadhar number must be 12 digits');
            return;
        }
        
        // Validate pincode if provided
        const pincode = formData.get('pincode');
        if (pincode && !/^\d{6}$/.test(pincode)) {
            alert('Pincode must be 6 digits');
            return;
        }

        // Validate profile picture if selected
        const profileImage = formData.get('profile_image');
        if (profileImage && profileImage.size > 0) {
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(profileImage.type)) {
                alert('Please upload a valid image file (JPG, JPEG, PNG, GIF)');
                return;
            }
            if (profileImage.size > 5 * 1024 * 1024) {
                alert('Profile picture size must be less than 5MB');
                return;
            }
        }
        
        showLoading(submitBtn, 'Saving...');
        
        fetch('profile-process.php?action=update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile updated successfully!');
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
    
    // Change Password Form Submission
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('changePasswordBtn');
        
        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');
        
        // Validate password match
        if (newPassword !== confirmPassword) {
            alert('New passwords do not match!');
            return;
        }
        
        // Validate password strength
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(newPassword)) {
            alert('Password must be at least 8 characters with uppercase, lowercase, and number');
            return;
        }
        
        showLoading(submitBtn, 'Changing...');
        
        fetch('profile-process.php?action=change_password', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Password changed successfully!');
                this.reset();
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
</script>
JS;

include '../includes/footer.php';
?>
