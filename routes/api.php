<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffActivationController;
use App\Http\Controllers\StudentActivationController;
use App\Http\Controllers\ScoreSheetController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\GradingSystemController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BehaviorAssessmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TerminalRemarkController;
use App\Http\Controllers\HomeworkController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BroadsheetController;
use App\Http\Controllers\StaffProfileController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\SystemAdminController;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
| Mirrored to match React Frontend dashboards and multi-role RBAC rules.
*/

Route::prefix('v1')->group(function () {

    // ==========================================
    // 1. PUBLIC AUTH, ACTIVATION & RESULT CHECKER ENDPOINTS
    // ==========================================
    Route::get('/result-checker', [ReportCardController::class, 'getTerminalReport']);
    Route::get('/result-checker/annual', [ReportCardController::class, 'getAnnualReport']);

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);

        // Staff Account Activation
        Route::post('/staff/lookup', [StaffActivationController::class, 'lookup']);
        Route::post('/staff/activate', [StaffActivationController::class, 'activate']);

        // Student Account Activation
        Route::post('/student/lookup', [StudentActivationController::class, 'lookup']);
        Route::post('/student/activate', [StudentActivationController::class, 'activate']);

        // Protected Auth & Session Verification
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    // ==========================================
    // 2. DASHBOARD & ROLE-BASED PERMISSION GROUPS
    // ==========================================

    // --- A. System Admin & Principal Dashboard (/dashboard/admin & /dashboard/headteacher) ---
    Route::prefix('dashboard/admin')->middleware(['auth:sanctum', 'role:admin,headteacher,principal'])->group(function () {
        Route::get('/summary', [DashboardController::class, 'adminSummary']);
        
        // Classes & Curriculum Setup
        Route::get('/classes', [ClassController::class, 'index']);
        Route::post('/classes', [ClassController::class, 'store']);
        Route::put('/classes/{id}', [ClassController::class, 'update']);
        Route::delete('/classes/{id}', [ClassController::class, 'destroy']);
        Route::post('/classes/{id}/form-master', [ClassController::class, 'assignFormMaster']);
        Route::post('/classes/{id}/map-subjects', [ClassController::class, 'mapSubjects']);

        // Subjects Management
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::put('/subjects/{id}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);

        // Grading Scale Rules
        Route::get('/grading-scales', [GradingSystemController::class, 'index']);
        Route::post('/grading-scales', [GradingSystemController::class, 'store']);
        Route::post('/grading-scales/batch-save', [GradingSystemController::class, 'batchSave']);

        // Student Admission, Profile Editing & Bulk Enrollment / Promotion
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/{id}', [StudentController::class, 'show']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::put('/students/{id}', [StudentController::class, 'update']);
        Route::post('/students/{id}', [StudentController::class, 'update']);
        Route::post('/students/bulk-import', [StudentController::class, 'bulkImport']);
        Route::post('/enrollments/enroll', [EnrollmentController::class, 'enrollStudent']);
        Route::post('/enrollments/bulk-enroll', [EnrollmentController::class, 'bulkEnroll']);
        Route::post('/enrollments/promote', [EnrollmentController::class, 'promoteStudents']);
        Route::get('/enrollments/history/{studentId}', [EnrollmentController::class, 'getHistory']);

        // Communication Campaigns & Templates
        Route::get('/communication/campaigns', [CommunicationController::class, 'getCampaigns']);
        Route::post('/communication/campaigns', [CommunicationController::class, 'createCampaign']);
        Route::get('/communication/templates', [CommunicationController::class, 'getTemplates']);

        // SaaS Subscription Billing
        Route::get('/subscriptions/plans', [SubscriptionController::class, 'getPlans']);
        Route::get('/subscriptions/active', [SubscriptionController::class, 'getSchoolSubscription']);

        // School Settings, Report Card Template & Active Session Configuration
        Route::get('/school/settings', [SchoolSettingController::class, 'show']);
        Route::put('/school/settings', [SchoolSettingController::class, 'update']);
        Route::post('/school/settings', [SchoolSettingController::class, 'update']);
    });

    // --- A2. Global System Admin / Site Admin Dashboard (/api/v1/system-admin) ---
    Route::prefix('system-admin')->middleware(['auth:sanctum', 'role:admin,super_admin,system_admin'])->group(function () {
        Route::get('/summary', [SystemAdminController::class, 'summary']);
        Route::get('/schools', [SystemAdminController::class, 'listSchools']);
        Route::post('/schools', [SystemAdminController::class, 'storeSchool']);
        Route::get('/billing-activity', [SystemAdminController::class, 'billingActivity']);
        Route::post('/plans', [SystemAdminController::class, 'savePlan']);
    });

    // --- B. Teacher / Form Master Dashboard (/dashboard/teacher) ---
    Route::prefix('dashboard/teacher')->middleware(['auth:sanctum', 'role:teacher,admin,headteacher,principal'])->group(function () {
        Route::get('/summary', [DashboardController::class, 'teacherSummary']);

        // Continuous Assessment (CA) & Exam Score Recording
        Route::get('/scores', [ScoreSheetController::class, 'getScores']);
        Route::post('/scores/batch-save', [ScoreSheetController::class, 'batchSave']);

        // Affective & Psychomotor Behavior Assessments
        Route::get('/behavior-assessments', [BehaviorAssessmentController::class, 'index']);
        Route::post('/behavior-assessments/batch-save', [BehaviorAssessmentController::class, 'batchSave']);

        // Daily Attendance Register
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance/batch-save', [AttendanceController::class, 'batchSave']);

        // Form Teacher Terminal Remarks
        Route::get('/terminal-remarks', [TerminalRemarkController::class, 'index']);
        Route::post('/terminal-remarks/batch-save', [TerminalRemarkController::class, 'batchSave']);

        // Homework Assignment Management
        Route::get('/homeworks', [HomeworkController::class, 'index']);
        Route::post('/homeworks', [HomeworkController::class, 'store']);
        Route::post('/homework-submissions/{id}/grade', [HomeworkController::class, 'gradeSubmission']);

        // Student Roster, Profile Editing & Class Promotion
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/{id}', [StudentController::class, 'show']);
        Route::put('/students/{id}', [StudentController::class, 'update']);
        Route::post('/students/{id}', [StudentController::class, 'update']);
        Route::post('/enrollments/promote', [EnrollmentController::class, 'promoteStudents']);
        Route::get('/enrollments/history/{studentId}', [EnrollmentController::class, 'getHistory']);

        // Class Summary Broadsheet & Printable Subject Score Sheet
        Route::get('/broadsheet', [BroadsheetController::class, 'getBroadsheet']);
        Route::get('/subject-scoresheet/print', [BroadsheetController::class, 'printSubjectScoresheet']);
        Route::get('/reports/terminal', [ReportCardController::class, 'getTerminalReport']);
        Route::get('/reports/annual', [ReportCardController::class, 'getAnnualReport']);

        // Teacher Profile Management (House Address, Phone, Password)
        Route::get('/profile', [StaffProfileController::class, 'show']);
        Route::put('/profile', [StaffProfileController::class, 'update']);
        Route::post('/profile', [StaffProfileController::class, 'update']);
    });

    // --- C. Cashier / Bursary Dashboard (/dashboard/cashier) ---
    Route::prefix('dashboard/cashier')->middleware(['auth:sanctum', 'role:cashier,admin,bursar'])->group(function () {
        Route::get('/summary', [DashboardController::class, 'cashierSummary']);

        // Fee Categories & Bill Structures
        Route::get('/fee-categories', [FeeCategoryController::class, 'index']);
        Route::post('/fee-categories', [FeeCategoryController::class, 'store']);
        Route::get('/fee-structures', [FeeStructureController::class, 'index']);
        Route::post('/fee-structures', [FeeStructureController::class, 'store']);
        Route::post('/fee-structures/batch-save', [FeeStructureController::class, 'batchSave']);

        // Payment Transactions & Receipt Generation
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/student-ledger/{studentId}', [PaymentController::class, 'getStudentLedger']);
        Route::get('/fee-structures/student-bill/{studentId}', [FeeStructureController::class, 'getStudentBill']);
    });

    // --- D. Student & Parent Portal (/dashboard/student) ---
    Route::prefix('dashboard/student')->middleware(['auth:sanctum', 'role:student,parent'])->group(function () {
        Route::get('/summary', [DashboardController::class, 'studentSummary']);

        // Terminal Report Cards
        Route::get('/reports/terminal', [ReportCardController::class, 'getTerminalReport']);

        // Student Financial Ledger & Outstanding Bills
        Route::get('/bill', [FeeStructureController::class, 'getStudentBill']);
        Route::get('/payments-ledger', [PaymentController::class, 'getStudentLedger']);

        // Homework View & Online Submission
        Route::get('/homeworks', [HomeworkController::class, 'index']);
        Route::post('/homeworks/{id}/submit', [HomeworkController::class, 'submitHomework']);
    });

    // ==========================================
    // 3. GENERAL RESOURCE ROUTES (FALLBACK / CORE)
    // ==========================================
    Route::get('/scores', [ScoreSheetController::class, 'getScores']);
    Route::post('/scores/batch-save', [ScoreSheetController::class, 'batchSave']);
    Route::get('/reports/terminal', [ReportCardController::class, 'getTerminalReport']);
    Route::get('/classes', [ClassController::class, 'index']);
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/grading-scales', [GradingSystemController::class, 'index']);
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/fee-categories', [FeeCategoryController::class, 'index']);
    Route::get('/fee-structures', [FeeStructureController::class, 'index']);
    Route::get('/homeworks', [HomeworkController::class, 'index']);
    Route::get('/communication/campaigns', [CommunicationController::class, 'getCampaigns']);
    Route::get('/subscriptions/plans', [SubscriptionController::class, 'getPlans']);
});
