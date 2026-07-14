<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Daiku Interior')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Head Scripts -->
    @stack('head-scripts')
    
    <!-- Custom Styles -->
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .yellow-gradient {
            background: linear-gradient(135deg, #f7b733 0%, #fc4a1a 100%);
        }
        .daiku-yellow {
            background-color: #fbbf24;
        }
        .daiku-yellow-hover:hover {
            background-color: #f59e0b;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Header Navigation -->
    <header class="fixed top-0 inset-x-0 z-50 bg-white shadow-sm">
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
                    <a href="{{ route('katalog') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('katalog*') ? 'text-yellow-600' : '' }}">Katalog</a>
                    <a href="{{ route('konsultasi.index') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('konsultasi*') ? 'text-yellow-600' : '' }}">Konsultasi</a>
                    @auth
                        <a href="{{ route('pesanan.saya') }}" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pesanan.saya') ? 'text-yellow-600' : '' }}">Pesanan Saya</a>
                    @else
                        <a href="#" onclick="showLoginAlert()" class="text-gray-700 hover:text-yellow-600 px-3 py-2 rounded-md text-sm font-medium">Pesanan Saya</a>
                    @endauth
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <img class="h-8 w-8 rounded-full bg-gray-300" src="https://ui-avatars.com/api/?name={{ auth()->user()->nama }}&background=fbbf24&color=fff" alt="{{ auth()->user()->nama }}">
                                <span class="ml-2 text-gray-700">{{ auth()->user()->nama }}</span>
                                <i class="fas fa-chevron-down ml-2 text-gray-400"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('dashboard.admin') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-2"></i>Admin Panel
                                    </a>
                                @elseif(auth()->user()->isDesigner())
                                    <a href="{{ route('dashboard.designer') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-drafting-compass mr-2"></i>Dashboard Designer
                                    </a>
                                @else
                                    <a href="{{ route('pesanan.saya') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-shopping-bag mr-2"></i>Pesanan Saya
                                    </a>
                                    <a href="{{ route('konsultasi.saya') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-comments mr-2"></i>Konsultasi Saya
                                    </a>
                                @endif
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
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
                    <button type="button" class="text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-yellow-500 p-2 rounded-md">
                        <i class="fas fa-bars"></i>
                    </button>
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
        <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <!-- Brand -->
                <div class="lg:col-span-5">
                    <a href="{{ route('home') }}" aria-label="Kembali ke beranda" class="inline-block mb-5">
                        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 w-auto">
                    </a>
                    <p class="text-gray-600 leading-relaxed max-w-md mb-6">
                        Siap mengubah ruang Anda menjadi karya desain yang nyaman dan fungsional. Tim Daiku siap bantu dari konsep sampai eksekusi.
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
                        <li><a href="{{ route('katalog') }}" class="text-gray-600 hover:text-amber-600 transition duration-150">Katalog</a></li>
                        <li><a href="{{ route('konsultasi.index') }}" class="text-gray-600 hover:text-amber-600 transition duration-150">Konsultasi</a></li>
                        <li><a href="{{ route('register') }}" class="text-gray-600 hover:text-amber-600 transition duration-150">Daftar Akun</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="lg:col-span-4">
                    <h3 class="text-base font-semibold tracking-wide text-gray-900 mb-4">Kontak</h3>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-envelope mt-1 text-amber-500"></i>
                            <span>info@daikuinterior.com</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-phone mt-1 text-amber-500"></i>
                            <span>+62 761-123456</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt mt-1 text-amber-500"></i>
                            <span>Pekanbaru, Riau</span>
                        </li>
                    </ul>
                    <div class="flex items-center gap-3 mt-5">
                        <a href="#" class="h-9 w-9 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:border-amber-400 hover:text-amber-600 transition duration-150" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="h-9 w-9 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:border-amber-400 hover:text-amber-600 transition duration-150" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="h-9 w-9 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:border-amber-400 hover:text-amber-600 transition duration-150" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
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
    
    <!-- Login Alert Modal -->
    <div id="loginModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Login Diperlukan</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Anda harus login terlebih dahulu untuk mengakses halaman "Pesanan Saya".
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="loginBtn" onclick="redirectToLogin()" 
                        class="px-4 py-2 bg-yellow-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-300 mb-2">
                        Login Sekarang
                    </button>
                    <button onclick="closeLoginModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <script>
        function showLoginAlert() {
            document.getElementById('loginModal').classList.remove('hidden');
        }
        
        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
        }
        
        function redirectToLogin() {
            window.location.href = "{{ route('login') }}";
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('loginModal');
            if (event.target === modal) {
                closeLoginModal();
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
