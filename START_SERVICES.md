# How to Fix "Unable to Create Account" Error

## Problem
MySQL/MariaDB service is not running, preventing database operations.

## Solution

### Step 1: Start XAMPP Services

1. Open **XAMPP Control Panel** (located at `C:\xampp\xampp-control.exe`)
2. Click the **Start** button next to **Apache** (if not already running)
3. Click the **Start** button next to **MySQL**
4. Wait until both services show green "Running" status

### Step 2: Verify Database Setup

After starting MySQL, run the following steps:

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Check if database `bachat_gat_db` exists
3. If it doesn't exist:
   - Click on "Import" tab
   - Choose the file `database/schema.sql` from your project
   - Click "Go" to import the database schema

### Step 3: Test Registration

1. Go to: http://localhost/bachat_gat/auth/register.php
2. Fill out the registration form
3. Submit

## Alternative: Start MySQL from Command Line

```powershell
# Start MySQL service
net start mysql

# Or if it's named differently
sc query | Select-String "mysql"
```

## Verify MySQL is Running

```powershell
netstat -ano | Select-String ":3306"
```

If you see output, MySQL is running on port 3306.

## Next Steps After Services Are Running

Once MySQL is running, I can help you:
1. Verify the database schema is correct
2. Check for any missing tables or columns
3. Test the registration process again
