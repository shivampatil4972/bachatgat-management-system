<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

// Page variables
$pageTitle = 'My Profile';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'My Profile', 'url' => '']
];

$user = getCurrentUser();
if (!is_array($user)) {
    $user = [
        'user_id' => $_SESSION['user_id'] ?? 0,
        'full_name' => $_SESSION['full_name'] ?? 'Admin',
        'email' => $_SESSION['email'] ?? '',
        'phone' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'profile_image' => 'default-avatar.png',
        'status' => 'active'
    ];
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Manage your admin profile information</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php $profileImage = !empty($user['profile_image']) ? UPLOADS_URL . 'profiles/' . $user['profile_image'] : ASSETS_URL . 'images/default-avatar.svg'; ?>
                    <img src="<?= htmlspecialchars($profileImage) ?>"
                         alt="Profile"
                         class="rounded-circle mb-3 border"
                         style="width: 120px; height: 120px; object-fit: cover;">

                    <h4 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h4>
                    <p class="text-muted mb-2">Administrator</p>
                    <span class="badge bg-success"><?= ucfirst($user['status'] ?? 'active') ?></span>

                    <hr class="my-4">

                    <div class="text-start">
                        <h6 class="text-muted mb-3">Quick Info</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-envelope me-2 text-primary"></i>
                            <small><?= htmlspecialchars($user['email']) ?></small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-phone me-2 text-primary"></i>
                            <small><?= htmlspecialchars($user['phone'] ?? '-') ?></small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>
                            <small>Joined <?= formatDate($user['created_at']) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                       value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" maxlength="10" required>
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
    document.getElementById('phone').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = document.getElementById('saveProfileBtn');

        const phone = formData.get('phone');
        if (!/^[6-9]\d{9}$/.test(phone)) {
            alert('Please enter a valid 10-digit mobile number starting with 6-9');
            return;
        }

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

    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = document.getElementById('changePasswordBtn');

        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');

        if (newPassword !== confirmPassword) {
            alert('New passwords do not match!');
            return;
        }

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
