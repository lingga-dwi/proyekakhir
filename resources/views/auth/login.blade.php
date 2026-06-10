@extends('layouts.auth')

@section('title', 'Login - Daiku Interior')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-8">
    <!-- Logo -->
    <div class="text-center mb-8">
        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 mx-auto mb-4">
    </div>

    <!-- Welcome Text -->
    <div class="text-center mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Selamat Datang</h2>
        <p class="text-gray-600">Silahkan login dulu!</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   placeholder="linggarjw5@gmail.com"
                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('email') border-red-500 @enderror"
                   required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <div class="relative">
                <input type="password"
                       name="password"
                       placeholder="Password"
                       class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('password') border-red-500 @enderror"
                       required>
                <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="w-4 h-4 text-yellow-500 border-gray-300 rounded focus:ring-yellow-500">
                <span class="ml-2 text-sm text-gray-600">Remember Me</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-800">Forgot Password?</a>
        </div>

        <!-- Login Button -->
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
            Login
        </button>
    </form>

    <!-- Register Link -->
    <div class="text-center mt-6">
        <p class="text-gray-600">Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-semibold">Daftar sekarang</a></p>
    </div>
</div>

@push('scripts')
<script>
// Toggle password visibility
document.querySelector('.fa-eye').addEventListener('click', function() {
    const passwordInput = document.querySelector('input[name="password"]');
    const icon = this;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>
@endpush
@endsection
