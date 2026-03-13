-- ========================================
-- QUICK REFERENCE: COMMON SQL QUERIES
-- Bachat Gat Smart Management System
-- ========================================

-- ========================================
-- DASHBOARD ANALYTICS QUERIES
-- ========================================

-- 1. Get total members count
SELECT COUNT(*) as total_members FROM members WHERE status = 'active';

-- 2. Get total savings across all members
SELECT SUM(total_savings) as total_savings FROM members;

-- 3. Get total active loans
SELECT COUNT(*) as active_loans 
FROM loans 
WHERE status IN ('approved', 'disbursed');

-- 4. Get total pending loan applications
SELECT COUNT(*) as pending_loans 
FROM loans 
WHERE status = 'pending';

-- 5. Get total outstanding loan amount
SELECT SUM(amount_remaining) as outstanding_amount 
FROM loans 
WHERE status IN ('approved', 'disbursed');

-- 6. Get monthly savings trend (last 6 months)
SELECT 
    month,
    SUM(amount) as total_amount,
    COUNT(DISTINCT member_id) as members_contributed
FROM savings
WHERE transaction_type = 'deposit'
    AND deposit_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY month
ORDER BY month DESC;

-- 7. Get loan distribution by status (for pie chart)
SELECT 
    status,
    COUNT(*) as count,
    SUM(loan_amount) as total_amount
FROM loans
GROUP BY status
ORDER BY count DESC;

-- 8. Get top 5 members by savings
SELECT 
    m.member_code,
    u.full_name,
    m.total_savings
FROM members m
INNER JOIN users u ON m.user_id = u.user_id
WHERE m.status = 'active'
ORDER BY m.total_savings DESC
LIMIT 5;

-- ========================================
-- MEMBER QUERIES
-- ========================================

-- 9. Get complete member profile
SELECT 
    m.*,
    u.full_name,
    u.email,
    u.phone,
    u.profile_image,
    u.status as account_status
FROM members m
INNER JOIN users u ON m.user_id = u.user_id
WHERE m.member_id = ?;

-- 10. Get member savings history
SELECT 
    saving_id,
    amount,
    deposit_date,
    transaction_type,
    transaction_mode,
    remarks
FROM savings
WHERE member_id = ?
ORDER BY deposit_date DESC;

-- 11. Get member loan summary
SELECT 
    loan_id,
    loan_number,
    loan_amount,
    total_amount,
    amount_paid,
    amount_remaining,
    status,
    application_date
FROM loans
WHERE member_id = ?
ORDER BY application_date DESC;

-- 12. Search members by name or member code
SELECT 
    m.member_id,
    m.member_code,
    u.full_name,
    u.email,
    u.phone,
    m.total_savings,
    m.joining_date,
    m.status
FROM members m
INNER JOIN users u ON m.user_id = u.user_id
WHERE u.full_name LIKE CONCAT('%', ?, '%')
    OR m.member_code LIKE CONCAT('%', ?, '%')
ORDER BY u.full_name;

-- ========================================
-- LOAN MANAGEMENT QUERIES
-- ========================================

-- 13. Get loan details with member info
SELECT 
    l.*,
    m.member_code,
    u.full_name as member_name,
    u.email,
    u.phone,
    admin.full_name as approved_by_name
FROM loans l
INNER JOIN members m ON l.member_id = m.member_id
INNER JOIN users u ON m.user_id = u.user_id
LEFT JOIN users admin ON l.approved_by = admin.user_id
WHERE l.loan_id = ?;

-- 14. Get pending loan applications
SELECT 
    l.loan_id,
    l.loan_number,
    m.member_code,
    u.full_name,
    l.loan_amount,
    l.purpose,
    l.application_date,
    DATEDIFF(CURDATE(), l.application_date) as days_pending
FROM loans l
INNER JOIN members m ON l.member_id = m.member_id
INNER JOIN users u ON m.user_id = u.user_id
WHERE l.status = 'pending'
ORDER BY l.application_date ASC;

-- 15. Calculate loan details (before approval)
SELECT 
    ? as loan_amount,
    ? as interest_rate,
    (? + (? * ? / 100)) as total_amount,
    ((? + (? * ? / 100)) / ?) as monthly_installment;

-- 16. Get overdue installments
SELECT 
    i.installment_id,
    l.loan_number,
    m.member_code,
    u.full_name,
    i.installment_number,
    i.due_date,
    i.installment_amount,
    DATEDIFF(CURDATE(), i.due_date) as days_overdue
FROM installments i
INNER JOIN loans l ON i.loan_id = l.loan_id
INNER JOIN members m ON l.member_id = m.member_id
INNER JOIN users u ON m.user_id = u.user_id
WHERE i.status IN ('pending', 'overdue')
    AND i.due_date < CURDATE()
ORDER BY i.due_date ASC;

-- ========================================
-- INSTALLMENT QUERIES
-- ========================================

-- 17. Get installment schedule for a loan
SELECT 
    installment_id,
    installment_number,
    due_date,
    installment_amount,
    paid_amount,
    payment_date,
    status,
    IF(status = 'pending' AND due_date < CURDATE(), 'Yes', 'No') as is_overdue
FROM installments
WHERE loan_id = ?
ORDER BY installment_number ASC;

-- 18. Record installment payment
UPDATE installments 
SET 
    paid_amount = ?,
    payment_date = ?,
    payment_mode = ?,
    transaction_reference = ?,
    status = 'paid',
    recorded_by = ?
WHERE installment_id = ?;

-- 19. Get upcoming installments (next 7 days)
SELECT 
    i.installment_id,
    l.loan_number,
    m.member_code,
    u.full_name,
    u.phone,
    i.due_date,
    i.installment_amount,
    DATEDIFF(i.due_date, CURDATE()) as days_until_due
FROM installments i
INNER JOIN loans l ON i.loan_id = l.loan_id
INNER JOIN members m ON l.member_id = m.member_id
INNER JOIN users u ON m.user_id = u.user_id
WHERE i.status = 'pending'
    AND i.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
ORDER BY i.due_date ASC;

-- ========================================
-- REPORT QUERIES
-- ========================================

-- 20. Monthly savings report
SELECT 
    m.member_code,
    u.full_name,
    COALESCE(SUM(CASE WHEN s.transaction_type = 'deposit' THEN s.amount ELSE 0 END), 0) as deposits,
    COALESCE(SUM(CASE WHEN s.transaction_type = 'withdrawal' THEN s.amount ELSE 0 END), 0) as withdrawals,
    COALESCE(SUM(CASE WHEN s.transaction_type = 'deposit' THEN s.amount ELSE -s.amount END), 0) as net_savings
FROM members m
INNER JOIN users u ON m.user_id = u.user_id
LEFT JOIN savings s ON m.member_id = s.member_id AND s.month = ?
WHERE m.status = 'active'
GROUP BY m.member_id
ORDER BY net_savings DESC;

-- 21. Loan disbursement report (date range)
SELECT 
    l.loan_number,
    l.disbursement_date,
    m.member_code,
    u.full_name,
    l.loan_amount,
    l.interest_rate,
    l.total_amount,
    l.installment_months,
    admin.full_name as approved_by
FROM loans l
INNER JOIN members m ON l.member_id = m.member_id
INNER JOIN users u ON m.user_id = u.user_id
LEFT JOIN users admin ON l.approved_by = admin.user_id
WHERE l.status = 'disbursed'
    AND l.disbursement_date BETWEEN ? AND ?
ORDER BY l.disbursement_date DESC;

-- 22. Member-wise loan outstanding report
SELECT 
    m.member_code,
    u.full_name,
    u.phone,
    COUNT(l.loan_id) as total_loans,
    SUM(l.total_amount) as total_loan_amount,
    SUM(l.amount_paid) as total_paid,
    SUM(l.amount_remaining) as total_outstanding
FROM members m
INNER JOIN users u ON m.user_id = u.user_id
LEFT JOIN loans l ON m.member_id = l.member_id 
    AND l.status IN ('approved', 'disbursed')
WHERE m.status = 'active'
GROUP BY m.member_id
HAVING total_outstanding > 0
ORDER BY total_outstanding DESC;

-- 23. Collection report (installments collected in date range)
SELECT 
    i.payment_date,
    COUNT(*) as installments_collected,
    SUM(i.paid_amount) as total_collection,
    admin.full_name as collected_by
FROM installments i
LEFT JOIN users admin ON i.recorded_by = admin.user_id
WHERE i.status = 'paid'
    AND i.payment_date BETWEEN ? AND ?
GROUP BY i.payment_date, i.recorded_by
ORDER BY i.payment_date DESC;

-- ========================================
-- AUTHENTICATION & USER MANAGEMENT
-- ========================================

-- 24. User login verification
SELECT 
    u.user_id,
    u.full_name,
    u.email,
    u.password,
    u.role,
    u.status,
    u.profile_image,
    m.member_id,
    m.member_code
FROM users u
LEFT JOIN members m ON u.user_id = m.user_id
WHERE u.email = ?
LIMIT 1;

-- 25. Create new member user
INSERT INTO users (full_name, email, phone, password, role, status)
VALUES (?, ?, ?, ?, 'member', 'active');

INSERT INTO members (user_id, member_code, address, city, state, pincode, joining_date)
VALUES (LAST_INSERT_ID(), ?, ?, ?, ?, ?, ?);

-- 26. Update user profile
UPDATE users 
SET 
    full_name = ?,
    phone = ?,
    updated_at = CURRENT_TIMESTAMP
WHERE user_id = ?;

UPDATE members
SET 
    address = ?,
    city = ?,
    state = ?,
    pincode = ?,
    bank_account = ?,
    bank_name = ?,
    ifsc_code = ?
WHERE user_id = ?;

-- ========================================
-- NOTIFICATION QUERIES
-- ========================================

-- 27. Get unread notifications for user
SELECT 
    notification_id,
    title,
    message,
    type,
    created_at
FROM notifications
WHERE user_id = ?
    AND is_read = FALSE
ORDER BY created_at DESC;

-- 28. Mark notification as read
UPDATE notifications
SET is_read = TRUE
WHERE notification_id = ?;

-- 29. Create notification
INSERT INTO notifications (user_id, title, message, type)
VALUES (?, ?, ?, ?);

-- ========================================
-- ACTIVITY LOG QUERIES
-- ========================================

-- 30. Log user activity
INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent)
VALUES (?, ?, ?, ?, ?);

-- 31. Get user activity history
SELECT 
    action,
    description,
    ip_address,
    created_at
FROM activity_logs
WHERE user_id = ?
ORDER BY created_at DESC
LIMIT 50;

-- ========================================
-- SYSTEM SETTINGS
-- ========================================

-- 32. Get loan settings
SELECT * FROM loan_settings ORDER BY setting_id DESC LIMIT 1;

-- 33. Update loan settings
UPDATE loan_settings
SET 
    interest_rate = ?,
    max_loan_amount = ?,
    min_loan_amount = ?,
    max_installment_months = ?,
    min_installment_months = ?,
    updated_by = ?,
    updated_at = CURRENT_TIMESTAMP
WHERE setting_id = 1;

-- 34. Get system setting
SELECT setting_value 
FROM system_settings 
WHERE setting_key = ?;

-- 35. Update system setting
UPDATE system_settings
SET setting_value = ?,
    updated_at = CURRENT_TIMESTAMP
WHERE setting_key = ?;

-- ========================================
-- ADVANCED ANALYTICS
-- ========================================

-- 36. Member growth trend (monthly new members)
SELECT 
    DATE_FORMAT(joining_date, '%Y-%m') as month,
    COUNT(*) as new_members
FROM members
WHERE joining_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY month
ORDER BY month ASC;

-- 37. Loan approval rate
SELECT 
    COUNT(*) as total_applications,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    ROUND((SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as approval_rate
FROM loans;

-- 38. Collection efficiency (installments paid on time)
SELECT 
    COUNT(*) as total_installments,
    SUM(CASE WHEN status = 'paid' AND payment_date <= due_date THEN 1 ELSE 0 END) as on_time_payments,
    SUM(CASE WHEN status = 'paid' AND payment_date > due_date THEN 1 ELSE 0 END) as late_payments,
    SUM(CASE WHEN status IN ('pending', 'overdue') AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue,
    ROUND((SUM(CASE WHEN status = 'paid' AND payment_date <= due_date THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as efficiency_rate
FROM installments;

-- ========================================
-- DATA CLEANUP & MAINTENANCE
-- ========================================

-- 39. Mark overdue installments
UPDATE installments
SET status = 'overdue'
WHERE status = 'pending'
    AND due_date < CURDATE();

-- 40. Delete old activity logs (older than 1 year)
DELETE FROM activity_logs
WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);

-- ========================================
-- END OF QUICK REFERENCE
-- ========================================
