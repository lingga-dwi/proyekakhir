@extends('layouts.dashboard')

@section('title', 'Detail Katalog - Admin Panel')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Katalog</h1>
            <p class="text-gray-600">Informasi lengkap desain interior</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.katalog.edit', $katalog) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="{{ route('admin.katalog.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $katalog->nama_desain }}</h2>
                        <p class="text-gray-600 mt-1">{{ $katalog->category ? $katalog->category->name : $katalog->kategori }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Harga Estimasi</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $katalog->getFormattedHargaAttribute() }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Deskripsi</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $katalog->deskripsi }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div>
                        <p class="text-sm text-gray-500">Style Tags</p>
                        <p class="text-gray-700">{{ $katalog->style_tags ?: 'Belum ada' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ukuran Ruangan</p>
                        <p class="text-gray-700">{{ $katalog->room_size ? $katalog->room_size . ' m²' : 'Belum ada' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Kategori Lama</p>
                        <p class="text-gray-700">{{ $katalog->kategori ?: 'Belum ada' }}</p>
                    </div>
                </div>

                @if($katalog->inspiration_story)
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Cerita Inspirasi</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $katalog->inspiration_story }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Gambar Utama</h3>
                @if($katalog->gambar_utama_url)
                    <img src="{{ $katalog->gambar_utama_url }}" alt="{{ $katalog->nama_desain }}" class="w-full h-48 object-cover rounded-lg border">
                @else
                    <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-3xl"></i>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Galeri</h3>
                @if($katalog->galeri_gambar_urls && count($katalog->galeri_gambar_urls) > 0)
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($katalog->galeri_gambar_urls as $image)
                            <img src="{{ $image }}" alt="{{ $katalog->nama_desain }}" class="w-full h-20 object-cover rounded-lg border">
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum ada galeri tambahan.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
