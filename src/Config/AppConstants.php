<?php

namespace Config;

/**
 * Application Constants
 * Centralized location for all hardcoded application values
 * Replaces scattered magic numbers and strings throughout codebase
 * 
 * These values are application-level and don't change per environment
 * For environment-specific values, use .env file and Env class
 */
class AppConstants
{
    // ========================================
    // Application Information
    // ========================================
    const APP_NAME = 'Bachat Gat Smart Management';
    const APP_SHORT_NAME = 'Bachat Gat';
    const APP_VERSION = '1.0.0';
    const APP_AUTHOR = 'Development Team';
    const APP_DESCRIPTION = 'Self-Help Group Financial Management System';
    
    // ========================================
    // Security Constants
    // ========================================
    const PASSWORD_HASH_ALGO = PASSWORD_BCRYPT;
    const PASSWORD_HASH_COST = 12;
    const SESSION_TIMEOUT = 1800; // 30 minutes
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOGIN_ATTEMPT_LOCKOUT = 900; // 15 minutes
    const CSRF_TOKEN_LENGTH = 32;
    
    // ========================================
    // Business Logic Constants
    // ========================================
    const MIN_LOAN_AMOUNT = 1000;
    const MAX_LOAN_AMOUNT = 1000000;
    const MIN_TENURE_MONTHS = 1;
    const MAX_TENURE_MONTHS = 120;
    const MIN_INTEREST_RATE = 0;
    const MAX_INTEREST_RATE = 50;
    const MIN_WITHDRAWAL_AMOUNT = 100;
    const DEFAULT_INTEREST_RATE = 12.5;
    
    // ========================================
    // Member Constants
    // ========================================
    const MEMBER_CODE_PREFIX = 'MEM';
    const MEMBER_STATUS_ACTIVE = 'active';
    const MEMBER_STATUS_INACTIVE = 'inactive';
    const MEMBER_STATUS_SUSPENDED = 'suspended';
    
    // Loan statuses
    const LOAN_STATUS_PENDING = 'pending';
    const LOAN_STATUS_APPROVED = 'approved';
    const LOAN_STATUS_DISBURSED = 'disbursed';
    const LOAN_STATUS_COMPLETED = 'completed';
    const LOAN_STATUS_REJECTED = 'rejected';
    
    // Transaction types
    const TRANSACTION_TYPE_DEPOSIT = 'deposit';
    const TRANSACTION_TYPE_WITHDRAWAL = 'withdrawal';
    const TRANSACTION_TYPE_LOAN_DISBURSEMENT = 'loan_disbursement';
    const TRANSACTION_TYPE_LOAN_REPAYMENT = 'loan_repayment';
    const TRANSACTION_TYPE_INTEREST_CREDIT = 'interest_credit';
    
    // ========================================
    // Pagination Constants
    // ========================================
    const DEFAULT_PAGE_SIZE = 50;
    const MIN_PAGE_SIZE = 5;
    const MAX_PAGE_SIZE = 100;
    
    // ========================================
    // Date/Time Constants
    // ========================================
    const DATE_FORMAT = 'Y-m-d';
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const TIME_FORMAT = 'H:i:s';
    const TIMEZONE = 'Asia/Kolkata';
    
    // ========================================
    // File Upload Constants
    // ========================================
    const MAX_UPLOAD_SIZE = 5242880; // 5 MB in bytes
    const UPLOAD_DIR_DOCUMENTS = 'assets/uploads/documents/';
    const UPLOAD_DIR_PROFILES = 'assets/uploads/profiles/';
    const ALLOWED_DOCUMENT_TYPES = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    const ALLOWED_PROFILE_TYPES = ['jpg', 'jpeg', 'png', 'gif'];
    
    // ========================================
    // Role Constants
    // ========================================
    const ROLE_ADMIN = 'admin';
    const ROLE_MEMBER = 'member';
    const ROLE_STAFF = 'staff';
    
    // ========================================
    // Validation Regex Patterns
    // ========================================
    const REGEX_EMAIL = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    const REGEX_PHONE = '/^[6-9]\d{9}$/'; // 10-digit Indian phone
    const REGEX_AADHAR = '/^\d{4}[\s-]?\d{4}[\s-]?\d{4}$/';
    const REGEX_PAN = '/^[A-Z]{5}[0-9]{4}[A-Z]$/';
    const REGEX_IFSC = '/^[A-Z]{4}0[A-Z0-9]{6}$/';
    
    // ========================================
    // API Constants
    // ========================================
    const API_RESPONSE_SUCCESS = true;
    const API_RESPONSE_FAILURE = false;
    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_CREATED = 201;
    const HTTP_STATUS_BAD_REQUEST = 400;
    const HTTP_STATUS_UNAUTHORIZED = 401;
    const HTTP_STATUS_FORBIDDEN = 403;
    const HTTP_STATUS_NOT_FOUND = 404;
    const HTTP_STATUS_SERVER_ERROR = 500;
    
    // ========================================
    // Alert/Message Types
    // ========================================
    const ALERT_TYPE_SUCCESS = 'success';
    const ALERT_TYPE_ERROR = 'error';
    const ALERT_TYPE_WARNING = 'warning';
    const ALERT_TYPE_INFO = 'info';
    
    // ========================================
    // Cache Constants
    // ========================================
    const CACHE_TTL_SHORT = 300; // 5 minutes
    const CACHE_TTL_MEDIUM = 1800; // 30 minutes
    const CACHE_TTL_LONG = 86400; // 24 hours
    const CACHE_DIR = 'cache/';
    
    // ========================================
    // Logging Constants
    // ========================================
    const LOG_LEVEL_DEBUG = 'debug';
    const LOG_LEVEL_INFO = 'info';
    const LOG_LEVEL_WARNING = 'warning';
    const LOG_LEVEL_ERROR = 'error';
    const LOG_LEVEL_CRITICAL = 'critical';
    const LOG_DIR = 'logs/';
    
    /**
     * Get all member statuses
     */
    public static function getMemberStatuses()
    {
        return [
            self::MEMBER_STATUS_ACTIVE,
            self::MEMBER_STATUS_INACTIVE,
            self::MEMBER_STATUS_SUSPENDED
        ];
    }
    
    /**
     * Get all loan statuses
     */
    public static function getLoanStatuses()
    {
        return [
            self::LOAN_STATUS_PENDING,
            self::LOAN_STATUS_APPROVED,
            self::LOAN_STATUS_DISBURSED,
            self::LOAN_STATUS_COMPLETED,
            self::LOAN_STATUS_REJECTED
        ];
    }
    
    /**
     * Get all transaction types
     */
    public static function getTransactionTypes()
    {
        return [
            self::TRANSACTION_TYPE_DEPOSIT,
            self::TRANSACTION_TYPE_WITHDRAWAL,
            self::TRANSACTION_TYPE_LOAN_DISBURSEMENT,
            self::TRANSACTION_TYPE_LOAN_REPAYMENT,
            self::TRANSACTION_TYPE_INTEREST_CREDIT
        ];
    }
    
    /**
     * Get all user roles
     */
    public static function getUserRoles()
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_MEMBER,
            self::ROLE_STAFF
        ];
    }
    
    /**
     * Get all alert types
     */
    public static function getAlertTypes()
    {
        return [
            self::ALERT_TYPE_SUCCESS,
            self::ALERT_TYPE_ERROR,
            self::ALERT_TYPE_WARNING,
            self::ALERT_TYPE_INFO
        ];
    }
}

// Make constants easily accessible
if (!function_exists('appConst')) {
    /**
     * Helper function to get app constants
     */
    function appConst($constantName, $default = null)
    {
        $constantKey = 'Config\\AppConstants::' . $constantName;
        
        if (defined($constantKey)) {
            return constant($constantKey);
        }
        
        return $default;
    }
}
