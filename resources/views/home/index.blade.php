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
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-4xl md:text-5xl font-semibold text-gray-900 mb-4">Layanan Satu Atap</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Berikut langkah-langkah pemesanan desain interior di website Daiku:
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 md:gap-8">
            <!-- Step 1 -->
            <div class="relative text-center">
                <div class="relative z-10 w-24 h-24 md:w-28 md:h-28 bg-gray-100 rounded-[28px] flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-map-marker-alt text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-800 mb-6">Jadwalkan Survey</h3>
                <p class="text-gray-600 max-w-xs mx-auto leading-relaxed">Pilih dari katalog atau ajukan desain custom Anda.</p>

                <div class="hidden md:block absolute top-10 left-[63%] w-[74%] pointer-events-none">
                    <svg viewBox="0 0 360 100" class="w-full h-24">
                        <path d="M8 70 C92 94 143 18 228 22 C275 24 318 34 352 42" fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
            
            <!-- Step 2 -->
            <div class="relative text-center">
                <div class="relative z-10 w-24 h-24 md:w-28 md:h-28 bg-gray-100 rounded-[28px] flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-calendar-alt text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-800 mb-6">Isi Detail Pesanan</h3>
                <p class="text-gray-600 max-w-xs mx-auto leading-relaxed">Masukkan ukuran, preferensi warna, upload denah jika ada.</p>

                <div class="hidden md:block absolute top-10 left-[63%] w-[74%] pointer-events-none">
                    <svg viewBox="0 0 360 100" class="w-full h-24">
                        <path d="M8 70 C92 94 143 18 228 22 C275 24 318 34 352 42" fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
            
            <!-- Step 3 -->
            <div class="text-center">
                <div class="w-24 h-24 md:w-28 md:h-28 bg-gray-100 rounded-[28px] flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-comments text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-800 mb-6">Konsultasi</h3>
                <p class="text-gray-600 max-w-xs mx-auto leading-relaxed">Diskusikan ide Anda bersama desainer profesional kami.</p>
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

@endsection
