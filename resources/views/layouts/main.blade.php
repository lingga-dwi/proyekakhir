<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $metaTitle = trim($__env->yieldContent('title')) ?: 'Daiku Interior Pekanbaru';
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: 'Jasa desain interior di Pekanbaru untuk hunian, kantor, dan ruang usaha.';
        $metaImage = trim($__env->yieldContent('meta_image')) ?: asset('images/logo/image.png');
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Daiku Interior">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>{{ $metaTitle }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Head Scripts -->
    @stack('head-scripts')
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Header Navigation -->
    <header x-data="{ mobileOpen: false }" @keydown.escape.window="mobileOpen = false" class="fixed top-0 inset-x-0 z-50 bg-white shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" aria-label="Kembali ke beranda">
                        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-5 w-auto">
                    </a>
                </div>
                
                <!-- Navigation Menu -->
                <div class="hidden md:flex space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('home') ? 'text-yellow-600' : '' }}">Beranda</a>
                    <a href="{{ route('about') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('about') ? 'text-yellow-600' : '' }}">Tentang Kami</a>
                    <a href="{{ route('katalog') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('katalog*') ? 'text-yellow-600' : '' }}">Katalog</a>
                    <a href="{{ route('konsultasi.index') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('konsultasi*') ? 'text-yellow-600' : '' }}">Konsultasi</a>
                </div>
                
                <!-- User Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <div class="relative w-56" x-data="{ open: false }">
                            @php
                                $roleLabel = match (auth()->user()->role) {
                                    'admin' => 'Admin',
                                    'designer' => 'Desainer',
                                    default => 'Pelanggan',
                                };
                            @endphp
                            <button type="button"
                                    @click="open = !open"
                                    :aria-expanded="open.toString()"
                                    aria-haspopup="menu"
                                    class="group relative z-10 flex w-full items-center gap-2.5 border bg-white px-2 py-1.5 text-left transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-400"
                                    :class="open
                                        ? 'rounded-t-xl border-gray-200 border-b-transparent'
                                        : 'rounded-xl border-gray-200 hover:border-amber-300 hover:shadow-sm'">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500" aria-hidden="true">
                                    <i class="fas fa-user text-sm"></i>
                                </span>
                                <span class="min-w-0 flex-1 leading-tight">
                                    <span class="block max-w-[112px] truncate text-xs font-bold text-slate-900">{{ auth()->user()->nama }}</span>
                                    <span class="mt-0.5 block text-[11px] font-medium text-gray-400">{{ $roleLabel }}</span>
                                </span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-600 transition-transform duration-200" :class="open ? 'rotate-180' : ''" aria-hidden="true"></i>
                            </button>
                            
                            <div x-cloak x-show="open" x-transition.origin.top.right @click.away="open = false" role="menu" class="absolute right-0 top-full z-50 w-full rounded-b-xl border border-t-0 border-gray-200 bg-white p-2 shadow-xl">
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('dashboard.admin') }}" role="menuitem" class="block rounded-xl px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-800">
                                        <i class="fas fa-cog mr-2"></i>Admin Panel
                                    </a>
                                @elseif(auth()->user()->isDesigner())
                                    <a href="{{ route('dashboard.designer') }}" role="menuitem" class="block rounded-xl px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-800">
                                        <i class="fas fa-drafting-compass mr-2"></i>Dashboard Designer
                                    </a>
                                @else
                                    <a href="{{ route('aktivitas.saya') }}" role="menuitem" class="block rounded-xl px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-800">
                                        <i class="fas fa-folder-open mr-2"></i>Aktivitas Saya
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-gray-100 pt-1">
                                    @csrf
                                    <button type="submit" role="menuitem" class="block w-full rounded-xl px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="{{ route('register') }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-yellow-600">Daftar</a>
                    @endauth
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button"
                            @click="mobileOpen = !mobileOpen"
                            :aria-expanded="mobileOpen.toString()"
                            aria-controls="mobile-navigation"
                            class="text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-yellow-500 p-2 rounded-md">
                        <span class="sr-only">Buka menu navigasi</span>
                        <i class="fas" :class="mobileOpen ? 'fa-times' : 'fa-bars'" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div id="mobile-navigation" x-cloak x-show="mobileOpen" x-transition class="md:hidden border-t border-gray-100 py-3">
                <div class="space-y-1">
                    <a href="{{ route('home') }}" @click="mobileOpen = false" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Beranda</a>
                    <a href="{{ route('about') }}" @click="mobileOpen = false" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('about') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Tentang Kami</a>
                    <a href="{{ route('katalog') }}" @click="mobileOpen = false" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('katalog*') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Katalog</a>
                    <a href="{{ route('konsultasi.index') }}" @click="mobileOpen = false" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('konsultasi*') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Konsultasi</a>
                </div>

                <div class="mt-3 border-t border-gray-100 pt-3">
                    @auth
                        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ auth()->user()->nama }}</p>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('dashboard.admin') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Admin Panel</a>
                        @elseif(auth()->user()->isDesigner())
                            <a href="{{ route('dashboard.designer') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Dashboard Designer</a>
                        @else
                            <a href="{{ route('aktivitas.saya') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('aktivitas.saya', 'pemesanan.show', 'konsultasi.show') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Aktivitas Saya</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="mt-1">
                            @csrf
                            <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50">Keluar</button>
                        </form>
                    @else
                        <div class="grid grid-cols-2 gap-2 px-3">
                            <a href="{{ route('login') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Login</a>
                            <a href="{{ route('register') }}" class="rounded-lg bg-amber-400 px-4 py-2 text-center text-sm font-semibold text-slate-900 hover:bg-amber-300">Daftar</a>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main class="pt-16">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-white text-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <!-- Brand -->
                <div class="lg:col-span-5">
                    <a href="{{ route('home') }}" aria-label="Kembali ke beranda" class="inline-block mb-5">
                        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 w-auto">
                    </a>
                    <p class="text-gray-600 leading-relaxed max-w-md mb-6">
                        Jasa desain interior dan furnitur custom untuk hunian, kantor, dan ruang usaha di Pekanbaru.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('konsultasi.index') }}" class="inline-flex items-center rounded-lg bg-amber-400 px-5 py-2.5 text-slate-900 font-semibold hover:bg-amber-300 transition duration-200">
                            Konsultasi Sekarang
                        </a>
                        <a href="{{ route('katalog') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-gray-700 hover:border-amber-400 hover:text-amber-600 transition duration-200">
                            Lihat Katalog
                        </a>
                    </div>
                </div>

                <!-- Links -->
                <div class="lg:col-span-3">
                    <h3 class="text-base font-semibold tracking-wide text-gray-900 mb-4">Navigasi</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('home') }}" class="text-gray-600 hover:text-amber-600 transition duration-150">Beranda</a></li>
                        <li><a href="{{ route('about') }}" class="text-gray-600 hover:text-amber-600 transition duration-150">Tentang Kami</a></li>
                        <li><a href="{{ route('katalog') }}" class="text-gray-600 hover:text-amber-600 transition duration-150">Katalog</a></li>
                        <li><a href="{{ route('konsultasi.index') }}" class="text-gray-600 hover:text-amber-600 transition duration-150">Konsultasi</a></li>
                        <li><a href="{{ route('register') }}" class="text-gray-600 hover:text-amber-600 transition duration-150">Daftar Akun</a></li>
                    </ul>
                </div>

                <!-- Studio -->
                <div class="lg:col-span-4">
                    <h3 class="text-base font-semibold tracking-wide text-gray-900 mb-4">Studio Pekanbaru</h3>
                    <p class="text-gray-600 leading-relaxed max-w-sm">
                        Lihat dokumentasi dan inspirasi terbaru Daiku melalui kanal resmi kami.
                    </p>
                    <div class="flex items-center gap-3 mt-5">
                        <a href="https://www.instagram.com/daikuinterior/" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 items-center gap-2 rounded-full border border-gray-300 px-4 text-gray-700 hover:border-amber-400 hover:text-amber-600 transition duration-150" aria-label="Instagram Daiku Interior">
                            <i class="fab fa-instagram"></i>
                            <span class="text-sm font-medium">@daikuinterior</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <div class="relative mt-2 overflow-hidden bg-gradient-to-r from-amber-300 via-yellow-400 to-yellow-300 text-white">
            <svg class="absolute left-0 top-0 h-16 w-full text-white" viewBox="0 0 1440 96" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 24 C140 -8 260 -8 390 24 C520 56 620 56 745 26 C870 -4 980 -10 1110 20 C1240 50 1330 18 1440 8 L1440 0 L0 0 Z" fill="currentColor" opacity="0.9"/>
            </svg>
            <svg class="absolute left-0 top-1 h-20 w-full text-amber-500" viewBox="0 0 1440 110" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 52 C150 12 250 4 380 42 C520 84 610 34 740 34 C860 34 930 74 1050 44 C1160 16 1280 20 1440 34 L1440 110 L0 110 Z" fill="currentColor" opacity="0.55"/>
            </svg>
            <svg class="absolute left-0 top-6 h-16 w-full text-yellow-300" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 34 C130 60 245 42 365 24 C500 2 570 30 700 22 C845 12 910 56 1045 34 C1190 10 1298 8 1440 30 L1440 100 L0 100 Z" fill="currentColor" opacity="0.85"/>
            </svg>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-8 flex items-end justify-between">
                <p class="text-sm text-white/90">&copy; {{ now()->year }} All Rights Reserved</p>
                <button type="button" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="h-9 w-9 rounded-full border border-white/90 flex items-center justify-center text-white hover:bg-white hover:text-amber-500 transition duration-200" aria-label="Kembali ke atas">
                    <i class="fas fa-chevron-up text-sm"></i>
                </button>
            </div>
        </div>
    </footer>
    
    @stack('scripts')
</body>
</html>
