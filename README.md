# Bachat Gat Smart Management System

## 🎓 College Project - Premium SHG Financial Management System

A modern, feature-rich web application for managing Self-Help Group (Bachat Gat) operations including member management, savings tracking, loan management, and comprehensive analytics.

### ⭐ Project Status: **UI OVERHAULED & PRODUCTION-READY**
- ✅ **Premium Bento-Glass UI** - Completely redesigned frontend with glassmorphism, smooth gradients, and micro-animations.
- ✅ **Fully Functional Portals** - Complete and distinct Admin and Member dashboards.
- ✅ **Clean Codebase** - All unused files, dead refactored scripts, and placeholder images have been removed.
- ✅ **Data Visualization** - Fully integrated, theme-aware Chart.js analytics with intelligent empty states.
- ✅ **Secure & Stable** - PDO prepared statements, password hashing, and clean MVC-inspired structure.

---

## ✨ Features

### 👤 **Authentication & Security**
- ✅ Secure login/registration system
- ✅ Password strength validation and bcrypt hashing
- ✅ Session management with secure timeouts
- ✅ Role-based access control (Admin & Member roles)
- ✅ PDO Prepared statements to prevent SQL Injection

### 👨‍💼 **Admin Features**
1. **Dashboard**
   - Real-time statistics overview
   - Quick access to all modules
   - Financial summary cards
2. **Member Management**
   - Add/Edit/Delete members
   - Member verification system
   - Auto-generated member codes
3. **Savings Management**
   - Record savings deposits (Regular, Recurring, Fixed Deposit, Emergency Fund)
   - Auto-update member balances via database triggers
   - Savings history with filters
4. **Loan Management**
   - Loan application processing
   - Automated loan number generation
   - EMI calculation engine and installment schedules
   - Repayment tracking (including partial payments)
5. **Reports & Analytics**
   - **Savings Analytics**: Monthly trends, savings by type
   - **Loan Analytics**: Loan status distribution, member growth
   - Interactive Chart.js visualizations with dynamic empty states
6. **Transaction Management**
   - Comprehensive transaction log with Credit/Debit tracking

### 👨‍💻 **Member Features**
1. **Member Dashboard**
   - Personal financial summary
   - Savings overview and active loans display
2. **My Savings & Loans**
   - Detailed savings history and growth visualization
   - Active and past loans, EMI schedule view, payment history
3. **Profile & Settings**
   - Edit personal information and update passwords
4. **Notifications Center**
   - Real-time alerts for loan approvals and payments

---

## 🛠️ Technology Stack

### **Backend**
- **PHP**: 8.0+ (Clean procedural/OOP hybrid)
- **MySQL**: 8.0+ (Normalized 3NF schema with triggers & views)
- **Database Wrapper**: Custom PDO wrapper for secure database interactions

### **Frontend**
- **Bootstrap 5.3.0**: Responsive UI framework
- **Custom CSS**: Premium Bento-Glass theme (`theme.css`)
- **Chart.js 4.4.0**: Interactive financial data visualization
- **DataTables**: Advanced table functionality (search, sort, filter, pagination)
- **Google Fonts**: Inter font family for clean typography

---

## 📁 Project Structure

```text
bachat_gat/
│
├── 📄 Configuration
│   ├── README.md                       # Project overview
│   ├── .env                            # Environment variables (gitignored)
│   └── .env.example                    # Environment template
│
├── 📁 config/
│   ├── config.php                      # Main application configuration
│   ├── constants.php                   # App constants
│   └── db.php                          # PDO Database wrapper
│
├── 📁 classes/
│   └── AuthController.php              # Authentication handler
│
├── 📁 helpers/
│   ├── functions.php                   # Global helper functions
│   └── session.php                     # Session management
│
├── 📁 includes/
│   ├── header.php                      # Common header & navigation
│   └── footer.php                      # Common footer
│
├── 📁 auth/                            # Authentication Views & Logic
├── 📁 admin/                           # Admin Portal (Dashboard, Loans, Reports, etc.)
├── 📁 member/                          # Member Portal (My Loans, My Savings, etc.)
├── 📁 pages/                           # Public Pages (About, Contact, Privacy, Terms, Help)
│
├── 📁 assets/
│   ├── css/                            # Theme and font styles
│   ├── images/                         # Project graphics & generated imagery
│   ├── js/                             # Theme switchers and interactions
│   └── uploads/                        # User generated content
│
└── 📁 database/
    └── bachat_gat_db.sql               # Complete MySQL database schema
```

---

## 🚀 Installation & Setup Guide

### **Prerequisites**
- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache/Nginx (XAMPP/WAMP recommended for local dev)

### **Installation Steps**

#### **Step 1: Setup Project Directory**
```bash
cd C:\xampp\htdocs
git clone https://github.com/shivampatil4972/bachatgat-management-system.git bachat_gat
cd bachat_gat
```

#### **Step 2: Database Setup**
1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2. Create a new database named `bachat_gat`
3. Import the SQL file located at `database/bachat_gat_db.sql`

#### **Step 3: Configure Environment**
```bash
# Copy template to actual .env file
cp .env.example .env
```
Edit the `.env` file with your database credentials:
```env
DB_HOST=localhost
DB_NAME=bachat_gat
DB_USER=root
DB_PASS=
```

#### **Step 4: Set Directory Permissions (Linux/Mac only)**
```bash
chmod 777 assets/uploads/documents
chmod 777 assets/uploads/profiles
chmod 777 logs
```

#### **Step 5: Access Application**
Navigate to: `http://localhost/bachat_gat/`

---

## 🔑 Default Login Credentials

**Admin Account:**
- Email: `admin@bachatgat.com`
- Password: `Admin@123`

**Member Account:**
- Email: `member1@bachatgat.com`
- Password: `member@123`

*(Note: Please change these passwords immediately upon deployment)*

---

## 🎨 Design Highlights

The project recently underwent a massive UI overhaul to bring it up to modern enterprise standards:
- **Glassmorphism**: Translucent cards with backdrop blur.
- **Dynamic Themes**: Fully functional Light/Dark mode toggle.
- **Custom Color Palette**: Rich Indigo/Purple gradients avoiding flat default colors.
- **Empty States**: Charts and tables gracefully handle missing data with beautiful empty state fallbacks.

---

## 📝 License

This project was created for educational purposes as a college project. Feel free to use it for learning and portfolio purposes.

*Last Updated: July 2026*  
*Version: 2.5 (Premium UI Release)*
