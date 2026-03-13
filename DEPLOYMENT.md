# Deployment Checklist - Bachat Gat Smart Management System

## Pre-Deployment Tasks

### 1. Security Hardening
- [ ] Change database credentials in `config/config.php`
- [ ] Set `ENVIRONMENT` to `'production'` in `config/config.php`
- [ ] Generate secure `SESSION_KEY` and `CSRF_SECRET`
- [ ] Remove or disable debugging features
- [ ] Set `display_errors = Off` in php.ini
- [ ] Enable `log_errors = On` in php.ini
- [ ] Change default admin password
- [ ] Delete test/demo member accounts
- [ ] Review and update email configuration
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Ensure upload directories have write permissions only
- [ ] Disable directory listing in .htaccess
- [ ] Add security headers in .htaccess

### 2. Database Tasks
- [ ] Backup current database
- [ ] Remove test/seed data
- [ ] Optimize database tables (`OPTIMIZE TABLE`)
- [ ] Add database indexes for performance
- [ ] Set up automated database backups
- [ ] Configure database connection pooling
- [ ] Review and optimize slow queries
- [ ] Set proper MySQL user privileges (minimal required)

### 3. Configuration Updates
- [ ] Update `BASE_URL` in config/config.php
- [ ] Update `ASSETS_URL` and `UPLOADS_URL`
- [ ] Configure SMTP settings for email
- [ ] Set proper timezone
- [ ] Configure session settings for production
- [ ] Set up error logging path
- [ ] Configure file upload limits
- [ ] Set memory limits appropriately

### 4. Code Review
- [ ] Remove debug statements (`var_dump`, `print_r`, `echo` for debugging)
- [ ] Review all SQL queries for optimization
- [ ] Ensure all user inputs are validated
- [ ] Check all file upload handlers
- [ ] Verify all authentication checks are in place
- [ ] Review session management code
- [ ] Check for exposed sensitive information
- [ ] Validate all API endpoints

### 5. Testing
- [ ] Test all user registration flows
- [ ] Test login/logout functionality
- [ ] Test password reset functionality
- [ ] Test member CRUD operations
- [ ] Test savings module thoroughly
- [ ] Test loan module and EMI calculations
- [ ] Test all reports and analytics
- [ ] Test file uploads
- [ ] Test on different browsers
- [ ] Test on mobile devices
- [ ] Load testing with multiple users
- [ ] SQL injection testing
- [ ] XSS vulnerability testing
- [ ] CSRF attack testing

### 6. Performance Optimization
- [ ] Enable PHP OPcache
- [ ] Enable Gzip compression
- [ ] Minify CSS and JavaScript
- [ ] Optimize images
- [ ] Set up CDN for static assets (optional)
- [ ] Enable browser caching
- [ ] Implement query caching
- [ ] Add database indexes on frequently queried columns
- [ ] Lazy load images
- [ ] Defer non-critical JavaScript

### 7. Backup Strategy
- [ ] Set up automated daily database backups
- [ ] Set up automated weekly full backups
- [ ] Test backup restoration process
- [ ] Store backups off-site/cloud
- [ ] Document backup procedures
- [ ] Set up backup monitoring/alerts

### 8. Monitoring & Logging
- [ ] Set up error logging
- [ ] Configure activity logging
- [ ] Set up uptime monitoring
- [ ] Configure email alerts for critical errors
- [ ] Set up performance monitoring
- [ ] Monitor disk space usage
- [ ] Monitor database size
- [ ] Set up security event logging

### 9. Documentation
- [ ] Update README.md with production details
- [ ] Document deployment process
- [ ] Create admin user guide
- [ ] Create member user guide
- [ ] Document API endpoints (if exposing)
- [ ] Create troubleshooting guide
- [ ] Document database schema
- [ ] Create backup/restore procedures

### 10. Legal & Compliance
- [ ] Add Terms of Service page
- [ ] Add Privacy Policy page
- [ ] Ensure GDPR compliance (if applicable)
- [ ] Add cookie consent notice
- [ ] Verify data retention policies
- [ ] Add contact information
- [ ] Include disclaimer if needed

---

## Deployment Steps

### Server Requirements
```
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- SSL certificate (recommended)
- Minimum 512MB RAM
- 1GB disk space
```

### Step 1: Prepare Server
```bash
# Update server
sudo apt update && sudo apt upgrade

# Install required packages
sudo apt install php8.0 php8.0-mysql php8.0-mbstring php8.0-curl php8.0-gd

# Install MySQL
sudo apt install mysql-server

# Install Apache/Nginx
sudo apt install apache2
# OR
sudo apt install nginx php8.0-fpm
```

### Step 2: Upload Files
```bash
# Using FTP/SFTP
# Upload all files except:
# - config/config.php (configure separately)
# - logs/ (create on server)
# - uploads/ (create on server)

# Set permissions
chmod 755 -R /var/www/bachat_gat
chmod 777 /var/www/bachat_gat/uploads
chmod 777 /var/www/bachat_gat/logs
```

### Step 3: Configure Database
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE bachat_gat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user
CREATE USER 'bachat_user'@'localhost' IDENTIFIED BY 'strong_password_here';

# Grant privileges
GRANT ALL PRIVILEGES ON bachat_gat.* TO 'bachat_user'@'localhost';
FLUSH PRIVILEGES;

# Import schema
mysql -u bachat_user -p bachat_gat < database.sql
```

### Step 4: Configure Application
```bash
# Edit config.php
vim config/config.php

# Update:
# - DB_HOST, DB_NAME, DB_USER, DB_PASS
# - BASE_URL
# - ENVIRONMENT = 'production'
# - SESSION_KEY (generate random string)
```

### Step 5: Configure Web Server

**For Apache:**
```apache
<VirtualHost *:80>
    ServerName bachatgat.com
    ServerAlias www.bachatgat.com
    DocumentRoot /var/www/bachat_gat
    
    <Directory /var/www/bachat_gat>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/bachat_error.log
    CustomLog ${APACHE_LOG_DIR}/bachat_access.log combined
</VirtualHost>
```

**For Nginx:**
```nginx
server {
    listen 80;
    server_name bachatgat.com www.bachatgat.com;
    root /var/www/bachat_gat;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Step 6: SSL Certificate (Recommended)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache
# OR for Nginx
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --apache -d bachatgat.com -d www.bachatgat.com
# OR for Nginx
sudo certbot --nginx -d bachatgat.com -d www.bachatgat.com

# Auto-renewal
sudo certbot renew --dry-run
```

### Step 7: Configure Scheduled Tasks
```bash
# Edit crontab
crontab -e

# Add backup task (daily at 2 AM)
0 2 * * * /usr/local/bin/backup_bachat.sh

# Add cleanup task (weekly)
0 3 * * 0 php /var/www/bachat_gat/scripts/cleanup.php
```

### Step 8: Final Testing
- [ ] Access website via domain
- [ ] Test SSL certificate
- [ ] Test all login flows
- [ ] Test critical functionalities
- [ ] Check error logs
- [ ] Verify email sending
- [ ] Test backup system

---

## Post-Deployment Tasks

### Immediate (Within 24 hours)
- [ ] Monitor error logs
- [ ] Test all critical features
- [ ] Verify backup completed successfully
- [ ] Check website performance
- [ ] Test email notifications
- [ ] Verify SSL certificate

### Weekly
- [ ] Review error logs
- [ ] Check backup integrity
- [ ] Monitor disk space
- [ ] Review user activity logs
- [ ] Check for security updates
- [ ] Monitor website uptime

### Monthly
- [ ] Update dependencies (if using Composer)
- [ ] Review and rotate logs
- [ ] Database optimization
- [ ] Security audit
- [ ] Performance review
- [ ] User feedback review

---

## Rollback Plan

In case of issues:

1. **Immediately:**
   ```bash
   # Restore previous version
   mv /var/www/bachat_gat /var/www/bachat_gat_new
   mv /var/www/bachat_gat_backup /var/www/bachat_gat
   
   # Restore database
   mysql -u bachat_user -p bachat_gat < backup_YYYYMMDD.sql
   ```

2. **Notify users** about the temporary issue

3. **Investigate** the problem

4. **Fix and redeploy** when ready

---

## Security .htaccess File

Create/update `.htaccess` in root directory:

```apache
# Disable directory listing
Options -Indexes

# Protect config files
<Files "config.php">
    Require all denied
</Files>

# Protect database file
<Files "database.sql">
    Require all denied
</Files>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Redirect to HTTPS (uncomment after SSL setup)
# RewriteEngine On
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Emergency Contacts

- **Hosting Support**: [Your hosting provider support]
- **Database Admin**: [DBA contact]
- **Developer**: [Your contact information]
- **Domain Registrar**: [Domain support]

---

## Useful Commands

```bash
# Check PHP version
php -v

# Check MySQL version
mysql --version

# View Apache error log
tail -f /var/log/apache2/error.log

# View Nginx error log
tail -f /var/log/nginx/error.log

# Check disk space
df -h

# Check MySQL status
systemctl status mysql

# Restart Apache
sudo systemctl restart apache2

# Restart Nginx
sudo systemctl restart nginx

# Test Nginx configuration
sudo nginx -t

# Database backup
mysqldump -u bachat_user -p bachat_gat > backup_$(date +%Y%m%d).sql

# Database restore
mysql -u bachat_user -p bachat_gat < backup_20240101.sql
```

---

## Performance Benchmarks

Target metrics:
- Page load time: < 2 seconds
- Time to first byte: < 500ms
- Database queries: < 50ms each
- Concurrent users: 100+
- Uptime: 99.9%

---

**Note**: Customize this checklist based on your specific hosting environment and requirements.

*Last Updated: December 2024*
