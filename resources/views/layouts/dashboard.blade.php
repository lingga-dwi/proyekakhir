<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - Daiku Interior')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="h-dvh overflow-hidden bg-slate-100 text-slate-900" x-data="{ sidebarOpen: false }">
    <div class="flex h-full min-h-0 overflow-hidden">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="sidebarOpen = false"></div>

        <aside class="fixed inset-y-0 left-0 z-50 flex h-dvh w-72 shrink-0 -translate-x-full flex-col overflow-hidden border-r border-slate-200 bg-white transition-transform duration-200 lg:relative lg:inset-auto lg:h-full lg:w-64 lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="flex h-20 items-center justify-between border-b border-slate-100 px-6">
                <a href="{{ route('home') }}" aria-label="Kembali ke beranda Daiku">
                    <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 w-auto">
                </a>
                <button type="button" class="text-slate-500 lg:hidden" @click="sidebarOpen = false" aria-label="Tutup menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            @php
                $baseNav = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition';
                $activeNav = 'bg-amber-400 text-slate-950 shadow-sm';
                $inactiveNav = 'text-slate-600 hover:bg-amber-50 hover:text-slate-950';
            @endphp
            <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-4 py-6">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('dashboard.admin') }}" class="{{ $baseNav }} {{ request()->routeIs('dashboard.admin') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-chart-pie w-5 text-center"></i><span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.pemesanan.index') }}" class="{{ $baseNav }} {{ request()->routeIs('admin.pemesanan.*', 'admin.proyek.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-briefcase w-5 text-center"></i><span>Pekerjaan</span>
                    </a>
                    <a href="{{ route('admin.katalog.index') }}" class="{{ $baseNav }} {{ request()->routeIs('admin.katalog.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-images w-5 text-center"></i><span>Kelola Katalog</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="{{ $baseNav }} {{ request()->routeIs('admin.users.*') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-users w-5 text-center"></i><span>Pengguna</span>
                    </a>
                @else
                    <a href="{{ route('dashboard.designer') }}" class="{{ $baseNav }} {{ request()->routeIs('dashboard.designer') ? $activeNav : $inactiveNav }}">
                        <i class="fas fa-drafting-compass w-5 text-center"></i><span>Proyek Saya</span>
                    </a>
                @endif
            </nav>

            <div class="border-t border-slate-100 p-4">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-400 font-bold text-slate-950">
                        {{ Str::upper(Str::substr(auth()->user()->nama, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ auth()->user()->nama }}</p>
                        <p class="text-xs capitalize text-slate-500">{{ auth()->user()->role }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i>Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex h-full min-w-0 flex-1 flex-col overflow-hidden">
            <header class="z-30 shrink-0 border-b border-slate-200 bg-white/95 px-4 py-4 backdrop-blur sm:px-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-600 lg:hidden" @click="sidebarOpen = true" aria-label="Buka menu">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="min-w-0">
                            <h1 class="truncate text-xl font-semibold text-slate-950 sm:text-2xl">@yield('page-title')</h1>
                            <p class="hidden truncate text-sm text-slate-500 sm:block">@yield('page-description')</p>
                        </div>
                    </div>
                    <a href="{{ route('home') }}" class="hidden items-center gap-2 text-sm font-medium text-slate-600 hover:text-amber-700 sm:inline-flex">
                        Lihat website <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                </div>
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <p class="font-semibold">Periksa kembali data berikut:</p>
                        <ul class="mt-2 list-disc pl-5"><li>{{ $errors->first() }}</li></ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
