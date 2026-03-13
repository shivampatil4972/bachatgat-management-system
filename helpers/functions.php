<?php
/**
 * Helper Functions
 * Bachat Gat Smart Management System
 * 
 * Common utility functions used throughout the application
 * 
 * @author Your Name
 * @version 1.0
 */

/**
 * Display flash message from session
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        $alertClass = ALERT_CLASSES[$type] ?? ALERT_CLASSES['info'];
        
        echo '<div class="' . $alertClass . '" role="alert">';
        echo '<i class="bi bi-' . getAlertIcon($type) . '"></i> ';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        
        // Clear flash message
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Set flash message in session
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get alert icon based on type
 */
function getAlertIcon($type) {
    $icons = [
        'success' => 'check-circle-fill',
        'error' => 'exclamation-triangle-fill',
        'warning' => 'exclamation-circle-fill',
        'info' => 'info-circle-fill'
    ];
    return $icons[$type] ?? 'info-circle-fill';
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match(REGEX_EMAIL, $email);
}

/**
 * Validate phone number (Indian format)
 */
function validatePhone($phone) {
    return preg_match(REGEX_PHONE, $phone);
}

/**
 * Validate Aadhar number
 */
function validateAadhar($aadhar) {
    return preg_match(REGEX_AADHAR, $aadhar);
}

/**
 * Validate PAN number
 */
function validatePAN($pan) {
    return preg_match(REGEX_PAN, strtoupper($pan));
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return [
            'valid' => false,
            'message' => 'Password must be at least 8 characters long.'
        ];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one uppercase letter.'
        ];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one number.'
        ];
    }
    
    return [
        'valid' => true,
        'message' => 'Password is strong.'
    ];
}

/**
 * Generate unique member code
 */
function generateMemberCode() {
    $db = Database::getInstance();
    // Use MAX to avoid duplicates when members have been deleted
    $lastCode = $db->selectValue("SELECT MAX(CAST(SUBSTRING(member_code, 4) AS UNSIGNED)) FROM members");
    $next = ($lastCode ?? 0) + 1;
    return MEMBER_CODE_PREFIX . str_pad($next, 3, '0', STR_PAD_LEFT);
}

/**
 * Generate unique loan number
 */
function generateLoanNumber() {
    $db = Database::getInstance();
    $count = $db->selectValue("SELECT COUNT(*) FROM loans") + 1;
    return LOAN_NUMBER_PREFIX . date('Y') . str_pad($count, 4, '0', STR_PAD_LEFT);
}

/**
 * Calculate loan total amount with interest
 */
function calculateLoanTotal($loanAmount, $interestRate) {
    return $loanAmount + ($loanAmount * $interestRate / 100);
}

/**
 * Calculate monthly installment
 */
function calculateMonthlyInstallment($totalAmount, $months) {
    return round($totalAmount / $months, 2);
}

/**
 * Upload file
 */
function uploadFile($file, $targetDir, $allowedTypes = []) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => FILE_UPLOAD_ERRORS[$file['error'] ?? UPLOAD_ERR_NO_FILE]];
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => MSG_ERROR_FILE_SIZE];
    }
    
    // Get file extension
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    if (!empty($allowedTypes) && !in_array($fileExt, $allowedTypes)) {
        return ['success' => false, 'message' => MSG_ERROR_FILE_TYPE];
    }
    
    // Generate unique filename
    $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
    $targetPath = $targetDir . $newFileName;
    
    // Create directory if not exists
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $newFileName, 'path' => $targetPath];
    }
    
    return ['success' => false, 'message' => MSG_ERROR_FILE_UPLOAD];
}

/**
 * Delete file
 */
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

/**
 * Get user's IP address
 */
function getIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}

/**
 * Get user agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
}

/**
 * Log activity
 */
function logActivity($userId, $action, $description = null) {
    $db = Database::getInstance();
    $db->insert(
        "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
        [$userId, $action, $description, getIpAddress(), getUserAgent()]
    );
}

/**
 * Send notification
 */
function sendNotification($userId, $title, $message, $type = 'info') {
    $db = Database::getInstance();
    return $db->insert(
        "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)",
        [$userId, $title, $message, $type]
    );
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount($userId) {
    $db = Database::getInstance();
    return $db->selectValue(
        "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
        [$userId]
    );
}

/**
 * Get recent notifications
 */
function getRecentNotifications($userId, $limit = 5) {
    $db = Database::getInstance();
    return $db->select(
        "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
        [$userId, $limit]
    );
}

/**
 * Clean input string
 */
function cleanInput($data) {
    return trim(stripslashes(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')));
}

/**
 * Check if request is POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 */
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get current URL
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Get previous URL
 */
function getPreviousUrl() {
    return $_SERVER['HTTP_REFERER'] ?? BASE_URL;
}

/**
 * JSON response
 */
function jsonResponse($success, $message, $data = []) {
    http_response_code($success ? 200 : 400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Paginate results
 */
function paginate($totalRecords, $currentPage = 1, $perPage = RECORDS_PER_PAGE) {
    $totalPages = ceil($totalRecords / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Generate pagination HTML
 */
function renderPagination($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($pagination['has_prev']) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '">Previous</a>';
        $html .= '</li>';
    }
    
    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    // Next button
    if ($pagination['has_next']) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '">Next</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Format number to Indian currency
 */
function formatIndianCurrency($number) {
    $number = (float)$number;
    $isNegative = $number < 0;
    $number = abs($number);

    // Get plain number without any commas
    $intPart  = (string)(int)$number;
    $decPart  = number_format($number - (int)$number, 2, '.', '');
    $decPart  = substr($decPart, 1); // ".xx"

    // Indian numbering: last 3 digits, then groups of 2
    if (strlen($intPart) <= 3) {
        $result = $intPart;
    } else {
        $lastThree = substr($intPart, -3);
        $remaining = substr($intPart, 0, -3);
        $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
        $result = $remaining . ',' . $lastThree;
    }

    return ($isNegative ? '-' : '') . CURRENCY_SYMBOL . $result . $decPart;
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100, $ending = '...') {
    if (strlen($text) > $length) {
        return substr($text, 0, $length - strlen($ending)) . $ending;
    }
    return $text;
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    return STATUS_BADGES[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
}

/**
 * Check if date is in past
 */
function isPastDate($date) {
    return strtotime($date) < strtotime('today');
}

/**
 * Check if date is in future
 */
function isFutureDate($date) {
    return strtotime($date) > strtotime('today');
}

/**
 * Get months between two dates
 */
function getMonthsBetween($date1, $date2) {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    return $interval->y * 12 + $interval->m;
}

/**
 * Get days between two dates
 */
function getDaysBetween($date1, $date2) {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    return $interval->days;
}

/**
 * Debug helper (only in development)
 */
if (!function_exists('dump')) {
    function dump($data) {
        if (ENVIRONMENT === 'development') {
            echo '<pre style="background:#1e293b;color:#10b981;padding:15px;border-radius:8px;margin:10px 0;font-size:12px;line-height:1.5;">';
            var_dump($data);
            echo '</pre>';
        }
    }
}

?>
