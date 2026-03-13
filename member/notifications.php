<?php
require_once '../config/config.php';

// Require login
requireLogin();

// Page variables
$pageTitle = 'Notifications';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => isAdmin() ? '../admin/dashboard.php' : 'dashboard.php'],
    ['title' => 'Notifications', 'url' => '']
];

// Get current user
$db = Database::getInstance();
$user = getCurrentUser();

// Mark all as read if requested
if (isset($_GET['mark_all_read'])) {
    $db->query(
        "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0",
        [$user['user_id']]
    );
    redirect('notifications.php');
}

// Delete notification if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $notificationId = (int)$_GET['delete'];
    $db->query(
        "DELETE FROM notifications WHERE notification_id = ? AND user_id = ?",
        [$notificationId, $user['user_id']]
    );
    setFlashMessage('Notification deleted successfully', 'success');
    redirect('notifications.php');
}

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build query
$query = "SELECT * FROM notifications WHERE user_id = ?";
$params = [$user['user_id']];

if ($filter === 'unread') {
    $query .= " AND is_read = 0";
} elseif ($filter === 'read') {
    $query .= " AND is_read = 1";
}

$query .= " ORDER BY created_at DESC LIMIT 100";

$notifications = $db->select($query, $params);
if (!is_array($notifications)) $notifications = [];

// Get counts
$unreadCount = (int)$db->selectValue(
    "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
    [$user['user_id']]
);

$totalCount = (int)$db->selectValue(
    "SELECT COUNT(*) FROM notifications WHERE user_id = ?",
    [$user['user_id']]
);
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Manage your notifications</p>
        </div>
        <div class="col-md-4 text-end">
            <?php if ($unreadCount > 0): ?>
            <a href="?mark_all_read=1" class="btn btn-primary">
                <i class="bi bi-check-all me-2"></i>Mark All as Read
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-bell fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Notifications</h6>
                            <h4 class="mb-0"><?= number_format($totalCount) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-bell-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Unread</h6>
                            <h4 class="mb-0"><?= number_format($unreadCount) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="mb-4">
        <div class="btn-group" role="group">
            <a href="?filter=all" class="btn btn-<?= $filter === 'all' ? 'primary' : 'outline-primary' ?>">
                All (<?= $totalCount ?>)
            </a>
            <a href="?filter=unread" class="btn btn-<?= $filter === 'unread' ? 'primary' : 'outline-primary' ?>">
                Unread (<?= $unreadCount ?>)
            </a>
            <a href="?filter=read" class="btn btn-<?= $filter === 'read' ? 'primary' : 'outline-primary' ?>">
                Read (<?= $totalCount - $unreadCount ?>)
            </a>
        </div>
    </div>
    
    <!-- Notifications List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash display-1 text-muted"></i>
                    <h4 class="mt-3">No Notifications</h4>
                    <p class="text-muted">You're all caught up!</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item <?= $notification['is_read'] ? '' : 'bg-light' ?>">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <?php
                                        $iconColors = [
                                            'success' => 'success',
                                            'info' => 'info',
                                            'warning' => 'warning',
                                            'error' => 'danger'
                                        ];
                                        $iconColor = $iconColors[$notification['type']] ?? 'secondary';
                                        
                                        $icons = [
                                            'success' => 'check-circle-fill',
                                            'info' => 'info-circle-fill',
                                            'warning' => 'exclamation-triangle-fill',
                                            'error' => 'x-circle-fill'
                                        ];
                                        $icon = $icons[$notification['type']] ?? 'bell-fill';
                                        ?>
                                        <i class="bi bi-<?= $icon ?> fs-4 text-<?= $iconColor ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?= htmlspecialchars($notification['title']) ?>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="badge bg-primary ms-2">New</span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="mb-1 text-muted"><?= htmlspecialchars($notification['message']) ?></p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i><?= formatDate($notification['created_at']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="ms-3">
                                <a href="?delete=<?= $notification['notification_id'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this notification?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
