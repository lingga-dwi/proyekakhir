@extends('layouts.auth')

@section('title', 'Daftar - Daiku Interior')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-8">
    <!-- Logo -->
    <div class="text-center mb-8">
        <img src="{{ asset('images/logo/image.png') }}" alt="Daiku Interior" class="h-6 mx-auto mb-4">
    </div>
    
    <!-- Welcome Text -->
    <div class="text-center mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Daftar Akun Baru</h2>
        <p class="text-gray-600">Bergabunglah dengan kami!</p>
    </div>
    
    <!-- Form -->
    <form method="POST" action="{{ route('register.post') }}">
        @csrf
        
        <!-- Nama -->
        <div class="mb-4">
            <input type="text" 
                   name="nama" 
                   value="{{ old('nama') }}"
                   placeholder="Nama Lengkap" 
                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('nama') border-red-500 @enderror"
                   required>
            @error('nama')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Email -->
        <div class="mb-4">
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
        
        <!-- Password -->
        <div class="mb-4">
            <div class="relative">
                <input type="password" 
                       name="password"
                       placeholder="Password" 
                       class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('password') border-red-500 @enderror"
                       required>
                <button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 toggle-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Confirm Password -->
        <div class="mb-4">
            <input type="password" 
                   name="password_confirmation"
                   placeholder="Konfirmasi Password" 
                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none"
                   required>
        </div>
        
        <!-- Alamat -->
        <div class="mb-4">
            <textarea name="alamat" 
                      placeholder="Alamat Lengkap"
                      rows="3"
                      class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('alamat') border-red-500 @enderror"
                      required>{{ old('alamat') }}</textarea>
            @error('alamat')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- No Telp -->
        <div class="mb-6">
            <input type="text" 
                   name="no_telp" 
                   value="{{ old('no_telp') }}"
                   placeholder="Nomor Telepon" 
                   class="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 focus:border-yellow-500 focus:bg-white focus:outline-none @error('no_telp') border-red-500 @enderror"
                   required>
            @error('no_telp')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Register Button -->
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
            Daftar
        </button>
    </form>
    
    <!-- Login Link -->
    <div class="text-center mt-6">
        <p class="text-gray-600">Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold">Login sekarang</a></p>
    </div>
</div>

@push('scripts')
<script>
// Toggle password visibility
document.querySelector('.toggle-password').addEventListener('click', function() {
    const passwordInput = document.querySelector('input[name="password"]');
    const icon = this.querySelector('i');
    
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
