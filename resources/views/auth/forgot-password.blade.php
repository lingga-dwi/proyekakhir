@extends('layouts.auth')

@section('title', 'Forgot Password - Daiku Interior')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-8">
    <div class="text-center mb-8">
        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 mx-auto mb-4">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Lupa Password?</h2>
        <p class="text-gray-600 text-sm">Masukkan email akun Anda, kami kirimkan link reset password.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-6">
            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   placeholder="Email"
                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('email') border-red-500 @enderror"
                   required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
            Kirim Link Reset
        </button>
    </form>

    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 text-sm">Kembali ke Login</a>
    </div>
</div>
@endsection
