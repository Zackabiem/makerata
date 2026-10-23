@extends('layouts.app')

@section('title', 'Staff Account Activation - EduPro Ledger')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" x-data="staffActivation()">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-3xl bg-slate-900 text-white flex items-center justify-center font-black text-2xl mx-auto mb-4 shadow-xl">
                <i class="fa-solid fa-user-gear"></i>
            </div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Staff Account Activation</h2>
            <p class="text-xs text-slate-500 font-medium mt-2">Verify school records and activate teacher or cashier account</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-xl">
            <!-- Step Indicators -->
            <div class="flex items-center justify-center gap-2 mb-8">
                <div class="h-1.5 rounded-full transition-all duration-300" :class="step === 1 ? 'w-8 bg-slate-900' : 'w-4 bg-slate-200'"></div>
                <div class="h-1.5 rounded-full transition-all duration-300" :class="step === 2 ? 'w-8 bg-slate-900' : 'w-4 bg-slate-200'"></div>
                <div class="h-1.5 rounded-full transition-all duration-300" :class="step === 3 ? 'w-8 bg-slate-900' : 'w-4 bg-slate-200'"></div>
                <div class="h-1.5 rounded-full transition-all duration-300" :class="step === 4 ? 'w-8 bg-slate-900' : 'w-4 bg-slate-200'"></div>
            </div>

            <!-- Error Banner -->
            <div x-show="error" x-cloak class="p-4 mb-6 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs font-bold flex items-start gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                <span x-text="error"></span>
            </div>

            <!-- Step 1: Look up Staff Record -->
            <div x-show="step === 1">
                <form @submit.prevent="lookupStaff()" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Registered Email</label>
                        <input type="email" x-model="email" required placeholder="staff.email@school.com"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Phone Last 4 Digits</label>
                        <input type="text" maxlength="4" x-model="phone_last_4" required placeholder="e.g. 1234"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900">
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-4 bg-slate-900 text-white font-bold text-sm rounded-xl hover:bg-black transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading">Verify Staff Record &rarr;</span>
                        <span x-show="loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Checking...</span>
                    </button>
                </form>
            </div>

            <!-- Step 2: Confirm Profile -->
            <div x-show="step === 2" x-cloak>
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2 mb-6">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Verified Profile</p>
                    <p class="text-sm font-black text-slate-900" x-text="staffData?.full_name"></p>
                    <p class="text-xs font-bold text-slate-600" x-text="staffData?.school_name + ' (' + staffData?.role + ')'"></p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" @click="step = 1" class="w-full sm:w-1/3 py-3.5 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-200 transition-colors">
                        Back
                    </button>
                    <button type="button" @click="step = 3" class="w-full sm:w-2/3 py-3.5 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-black transition-colors">
                        Proceed to Set Password &rarr;
                    </button>
                </div>
            </div>

            <!-- Step 3: Set Credentials -->
            <div x-show="step === 3" x-cloak>
                <form @submit.prevent="activateAccount()" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">New Password</label>
                        <input type="password" x-model="password" required placeholder="Min. 8 characters"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Confirm Password</label>
                        <input type="password" x-model="password_confirmation" required placeholder="Re-enter password"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900">
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-4 bg-slate-900 text-white font-bold text-sm rounded-xl hover:bg-black transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading"><i class="fa-solid fa-check-circle mr-1"></i> Complete Activation</span>
                        <span x-show="loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Activating...</span>
                    </button>
                </form>
            </div>

            <!-- Step 4: Success -->
            <div x-show="step === 4" x-cloak class="text-center py-6 space-y-4">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900">Account Activated!</h3>
                <p class="text-xs text-slate-500 font-medium">Your credentials have been saved. You can now sign in to your staff portal.</p>
                <a href="{{ route('login') }}" class="w-full py-3.5 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-black transition-all block">
                    Proceed to Sign In &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function staffActivation() {
    return {
        step: 1,
        email: '',
        phone_last_4: '',
        password: '',
        password_confirmation: '',
        staffData: null,
        loading: false,
        error: null,

        async lookupStaff() {
            this.error = null;
            this.loading = true;
            try {
                const res = await fetch('{{ url("/api/v1/auth/staff/lookup") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email: this.email, phone_last_4: this.phone_last_4 })
                });
                const data = await res.json();
                if (!res.ok || data.status !== 'success') {
                    throw new Error(data.message || 'Lookup failed.');
                }
                this.staffData = data.data;
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
                const res = await fetch('{{ url("/api/v1/auth/staff/activate") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        staff_id: this.staffData.staff_id,
                        phone_last_4: this.phone_last_4,
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
