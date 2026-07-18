<?php
/**
 * Admin Notifications
 * Redirects to the shared notifications page which handles both admin and member views
 */
require_once '../config/config.php';
requireAdmin();

// Preserve any query parameters (mark_all_read, delete, filter)
$queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
redirect('../member/notifications.php' . $queryString);
