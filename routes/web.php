<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\BroadsheetController;
use App\Http\Controllers\SitemapController;

/*
|--------------------------------------------------------------------------
| Web Routes (Laravel Blade Views & Web Session Handlers)
|--------------------------------------------------------------------------
*/

// XML Sitemap for SEO Crawlers
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Home / Landing Page
Route::get('/', function () {
    return view('home');
})->name('home');

// Auth Web Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'webLogin'])->name('login.post');
Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');

// Staff Activation Web View
Route::get('/staff/activate', function () {
    return view('auth.staff_activation');
})->name('staff.activate');

// Student Activation Web View
Route::get('/student/activate', function () {
    return view('auth.student_activation');
})->name('student.activate');

// Dashboard Web View
Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Printable Terminal Report View (Blade template)
Route::get('/reports/terminal/print', [ReportCardController::class, 'getTerminalReport'])->name('reports.terminal.print');
Route::get('/reports/annual/print', [ReportCardController::class, 'getAnnualReport'])->name('reports.annual.print');
Route::get('/reports/broadsheet/print', [BroadsheetController::class, 'getBroadsheet'])->name('reports.broadsheet.print');
Route::get('/reports/subject-scoresheet/print', [BroadsheetController::class, 'printSubjectScoresheet'])->name('reports.subject_scoresheet.print');
