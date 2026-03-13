-- ========================================
-- USER TOKENS TABLE (For Remember Me & Password Reset)
-- ========================================

CREATE TABLE IF NOT EXISTS `user_tokens` (
  `token_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `type` ENUM('remember_me', 'password_reset', 'email_verification') NOT NULL DEFAULT 'remember_me',
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_user_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- ADD INDEX FOR LOGIN ATTEMPTS
-- ========================================

-- This helps with checkLoginAttempts function performance
ALTER TABLE `activity_logs` ADD INDEX `idx_action_created` (`action`, `created_at`);

-- ========================================
-- INSTRUCTIONS
-- ========================================

-- Run this migration file if you've already created the database using schema.sql
-- This adds the user_tokens table which is required for:
-- 1. Remember Me functionality
-- 2. Password Reset functionality
-- 3. Email Verification (future feature)

-- To run this migration:
-- 1. Open phpMyAdmin or MySQL command line
-- 2. Select your database (bachat_gat_db)
-- 3. Run this SQL file

-- OR use command line:
-- mysql -u root -p bachat_gat_db < migration_user_tokens.sql
