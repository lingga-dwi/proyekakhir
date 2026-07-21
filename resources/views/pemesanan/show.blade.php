@extends('layouts.main')

@section('title', 'Detail Pesanan - Daiku Interior')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Detail Pesanan #{{ $pemesanan->id }}</h1>
                    <p class="text-gray-600">Dibuat pada {{ $pemesanan->created_at->format('d M Y H:i') }}</p>
                </div>
                <div class="text-right">
                    @switch($pemesanan->status_pemesanan)
                        @case('pending')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-2"></i>Menunggu Konfirmasi
                            </span>
                            @break
                        @case('dikonfirmasi')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-check mr-2"></i>Dikonfirmasi
                            </span>
                            @break
                        @case('sedang_dikerjakan')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                <i class="fas fa-cog mr-2"></i>Sedang Dikerjakan
                            </span>
                            @break
                        @case('selesai')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>Selesai
                            </span>
                            @break
                        @default
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($pemesanan->status_pemesanan) }}
                            </span>
                    @endswitch
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Pelanggan -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pelanggan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Nama</label>
                            <p class="text-gray-800">{{ $pemesanan->user->nama }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Email</label>
                            <p class="text-gray-800">{{ $pemesanan->user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">No. Telepon</label>
                            <p class="text-gray-800">{{ $pemesanan->user->no_telp }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Alamat</label>
                            <p class="text-gray-800">{{ $pemesanan->user->alamat }}</p>
                        </div>
                    </div>
                </div>

                <!-- Detail Proyek -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Proyek</h2>
                    @php
                        $selectedKatalog = $pemesanan->katalog ?: ($pemesanan->rfq ? $pemesanan->rfq->katalog : null);
                    @endphp
                    @if($selectedKatalog)
                    <div class="mb-4 p-4 bg-yellow-50 rounded-lg">
                        <h3 class="font-medium text-gray-800">Desain Terpilih</h3>
                        <p class="text-gray-600">{{ $selectedKatalog->nama_desain }}</p>
                        <p class="text-sm text-gray-500">{{ $selectedKatalog->category ? $selectedKatalog->category->name : $selectedKatalog->kategori }}</p>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jenis Proyek</label>
                            <p class="text-gray-800 capitalize">{{ str_replace('_', ' ', $pemesanan->jenis_proyek) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jenis Bangunan</label>
                            <p class="text-gray-800 capitalize">{{ str_replace('_', ' ', $pemesanan->jenis_bangunan) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Luas Area</label>
                            <p class="text-gray-800">{{ $pemesanan->luas_area }} m²</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jumlah Ruangan</label>
                            <p class="text-gray-800">{{ $pemesanan->jumlah_ruangan }} ruangan</p>
                        </div>
                        @if($pemesanan->total_harga > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Estimasi Biaya</label>
                            <p class="text-gray-800">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Preferensi Desain -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Preferensi Desain</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Gaya Desain</label>
                            <p class="text-gray-800 capitalize">{{ $pemesanan->gaya_desain_preferensi }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Warna Dominan</label>
                            <p class="text-gray-800">{{ $pemesanan->warna_dominan }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Deskripsi Keinginan</label>
                            <p class="text-gray-800">{{ $pemesanan->deskripsi_keinginan_desain }}</p>
                        </div>
                    </div>
                </div>

                <!-- File Upload -->
                @if($pemesanan->upload_denah_foto && count($pemesanan->upload_denah_foto) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">File Denah/Referensi</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($pemesanan->upload_denah_foto as $file)
                        <div class="border border-gray-200 rounded-lg p-4 text-center">
                            <i class="fas fa-file-image text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600">{{ basename($file) }}</p>
                            <a href="{{ Storage::url($file) }}" target="_blank" class="text-blue-600 text-sm hover:text-blue-800">
                                Lihat File
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Progress Timeline -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Progress Proyek</h2>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $pemesanan->status_pemesanan == 'pending' || $pemesanan->status_pemesanan == 'dikonfirmasi' || $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Pesanan Diterima</p>
                                <p class="text-sm text-gray-500">{{ $pemesanan->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $pemesanan->status_pemesanan == 'dikonfirmasi' || $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Konsultasi Selesai</p>
                                <p class="text-sm text-gray-500">{{ $pemesanan->status_pemesanan == 'dikonfirmasi' || $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? $pemesanan->updated_at->format('d M Y H:i') : 'Belum selesai' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Pengerjaan Dimulai</p>
                                <p class="text-sm text-gray-500">{{ $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? 'Sedang dikerjakan' : 'Menunggu konfirmasi' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $pemesanan->status_pemesanan == 'selesai' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Proyek Selesai</p>
                                <p class="text-sm text-gray-500">{{ $pemesanan->status_pemesanan == 'selesai' ? $pemesanan->updated_at->format('d M Y H:i') : 'Belum selesai' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Hubungi Kami</h2>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-yellow-500 w-5"></i>
                            <span class="ml-3 text-gray-700">Pekanbaru, Riau</span>
                        </div>
                        <a href="{{ route('konsultasi.index') }}" class="flex items-center text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-comments text-yellow-500 w-5"></i>
                            <span class="ml-3">Buka layanan konsultasi</span>
                        </a>
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                <!-- Admin Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Admin Actions</h2>
                    <form action="{{ route('admin.pemesanan.updateStatus', $pemesanan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-2">Update Status</label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500">
                                    <option value="pending" {{ $pemesanan->status_pemesanan == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="dikonfirmasi" {{ $pemesanan->status_pemesanan == 'dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
                                    <option value="sedang_dikerjakan" {{ $pemesanan->status_pemesanan == 'sedang_dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                    <option value="selesai" {{ $pemesanan->status_pemesanan == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="dibatalkan" {{ $pemesanan->status_pemesanan == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
