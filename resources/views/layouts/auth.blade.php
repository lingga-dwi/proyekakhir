<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Daiku Interior')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body class="bg-gray-100 min-h-screen flex">
    <!-- Left Side - Image -->
    <div class="hidden lg:flex lg:w-1/2 bg-cover bg-center" style="background-image: url('{{ asset('images/katalog/rumah/rumah (660).jpg') }}');">
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
    
    @stack('scripts')
</body>
</html>
