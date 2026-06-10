@extends('layouts.main')

@section('title', 'Pesanan Saya - Daiku Interior')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Pesanan Saya</h1>
            <p class="text-gray-600 mt-2">Kelola dan pantau progress pesanan desain interior Anda</p>
        </div>

        <!-- Quick Actions -->
        <div class="mb-6">
            <a href="{{ route('pemesanan.create') }}" class="inline-flex items-center px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200">
                <i class="fas fa-plus mr-2"></i>Buat Pesanan Baru
            </a>
        </div>

        <!-- Orders List -->
        @if($pemesanans->count() > 0)
        <div class="space-y-6">
            @foreach($pemesanans as $pemesanan)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                @if($pemesanan->katalog)
                                    {{ $pemesanan->katalog->nama_desain }}
                                @elseif($pemesanan->rfq && $pemesanan->rfq->katalog)
                                    {{ $pemesanan->rfq->katalog->nama_desain }}
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $pemesanan->jenis_proyek)) }}
                                @endif
                            </h3>
                            <p class="text-sm text-gray-500">Pesanan #{{ $pemesanan->id }} • {{ $pemesanan->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            @switch($pemesanan->status_pemesanan)
                                @case('pending')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-2"></i>Menunggu Konfirmasi
                                    </span>
                                    @break
                                @case('dikonfirmasi')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-check mr-2"></i>Dikonfirmasi
                                    </span>
                                    @break
                                @case('sedang_dikerjakan')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                        <i class="fas fa-cog mr-2"></i>Sedang Dikerjakan
                                    </span>
                                    @break
                                @case('selesai')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-2"></i>Selesai
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($pemesanan->status_pemesanan) }}
                                    </span>
                            @endswitch
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Jenis Proyek</p>
                            <p class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $pemesanan->jenis_proyek)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Jenis Bangunan</p>
                            <p class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $pemesanan->jenis_bangunan)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Luas Area</p>
                            <p class="font-medium text-gray-800">{{ $pemesanan->luas_area }} m²</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @php
                        $progress = match($pemesanan->status_pemesanan) {
                            'pending' => 25,
                            'dikonfirmasi' => 50,
                            'sedang_dikerjakan' => 75,
                            'selesai' => 100,
                            default => 0
                        };
                    @endphp
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-600">Progress</span>
                            <span class="text-sm font-medium text-gray-600">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Terakhir diupdate: {{ $pemesanan->updated_at->diffForHumans() }}
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('pemesanan.show', $pemesanan->id) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition duration-200">
                                <i class="fas fa-eye mr-2"></i>Detail
                            </a>
                            @if($pemesanan->status_pemesanan !== 'selesai')
                            <button class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition duration-200">
                                <i class="fas fa-comments mr-2"></i>Chat Designer
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($pemesanans->hasPages())
        <div class="mt-8">
            {{ $pemesanans->links() }}
        </div>
        @endif

        @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="bg-white rounded-lg shadow-sm p-12">
                <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-4">Belum Ada Pesanan</h3>
                <p class="text-gray-500 mb-8">Anda belum memiliki pesanan desain interior. Mulai konsultasi dengan designer kami sekarang!</p>
                
                <div class="space-y-4">
                    <a href="{{ route('katalog') }}" 
                       class="inline-flex items-center px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200">
                        <i class="fas fa-th-large mr-2"></i>Lihat Katalog Desain
                    </a>
                    <div class="text-center">
                        <span class="text-gray-400">atau</span>
                    </div>
                    <a href="{{ route('pemesanan.create') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Buat Pesanan Custom
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

