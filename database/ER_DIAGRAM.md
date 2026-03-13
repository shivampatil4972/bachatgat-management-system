# 📐 DATABASE ENTITY RELATIONSHIP DIAGRAM
## Bachat Gat Smart Management System

---

## 🎯 COMPLETE ER DIAGRAM (Text Representation)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        BACHAT GAT DATABASE STRUCTURE                        │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────┐
│       USERS         │ ◄─────────────────┐
├─────────────────────┤                   │
│ 🔑 user_id (PK)     │                   │
│    full_name        │                   │
│ 🔐 email (UNIQUE)   │                   │
│    phone            │                   │
│    password (HASH)  │                   │
│    role (ENUM)      │                   │
│    profile_image    │                   │
│    status           │                   │
│    created_at       │                   │
│    updated_at       │                   │
└─────────────────────┘                   │
         │                                │
         │ 1                              │
         │                                │
         │ M                              │
         ▼                                │
┌─────────────────────┐                   │
│      MEMBERS        │                   │
├─────────────────────┤                   │
│ 🔑 member_id (PK)   │                   │
│ 🔗 user_id (FK)     │───────────────────┘
│    member_code      │
│    address          │
│    city, state      │
│    pincode          │
│    aadhar_number    │
│    pan_number       │
│    bank_account     │
│    bank_name        │
│    ifsc_code        │
│    joining_date     │
│ 💰 total_savings    │ ◄──── Auto-calculated by trigger
│    status           │
└─────────────────────┘
         │
         │ 1
         │
         ├──────────────────┬──────────────────┐
         │ M                │ M                │ M
         ▼                  ▼                  ▼
┌─────────────────┐  ┌─────────────────┐  ┌──────────────────┐
│    SAVINGS      │  │      LOANS      │  │  TRANSACTIONS    │
├─────────────────┤  ├─────────────────┤  ├──────────────────┤
│ 🔑 saving_id    │  │ 🔑 loan_id (PK) │  │ 🔑 transaction_id│
│ 🔗 member_id    │  │ 🔗 member_id    │  │ 🔗 member_id     │
│    amount       │  │    loan_number  │  │    type          │
│    deposit_date │  │    loan_amount  │  │    amount        │
│    month        │  │    interest_rate│  │    reference_id  │
│    trans_type   │  │ 💵 total_amount │  │    date          │
│    trans_mode   │  │    installments │  │    description   │
│    reference_no │  │    monthly_inst │  │    recorded_by   │
│    remarks      │  │    app_date     │  └──────────────────┘
│ 🔗 recorded_by  │  │    approval_date│
└─────────────────┘  │    disb_date    │
         │           │    purpose      │
         │           │    status       │
         │           │ 💰 amount_paid  │ ◄──── Auto-updated
         │           │ 💰 amount_rem   │       by trigger
         │           │ 🔗 approved_by  │
         │           │    remarks      │
         │           └─────────────────┘
         │                    │
         │ Trigger            │ 1
         │ Updates            │
         │ total_savings      │ M
         │                    ▼
         │           ┌─────────────────────┐
         │           │   INSTALLMENTS      │
         │           ├─────────────────────┤
         │           │ 🔑 installment_id   │
         │           │ 🔗 loan_id (FK)     │
         │           │    installment_no   │
         │           │    due_date         │
         │           │    installment_amt  │
         │           │    paid_amount      │
         │           │    payment_date     │
         │           │    payment_mode     │
         │           │    trans_reference  │
         │           │    status           │
         │           │    late_fee         │
         │           │ 🔗 recorded_by      │
         │           └─────────────────────┘
         │                    │
         │                    │ Trigger
         │                    │ Updates
         │                    │ loan amounts
         │                    │
┌────────┴──────────────────────────────────────────────┐
│                 SUPPORTING TABLES                     │
└───────────────────────────────────────────────────────┘

┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│  NOTIFICATIONS   │  │ ACTIVITY_LOGS    │  │ LOAN_SETTINGS    │
├──────────────────┤  ├──────────────────┤  ├──────────────────┤
│ 🔑 notif_id      │  │ 🔑 log_id        │  │ 🔑 setting_id    │
│ 🔗 user_id       │  │ 🔗 user_id       │  │    interest_rate │
│    title         │  │    action        │  │    max_loan_amt  │
│    message       │  │    description   │  │    min_loan_amt  │
│    type          │  │    ip_address    │  │    max_months    │
│    is_read       │  │    user_agent    │  │    min_months    │
│    created_at    │  │    created_at    │  │    updated_by    │
└──────────────────┘  └──────────────────┘  └──────────────────┘

┌──────────────────┐
│ SYSTEM_SETTINGS  │
├──────────────────┤
│ 🔑 setting_id    │
│    setting_key   │
│    setting_value │
│    description   │
│    updated_at    │
└──────────────────┘
```

---

## 🔗 RELATIONSHIPS EXPLAINED

### 1️⃣ **USERS → MEMBERS** (One-to-One)
- **Type:** 1:1 relationship
- **Foreign Key:** `members.user_id` → `users.user_id`
- **Cascade:** DELETE CASCADE
- **Logic:** Each user account can be linked to one member profile
- **Note:** Admins don't have member profiles

### 2️⃣ **MEMBERS → SAVINGS** (One-to-Many)
- **Type:** 1:M relationship
- **Foreign Key:** `savings.member_id` → `members.member_id`
- **Cascade:** DELETE CASCADE
- **Logic:** One member can have multiple savings transactions
- **Trigger:** Updates `members.total_savings` on INSERT

### 3️⃣ **MEMBERS → LOANS** (One-to-Many)
- **Type:** 1:M relationship
- **Foreign Key:** `loans.member_id` → `members.member_id`
- **Cascade:** DELETE CASCADE
- **Logic:** One member can have multiple loans
- **Business Rule:** Only one active loan at a time (enforced in PHP)

### 4️⃣ **LOANS → INSTALLMENTS** (One-to-Many)
- **Type:** 1:M relationship
- **Foreign Key:** `installments.loan_id` → `loans.loan_id`
- **Cascade:** DELETE CASCADE
- **Logic:** One loan has multiple monthly installments
- **Trigger:** Updates loan's `amount_paid` and `amount_remaining` when installment is paid

### 5️⃣ **USERS → NOTIFICATIONS** (One-to-Many)
- **Type:** 1:M relationship
- **Foreign Key:** `notifications.user_id` → `users.user_id`
- **Cascade:** DELETE CASCADE
- **Logic:** One user receives many notifications

### 6️⃣ **USERS → ACTIVITY_LOGS** (One-to-Many)
- **Type:** 1:M relationship
- **Foreign Key:** `activity_logs.user_id` → `users.user_id`
- **Cascade:** SET NULL (preserve logs even if user deleted)
- **Logic:** Track all user actions for audit

### 7️⃣ **USERS → RECORDED_BY** (Many references)
- **Type:** Multiple foreign keys from different tables
- **Tables:** `savings.recorded_by`, `installments.recorded_by`, `loans.approved_by`
- **Logic:** Track which admin user performed the action

---

## 📊 DATABASE VIEWS

### VIEW 1: **view_member_summary**
```sql
Combines: users + members + loans (aggregated)
Purpose: Quick member overview with financial summary
```

### VIEW 2: **view_loan_summary**
```sql
Combines: loans + members + users + installments (aggregated)
Purpose: Comprehensive loan details with repayment progress
```

---

## ⚡ AUTOMATED TRIGGERS

### TRIGGER 1: **after_savings_insert**
```
Event: AFTER INSERT on savings table
Action: 
  - IF deposit → members.total_savings += amount
  - IF withdrawal → members.total_savings -= amount
```

### TRIGGER 2: **after_installment_payment**
```
Event: AFTER UPDATE on installments table
Condition: status changed to 'paid'
Action:
  - loans.amount_paid += installment.paid_amount
  - loans.amount_remaining -= installment.paid_amount
  - IF amount_remaining <= 0 → loans.status = 'completed'
```

---

## 🎯 DATA FLOW EXAMPLES

### 📥 **New Member Registration Flow**
```
1. INSERT into users table
   ↓
2. Get LAST_INSERT_ID() → user_id
   ↓
3. INSERT into members table with user_id
   ↓
4. Generate unique member_code (MEM001, MEM002...)
   ↓
5. CREATE notification for admin
   ↓
6. LOG activity in activity_logs
```

---

### 💰 **Savings Deposit Flow**
```
1. INSERT into savings table
   ↓
2. TRIGGER: after_savings_insert executes
   ↓
3. UPDATE members.total_savings automatically
   ↓
4. INSERT into transactions (audit log)
   ↓
5. CREATE notification for member
   ↓
6. UPDATE dashboard statistics
```

---

### 📝 **Loan Application & Approval Flow**
```
STEP 1: Member applies for loan
   ↓
1. INSERT into loans (status = 'pending')
   ↓
2. Calculate: total_amount = loan_amount + interest
   ↓
3. Calculate: monthly_installment = total_amount / months
   ↓
4. CREATE notification for admin
   ↓

STEP 2: Admin approves loan
   ↓
5. UPDATE loans (status = 'approved', approved_by = admin_id)
   ↓
6. GENERATE installments schedule
   ↓
7. FOR each month:
      INSERT into installments (due_date, amount, status = 'pending')
   ↓
8. UPDATE loans (status = 'disbursed', disbursement_date = today)
   ↓
9. INSERT into transactions (type = 'loan_disbursement')
   ↓
10. CREATE notification for member
```

---

### 💳 **Installment Payment Flow**
```
1. UPDATE installments
   SET paid_amount = X, payment_date = today, status = 'paid'
   ↓
2. TRIGGER: after_installment_payment executes
   ↓
3. UPDATE loans
   SET amount_paid += X, amount_remaining -= X
   ↓
4. IF amount_remaining <= 0:
      UPDATE loans SET status = 'completed'
   ↓
5. INSERT into transactions (type = 'installment_payment')
   ↓
6. CREATE notification for member
   ↓
7. CHECK if next installment is due soon → send reminder
```

---

## 🔐 SECURITY CONSIDERATIONS

### ✅ **Implemented Security**

1. **Password Security:**
   - Stored as bcrypt hash (60 chars)
   - Never stored in plain text
   - Verified using `password_verify()`

2. **SQL Injection Prevention:**
   - All queries use PDO prepared statements
   - Parameterized queries with binding
   - No direct string concatenation in SQL

3. **Data Integrity:**
   - Foreign key constraints prevent orphaned records
   - UNIQUE constraints on email, member_code, loan_number
   - NOT NULL constraints on critical fields

4. **Audit Trail:**
   - All financial transactions logged in `transactions` table
   - All user actions logged in `activity_logs`
   - Timestamps on all tables

5. **Role-Based Access:**
   - `users.role` ENUM ('admin', 'member')
   - Different privileges enforced in PHP
   - Sensitive operations require admin role

---

## 📈 SCALABILITY & PERFORMANCE

### ✅ **Performance Optimizations**

1. **Indexes:**
   - Primary keys on all tables (clustered index)
   - Indexes on all foreign keys
   - Indexes on frequently queried columns (email, member_code, loan_number)
   - Composite indexes on date + member_id for reports

2. **Views:**
   - Pre-aggregated data in views
   - Reduces complex JOIN queries
   - Faster dashboard loading

3. **Triggers:**
   - Automatic calculations reduce application logic
   - Ensures data consistency
   - Faster than separate UPDATE queries

4. **Efficient Data Types:**
   - DECIMAL for currency (no floating point errors)
   - ENUM for status fields (compact storage)
   - VARCHAR with appropriate lengths
   - Appropriate use of INT vs BIGINT

---

## 🎓 LEARNING OUTCOMES

By implementing this database, you demonstrate:

✅ **Database Design Skills:**
- Normalization (3NF)
- Entity-Relationship modeling
- Referential integrity
- Database constraints

✅ **Advanced SQL:**
- Complex JOIN queries
- Aggregate functions
- Subqueries
- Views and triggers
- Stored procedures (potential future)

✅ **Security Best Practices:**
- Password hashing
- Prepared statements
- Audit logging
- Data validation

✅ **Business Logic:**
- Financial calculations
- Workflow management
- Status tracking
- Reporting and analytics

---

**💡 TIP:** Print this diagram and keep it handy while coding the PHP backend!

---

**Version:** 1.0  
**Created:** March 2026  
**Perfect for:** College Projects • Portfolios • Interviews
