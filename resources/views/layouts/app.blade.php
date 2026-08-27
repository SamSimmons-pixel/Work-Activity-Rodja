<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-user-id" content="{{ auth()->id() }}">

    <title>{{ $title ?? 'Dashboard' }} - Work Activity</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Quill.js WYSIWYG Editor Styles -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full antialiased text-slate-900 bg-slate-50 selection:bg-indigo-500 selection:text-white">
    <div class="min-h-full flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Brand & Left Nav -->
                    <div class="flex items-center gap-8">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-sm shadow-indigo-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <span class="font-bold text-lg text-slate-900 tracking-tight">Work Activity</span>
                        </a>

                        <!-- Navigation Links -->
                        <nav class="hidden md:flex items-center gap-1">
                            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                Aktivitas Kerja
                            </a>

                            <a href="{{ route('reviews.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('reviews.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                Review Kinerja
                            </a>

                            <a href="{{ route('organization.chart') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('organization.chart') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                Bagan Organisasi
                            </a>

                            @can('user.manage')
                                <a href="{{ route('admin.users') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.users*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    Pengguna
                                </a>
                            @endcan

                            @can('role.manage')
                                <a href="{{ route('admin.roles') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.roles*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    Peran & Izin
                                </a>
                            @endcan

                            @can('division.manage')
                                <a href="{{ route('admin.organization') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.organization*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    Divisi & Posisi
                                </a>
                            @endcan
                        </nav>
                    </div>

                    <!-- Right User & Notification Menu -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Real-time Notification Bell Ring Dropdown -->
                        <livewire:notifications.notification-menu />

                        <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                        <a href="{{ route('profile') }}" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-50 transition cursor-pointer {{ request()->routeIs('profile') ? 'bg-indigo-50/70 ring-1 ring-indigo-200' : '' }}">
                            <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                {{ strtoupper(substr(auth()->user()->full_name, 0, 2)) }}
                            </div>
                            <div class="hidden sm:flex flex-col text-left">
                                <span class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()->full_name }}</span>
                                <span class="text-2xs text-slate-500">{{ auth()->user()->role?->name ?? 'User' }} &bull; {{ auth()->user()->position?->name ?? (auth()->user()->division?->name ?? 'Internal') }}</span>
                            </div>
                        </a>

                        <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                title="Keluar dari akun"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Slot -->
        <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    <!-- Quill.js WYSIWYG Editor Script -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    @livewireScripts
</body>
</html>
