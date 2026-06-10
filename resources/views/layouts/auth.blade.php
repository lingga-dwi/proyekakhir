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
</head>
<body class="bg-gray-100 min-h-screen flex">
    <!-- Left Side - Image -->
    <div class="hidden lg:flex lg:w-1/2 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
        <div class="flex items-center justify-center w-full bg-black bg-opacity-40">
            <div class="text-center text-white">
                <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-8 mx-auto mb-6">
                <h1 class="text-4xl font-bold mb-4">Wujudkan Interior Impian Anda</h1>
                <p class="text-xl">Bersama Daiku Interior Pekanbaru</p>
            </div>
        </div>
    </div>
    
    <!-- Right Side - Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
        <div class="max-w-md w-full">
            @yield('content')
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('scripts')
</body>
</html>
