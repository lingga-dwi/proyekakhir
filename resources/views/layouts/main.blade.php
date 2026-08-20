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
    @include('partials.favicon')
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
            <div class="grid h-16 grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center">
                <!-- Logo -->
                <div class="col-start-1 row-start-1 flex items-center justify-self-start">
                    <a href="{{ route('home') }}" aria-label="Kembali ke beranda">
                        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-5 w-auto">
                    </a>
                </div>
                
                <!-- Navigation Menu -->
                <div class="col-start-2 row-start-1 hidden items-center space-x-8 lg:flex">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('home') ? 'text-yellow-600' : '' }}">Beranda</a>
                    <a href="{{ route('about') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('about') ? 'text-yellow-600' : '' }}">Tentang Kami</a>
                    <a href="{{ route('katalog') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('katalog*') ? 'text-yellow-600' : '' }}">Katalog</a>
                    <a href="{{ route('konsultasi.index') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('konsultasi.index', 'konsultasi.create') ? 'text-yellow-600' : '' }}">Konsultasi</a>
                </div>
                
                <!-- User Menu -->
                <div class="col-start-3 row-start-1 hidden items-center justify-self-end gap-2 lg:flex">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('dashboard.admin') }}"
                               class="flex h-[46px] w-40 items-center gap-2 rounded-xl border px-2 py-1.5 text-left transition duration-200 hover:border-amber-300 hover:shadow-sm {{ request()->routeIs('dashboard.admin', 'admin.*') ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-gray-200 bg-white text-slate-700' }}">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-visible rounded-lg bg-gray-100 text-gray-500" aria-hidden="true">
                                    <i class="fas fa-cog text-sm"></i>
                                </span>
                                <span class="whitespace-nowrap text-xs font-bold">Admin Panel</span>
                            </a>
                        @endif
                        @if(auth()->user()->isDesigner())
                            <a href="{{ route('dashboard.designer') }}"
                               aria-label="Buka Desainer Panel"
                               class="flex h-[46px] w-40 items-center gap-2 rounded-xl border px-2 py-1.5 text-left transition duration-200 hover:border-amber-300 hover:shadow-sm {{ request()->routeIs('dashboard.designer', 'designer.*') ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-gray-200 bg-white text-slate-700' }}">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500" aria-hidden="true">
                                    <i class="fas fa-cog text-sm"></i>
                                </span>
                                <span class="whitespace-nowrap text-xs font-bold">Desainer Panel</span>
                            </a>
                        @endif
                        @if(auth()->user()->isPelanggan())
                            <a href="{{ route('pesanan.saya') }}"
                               class="flex h-[46px] w-40 items-center gap-2 rounded-xl border px-2 py-1.5 text-left transition duration-200 hover:border-amber-300 hover:shadow-sm {{ request()->routeIs('pesanan.saya', 'pemesanan.show', 'konsultasi.show') ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-gray-200 bg-white text-slate-700' }}">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500" aria-hidden="true">
                                    <i class="fas fa-folder-open text-sm"></i>
                                </span>
                                <span class="whitespace-nowrap text-xs font-bold">Pesanan Saya</span>
                            </a>
                        @endif
                        <div class="relative w-16" x-data="{ open: false }">
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
                                    aria-label="Buka menu akun"
                                    class="group relative z-10 flex h-[46px] w-full items-center justify-between gap-1 rounded-xl border bg-white px-2 py-1.5 text-left transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-400"
                                    :class="open
                                        ? 'border-amber-300 shadow-sm'
                                        : 'border-gray-200 hover:border-amber-300 hover:shadow-sm'">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500" aria-hidden="true">
                                    <i class="fas fa-user text-sm"></i>
                                </span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-600 transition-transform duration-200" :class="open ? 'rotate-180' : ''" aria-hidden="true"></i>
                            </button>
                            
                            <div x-cloak x-show="open" x-transition.origin.top.right @click.away="open = false" role="menu" class="absolute right-0 top-full z-50 mt-2 w-64 rounded-xl border border-gray-200 bg-white p-2 shadow-xl">
                                <div class="flex items-center gap-3 border-b border-gray-100 px-3 py-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-500" aria-hidden="true">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <span class="min-w-0 leading-tight">
                                        <span class="block truncate text-sm font-bold text-slate-900">{{ auth()->user()->nama }}</span>
                                        <span class="mt-1 block text-xs font-medium text-gray-400">{{ $roleLabel }}</span>
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-gray-100 pt-1">
                                    @csrf
                                    <button type="submit" role="menuitem" class="block w-full rounded-xl px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                        @include('partials.notifications')
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="{{ route('register') }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-yellow-600">Daftar</a>
                    @endauth
                </div>
                
                <!-- Mobile menu button -->
                <div class="col-start-3 row-start-1 flex items-center gap-2 justify-self-end lg:hidden">
                    @auth
                        @include('partials.notifications')
                    @endauth
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

            <div id="mobile-navigation" x-cloak x-show="mobileOpen" x-transition class="border-t border-gray-100 py-3 lg:hidden">
                <div class="space-y-1">
                    <a href="{{ route('home') }}" @click="mobileOpen = false" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Beranda</a>
                    <a href="{{ route('about') }}" @click="mobileOpen = false" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('about') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Tentang Kami</a>
                    <a href="{{ route('katalog') }}" @click="mobileOpen = false" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('katalog*') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Katalog</a>
                    <a href="{{ route('konsultasi.index') }}" @click="mobileOpen = false" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('konsultasi.index', 'konsultasi.create') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Konsultasi</a>
                </div>

                <div class="mt-3 border-t border-gray-100 pt-3">
                    @auth
                        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ auth()->user()->nama }}</p>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('dashboard.admin') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Admin Panel</a>
                        @elseif(auth()->user()->isDesigner())
                            <a href="{{ route('dashboard.designer') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Desainer Panel</a>
                        @else
                            <a href="{{ route('pesanan.saya') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('pesanan.saya', 'pemesanan.show', 'konsultasi.show') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">Pesanan Saya</a>
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
    
    @include('partials.notification-card')

    <!-- Main Content -->
    <main class="pt-16">
        @yield('content')
    </main>
    
    <!-- Footer -->
    @php
        $footerWhatsapp = preg_replace('/\D+/', '', config('services.daiku.whatsapp_number'));
        $footerWhatsappUrl = 'https://wa.me/'.$footerWhatsapp.'?text='.rawurlencode('Halo Daiku, saya ingin berkonsultasi mengenai kebutuhan interior saya.');
    @endphp
    @if(!request()->routeIs('pesanan.saya'))
    <footer class="relative mt-32 bg-slate-900 text-white">
        @if(!request()->routeIs('konsultasi*'))
        <div class="absolute inset-x-0 -top-24 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="relative overflow-hidden rounded-md bg-[#fff8ef] px-7 py-10 text-black shadow-lg sm:px-12 lg:flex lg:items-center lg:justify-between lg:gap-10 lg:py-14" aria-labelledby="footer-cta-title">
                <div class="absolute -right-16 -top-24 h-80 w-80 rounded-full border border-amber-300/40" aria-hidden="true"></div>
                <div class="absolute right-10 top-12 h-64 w-64 rounded-full border border-amber-300/40" aria-hidden="true"></div>
                <div class="relative">
                    <h2 id="footer-cta-title" class="text-3xl font-bold tracking-tight sm:text-4xl">Butuh bantuan atau konsultasi gratis?</h2>
                    <p class="mt-4 text-base text-slate-700">Mari wujudkan interior yang fungsional dan sesuai kebutuhan ruang Anda.</p>
                </div>
                <a href="{{ route('konsultasi.index') }}" class="relative mt-7 inline-flex shrink-0 items-center justify-center rounded bg-slate-900 px-8 py-4 font-semibold text-white transition hover:bg-amber-500 hover:text-slate-950 focus:outline-none focus:ring-4 focus:ring-amber-300 lg:mt-0">
                    Hubungi Kami
                </a>
            </section>
        </div>
        @endif

        <div class="mx-auto max-w-7xl px-4 pb-8 {{ request()->routeIs('konsultasi*') ? 'pt-16 lg:pt-20' : 'pt-40 lg:pt-44' }} sm:px-6 lg:px-8">
            <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <a href="{{ route('home') }}" aria-label="Kembali ke beranda" class="inline-block">
                        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-8 w-auto brightness-0 invert">
                    </a>
                    <p class="mt-6 max-w-xs text-sm leading-relaxed text-white/75">One stop solution untuk desain interior dan furnitur custom.</p>
                    <div class="mt-7 flex gap-3">
                        <a href="https://www.instagram.com/daiku.portfolio/" target="_blank" rel="noopener noreferrer" class="inline-flex h-9 w-9 items-center justify-center rounded bg-white text-slate-900 transition hover:bg-amber-400" aria-label="Instagram Daiku"><i class="fab fa-instagram"></i></a>
                        <a href="{{ $footerWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-9 w-9 items-center justify-center rounded bg-white text-slate-900 transition hover:bg-amber-400" aria-label="WhatsApp Daiku"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <div>
                    <h3 class="text-xl font-bold">Layanan</h3>
                    <ul class="mt-6 space-y-4 text-sm text-white/80">
                        <li><a href="{{ route('konsultasi.index') }}" class="transition hover:text-amber-400">Konsultasi desain interior</a></li>
                        <li><a href="{{ route('katalog') }}" class="transition hover:text-amber-400">Furnitur custom</a></li>
                        <li><a href="{{ route('katalog') }}" class="transition hover:text-amber-400">Renovasi ruang</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xl font-bold">Navigasi</h3>
                    <ul class="mt-6 space-y-4 text-sm text-white/80">
                        <li><a href="{{ route('home') }}" class="transition hover:text-amber-400">Beranda</a></li>
                        <li><a href="{{ route('katalog') }}" class="transition hover:text-amber-400">Portofolio</a></li>
                        <li><a href="{{ route('about') }}" class="transition hover:text-amber-400">Tentang Kami</a></li>
                        <li><a href="{{ route('konsultasi.index') }}" class="transition hover:text-amber-400">Konsultasi</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xl font-bold">Kontak</h3>
                    <ul class="mt-6 space-y-4 text-sm leading-relaxed text-white/80">
                        <li class="flex gap-3"><i class="fas fa-location-dot mt-1 text-amber-400" aria-hidden="true"></i><span>Pekanbaru, Riau, Indonesia</span></li>
                        <li><a href="{{ $footerWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 transition hover:text-amber-400"><i class="fab fa-whatsapp text-amber-400" aria-hidden="true"></i><span>+{{ $footerWhatsapp }}</span></a></li>
                        <li><a href="mailto:fendrabudiono@gmail.com" class="flex items-center gap-3 transition hover:text-amber-400"><i class="fas fa-envelope text-amber-400" aria-hidden="true"></i><span>fendrabudiono@gmail.com</span></a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-16 flex flex-col gap-4 border-t border-white/25 pt-6 text-sm text-white/55 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ now()->year }} Daiku Interior. Hak cipta dilindungi.</p>
                <button type="button" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="self-start transition hover:text-amber-400 sm:self-auto">Kembali ke atas <i class="fas fa-arrow-up ml-1" aria-hidden="true"></i></button>
            </div>
        </div>
    </footer>
    @endif

    <div id="imageLightbox" onclick="if (event.target === this) closeImageLightbox()" class="fixed inset-0 z-70 hidden items-center justify-center bg-slate-950/95 p-4 sm:p-8" role="dialog" aria-modal="true" aria-labelledby="imageLightboxCaption">
        <button type="button" onclick="closeImageLightbox()" class="absolute right-4 top-4 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-2xl text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-amber-400" aria-label="Tutup gambar besar">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
        <figure onclick="event.stopPropagation()" class="flex max-h-full max-w-7xl flex-col items-center gap-4">
            <img id="imageLightboxImage" src="" alt="" class="max-h-[calc(100vh-7rem)] max-w-full object-contain" decoding="async">
            <figcaption id="imageLightboxCaption" class="text-center text-sm text-white/80"></figcaption>
        </figure>
    </div>

    <script>
        function openImageLightbox(source, alt = '') {
            const lightbox = document.getElementById('imageLightbox');
            const image = document.getElementById('imageLightboxImage');
            const caption = document.getElementById('imageLightboxCaption');
            if (!lightbox || !image || !source) return;

            image.src = source;
            image.alt = alt;
            caption.textContent = alt;
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeImageLightbox() {
            const lightbox = document.getElementById('imageLightbox');
            const image = document.getElementById('imageLightboxImage');
            const sidebar = document.getElementById('detailSidebar');
            if (!lightbox) return;

            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            if (image) image.src = '';
            if (!sidebar || sidebar.classList.contains('translate-x-full')) {
                document.body.classList.remove('overflow-hidden');
            }
        }

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeImageLightbox();
        });
    </script>
    
    @stack('scripts')
</body>
</html>
