@extends('layouts.app')

@section('title', 'Sign In - EduPro Ledger')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-3xl bg-slate-900 text-white flex items-center justify-center font-black text-2xl mx-auto mb-4 shadow-xl">
                E
            </div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Portal Sign In</h2>
            <p class="text-xs text-slate-500 font-medium mt-2">Enter your email and password to access your role dashboard</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-xl">
            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs font-bold space-y-1">
                        @foreach ($errors->all() as $error)
                            <p><i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="your.email@school.com"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Password</label>
                    <input type="password" name="password" required
                           placeholder="••••••••"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900">
                </div>

                <button type="submit" class="w-full py-4 bg-slate-900 text-white font-bold text-sm rounded-xl hover:bg-black transition-all shadow-md">
                    Sign In
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col gap-2 text-center text-xs font-medium text-slate-500">
                <p>Need to activate your staff account? <a href="{{ route('staff.activate') }}" class="text-slate-900 font-bold hover:underline">Staff Activation</a></p>
                <p>Student or parent? <a href="{{ route('student.activate') }}" class="text-indigo-600 font-bold hover:underline">Student Activation</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
