@extends('layouts.app')

@section('title', 'Dashboard - EduPro Ledger')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 sm:py-8 sm:px-6 lg:px-8">
    <!-- Header Banner -->
    <div class="bg-slate-900 text-white rounded-2xl sm:rounded-3xl p-6 sm:p-8 mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shadow-xl">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-400 bg-indigo-950/60 px-3 py-1 rounded-full border border-indigo-900">
                Logged in as {{ ucfirst(auth()->user()->role) }}
            </span>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-black mt-2">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="text-xs text-slate-400 mt-1 font-medium break-all sm:break-normal">{{ auth()->user()->email }} | {{ auth()->user()->school ? auth()->user()->school->school_name : 'EduPro School System' }}</p>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-emerald-400 bg-emerald-950/50 px-3.5 py-2 rounded-xl border border-emerald-900 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> System Active
            </span>
        </div>
    </div>

    <!-- Quick Action Cards based on role -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
        @if(auth()->user()->role === 'headteacher' || auth()->user()->role === 'admin')
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-900 font-bold mb-4">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Terminal Reports & Approval</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">Review terminal subject score sheets, class rankings and append electronic headteacher stamps.</p>
                <a href="{{ url('/reports/terminal/print') }}" target="_blank" class="inline-block text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    Open Print Preview &rarr;
                </a>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-900 font-bold mb-4">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Staff Management</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">Manage teachers, cashiers, class assignments, and monitor staff activation status.</p>
                <a href="{{ route('staff.activate') }}" class="inline-block text-xs font-bold text-slate-900 hover:text-black">
                    Staff Activation Portal &rarr;
                </a>
            </div>
        @endif

        @if(auth()->user()->role === 'teacher')
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-900 font-bold mb-4">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Batch Score Sheet Entry</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">Enter CA1, CA2, CA3, Exam, Attendance and Psychomotor ratings for assigned classes.</p>
                <a href="{{ url('/api/v1/scores/batch-save') }}" class="inline-block text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    API Endpoint Active &rarr;
                </a>
            </div>
        @endif

        @if(auth()->user()->role === 'cashier')
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-900 font-bold mb-4">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Fee Payments & Receipts</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">Log tuition payments, generate print receipts, and issue watermark report clearances.</p>
            </div>
        @endif

        @if(auth()->user()->role === 'student')
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-900 font-bold mb-4">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Terminal Report Cards</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">View terminal grades, position in class, attendance records, and headteacher remarks.</p>
                <a href="{{ url('/api/v1/reports/terminal') }}" target="_blank" class="inline-block text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    Get Terminal Report Data &rarr;
                </a>
            </div>
        @endif
    </div>

    <!-- API System Quick Reference for Laravel -->
    <div class="bg-slate-100 border border-slate-200 rounded-2xl sm:rounded-3xl p-5 sm:p-6 text-xs text-slate-600 space-y-2">
        <p class="font-bold text-slate-900 uppercase tracking-wider text-[10px]">Laravel API Route Status</p>
        <p>Your Laravel application is running on PHP with active Sanctum API endpoints (`/api/v1/*`) and Blade Web Views.</p>
    </div>
</div>
@endsection
