@extends('layouts.main')

@section('title', 'Daiku Interior - Wujudkan Interior Impian Anda')

@section('content')
<!-- Success Message -->
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 mx-4">
    <div class="max-w-7xl mx-auto">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
</div>
@endif

<!-- Hero Section -->
<section class="relative min-h-screen bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-screen flex items-center">
        <div class="text-center text-white w-full">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Wujudkan Interior Impian Anda Bersama<br>
                <span class="text-yellow-500">Daiku Interior Pekanbaru</span>
            </h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
                Bergabunglah dengan ribuan pelanggan yang telah mempercayakan desain interior rumah mereka kepada kami
            </p>
            <a href="{{ route('katalog') }}" class="inline-block bg-yellow-500 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-yellow-600 transition duration-200">
                Buat Pesanan Sekarang
            </a>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">Layanan Satu Atap</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Berikut langkah-langkah pemesanan desain interior di website Daiku:
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Step 1 -->
            <div class="relative text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-map-marker-alt text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Jadwalkan Survey</h3>
                <p class="text-gray-600">Pilih dari katalog atau ajukan desain custom Anda.</p>

                <div class="hidden md:block absolute top-8 -right-8 w-40">
                    <svg viewBox="0 0 160 40" class="w-full h-10">
                        <path d="M0 20 C40 0 80 40 160 20" fill="none" stroke="#9CA3AF" stroke-width="2"/>
                    </svg>
                </div>
            </div>
            
            <!-- Step 2 -->
            <div class="relative text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-clipboard-list text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Isi Detail Pesanan</h3>
                <p class="text-gray-600">Masukkan ukuran, preferensi warna, upload denah jika ada.</p>

                <div class="hidden md:block absolute top-8 -right-8 w-40">
                    <svg viewBox="0 0 160 40" class="w-full h-10">
                        <path d="M0 20 C40 40 80 0 160 20" fill="none" stroke="#9CA3AF" stroke-width="2"/>
                    </svg>
                </div>
            </div>
            
            <!-- Step 3 -->
            <div class="text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-comments text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Konsultasi</h3>
                <p class="text-gray-600">Diskusikan ide Anda bersama desainer profesional kami.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Designs Section -->
<section class="py-16 bg-yellow-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Galeri Inspirasi Desain</h2>
            <p class="text-lg text-yellow-100 max-w-2xl mx-auto">
                Lihat beberapa gaya desain yang bisa kami wujudkan untuk Anda.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($featuredKatalogs as $katalog)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden group hover:shadow-xl transition duration-300">
                <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                    @if($katalog->gambar_utama_url)
                        <img src="{{ $katalog->gambar_utama_url }}" 
                             alt="{{ $katalog->nama_desain }}" 
                             class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" 
                             alt="{{ $katalog->nama_desain }}" 
                             class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                    @endif
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $katalog->nama_desain }}</h3>
                    <p class="text-gray-600 mb-2">{{ $katalog->category ? $katalog->category->name : $katalog->kategori }}</p>
                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($katalog->deskripsi, 100) }}</p>
                    <a href="{{ route('katalog.detail', $katalog->id) }}" class="inline-block bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition duration-200">
                        Mulai Konsultasi
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-8">
            <a href="{{ route('katalog') }}" class="inline-flex items-center text-white text-lg font-semibold hover:text-yellow-200 transition duration-200">
                Show More
                <i class="fas fa-plus ml-2 w-6 h-6 bg-white text-yellow-500 rounded-full flex items-center justify-center"></i>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gray-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="flex items-center justify-center mb-4">
            <i class="fas fa-shield-alt text-4xl text-yellow-500 mr-4"></i>
            <div>
                <h3 class="text-xl font-semibold">All your data are safe</h3>
                <p class="text-gray-300">We are using the most advanced security to provide you the best experience ever.</p>
            </div>
        </div>
    </div>
</section>
@endsection
