<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - Daiku Interior')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        .sidebar-item:hover {
            background-color: #fef3c7;
            border-radius: 0.5rem;
        }
        .sidebar-item.active {
            background-color: #fbbf24;
            color: white;
            border-radius: 0.5rem;
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
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b">
                <div class="flex items-center justify-center">
                    <a href="{{ route('home') }}" aria-label="Kembali ke beranda">
                        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-5 w-auto">
                    </a>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="{{ route('dashboard.admin') }}" class="sidebar-item {{ request()->routeIs('dashboard.admin') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium">
                    <i class="fas fa-th-large mr-3"></i>Dashboard
                </a>
                <a href="{{ route('admin.pemesanan.index') }}" class="sidebar-item {{ request()->routeIs('admin.pemesanan.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                    <i class="fas fa-users mr-3"></i>Kelola Pemesanan
                </a>
                <a href="{{ route('admin.katalog.index') }}" class="sidebar-item {{ request()->routeIs('admin.katalog.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                    <i class="fas fa-th-list mr-3"></i>Kelola Katalog
                </a>
                <a href="{{ route('admin.pelanggan.index') }}" class="sidebar-item {{ request()->routeIs('admin.pelanggan.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                    <i class="fas fa-database mr-3"></i>Data Pelanggan
                </a>
                <a href="{{ route('admin.proyek.index') }}" class="sidebar-item {{ request()->routeIs('admin.proyek.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                    <i class="fas fa-cog mr-3"></i>Status Proyek
                </a>
                <a href="{{ route('admin.users.index') }}" class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                    <i class="fas fa-users-cog mr-3"></i>Manajemen User
                </a>
            </nav>
            
            <!-- User Profile -->
            <div class="p-4 border-t bg-gray-50">
                <div class="flex items-center">
                    <img src="https://ui-avatars.com/api/?name={{ auth()->user()->nama }}&background=fbbf24&color=fff" 
                         alt="{{ auth()->user()->nama }}" 
                         class="w-10 h-10 rounded-full">
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-800">{{ auth()->user()->nama }}</p>
                        <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">
                        <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">@yield('page-title')</h1>
                        <p class="text-gray-600">@yield('page-description')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="relative">
                            <input type="text" placeholder="Search..." class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                        
                        <!-- Filter -->
                        <button class="flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
