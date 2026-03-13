<?php
require_once '../config/config.php';

// Require member access
requireMember();

// Page variables
$pageTitle = 'Settings';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Settings', 'url' => '']
];

$db = Database::getInstance();
$user = getCurrentUser();
$successMessage = '';
$errorMessage = '';

// Fetch member record
$member = $db->selectOne("SELECT * FROM members WHERE user_id = ?", [$user['user_id']]);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errorMessage = 'All password fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = 'New passwords do not match.';
        } else {
            $userRecord = $db->selectOne("SELECT * FROM users WHERE user_id = ?", [$user['user_id']]);
            if (!$userRecord || !password_verify($currentPassword, $userRecord['password'])) {
                $errorMessage = 'Current password is incorrect.';
            } else {
                $passwordCheck = validatePassword($newPassword);
                if (!$passwordCheck['valid']) {
                    $errorMessage = $passwordCheck['message'];
                } else {
                    $db->update(
                        "UPDATE users SET password = ? WHERE user_id = ?",
                        [password_hash($newPassword, PASSWORD_DEFAULT), $user['user_id']]
                    );
                    $successMessage = 'Password changed successfully!';
                    logActivity($user['user_id'], 'password_changed', 'Member changed their password');
                }
            }
        }
    }

    if ($action === 'update_profile_settings') {
        // Update notification preferences (stored in session/member prefs)
        $phone = sanitize($_POST['phone'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        if (empty($phone) || empty($email)) {
            $errorMessage = 'Phone and email cannot be empty.';
        } else {
            // Check email uniqueness (exclude current user)
            $emailExists = $db->selectValue(
                "SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?",
                [$email, $user['user_id']]
            );
            if ($emailExists) {
                $errorMessage = 'That email is already in use by another account.';
            } else {
                $db->update(
                    "UPDATE users SET email = ?, phone = ?, updated_at = NOW() WHERE user_id = ?",
                    [$email, $phone, $user['user_id']]
                );
                $_SESSION['email'] = $email;
                $successMessage = 'Contact details updated successfully!';
                logActivity($user['user_id'], 'profile_updated', 'Member updated contact settings');
                // Refresh user
                $user = getCurrentUser();
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Manage your account settings and password</p>
        </div>
    </div>

    <?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($successMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($errorMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Contact Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Contact Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile_settings">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email"
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Update Contact Details
                                </button>
                                <a href="profile.php" class="btn btn-outline-secondary ms-2">
                                    <i class="bi bi-person me-2"></i>Edit Full Profile
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lock me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="new_password" required>
                                <small class="text-muted">Min 8 chars, with uppercase, lowercase &amp; number</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key me-2"></i>Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avatar rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <h5 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                    <?php if (isset($_SESSION['member_code'])): ?>
                    <span class="badge bg-success mb-2"><?= htmlspecialchars($_SESSION['member_code']) ?></span>
                    <?php endif; ?>
                    <span class="badge bg-primary mb-3">Member</span>
                    <hr>
                    <div class="text-start">
                        <p class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?= htmlspecialchars($user['email']) ?></p>
                        <p class="mb-2"><i class="bi bi-phone me-2 text-primary"></i><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                        <p class="mb-2"><i class="bi bi-calendar-check me-2 text-primary"></i>Joined <?= $user['created_at'] ? date('d M Y', strtotime($user['created_at'])) : 'N/A' ?></p>
                        <?php if ($member): ?>
                        <p class="mb-0">
                            <i class="bi bi-circle-fill me-2 <?= $member['status'] === 'active' ? 'text-success' : 'text-danger' ?>" style="font-size:0.6rem;vertical-align:middle"></i>
                            <?= ucfirst($member['status'] ?? 'active') ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-grid me-2"></i>Quick Links</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-person me-2"></i>Edit Profile
                    </a>
                    <a href="my-savings.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-piggy-bank me-2"></i>My Savings
                    </a>
                    <a href="my-loans.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-bank me-2"></i>My Loans
                    </a>
                    <a href="notifications.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-bell me-2"></i>Notifications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
