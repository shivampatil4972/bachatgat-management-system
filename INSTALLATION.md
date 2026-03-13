# 🚀 QUICK START GUIDE
## Bachat Gat Smart Management System

---

## 📋 PREREQUISITES

Before starting, ensure you have:

- ✅ **XAMPP** or **WAMP** installed (PHP 8.0+ & MySQL 8.0+)
- ✅ **Web Browser** (Chrome, Firefox, Edge)
- ✅ **Text Editor** (VS Code, Sublime, Notepad++)
- ✅ **Basic PHP & MySQL knowledge**

---

## ⚡ INSTALLATION STEPS

### **Step 1: Download/Clone Project**

```bash
# If using Git
git clone <repository-url>

# Or download ZIP and extract to:
C:\xampp\htdocs\bachat_gat
```

---

### **Step 2: Start XAMPP/WAMP**

1. Open XAMPP/WAMP Control Panel
2. Start **Apache** server
3. Start **MySQL** server
4. Verify both are running (green indicator)

---

### **Step 3: Create Database**

#### **Option A: Using phpMyAdmin**

1. Open browser: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose file: `C:\bachat_gat\database\schema.sql`
4. Click "Go"
5. Verify 11 tables created

#### **Option B: Using MySQL Command Line**

```bash
# Open Command Prompt
cd C:\xampp\mysql\bin

# Login to MySQL
mysql -u root -p
# (Press Enter if no password)

# Import database
source C:\bachat_gat\database\schema.sql

# Verify
USE bachat_gat_db;
SHOW TABLES;
```

**Expected Output:**
```
+---------------------------+
| Tables_in_bachat_gat_db   |
+---------------------------+
| activity_logs             |
| installments              |
| loan_settings             |
| loans                     |
| members                   |
| notifications             |
| savings                   |
| system_settings           |
| transactions              |
| users                     |
| view_loan_summary         |
| view_member_summary       |
+---------------------------+
11 rows in set
```

---

### **Step 4: Configure Database Connection**

Open `C:\bachat_gat\config\db.php` and verify:

```php
private $host = 'localhost';      // ✅ Correct
private $db_name = 'bachat_gat_db'; // ✅ Correct
private $username = 'root';        // ✅ Change if different
private $password = '';            // ✅ Add password if set
```

---

### **Step 5: Test Connection**

1. Open browser
2. Navigate to: `http://localhost/bachat_gat/test-connection.php`
3. Check for **GREEN "CONNECTED"** status

**✅ Expected Result:**
```
Connection Status: CONNECTED ✓
Tables Count: 11 tables found ✓
Users Table: 4 users found ✓
Admin User: admin@bachatgat.com ✓
Database Views: 2 views found ✓
```

**❌ If you see errors:**
- Check MySQL is running
- Verify database imported correctly
- Check credentials in `config/db.php`
- Ensure PHP PDO extension enabled

---

### **Step 6: Test Login Credentials**

**Admin Account:**
- Email: `admin@bachatgat.com`
- Password: `admin@123`

**Member Accounts:**
- Email: `rajesh@example.com` | Password: `member@123`
- Email: `priya@example.com` | Password: `member@123`
- Email: `amit@example.com` | Password: `member@123`

*(Login page will be created in Step 3)*

---

## 🎯 CURRENT PROJECT STATUS

### **✅ Completed:**

**STEP 1: Database Schema**
- ✅ 11 tables with relationships
- ✅ 2 automated triggers
- ✅ 2 database views
- ✅ Sample data loaded
- ✅ Complete documentation

**STEP 2: PDO Database Connection**
- ✅ Singleton pattern connection
- ✅ Prepared statements
- ✅ Error handling & logging
- ✅ Helper functions
- ✅ Security configurations

### **⏳ Coming Next:**

**STEP 3: Authentication System**
- Login page
- Registration page
- Session management
- Role-based access control

---

## 📂 PROJECT FILE STRUCTURE

```
bachat_gat/
│
├── 📂 database/
│   ├── schema.sql                 ✅ Import this first
│   ├── DATABASE_DOCUMENTATION.md
│   ├── COMMON_QUERIES.sql
│   └── README.md
│
├── 📂 config/
│   ├── db.php                     ✅ Database connection
│   ├── config.php                 ✅ App configuration
│   ├── constants.php              ✅ Constants & messages
│   └── README.md
│
├── 📂 logs/                       ✅ Auto-created
│   └── db_errors.log              (Error logs)
│
├── 📂 assets/
│   └── 📂 uploads/
│       ├── profiles/              (Profile pictures)
│       └── documents/             (Loan documents)
│
├── test-connection.php            ✅ Test this page
├── .env.example                   (Environment template)
├── .gitignore                     (Git ignore rules)
└── PROJECT_STRUCTURE.md           (Full structure)
```

---

## 🔧 TROUBLESHOOTING

### **Problem 1: "Database connection failed"**

**Solution:**
```bash
# Check MySQL service
services.msc
# Find "MySQL" and ensure it's running

# Or restart XAMPP MySQL
```

---

### **Problem 2: "Access denied for user 'root'@'localhost'"**

**Solution:**
```php
// Edit config/db.php
private $password = 'your_password'; // Add your MySQL password
```

---

### **Problem 3: "Table doesn't exist"**

**Solution:**
```sql
-- Re-import schema
DROP DATABASE IF EXISTS bachat_gat_db;
source C:\bachat_gat\database\schema.sql
```

---

### **Problem 4: "Call to undefined function PDO"**

**Solution:**
```ini
# Edit php.ini (in XAMPP: C:\xampp\php\php.ini)
# Find and uncomment:
extension=pdo_mysql

# Restart Apache
```

---

### **Problem 5: "Page not found"**

**Solution:**
```
# Ensure project is in correct location:
C:\xampp\htdocs\bachat_gat\

# Access via:
http://localhost/bachat_gat/test-connection.php
```

---

## 📊 VERIFY INSTALLATION

Run these checks:

### ✅ **Check 1: Files Exist**
```
C:\bachat_gat\config\db.php          ✓
C:\bachat_gat\config\config.php      ✓
C:\bachat_gat\database\schema.sql    ✓
C:\bachat_gat\test-connection.php    ✓
```

### ✅ **Check 2: MySQL Database**
```sql
SHOW DATABASES;
-- Should show: bachat_gat_db

USE bachat_gat_db;
SELECT COUNT(*) FROM users;
-- Should return: 4
```

### ✅ **Check 3: Web Access**
```
http://localhost/bachat_gat/test-connection.php
-- Should show: Connection Status CONNECTED
```

### ✅ **Check 4: PHP Version**
```bash
php -v
-- Should be: PHP 8.0 or higher
```

---

## 🎓 NEXT STEPS

Once installation is verified:

1. ✅ **Delete** `test-connection.php` (for security)
2. ✅ **Confirm** to proceed with **Step 3: Authentication System**
3. ✅ **Review** [config/README.md](config/README.md) for details

---

## 📞 SUPPORT

### **Common Resources:**

- **Database Schema:** [database/DATABASE_DOCUMENTATION.md](database/DATABASE_DOCUMENTATION.md)
- **SQL Queries:** [database/COMMON_QUERIES.sql](database/COMMON_QUERIES.sql)
- **Configuration Guide:** [config/README.md](config/README.md)
- **Project Structure:** [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md)

### **Default Credentials:**

| Role   | Email                  | Password     |
|--------|------------------------|--------------|
| Admin  | admin@bachatgat.com    | admin@123    |
| Member | rajesh@example.com     | member@123   |
| Member | priya@example.com      | member@123   |
| Member | amit@example.com       | member@123   |

---

## ✨ SYSTEM REQUIREMENTS MET

- ✅ PHP 8.0+
- ✅ MySQL 8.0+
- ✅ PDO Extension enabled
- ✅ Apache web server
- ✅ 50MB disk space minimum
- ✅ Modern web browser

---

## 🎉 INSTALLATION COMPLETE!

Your Bachat Gat Smart Management System is now installed and ready for development.

**Current Progress:** 20% (2/10 steps complete)

**Next:** Authentication System (Login & Registration)

---

**Version:** 1.0  
**Last Updated:** March 2026  
**Status:** ✅ Ready for Development
