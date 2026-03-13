# Registration Error - Quick Fix Guide

## Issue Identified
**Problem:** MySQL/MariaDB database service is not running
**Error Message:** "An error occurred. Please try again."

## Root Cause
Your application cannot connect to the database because the MySQL service in XAMPP is not started. This prevents any database operations, including user registration.

## Quick Fix Steps

### 1. Start XAMPP MySQL Service

**Option A: Using XAMPP Control Panel (Recommended)**
1. Navigate to `C:\xampp\` and run `xampp-control.exe`
2. Click **Start** next to **MySQL**
3. Wait for the status to turn green and show "Running"

**Option B: Using Windows Services**
1. Press `Win + R`
2. Type `services.msc` and press Enter
3. Find "MySQL" or "MariaDB" service
4. Right-click and select "Start"

**Option C: Using Command Line (Run as Administrator)**
```powershell
net start mysql
```

### 2. Verify MySQL is Running

Run this command in PowerShell:
```powershell
netstat -ano | Select-String ":3306"
```

You should see output showing port 3306 is in use.

### 3. Test Database Connection

Open your browser and visit:
```
http://localhost/bachat_gat/test-connection.php
```

This page will show:
- Database connection status
- Number of tables
- Number of users and members
- Any configuration issues

### 4. Import Database (If Not Already Done)

If the test shows "0 tables found" or errors:

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create a database OR select existing `bachat_gat_db`
3. Go to "Import" tab
4. Click "Choose File"
5. Select: `C:\xampp\htdocs\bachat_gat\database\schema.sql`
6. Click "Go" to import

### 5. Test Registration Again

1. Visit: `http://localhost/bachat_gat/auth/register.php`
2. Fill in the registration form with test data:
   - Full Name: Test User
   - Email: test@example.com
   - Phone: 9876543210
   - Password: Test@123
   - Confirm Password: Test@123
   - Address, City, State, Pincode: (any values)
3. Submit the form

### 6. Check for Detailed Errors (Development Mode)

I've updated the code to show detailed error messages when in development mode. If you still get an error, it will now show the specific database error message.

## Common Issues & Solutions

### Issue: "MySQL service won't start"
**Solution:**
1. Check if port 3306 is already in use:
   ```powershell
   netstat -ano | Select-String ":3306"
   ```
2. If another process is using port 3306, you can:
   - Stop that process, OR
   - Change MySQL port in `C:\xampp\mysql\bin\my.ini`

### Issue: "Access denied for user 'root'@'localhost'"
**Solution:**
Edit `C:\xampp\htdocs\bachat_gat\config\db.php` and check:
```php
private $username = 'root';
private $password = '';  // Default is empty for XAMPP
```

### Issue: "Database 'bachat_gat_db' does not exist"
**Solution:**
Import the database schema as shown in Step 4 above.

### Issue: "Column not found" errors
**Solution:**
Your database schema is outdated. Re-import the schema:
1. In phpMyAdmin, select `bachat_gat_db`
2. Click "Operations" tab
3. Scroll to "Remove database" and drop it
4. Create new database: `bachat_gat_db`
5. Import `database/schema.sql` again

## Verification Checklist

Before attempting registration, verify:

- [ ] XAMPP MySQL service is running (green status in XAMPP Control Panel)
- [ ] Port 3306 is accessible (test with `netstat` command)
- [ ] Database `bachat_gat_db` exists (check in phpMyAdmin)
- [ ] All required tables exist (users, members, notifications, activity_logs, etc.)
- [ ] Test connection page shows all green checks

## Default Admin Credentials

After importing the database, you can also test with the default admin account:

- Email: admin@bachatgat.com
- Password: admin@123

Login at: `http://localhost/bachat_gat/auth/login.php`

## Need More Help?

If you still face issues after following these steps, check:

1. **Error Log**: `C:\xampp\htdocs\bachat_gat\logs\db_errors.log`
2. **PHP Error Log**: `C:\xampp\php\logs\php_error_log`
3. **MySQL Error Log**: `C:\xampp\mysql\data\mysql_error.log`

The detailed error message (now shown in development mode) will help identify the specific problem.
