# Password Reset Feature - Documentation

## Overview
The password reset feature allows both **users** and **admins** to securely reset their passwords through the login page. This feature provides a seamless and secure way to recover account access.

## Features

✅ **Unified System**: Works for both admin and member accounts  
✅ **Secure Token-Based**: Uses cryptographically secure tokens  
✅ **Time-Limited**: Reset links expire after 1 hour  
✅ **One-Time Use**: Tokens are invalidated after password reset  
✅ **Email Notifications**: Sends reset link and confirmation emails  
✅ **Password Strength Indicator**: Real-time password strength validation  
✅ **Responsive Design**: Beautiful UI matching the login page style  
✅ **Security Best Practices**: Prevents email enumeration attacks  

## Files Structure

```
auth/
├── login.php                      # Login page with "Forgot Password" link
├── forgot-password.php            # Password reset request page
├── forgot-password-process.php    # Handles reset token generation
├── reset-password.php             # Password reset page (with token)
└── reset-password-process.php     # Handles password update
```

## Database Requirements

### User Tokens Table
The feature requires the `user_tokens` table. Run this migration if not already done:

```sql
-- Run: database/migration_user_tokens.sql
```

The table stores:
- Remember Me tokens
- Password Reset tokens  
- Email Verification tokens (future)

## How It Works

### Step 1: Request Password Reset
1. User clicks "Forgot Password?" on login page
2. User enters their registered email address
3. System validates email and generates secure token
4. Token stored in database with 1-hour expiration
5. Reset link sent via email

### Step 2: Reset Password
1. User clicks reset link from email
2. Token validated (exists, not expired, not used)
3. User enters new password with strength indicator
4. Password updated and all tokens invalidated
5. Confirmation email sent
6. User redirected to login page

## Security Features

### 1. Token Security
- **64-character random tokens** using `random_bytes()`
- **One-time use** - deleted after successful reset
- **Time-limited** - expires after 1 hour
- **Unique per user** - old tokens deleted when new one generated

### 2. Email Enumeration Prevention
```php
// Always returns success, even if email not found
// Prevents attackers from discovering valid emails
```

### 3. Timing Attack Prevention
```php
// Simulated delay for non-existent emails
usleep(500000); // 0.5 seconds
```

### 4. Account Status Check
- Only active accounts can reset passwords
- Inactive accounts are rejected with appropriate message

### 5. Session Security
```php
// All remember_me tokens deleted on password reset
// Forces re-login on all devices
```

## Usage Guide

### For Users (Members)
1. Go to login page: `http://yoursite.com/auth/login.php`
2. Click **"Forgot Password?"** link
3. Enter your registered email
4. Check email for reset link
5. Click link and enter new password
6. Login with new password

### For Admins
Same process as members - the system automatically detects if the user is an admin or member based on their email.

## Email Configuration

### Development Mode
In development, emails may not be sent if mail server is not configured. The system will return the reset link in the response for testing:

```json
{
  "success": true,
  "message": "Password reset link generated (email not configured).",
  "data": {
    "reset_link": "http://localhost/auth/reset-password.php?token=...",
    "note": "Email server not configured. Use the link above."
  }
}
```

### Production Setup
For production, configure one of:

#### Option 1: PHP mail() function
Configure your server's SMTP settings in `php.ini`

#### Option 2: PHPMailer (Recommended)
Install and configure PHPMailer for better email delivery:
```bash
composer require phpmailer/phpmailer
```

Update `forgot-password-process.php` to use PHPMailer.

## Password Requirements

- **Minimum length**: 8 characters
- **Recommended**: Include uppercase, lowercase, numbers, and symbols
- **Strength indicator**: Shows real-time password strength

### Password Strength Levels
- **Weak**: < 8 characters or simple patterns
- **Medium**: 8+ characters with mixed case and numbers
- **Strong**: 12+ characters with uppercase, lowercase, numbers, and symbols

## Customization

### Change Token Expiration Time
In `forgot-password-process.php`:
```php
// Change from 1 hour to 30 minutes
$expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
```

### Customize Email Template
Edit email templates in:
- `forgot-password-process.php` - Reset request email
- `reset-password-process.php` - Confirmation email

### Modify Password Requirements
In `reset-password.php` and `reset-password-process.php`:
```php
// Change minimum length
if (strlen($newPassword) < 12) { // Changed from 8 to 12
    throw new Exception('Password must be at least 12 characters long');
}
```

## Testing

### Test Password Reset Flow

1. **Request Reset**
   ```
   URL: http://localhost/xampp/htdocs/bachat_gat/auth/forgot-password.php
   Email: admin@bachatgat.com (or any registered email)
   ```

2. **Check Database**
   ```sql
   SELECT * FROM user_tokens WHERE type = 'password_reset';
   ```

3. **Get Reset Link**
   - Check email or response data in development mode
   - Copy token from database

4. **Reset Password**
   ```
   URL: http://localhost/xampp/htdocs/bachat_gat/auth/reset-password.php?token=YOUR_TOKEN
   ```

5. **Verify Reset**
   - Try logging in with new password
   - Check that token is deleted from database

## Troubleshooting

### Issue: Email not received
**Solution**: 
- Check email spam/junk folder
- In development, check response data for reset link
- Verify email server configuration
- Check error logs: `error_log("message")`

### Issue: Token expired or invalid
**Solution**:
- Request new reset link (tokens expire after 1 hour)
- Check if token exists in database
- Ensure token wasn't already used

### Issue: "Account not active" message
**Solution**:
- Check user status in database: `SELECT status FROM users WHERE email = ?`
- Contact admin to activate account

### Issue: Passwords don't match
**Solution**:
- Ensure both password fields are identical
- Check for extra spaces
- Use "Show Password" toggle to verify

## Database Cleanup

### Remove Expired Tokens
Add this to a daily cron job:
```sql
-- Delete expired tokens
DELETE FROM user_tokens 
WHERE expires_at < NOW();
```

Or create a PHP cleanup script:
```php
// cleanup-expired-tokens.php
$db->delete("DELETE FROM user_tokens WHERE expires_at < NOW()");
```

## Migration from Old Reset Files

If you were using the old reset files (`reset-admin.php`, `reset-user-password.php`):

1. ✅ New system is active and working
2. ⚠️ Old files are still available for emergency use
3. 🗑️ Can safely delete old files once verified:
   - `reset-admin.php`
   - `reset-user-password.php`

## API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Password reset link sent successfully",
  "data": {}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description here",
  "data": {}
}
```

## Best Practices

1. **Always use HTTPS in production** - Reset links contain sensitive tokens
2. **Enable email logging** - Track password reset requests
3. **Monitor for abuse** - Rate limit reset requests per IP/email
4. **User education** - Inform users about password best practices
5. **Regular cleanup** - Remove expired tokens from database

## Future Enhancements

Potential improvements:
- [ ] Rate limiting (max 3 requests per hour per email)
- [ ] SMS-based password reset
- [ ] Two-factor authentication
- [ ] Password history (prevent reuse of last 5 passwords)
- [ ] Account lockout after multiple failed attempts
- [ ] Admin notification for password resets

## Support

For issues or questions:
1. Check this documentation
2. Review error logs
3. Test in development mode
4. Check database for tokens
5. Contact system administrator

---

**Version**: 1.0  
**Last Updated**: March 2026  
**Author**: Bachat Gat Development Team
