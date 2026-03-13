<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_settings') {
        try {
            $settingsToUpdate = [
                'app_name'                   => sanitize($_POST['app_name'] ?? 'Bachat Gat'),
                'currency_symbol'            => sanitize($_POST['currency_symbol'] ?? '₹'),
                'date_format'                => sanitize($_POST['date_format'] ?? 'Y-m-d'),
                'records_per_page'           => (int)($_POST['records_per_page'] ?? 10),
                'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? '1' : '0',
                'enable_sms_notifications'   => isset($_POST['enable_sms_notifications']) ? '1' : '0',
            ];

            foreach ($settingsToUpdate as $key => $value) {
                $exists = $db->selectValue(
                    "SELECT COUNT(*) FROM system_settings WHERE setting_key = ?", [$key]
                );
                if ($exists) {
                    $db->update(
                        "UPDATE system_settings SET setting_value = ? WHERE setting_key = ?",
                        [$value, $key]
                    );
                } else {
                    $db->insert(
                        "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)",
                        [$key, $value]
                    );
                }
            }
            $successMessage = 'Settings updated successfully!';
            logActivity($user['user_id'], 'settings_updated', 'System settings updated');
        } catch (Exception $e) {
            $errorMessage = 'Error updating settings: ' . $e->getMessage();
        }
    }

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
            if (!password_verify($currentPassword, $userRecord['password'])) {
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
                    logActivity($user['user_id'], 'password_changed', 'Admin password changed');
                }
            }
        }
    }
}

// Fetch current settings
$settingsRows = $db->select("SELECT setting_key, setting_value FROM system_settings", []);
$settings = [];
if (is_array($settingsRows)) {
    foreach ($settingsRows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
// Defaults
$settings = array_merge([
    'app_name'                   => 'Bachat Gat Smart Management',
    'currency_symbol'            => '₹',
    'date_format'                => 'Y-m-d',
    'records_per_page'           => '10',
    'enable_email_notifications' => '1',
    'enable_sms_notifications'   => '0',
], $settings);
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Manage system settings and your account</p>
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
        <!-- System Settings -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>System Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_settings">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Application Name</label>
                                <input type="text" class="form-control" name="app_name"
                                       value="<?= htmlspecialchars($settings['app_name']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Currency Symbol</label>
                                <input type="text" class="form-control" name="currency_symbol"
                                       value="<?= htmlspecialchars($settings['currency_symbol']) ?>" maxlength="5" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Records Per Page</label>
                                <select class="form-select" name="records_per_page">
                                    <?php foreach ([10, 25, 50, 100] as $n): ?>
                                    <option value="<?= $n ?>" <?= $settings['records_per_page'] == $n ? 'selected' : '' ?>><?= $n ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date Format</label>
                                <select class="form-select" name="date_format">
                                    <option value="Y-m-d"  <?= $settings['date_format'] === 'Y-m-d'  ? 'selected' : '' ?>>YYYY-MM-DD (<?= date('Y-m-d') ?>)</option>
                                    <option value="d-m-Y"  <?= $settings['date_format'] === 'd-m-Y'  ? 'selected' : '' ?>>DD-MM-YYYY (<?= date('d-m-Y') ?>)</option>
                                    <option value="d/m/Y"  <?= $settings['date_format'] === 'd/m/Y'  ? 'selected' : '' ?>>DD/MM/YYYY (<?= date('d/m/Y') ?>)</option>
                                    <option value="M j, Y" <?= $settings['date_format'] === 'M j, Y' ? 'selected' : '' ?>>Mon D, YYYY (<?= date('M j, Y') ?>)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notifications</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_notif"
                                               name="enable_email_notifications"
                                               <?= $settings['enable_email_notifications'] == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="email_notif">Email Notifications</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="sms_notif"
                                               name="enable_sms_notifications"
                                               <?= $settings['enable_sms_notifications'] == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sms_notif">SMS Notifications</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Save Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lock me-2"></i>Change Admin Password</h5>
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
                                <small class="text-muted">Min 8 chars with uppercase, lowercase & number</small>
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

        <!-- Admin Info Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avatar rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <h5 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                    <span class="badge bg-danger mb-3">Administrator</span>
                    <hr>
                    <div class="text-start">
                        <p class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?= htmlspecialchars($user['email']) ?></p>
                        <p class="mb-2"><i class="bi bi-phone me-2 text-primary"></i><?= htmlspecialchars($user['phone']) ?></p>
                        <p class="mb-0"><i class="bi bi-calendar me-2 text-primary"></i>Joined <?= $user['created_at'] ? date('d M Y', strtotime($user['created_at'])) : 'N/A' ?></p>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Info</h6>
                </div>
                <div class="card-body">
                    <?php
                    $totalMembers    = (int)$db->selectValue("SELECT COUNT(*) FROM members WHERE status = 'active'");
                    $totalSavings    = (float)$db->selectValue("SELECT COALESCE(SUM(amount),0) FROM savings WHERE transaction_type='deposit'");
                    $totalLoans      = (int)$db->selectValue("SELECT COUNT(*) FROM loans");
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Active Members</span>
                        <strong><?= $totalMembers ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Savings</span>
                        <strong><?= formatIndianCurrency($totalSavings) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Loans</span>
                        <strong><?= $totalLoans ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">PHP Version</span>
                        <strong><?= phpversion() ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
