@extends('layouts.app')

@section('title', 'Welcome - EduPro Ledger Laravel Portal')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-8 sm:mb-12">
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 border border-indigo-100 rounded-full text-indigo-700 text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-3 sm:mb-4">
            <i class="fa-solid fa-server"></i> Native PHP Laravel Standalone Portal
        </span>
        <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-slate-900 tracking-tight leading-tight">
            School Ledger & Terminal Reports Platform
        </h1>
        <p class="mt-3 sm:mt-4 text-xs sm:text-sm md:text-base text-slate-500 font-medium leading-relaxed">
            Complete institutional portal supporting multi-role authentication, batch score entry, terminal report generation, and student accounts.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 max-w-5xl mx-auto">
        <!-- Card 1: Staff Activation -->
        <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-900 text-white flex items-center justify-center mb-4 sm:mb-6 text-lg sm:text-xl shadow-md">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 mb-2">Staff Activation</h2>
                <p class="text-xs text-slate-500 leading-relaxed font-medium mb-6">
                    Teachers, Cashiers & Administrators can look up their registered email and phone challenge code to create access passwords.
                </p>
            </div>
            <a href="{{ route('staff.activate') }}" class="w-full py-3.5 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-black transition-all text-center block shadow-sm">
                Activate Staff Account &rarr;
            </a>
        </div>

        <!-- Card 2: Student Activation -->
        <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-600 text-white flex items-center justify-center mb-4 sm:mb-6 text-lg sm:text-xl shadow-md">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 mb-2">Student Activation</h2>
                <p class="text-xs text-slate-500 leading-relaxed font-medium mb-6">
                    Students and Parents can activate their portal access using admission number and parent security verification code.
                </p>
            </div>
            <a href="{{ route('student.activate') }}" class="w-full py-3.5 bg-indigo-600 text-white font-bold text-xs rounded-xl hover:bg-indigo-700 transition-all text-center block shadow-sm">
                Activate Student Account &rarr;
            </a>
        </div>

        <!-- Card 3: Direct Portal Login -->
        <div class="bg-white border border-slate-200 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-600 text-white flex items-center justify-center mb-4 sm:mb-6 text-lg sm:text-xl shadow-md">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 mb-2">Portal Sign In</h2>
                <p class="text-xs text-slate-500 leading-relaxed font-medium mb-6">
                    Already activated? Sign in to access your Headteacher dashboard, Teacher score sheets, Cashier fees ledger, or Student report cards.
                </p>
            </div>
            <a href="{{ route('login') }}" class="w-full py-3.5 bg-emerald-600 text-white font-bold text-xs rounded-xl hover:bg-emerald-700 transition-all text-center block shadow-sm">
                Sign In to Portal &rarr;
            </a>
        </div>
    </div>
</div>
@endsection
