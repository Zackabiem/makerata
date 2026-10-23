@extends('layouts.app')

@section('title', 'Student Account Activation - EduPro Ledger')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" x-data="studentActivation()">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-3xl bg-indigo-600 text-white flex items-center justify-center font-black text-2xl mx-auto mb-4 shadow-xl">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Student Portal Activation</h2>
            <p class="text-xs text-slate-500 font-medium mt-2">Verify admission code and parent security verification</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-xl">
            <!-- Step Indicators -->
            <div class="flex items-center justify-center gap-2 mb-8">
                <div class="h-1.5 rounded-full transition-all duration-300" :class="step === 1 ? 'w-8 bg-indigo-600' : 'w-4 bg-slate-200'"></div>
                <div class="h-1.5 rounded-full transition-all duration-300" :class="step === 2 ? 'w-8 bg-indigo-600' : 'w-4 bg-slate-200'"></div>
                <div class="h-1.5 rounded-full transition-all duration-300" :class="step === 3 ? 'w-8 bg-indigo-600' : 'w-4 bg-slate-200'"></div>
                <div class="h-1.5 rounded-full transition-all duration-300" :class="step === 4 ? 'w-8 bg-indigo-600' : 'w-4 bg-slate-200'"></div>
            </div>

            <!-- Error Banner -->
            <div x-show="error" x-cloak class="p-4 mb-6 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs font-bold flex items-start gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                <span x-text="error"></span>
            </div>

            <!-- Step 1: Look up Student -->
            <div x-show="step === 1">
                <form @submit.prevent="lookupStudent()" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Admission Number</label>
                        <input type="text" x-model="admission_no" required placeholder="e.g. ADM/2026/001"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Parent Phone Last 4 Digits</label>
                        <input type="text" maxlength="4" x-model="parent_phone_last_4" required placeholder="e.g. 1234"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-4 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading">Find Student Profile &rarr;</span>
                        <span x-show="loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Checking...</span>
                    </button>
                </form>
            </div>

            <!-- Step 2: Confirm Profile -->
            <div x-show="step === 2" x-cloak>
                <div class="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100 space-y-2 mb-6">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Student Profile Found</p>
                    <p class="text-sm font-black text-slate-900" x-text="studentData?.full_name"></p>
                    <p class="text-xs font-bold text-slate-600" x-text="'School: ' + studentData?.school_name"></p>
                    <p class="text-xs font-semibold text-slate-500" x-text="'Admission No: ' + studentData?.admission_no"></p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" @click="step = 1" class="w-full sm:w-1/3 py-3.5 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-200 transition-colors">
                        Back
                    </button>
                    <button type="button" @click="step = 3" class="w-full sm:w-2/3 py-3.5 bg-indigo-600 text-white font-bold text-xs rounded-xl hover:bg-indigo-700 transition-colors">
                        Confirm & Create Password &rarr;
                    </button>
                </div>
            </div>

            <!-- Step 3: Set Credentials -->
            <div x-show="step === 3" x-cloak>
                <form @submit.prevent="activateAccount()" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Student Email (Optional)</label>
                        <input type="email" x-model="email" placeholder="student@email.com (optional)"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">New Password</label>
                        <input type="password" x-model="password" required placeholder="Min. 8 characters"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Confirm Password</label>
                        <input type="password" x-model="password_confirmation" required placeholder="Re-enter password"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-4 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading"><i class="fa-solid fa-check-circle mr-1"></i> Activate Student Account</span>
                        <span x-show="loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Activating...</span>
                    </button>
                </form>
            </div>

            <!-- Step 4: Success -->
            <div x-show="step === 4" x-cloak class="text-center py-6 space-y-4">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900">Activation Successful!</h3>
                <p class="text-xs text-slate-500 font-medium">Your student portal account is now active.</p>
                <a href="{{ route('login') }}" class="w-full py-3.5 bg-indigo-600 text-white font-bold text-xs rounded-xl hover:bg-indigo-700 transition-all block">
                    Sign In to Portal &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function studentActivation() {
    return {
        step: 1,
        admission_no: '',
        parent_phone_last_4: '',
        email: '',
        password: '',
        password_confirmation: '',
        studentData: null,
        loading: false,
        error: null,

        async lookupStudent() {
            this.error = null;
            this.loading = true;
            try {
                const res = await fetch('{{ url("/api/v1/auth/student/lookup") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ admission_no: this.admission_no, parent_phone_last_4: this.parent_phone_last_4 })
                });
                const data = await res.json();
                if (!res.ok || data.status !== 'success') {
                    throw new Error(data.message || 'Lookup failed.');
                }
                this.studentData = data.data;
                this.step = 2;
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        },

        async activateAccount() {
            this.error = null;
            if (this.password !== this.password_confirmation) {
                this.error = 'Passwords do not match.';
                return;
            }
            if (this.password.length < 8) {
                this.error = 'Password must be at least 8 characters.';
                return;
            }
            this.loading = true;
            try {
                const res = await fetch('{{ url("/api/v1/auth/student/activate") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        student_id: this.studentData.student_id,
                        parent_phone_last_4: this.parent_phone_last_4,
                        email: this.email || undefined,
                        password: this.password,
                        password_confirmation: this.password_confirmation
                    })
                });
                const data = await res.json();
                if (!res.ok || data.status !== 'success') {
                    throw new Error(data.message || 'Activation failed.');
                }
                this.step = 4;
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
