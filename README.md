# EduPro Ledger - Complete Laravel PHP & MySQL Backend

This directory (`laravel-app/`) contains the standalone, production-ready **Laravel 11 PHP application structure** for **EduPro Ledger (School Management & Grading System)**.

It has been designed and constructed as a self-contained Laravel backend mirroring all features, data models, business rules, and API endpoints from the original platform specification.

---

## 📁 Directory Structure Overview

```
laravel-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php            # Multi-role authentication & session handling
│   │   │   ├── StaffActivationController.php # Staff account lookup & password activation
│   │   │   ├── StudentActivationController.php# Student/Parent account lookup & setup
│   │   │   ├── ClassController.php           # Class & arm management
│   │   │   ├── SubjectController.php         # Subject catalog management
│   │   │   ├── StudentController.php         # Student admission, profile updates & roster
│   │   │   ├── EnrollmentController.php       # Class enrollment, bulk promotion & transfer history
│   │   │   ├── ScoreSheetController.php      # Score entry & batch save without zero-override bugs
│   │   │   ├── BroadsheetController.php      # Master class broadsheets & printable scoresheets
│   │   │   ├── ReportCardController.php      # Live termly & annual report cards & result checker
│   │   │   ├── TerminalRemarkController.php  # Headteacher/Form Master remarks & attendance
│   │   │   ├── BehaviorAssessmentController.php # Affective & psychomotor rating domain entries
│   │   │   ├── GradingSystemController.php   # Custom school grading scale definitions
│   │   │   ├── AttendanceController.php      # Daily & termly attendance tracking
│   │   │   ├── HomeworkController.php        # Homework assignments & grading
│   │   │   ├── PaymentController.php         # Fee payments & transaction logs
│   │   │   ├── FeeStructureController.php    # Class fee category configurations
│   │   │   ├── CommunicationController.php  # Bulk SMS/Email messaging & campaigns
│   │   │   ├── SubscriptionController.php   # SaaS subscription tier monitoring
│   │   │   ├── SchoolSettingController.php  # School profile, report card template & active term/session
│   │   │   ├── SystemAdminController.php   # Global multi-tenant site admin, school network & billing logs
│   │   │   └── DashboardController.php      # Role-based metrics for Admin, Headteacher, Teacher, Student
│   │   └── Middleware/
│   │       ├── CheckRole.php              # Role-based access control (RBAC)
│   │       └── EnsureSchoolTenant.php     # Multi-tenant data isolation by school_id
│   ├── Models/                            # Eloquent Models (User, Student, Staff, TerminalScoreSheet, etc.)
│   └── Services/
│       ├── RankingService.php             # Live dense ordinal ranking (1st, 2nd, 3rd...)
│       └── ReportCardService.php          # Live class stats (Avg, Highest, Lowest)
├── bootstrap/
│   └── app.php                            # Framework bootstrapping
├── config/                                # Application configuration files
├── database/
│   ├── migrations/                        # Database schema migrations
│   └── seeders/                           # Database seeders (optional)
├── public/
│   └── index.php                          # HTTP entry point
├── resources/
│   └── views/
│       └── reports/
│           ├── termly_report.blade.php    # Printable Blade report template
│           └── annual_report.blade.php    # Printable annual summary template
├── routes/
│   ├── api.php                            # Complete RESTful API endpoints (/api/v1/...)
│   ├── web.php                            # Printable web & PDF routes
│   └── console.php
├── .env.example                            # MySQL & environment configuration template
├── artisan                                # Laravel CLI tool
└── composer.json                          # Dependencies (Laravel 11, Sanctum, DomPDF)
```

---

## 🚀 Local Installation & Setup Guide

### 1. System Requirements
* **PHP** `>= 8.2` (with `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `gd` extensions enabled)
* **Composer** `>= 2.0`
* **MySQL** `>= 8.0` or **MariaDB** `>= 10.5`

### 2. Step-by-Step Installation

```bash
# 1. Navigate into the Laravel project directory
cd laravel-app

# 2. Install PHP package dependencies
composer install

# 3. Create your local environment file
cp .env.example .env

# 4. Open .env and set up your MySQL database connection:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=edupro_ledger
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 5. Generate application encryption key
php artisan key:generate

# 6. Run database migrations to create all application tables
php artisan migrate

# 7. (Optional) Run database seeders if needed
php artisan db:seed

# 8. Launch the local Laravel development server
php artisan serve
```
By default, your application will be running at `http://127.0.0.1:8000`.

---

## 📡 API Endpoint Reference (`/api/v1`)

### 1. Authentication & Account Setup
* `POST /api/v1/auth/login` - User sign-in (returns session token & role payload)
* `GET /api/v1/auth/me` - Fetch authenticated user details
* `POST /api/v1/auth/logout` - Revoke session
* `POST /api/v1/auth/staff/lookup` - Verify staff credentials for activation
* `POST /api/v1/auth/staff/activate` - Set staff password & activate account
* `POST /api/v1/auth/student/lookup` - Verify student admission number & parent phone
* `POST /api/v1/auth/student/activate` - Activate student portal access

### 2. Result Checker (Public / Student Access)
* `GET /api/v1/result-checker?admission_no=...&session_id=...&term_id=...` - Query termly report card
* `GET /api/v1/result-checker/annual?admission_no=...&session_id=...` - Query annual summary report card

### 3. School Settings & Report Template Setup
* `GET /api/v1/school/settings` - Fetch school profile, branding & report card template rules
* `PUT /api/v1/school/settings` - Update school motto, logo, stamp, active session, term & template configuration

### 3b. Global System Admin / Site Admin Dashboard
* `GET /api/v1/system-admin/summary` - Platform-wide multi-tenant stats (total schools, students, MRR)
* `GET /api/v1/system-admin/schools` - Search, filter, and view all registered school network profiles
* `POST /api/v1/system-admin/schools` - Provision a new school tenant on the platform
* `GET /api/v1/system-admin/billing-activity` - Global payment and subscription transaction audit trail
* `POST /api/v1/system-admin/plans` - Configure SaaS subscription plan pricing tiers and limits

### 4. Headteacher / Admin Portal
* `GET /api/v1/dashboard/headteacher` - Overview statistics, class breakdown & recent activity
* `GET /api/v1/classes` - List classes & arms
* `POST /api/v1/classes` - Create new class/arm
* `GET /api/v1/students` - Student roster with filters
* `POST /api/v1/students` - Single student admission
* `PUT /api/v1/students/{id}` - Edit student profile
* `POST /api/v1/enrollments/promote` - Bulk class promotion
* `GET /api/v1/grading-scales` - View grading scale thresholds
* `POST /api/v1/grading-scales/batch-save` - Save grading scale ranges

### 5. Teacher Portal
* `GET /api/v1/dashboard/teacher` - Assigned classes & subject metrics
* `GET /api/v1/score-sheets` - Subject score entries
* `POST /api/v1/score-sheets/batch-save` - Batch save continuous assessment & exam scores
* `GET /api/v1/broadsheet` - Master class broadsheet calculation
* `GET /api/v1/subject-scoresheet/print` - Printable subject score sheet
* `GET /api/v1/terminal-remarks` - Fetch form master & headteacher remarks
* `POST /api/v1/terminal-remarks` - Save student terminal remarks & attendance counts
* `GET /api/v1/behavior-assessments` - Domain ratings (affective/psychomotor)
* `POST /api/v1/behavior-assessments` - Save behavioral ratings

### 6. Cashier / Bursar Portal
* `GET /api/v1/dashboard/cashier` - Revenue metrics & payment activity
* `GET /api/v1/payments` - Transaction history
* `POST /api/v1/payments` - Record fee payment & generate receipt
* `GET /api/v1/fee-structures` - Class fee structures
* `POST /api/v1/fee-structures` - Configure fee items by class

---

## ⚡ Key Technical Features & Business Logic

1. **Non-Destructive Score Saving (`ScoreSheetController.php`)**:
   Unentered continuous assessment or exam score fields remain `null`/blank in the database instead of defaulting to `0`. This guarantees clean score sheets without accidental grade drops.
2. **Dynamic Class Statistics (`ReportCardService.php`)**:
   Computes class averages, highest score, lowest score, and position per subject dynamically upon report card generation.
3. **Dense Ordinal Ranking (`RankingService.php`)**:
   Formats student position into human-readable ordinal strings (`1st`, `2nd`, `3rd`, `11th`, `21st`) handling ties gracefully.
4. **Multi-Tenant Isolation (`EnsureSchoolTenant.php`)**:
   Filters data queries by `school_id` to ensure complete data security across multiple schools.
5. **No Data Pre-Seeding**:
   The database schema starts clean without synthetic data so that live operational data can be entered directly.
