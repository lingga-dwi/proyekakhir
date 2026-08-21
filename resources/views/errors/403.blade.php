<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Akses Ditolak - Daiku Interior</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 text-center">
    <a href="{{ route('home') }}" class="mb-8">
        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-8 w-auto">
    </a>

    <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">403</p>
    <h1 class="mt-3 text-2xl font-bold text-slate-950 sm:text-3xl">Anda tidak memiliki akses ke halaman ini</h1>
    <p class="mt-3 max-w-md text-sm leading-relaxed text-gray-600">
        Halaman yang Anda tuju khusus untuk peran tertentu. Coba kembali ke beranda atau hubungi tim Daiku bila Anda merasa ini keliru.
    </p>

    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-6 py-3 font-semibold text-slate-950 transition hover:bg-amber-300">
            Kembali ke Beranda
        </a>
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('dashboard.admin') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-3 font-semibold text-slate-700 transition hover:bg-gray-100">Buka Dashboard</a>
            @elseif(auth()->user()->isDesigner())
                <a href="{{ route('dashboard.designer') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-3 font-semibold text-slate-700 transition hover:bg-gray-100">Buka Dashboard</a>
            @elseif(auth()->user()->isPelanggan())
                <a href="{{ route('pesanan.saya') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-3 font-semibold text-slate-700 transition hover:bg-gray-100">Pesanan Saya</a>
            @endif
        @endauth
    </div>
</body>
</html>
