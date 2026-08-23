@extends('layouts.auth')

@section('title', 'Login - Daiku Interior')

@section('content')
<div>
    <div class="mb-10">
        <a href="{{ route('home') }}" class="inline-flex" aria-label="Daiku Interior - kembali ke beranda">
            <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-9 w-auto sm:h-10">
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Selamat Datang</h1>
        <p class="mt-3 text-sm text-slate-500 sm:text-base">Silakan login di sini.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 flex gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">
            <i class="fas fa-circle-check mt-0.5"></i>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
            <div class="relative">
                <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400" aria-hidden="true"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    autofocus
                    class="w-full rounded-xl border bg-white py-3.5 pl-11 pr-4 text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 focus:ring-amber-100 @error('email') border-red-400 focus:border-red-400 @else border-slate-300 focus:border-amber-500 @enderror"
                    required
                    aria-describedby="email-error"
                >
            </div>
            @error('email')
                <p id="email-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
            <div class="relative">
                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400" aria-hidden="true"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    class="w-full rounded-xl border bg-white py-3.5 pl-11 pr-12 text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 focus:ring-amber-100 @error('password') border-red-400 focus:border-red-400 @else border-slate-300 focus:border-amber-500 @enderror"
                    required
                >
                <button type="button" id="togglePassword" class="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tampilkan password" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-amber-700 transition hover:text-amber-800">Lupa password?</a>
        </div>

        <button type="submit" class="group flex w-full items-center justify-center gap-3 rounded-xl bg-amber-400 px-5 py-3.5 font-semibold text-slate-950 shadow-[0_12px_28px_rgba(251,191,36,0.24)] transition hover:bg-amber-300 focus:outline-none focus:ring-4 focus:ring-amber-200">
            Masuk
            <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        Belum memiliki akun?
        <a href="{{ route('register') }}" class="font-semibold text-slate-900 underline decoration-amber-400 decoration-2 underline-offset-4 transition hover:text-amber-700">Daftar sekarang</a>
    </p>
</div>

@push('scripts')
<script>
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', () => {
    const willShow = passwordInput.type === 'password';
    passwordInput.type = willShow ? 'text' : 'password';
    togglePassword.setAttribute('aria-pressed', String(willShow));
    togglePassword.setAttribute('aria-label', willShow ? 'Sembunyikan password' : 'Tampilkan password');

    const icon = togglePassword.querySelector('i');
    icon.classList.toggle('fa-eye', ! willShow);
    icon.classList.toggle('fa-eye-slash', willShow);
});
</script>
@endpush
@endsection
