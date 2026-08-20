<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    <title>@yield('title', 'Daiku Interior')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-[#f4f2ed] text-slate-950 antialiased">
    <main class="flex min-h-screen items-center justify-center p-3 sm:p-6 lg:p-10">
        <section class="grid w-full max-w-[1280px] overflow-hidden rounded-[28px] border border-black/5 bg-white shadow-[0_28px_80px_rgba(15,23,42,0.12)] lg:min-h-[760px] lg:grid-cols-[1.08fr_0.92fr]">
            <div class="relative min-h-52 overflow-hidden bg-slate-100 sm:min-h-72 lg:m-5 lg:min-h-0 lg:rounded-[22px]">
                <img
                    src="{{ asset('images/hero/daiku-home-hero.jpg') }}"
                    alt="Interior kamar bergaya modern karya Daiku Interior"
                    class="absolute inset-0 h-full w-full object-cover object-center"
                    fetchpriority="high"
                >
            </div>

            <div class="flex items-center px-6 py-9 sm:px-12 sm:py-12 lg:px-16 xl:px-20">
                <div class="mx-auto w-full max-w-[470px]">
                    @yield('content')
                </div>
            </div>
        </section>
    </main>

    @stack('scripts')
</body>
</html>
