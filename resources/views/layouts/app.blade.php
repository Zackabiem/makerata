<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'EduPro Ledger - School Management System')</title>

    <!-- Tailwind CSS CDN for production-ready styling on shared host -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js for lightweight client-side reactivity without build tools -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome / Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-900 font-sans antialiased selection:bg-slate-900 selection:text-white flex flex-col min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Brand Logo -->
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 sm:gap-3 group">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-base sm:text-lg group-hover:scale-105 transition-transform shadow-md">
                            E
                        </div>
                        <div>
                            <span class="font-black text-slate-900 text-base sm:text-lg tracking-tight block leading-none">EduPro Ledger</span>
                            <span class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-400 tracking-wider">Laravel Edition</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        <span class="text-xs font-semibold px-3 py-1.5 bg-slate-100 rounded-full text-slate-700">
                            <i class="fa-solid fa-circle-user mr-1 text-slate-400"></i> {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
                        </span>
                        <a href="{{ route('dashboard') }}" class="text-xs font-bold px-4 py-2 bg-slate-900 text-white rounded-xl hover:bg-black transition-colors shadow-sm">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-slate-500 hover:text-slate-900 px-3 py-2">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('staff.activate') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 px-3 py-2">
                            Staff Activation
                        </a>
                        <a href="{{ route('student.activate') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 px-3 py-2">
                            Student Activation
                        </a>
                        <a href="{{ route('login') }}" class="text-xs font-bold px-4 py-2 bg-slate-900 text-white rounded-xl hover:bg-black transition-all shadow-sm">
                            Sign In
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex items-center md:hidden">
                    <button @click="mobileOpen = !mobileOpen" type="button" class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none" aria-label="Toggle Navigation">
                        <i class="fa-solid" :class="mobileOpen ? 'fa-xmark text-xl' : 'fa-bars text-xl'"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Slide-Down Menu -->
        <div x-show="mobileOpen" x-cloak @click.away="mobileOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="md:hidden border-t border-slate-100 bg-white px-4 pt-3 pb-6 space-y-3 shadow-lg">
            @auth
                <div class="px-3 py-2 bg-slate-50 rounded-xl mb-2">
                    <p class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-500 font-semibold uppercase">{{ ucfirst(auth()->user()->role) }}</p>
                </div>
                <a href="{{ route('dashboard') }}" class="block text-sm font-bold text-slate-900 hover:bg-slate-50 px-3 py-2.5 rounded-xl">
                    <i class="fa-solid fa-gauge mr-2 text-slate-400"></i> Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-2.5 rounded-xl">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                    </button>
                </form>
            @else
                <a href="{{ route('staff.activate') }}" class="block text-sm font-bold text-slate-700 hover:bg-slate-50 px-3 py-2.5 rounded-xl">
                    <i class="fa-solid fa-user-gear mr-2 text-slate-400"></i> Staff Activation
                </a>
                <a href="{{ route('student.activate') }}" class="block text-sm font-bold text-indigo-600 hover:bg-indigo-50 px-3 py-2.5 rounded-xl">
                    <i class="fa-solid fa-graduation-cap mr-2 text-indigo-400"></i> Student Activation
                </a>
                <a href="{{ route('login') }}" class="block text-center text-sm font-bold bg-slate-900 text-white py-3 rounded-xl hover:bg-black mt-2 shadow-sm">
                    Sign In
                </a>
            @endauth
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl text-xs font-bold flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-red-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Main Body Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-400 font-medium">
            &copy; {{ date('Y') }} EduPro Ledger - Complete School Management System (Laravel Edition). Built for shared hosting & cloud deployments.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
