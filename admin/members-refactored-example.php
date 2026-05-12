<?php
/**
 * Refactored Members Page (Example)
 * Shows how to use Service Layer Architecture
 * 
 * Compare with admin/members.php to see the improvements
 */

// Use new improved config
require_once dirname(__DIR__) . '/config/config-v2.php';
require_once dirname(__DIR__) . '/config/db.php';

// Check authentication
AdminMiddleware::handle();

// Initialize service
$database = new Database();
$memberService = new MemberService($database);

// Handle requests
$action = $_GET['action'] ?? 'list';
$page = (int)($_GET['page'] ?? 1);
$limit = 50;

switch ($action) {
    case 'list':
        handleListMembers($memberService, $page, $limit);
        break;
    
    case 'view':
        handleViewMember($memberService, $_GET['id'] ?? 0);
        break;
    
    case 'create':
        handleCreateMember($memberService, $_POST);
        break;
    
    case 'update':
        handleUpdateMember($memberService, $_POST);
        break;
    
    case 'delete':
        handleDeleteMember($memberService, $_POST['id'] ?? 0);
        break;
    
    default:
        sendJson(Response::error('Invalid action', 400));
}

/**
 * Handle list members
 */
function handleListMembers($service, $page, $limit) {
    $filters = [
        'search' => $_GET['search'] ?? ''
    ];
    
    $result = $service->getAll($page, $limit, $filters);
    
    // If HTML request (from page), render HTML
    if (!isset($_GET['json'])) {
        include 'members-view.php';
        return;
    }
    
    // If JSON request (from API), return JSON
    sendJson(Response::success('Members retrieved', $result));
}

/**
 * Handle view member
 */
function handleViewMember($service, $memberId) {
    $member = $service->getById($memberId);
    
    if (!$member) {
        return sendJson(Response::notFound('Member not found'));
    }
    
    $stats = $service->getStats($memberId);
    
    if (!isset($_GET['json'])) {
        include 'member-detail-view.php';
        return;
    }
    
    sendJson(Response::success('Member details', [
        'member' => $member,
        'stats' => $stats['data'] ?? $stats
    ]));
}

/**
 * Handle create member
 */
function handleCreateMember($service, $data) {
    if (empty($_POST)) {
        return sendJson(Response::error('No data provided', 400));
    }
    
    $result = $service->create($data);
    sendJson($result);
}

/**
 * Handle update member
 */
function handleUpdateMember($service, $data) {
    $memberId = $data['member_id'] ?? 0;
    
    if (!$memberId) {
        return sendJson(Response::error('Member ID required', 400));
    }
    
    $result = $service->update($memberId, $data);
    sendJson($result);
}

/**
 * Handle delete member
 */
function handleDeleteMember($service, $memberId) {
    if (!$memberId) {
        return sendJson(Response::error('Member ID required', 400));
    }
    
    $result = $service->delete($memberId);
    sendJson($result);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Members - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/bootstrap.min.css">
</head>
<body>
    <?php if ($action === 'list'): ?>
        <div class="container mt-4">
            <h1>Members Management</h1>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Member Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Savings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['data'] as $member): ?>
                    <tr>
                        <td><?= htmlspecialchars($member['member_code']) ?></td>
                        <td><?= htmlspecialchars($member['full_name']) ?></td>
                        <td><?= htmlspecialchars($member['email']) ?></td>
                        <td><?= htmlspecialchars($member['phone']) ?></td>
                        <td>₹<?= number_format($member['total_savings'], 2) ?></td>
                        <td>
                            <a href="?action=view&id=<?= $member['member_id'] ?>" class="btn btn-sm btn-info">View</a>
                            <button class="btn btn-sm btn-danger" onclick="deleteMember(<?= $member['member_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $result['pages']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
    
    <script src="<?= APP_URL ?>/assets/js/bootstrap.min.js"></script>
</body>
</html>
