# 📊 DATABASE SCHEMA DOCUMENTATION
## Bachat Gat Smart Management System

---

## 🎯 Database Overview

**Database Name:** `bachat_gat_db`  
**Character Set:** utf8mb4  
**Collation:** utf8mb4_unicode_ci  
**Total Tables:** 11  
**Total Views:** 2  
**Total Triggers:** 2

---

## 📋 TABLE STRUCTURE & RELATIONSHIPS

### 1️⃣ **users** (Core Authentication Table)
**Purpose:** Stores user login credentials and basic profile information

| Column | Type | Description |
|--------|------|-------------|
| user_id | INT (PK) | Unique user identifier |
| full_name | VARCHAR(100) | User's full name |
| email | VARCHAR(100) | Unique email (login username) |
| phone | VARCHAR(15) | Contact number |
| password | VARCHAR(255) | Hashed password using `password_hash()` |
| role | ENUM | 'admin' or 'member' |
| profile_image | VARCHAR(255) | Profile picture filename |
| status | ENUM | 'active' or 'inactive' |
| created_at | TIMESTAMP | Registration timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

**Indexes:**
- `idx_email` - Fast email lookup for login
- `idx_role` - Filter by user role

**Default Admin Credentials:**
- Email: `admin@bachatgat.com`
- Password: `admin@123`

---

### 2️⃣ **members** (Extended Member Information)
**Purpose:** Stores detailed member profile and financial summary

| Column | Type | Description |
|--------|------|-------------|
| member_id | INT (PK) | Unique member identifier |
| user_id | INT (FK) | Links to users table |
| member_code | VARCHAR(20) | Unique member code (e.g., MEM001) |
| address | TEXT | Complete address |
| city | VARCHAR(50) | City name |
| state | VARCHAR(50) | State name |
| pincode | VARCHAR(10) | Postal code |
| aadhar_number | VARCHAR(12) | Unique Aadhar ID |
| pan_number | VARCHAR(10) | Unique PAN number |
| bank_account | VARCHAR(20) | Bank account number |
| bank_name | VARCHAR(100) | Bank name |
| ifsc_code | VARCHAR(11) | IFSC code |
| joining_date | DATE | Member joining date |
| total_savings | DECIMAL(12,2) | Auto-calculated total savings |
| status | ENUM | 'active' or 'inactive' |

**Relationships:**
- `user_id` → `users.user_id` (CASCADE DELETE)

**Business Logic:**
- `total_savings` is automatically updated by trigger when savings are deposited/withdrawn
- `member_code` must be unique (used for reports and identification)

---

### 3️⃣ **savings** (Monthly Savings Transactions)
**Purpose:** Tracks all deposit and withdrawal transactions

| Column | Type | Description |
|--------|------|-------------|
| saving_id | INT (PK) | Unique transaction ID |
| member_id | INT (FK) | Links to members table |
| amount | DECIMAL(10,2) | Transaction amount |
| deposit_date | DATE | Transaction date |
| month | VARCHAR(7) | Format: YYYY-MM (for monthly reports) |
| transaction_type | ENUM | 'deposit' or 'withdrawal' |
| transaction_mode | ENUM | 'cash', 'online', or 'cheque' |
| reference_number | VARCHAR(50) | Transaction reference (optional) |
| remarks | TEXT | Additional notes |
| recorded_by | INT (FK) | Admin who recorded this |

**Indexes:**
- `idx_member_id` - Filter by member
- `idx_deposit_date` - Date-based queries
- `idx_month` - Monthly report generation

**Trigger:**
- `after_savings_insert` - Automatically updates `members.total_savings`

---

### 4️⃣ **loan_settings** (Global Loan Configuration)
**Purpose:** Stores system-wide loan interest rates and limits

| Column | Type | Description |
|--------|------|-------------|
| setting_id | INT (PK) | Setting ID |
| interest_rate | DECIMAL(5,2) | Annual interest rate (%) |
| max_loan_amount | DECIMAL(12,2) | Maximum loan allowed |
| min_loan_amount | DECIMAL(10,2) | Minimum loan allowed |
| max_installment_months | INT | Maximum installment period |
| min_installment_months | INT | Minimum installment period |
| updated_by | INT (FK) | Admin who updated settings |

**Default Values:**
- Interest Rate: 12% per annum
- Max Loan: ₹1,00,000
- Min Loan: ₹5,000
- Installment Range: 3-24 months

---

### 5️⃣ **loans** (Loan Applications & Status)
**Purpose:** Manages loan lifecycle from application to completion

| Column | Type | Description |
|--------|------|-------------|
| loan_id | INT (PK) | Unique loan identifier |
| member_id | INT (FK) | Borrower member ID |
| loan_number | VARCHAR(20) | Unique loan number |
| loan_amount | DECIMAL(12,2) | Principal amount |
| interest_rate | DECIMAL(5,2) | Applied interest rate |
| total_amount | DECIMAL(12,2) | loan_amount + interest |
| installment_months | INT | Number of monthly installments |
| monthly_installment | DECIMAL(10,2) | Fixed monthly payment |
| application_date | DATE | When applied |
| approval_date | DATE | When approved |
| disbursement_date | DATE | When disbursed |
| purpose | TEXT | Loan purpose description |
| status | ENUM | Loan status (see below) |
| amount_paid | DECIMAL(12,2) | Total paid so far |
| amount_remaining | DECIMAL(12,2) | Remaining balance |
| approved_by | INT (FK) | Admin who approved |
| remarks | TEXT | Admin notes |

**Loan Status Flow:**
1. `pending` - Application submitted
2. `approved` - Admin approved
3. `rejected` - Admin rejected
4. `disbursed` - Money disbursed to member
5. `completed` - Fully paid
6. `defaulted` - Payment defaulted

**Calculation Logic:**
```
total_amount = loan_amount + (loan_amount × interest_rate / 100)
monthly_installment = total_amount / installment_months
amount_remaining = total_amount - amount_paid
```

---

### 6️⃣ **installments** (Loan Installment Schedule)
**Purpose:** Tracks individual monthly installment payments

| Column | Type | Description |
|--------|------|-------------|
| installment_id | INT (PK) | Unique installment ID |
| loan_id | INT (FK) | Links to loans table |
| installment_number | INT | 1, 2, 3... (sequence) |
| due_date | DATE | Payment due date |
| installment_amount | DECIMAL(10,2) | Expected payment |
| paid_amount | DECIMAL(10,2) | Actual payment received |
| payment_date | DATE | When paid |
| payment_mode | ENUM | 'cash', 'online', 'cheque' |
| transaction_reference | VARCHAR(50) | Payment reference |
| status | ENUM | 'pending', 'paid', 'partial', 'overdue' |
| late_fee | DECIMAL(8,2) | Penalty for late payment |
| recorded_by | INT (FK) | Admin who recorded payment |

**Trigger:**
- `after_installment_payment` - Updates loan's `amount_paid` and `amount_remaining`
- Auto-completes loan when fully paid

---

### 7️⃣ **transactions** (Complete Audit Log)
**Purpose:** Comprehensive transaction history for all financial activities

| Column | Type | Description |
|--------|------|-------------|
| transaction_id | INT (PK) | Unique transaction ID |
| member_id | INT (FK) | Member involved |
| transaction_type | ENUM | Type of transaction |
| amount | DECIMAL(12,2) | Transaction amount |
| reference_id | INT | Links to related record |
| transaction_date | DATE | Transaction date |
| description | TEXT | Transaction details |
| recorded_by | INT (FK) | Admin who recorded |

**Transaction Types:**
- `saving_deposit` - Savings deposit
- `saving_withdrawal` - Savings withdrawal
- `loan_disbursement` - Loan amount given
- `installment_payment` - Loan installment paid

---

### 8️⃣ **notifications** (User Notifications)
**Purpose:** System notifications for users

| Column | Type | Description |
|--------|------|-------------|
| notification_id | INT (PK) | Notification ID |
| user_id | INT (FK) | Recipient user |
| title | VARCHAR(100) | Notification title |
| message | TEXT | Notification message |
| type | ENUM | 'info', 'success', 'warning', 'error' |
| is_read | BOOLEAN | Read status |
| created_at | TIMESTAMP | When created |

---

### 9️⃣ **system_settings** (App Configuration)
**Purpose:** General system configuration key-value pairs

**Default Settings:**
- `app_name` - Application name
- `currency_symbol` - ₹ (Indian Rupee)
- `date_format` - Y-m-d
- `records_per_page` - 10
- `enable_email_notifications` - 1
- `enable_sms_notifications` - 0

---

### 🔟 **activity_logs** (Security Audit Trail)
**Purpose:** Track all user activities for security

| Column | Type | Description |
|--------|------|-------------|
| log_id | INT (PK) | Log ID |
| user_id | INT (FK) | User who performed action |
| action | VARCHAR(100) | Action performed |
| description | TEXT | Action details |
| ip_address | VARCHAR(45) | User's IP address |
| user_agent | TEXT | Browser/device info |
| created_at | TIMESTAMP | When action occurred |

---

## 🔍 DATABASE VIEWS

### 1. **view_member_summary**
Aggregated member information with savings and loan summary

**Columns:**
- member_id, member_code, full_name, email, phone
- total_savings
- total_loans (count)
- outstanding_loan_amount
- status

**Usage:** Dashboard analytics, member list with financial summary

---

### 2. **view_loan_summary**
Comprehensive loan details with member info and installment progress

**Columns:**
- loan_id, loan_number, member_code, member_name
- loan_amount, total_amount, amount_paid, amount_remaining
- installment_months, monthly_installment
- total_installments, paid_installments
- application_date, approval_date, status

**Usage:** Loan reports, dashboard charts, installment tracking

---

## ⚡ DATABASE TRIGGERS

### 1. **after_savings_insert**
**Event:** AFTER INSERT on `savings`  
**Purpose:** Auto-update member's total_savings

**Logic:**
```sql
IF transaction_type = 'deposit' THEN
    total_savings = total_savings + amount
ELSE IF transaction_type = 'withdrawal' THEN
    total_savings = total_savings - amount
```

---

### 2. **after_installment_payment**
**Event:** AFTER UPDATE on `installments`  
**Purpose:** Update loan payment status

**Logic:**
```sql
IF status changed to 'paid' THEN
    UPDATE loans SET 
        amount_paid = amount_paid + paid_amount
        amount_remaining = amount_remaining - paid_amount
    
    IF amount_remaining <= 0 THEN
        SET status = 'completed'
```

---

## 🔐 SECURITY FEATURES

### ✅ Implemented Security Measures:

1. **Password Hashing:**
   - All passwords stored using PHP's `password_hash()` with bcrypt
   - Sample hash: `$2y$10$...` (60 characters)

2. **Foreign Key Constraints:**
   - CASCADE DELETE for dependent records
   - Prevents orphaned data

3. **Indexes for Performance:**
   - All frequently queried columns indexed
   - Composite indexes on member_id, date columns

4. **Data Integrity:**
   - UNIQUE constraints on email, member_code, loan_number
   - NOT NULL constraints on critical fields
   - ENUM constraints for status fields

5. **Audit Trail:**
   - activity_logs table tracks all user actions
   - timestamp tracking on all tables
   - IP address and user agent logging

---

## 📊 SAMPLE DATA INCLUDED

✅ **Default Admin User:**
- Email: admin@bachatgat.com
- Password: admin@123
- Role: admin

✅ **3 Sample Members:**
- MEM001 - Rajesh Kumar
- MEM002 - Priya Sharma
- MEM003 - Amit Patel
- All with password: member@123

✅ **Sample Savings Records:**
- Each member has 1 deposit record for March 2024

---

## 🚀 INSTALLATION STEPS

### Step 1: Import Database
```sql
mysql -u root -p < schema.sql
```

### Step 2: Verify Installation
```sql
USE bachat_gat_db;
SHOW TABLES;
SELECT * FROM users;
```

### Step 3: Test Login Credentials
- Admin: admin@bachatgat.com / admin@123
- Member: rajesh@example.com / member@123

---

## 📈 SCALABILITY CONSIDERATIONS

### ✅ Built-in Scalability Features:

1. **Indexed Columns:** Fast queries even with large datasets
2. **Normalized Structure:** Minimal data redundancy
3. **Efficient Triggers:** Only update necessary fields
4. **Partitionable Tables:** Can be partitioned by date if needed
5. **View Optimization:** Pre-aggregated data for reports

### 🔮 Future Enhancements Possible:

- Add archival tables for old records
- Implement data partitioning by year
- Add full-text search on member names
- Create materialized views for heavy reports
- Add Redis caching layer

---

## 📞 DATABASE RELATIONSHIP DIAGRAM

```
users (1) ──────< (M) members
  │                      │
  │                      ├───< (M) savings
  │                      │
  │                      └───< (M) loans
  │                               │
  │                               └───< (M) installments
  │
  ├───< (M) notifications
  │
  └───< (M) activity_logs

loan_settings (1) ─────── Used for loan calculations
system_settings (1) ────── Global app configuration
transactions (M) ─────────── Complete audit trail
```

---

## ✨ KEY DESIGN DECISIONS

1. **Separate users and members tables:**
   - Allows non-member users (admins, staff)
   - Clean separation of auth and profile data

2. **Auto-calculated fields:**
   - total_savings updated by trigger
   - amount_remaining updated automatically
   - Reduces calculation errors

3. **Month column in savings:**
   - Format: YYYY-MM
   - Enables fast monthly report generation

4. **Enum for status fields:**
   - Type-safe status values
   - Better than using strings or integers

5. **Comprehensive audit trail:**
   - transactions table logs everything
   - activity_logs for security
   - created_at/updated_at timestamps

---

**🎓 Perfect for:**
- College Projects ✅
- Portfolio Showcase ✅
- Interview Demonstrations ✅
- Real-world SHG Management ✅

---

**Version:** 1.0  
**Last Updated:** March 2026  
**Author:** Senior Full-Stack Developer
