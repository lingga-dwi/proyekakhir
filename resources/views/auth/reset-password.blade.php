@extends('layouts.auth')

@section('title', 'Reset Password - Daiku Interior')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-8">
    <div class="text-center mb-8">
        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 mx-auto mb-4">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Reset Password</h2>
        <p class="text-gray-600 text-sm">Masukkan password baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-4">
            <input type="email"
                   name="email"
                   value="{{ old('email', $email) }}"
                   placeholder="Email"
                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('email') border-red-500 @enderror"
                   required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <input type="password"
                   name="password"
                   placeholder="Password baru"
                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('password') border-red-500 @enderror"
                   required>
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <input type="password"
                   name="password_confirmation"
                   placeholder="Konfirmasi password baru"
                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none"
                   required>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
            Simpan Password Baru
        </button>
    </form>

    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 text-sm">Kembali ke Login</a>
    </div>
</div>
@endsection
