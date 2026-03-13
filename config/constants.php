<?php
/**
 * Application Constants
 * Bachat Gat Smart Management System
 * 
 * This file contains application-wide constant definitions
 * Used for validation messages, notification templates, etc.
 * 
 * @author Your Name
 * @version 1.0
 */

// ========================================
// VALIDATION MESSAGES
// ========================================

// Success messages
define('MSG_LOGIN_SUCCESS', 'Login successful! Welcome back.');
define('MSG_LOGOUT_SUCCESS', 'You have been logged out successfully.');
define('MSG_REGISTER_SUCCESS', 'Registration successful! You can now login.');
define('MSG_PROFILE_UPDATED', 'Profile updated successfully.');
define('MSG_PASSWORD_CHANGED', 'Password changed successfully.');

// Member messages
define('MSG_MEMBER_ADDED', 'Member added successfully.');
define('MSG_MEMBER_UPDATED', 'Member updated successfully.');
define('MSG_MEMBER_DELETED', 'Member deleted successfully.');
define('MSG_MEMBER_ACTIVATED', 'Member activated successfully.');
define('MSG_MEMBER_DEACTIVATED', 'Member deactivated successfully.');

// Savings messages
define('MSG_SAVINGS_ADDED', 'Savings recorded successfully.');
define('MSG_SAVINGS_UPDATED', 'Savings updated successfully.');
define('MSG_SAVINGS_DELETED', 'Savings record deleted successfully.');
define('MSG_WITHDRAWAL_RECORDED', 'Withdrawal recorded successfully.');

// Loan messages
define('MSG_LOAN_APPLIED', 'Loan application submitted successfully.');
define('MSG_LOAN_APPROVED', 'Loan approved successfully.');
define('MSG_LOAN_REJECTED', 'Loan rejected.');
define('MSG_LOAN_DISBURSED', 'Loan disbursed successfully.');
define('MSG_LOAN_UPDATED', 'Loan updated successfully.');
define('MSG_LOAN_DELETED', 'Loan deleted successfully.');

// Installment messages
define('MSG_INSTALLMENT_PAID', 'Installment payment recorded successfully.');
define('MSG_INSTALLMENT_UPDATED', 'Installment updated successfully.');
define('MSG_PAYMENT_SUCCESS', 'Payment processed successfully.');

// Error messages
define('MSG_ERROR_GENERAL', 'Something went wrong. Please try again.');
define('MSG_ERROR_LOGIN', 'Invalid email or password.');
define('MSG_ERROR_SESSION', 'Session expired. Please login again.');
define('MSG_ERROR_PERMISSION', 'You do not have permission to access this page.');
define('MSG_ERROR_NOT_FOUND', 'Record not found.');
define('MSG_ERROR_DUPLICATE', 'Record already exists.');
define('MSG_ERROR_DATABASE', 'Database error occurred. Please contact administrator.');
define('MSG_ERROR_VALIDATION', 'Please check the form for errors.');
define('MSG_ERROR_FILE_UPLOAD', 'File upload failed. Please try again.');
define('MSG_ERROR_FILE_SIZE', 'File size exceeds maximum allowed limit.');
define('MSG_ERROR_FILE_TYPE', 'Invalid file type.');

// Validation messages
define('MSG_REQUIRED_FIELD', 'This field is required.');
define('MSG_INVALID_EMAIL', 'Invalid email address.');
define('MSG_INVALID_PHONE', 'Invalid phone number.');
define('MSG_PASSWORD_TOO_SHORT', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.');
define('MSG_PASSWORD_MISMATCH', 'Passwords do not match.');
define('MSG_EMAIL_EXISTS', 'Email address already registered.');
define('MSG_INVALID_AMOUNT', 'Invalid amount entered.');
define('MSG_INSUFFICIENT_FUNDS', 'Insufficient funds.');

// ========================================
// NOTIFICATION TEMPLATES
// ========================================

// Loan notification templates
define('NOTIFY_TEMPLATE_LOAN_APPLIED', [
    'title' => 'New Loan Application',
    'message' => 'A new loan application of %s has been submitted by %s (Member: %s).'
]);

define('NOTIFY_TEMPLATE_LOAN_APPROVED', [
    'title' => 'Loan Approved',
    'message' => 'Your loan application for %s has been approved. Loan Number: %s'
]);

define('NOTIFY_TEMPLATE_LOAN_REJECTED', [
    'title' => 'Loan Rejected',
    'message' => 'Your loan application for %s has been rejected. Reason: %s'
]);

define('NOTIFY_TEMPLATE_LOAN_DISBURSED', [
    'title' => 'Loan Disbursed',
    'message' => 'Your loan amount of %s has been disbursed. Please check your account.'
]);

define('NOTIFY_TEMPLATE_INSTALLMENT_DUE', [
    'title' => 'Installment Due',
    'message' => 'Your installment of %s for Loan %s is due on %s.'
]);

define('NOTIFY_TEMPLATE_INSTALLMENT_OVERDUE', [
    'title' => 'Installment Overdue',
    'message' => 'Your installment of %s for Loan %s is overdue. Please pay immediately to avoid late fees.'
]);

define('NOTIFY_TEMPLATE_INSTALLMENT_PAID', [
    'title' => 'Payment Received',
    'message' => 'Your installment payment of %s has been received. Remaining balance: %s'
]);

define('NOTIFY_TEMPLATE_SAVINGS_DEPOSITED', [
    'title' => 'Savings Deposited',
    'message' => 'Your savings deposit of %s has been recorded. Total savings: %s'
]);

define('NOTIFY_TEMPLATE_WELCOME', [
    'title' => 'Welcome to Bachat Gat',
    'message' => 'Welcome %s! Your member code is %s. You can now start saving and apply for loans.'
]);

// ========================================
// EMAIL TEMPLATES
// ========================================

define('EMAIL_TEMPLATE_HEADER', '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; }
        .footer { background: #1e293b; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; font-size: 12px; }
        .button { display: inline-block; background: #6366f1; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .info-box { background: white; padding: 15px; border-left: 4px solid #6366f1; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . APP_NAME . '</h1>
        </div>
        <div class="content">
');

define('EMAIL_TEMPLATE_FOOTER', '
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
');

// ========================================
// DASHBOARD CARD ICONS
// ========================================

define('ICON_MEMBERS', '<i class="bi bi-people-fill"></i>');
define('ICON_SAVINGS', '<i class="bi bi-piggy-bank-fill"></i>');
define('ICON_LOANS', '<i class="bi bi-cash-stack"></i>');
define('ICON_INSTALLMENTS', '<i class="bi bi-calendar-check-fill"></i>');
define('ICON_PENDING', '<i class="bi bi-clock-history"></i>');
define('ICON_APPROVED', '<i class="bi bi-check-circle-fill"></i>');
define('ICON_REJECTED', '<i class="bi bi-x-circle-fill"></i>');
define('ICON_REPORTS', '<i class="bi bi-file-earmark-bar-graph-fill"></i>');
define('ICON_SETTINGS', '<i class="bi bi-gear-fill"></i>');
define('ICON_NOTIFICATIONS', '<i class="bi bi-bell-fill"></i>');
define('ICON_PROFILE', '<i class="bi bi-person-circle"></i>');
define('ICON_LOGOUT', '<i class="bi bi-box-arrow-right"></i>');

// ========================================
// STATUS BADGES
// ========================================

define('STATUS_BADGES', [
    // User status
    'active' => '<span class="badge bg-success">Active</span>',
    'inactive' => '<span class="badge bg-secondary">Inactive</span>',
    
    // Loan status
    'pending' => '<span class="badge bg-warning">Pending</span>',
    'approved' => '<span class="badge bg-info">Approved</span>',
    'rejected' => '<span class="badge bg-danger">Rejected</span>',
    'disbursed' => '<span class="badge bg-primary">Disbursed</span>',
    'completed' => '<span class="badge bg-success">Completed</span>',
    'defaulted' => '<span class="badge bg-dark">Defaulted</span>',
    
    // Installment status
    'paid' => '<span class="badge bg-success">Paid</span>',
    'partial' => '<span class="badge bg-warning">Partial</span>',
    'overdue' => '<span class="badge bg-danger">Overdue</span>',
]);

// ========================================
// TRANSACTION TYPES
// ========================================

define('TRANSACTION_TYPES', [
    'saving_deposit' => 'Savings Deposit',
    'saving_withdrawal' => 'Savings Withdrawal',
    'loan_disbursement' => 'Loan Disbursement',
    'installment_payment' => 'Installment Payment',
]);

// ========================================
// PAYMENT MODES
// ========================================

define('PAYMENT_MODES', [
    'cash' => 'Cash',
    'online' => 'Online Transfer',
    'cheque' => 'Cheque',
]);

// ========================================
// REPORT TYPES
// ========================================

define('REPORT_TYPES', [
    'savings' => 'Savings Report',
    'loans' => 'Loan Report',
    'collections' => 'Collection Report',
    'members' => 'Member Report',
    'outstanding' => 'Outstanding Loan Report',
    'overdue' => 'Overdue Installments Report',
]);

// ========================================
// MONTHS ARRAY
// ========================================

define('MONTHS', [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December',
]);

// ========================================
// INDIAN STATES
// ========================================

define('INDIAN_STATES', [
    'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
    'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
    'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
    'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
    'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
    'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
    'Andaman and Nicobar Islands', 'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu',
    'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry'
]);

// ========================================
// REGEX PATTERNS
// ========================================

define('REGEX_EMAIL', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
define('REGEX_PHONE', '/^[6-9]\d{9}$/'); // Indian mobile number
define('REGEX_AADHAR', '/^\d{12}$/'); // 12 digit Aadhar number
define('REGEX_PAN', '/^[A-Z]{5}[0-9]{4}[A-Z]$/'); // PAN card format
define('REGEX_PINCODE', '/^\d{6}$/'); // 6 digit pincode
define('REGEX_IFSC', '/^[A-Z]{4}0[A-Z0-9]{6}$/'); // IFSC code format
define('REGEX_ACCOUNT_NUMBER', '/^\d{9,18}$/'); // Bank account number

// ========================================
// ALERT CLASS MAPPING
// ========================================

define('ALERT_CLASSES', [
    'success' => 'alert alert-success alert-dismissible fade show',
    'error' => 'alert alert-danger alert-dismissible fade show',
    'warning' => 'alert alert-warning alert-dismissible fade show',
    'info' => 'alert alert-info alert-dismissible fade show',
]);

// ========================================
// PAGINATION CONFIG
// ========================================

define('PAGINATION_CONFIG', [
    'per_page' => RECORDS_PER_PAGE,
    'links_count' => PAGINATION_LINKS,
    'prev_text' => '&laquo; Previous',
    'next_text' => 'Next &raquo;',
    'first_text' => 'First',
    'last_text' => 'Last',
]);

// ========================================
// EXPORT FORMATS
// ========================================

define('EXPORT_FORMATS', [
    'pdf' => 'PDF',
    'excel' => 'Excel',
    'csv' => 'CSV',
]);

// ========================================
// LOAN PURPOSES
// ========================================

define('LOAN_PURPOSES', [
    'Business' => 'Business',
    'Education' => 'Education',
    'Medical' => 'Medical Emergency',
    'Agriculture' => 'Agriculture',
    'Home Improvement' => 'Home Improvement',
    'Marriage' => 'Marriage',
    'Personal' => 'Personal',
    'Other' => 'Other',
]);

// ========================================
// FILE UPLOAD ERRORS
// ========================================

define('FILE_UPLOAD_ERRORS', [
    UPLOAD_ERR_OK => 'File uploaded successfully',
    UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit',
    UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit',
    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
]);

// ========================================
// JAVASCRIPT CONSTANTS (for inline use)
// ========================================

/**
 * Get JavaScript constants
 * Usage: echo getJsConstants();
 */
function getJsConstants() {
    return '<script>
        const APP_CONFIG = {
            baseUrl: "' . BASE_URL . '",
            assetsUrl: "' . ASSETS_URL . '",
            currencySymbol: "' . CURRENCY_SYMBOL . '",
            dateFormat: "' . DATE_FORMAT . '",
            recordsPerPage: ' . RECORDS_PER_PAGE . ',
            csrfToken: "' . (isset($_SESSION[CSRF_TOKEN_NAME]) ? $_SESSION[CSRF_TOKEN_NAME] : '') . '",
            isLoggedIn: ' . (isLoggedIn() ? 'true' : 'false') . ',
            userRole: "' . ($_SESSION['role'] ?? '') . '"
        };
    </script>';
}

// ========================================
// META TAGS (for SEO)
// ========================================

define('META_DESCRIPTION', 'Bachat Gat Smart Management System - A comprehensive financial management solution for Self-Help Groups');
define('META_KEYWORDS', 'Bachat Gat, SHG, Self Help Group, Financial Management, Loan Management, Savings Management');
define('META_AUTHOR', APP_AUTHOR);

?>
