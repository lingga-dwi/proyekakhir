@extends('layouts.main')

@section('title', 'Detail Konsultasi')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('aktivitas.saya', ['tab' => 'konsultasi']) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Aktivitas Saya
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Consultation Detail -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Konsultasi #{{ $konsultasi->id }}</h1>
                        <p class="text-blue-100">{{ $konsultasi->getJenisKonsultasiLabel() }}</p>
                    </div>
                    <div class="text-right">
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($konsultasi->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($konsultasi->status == 'confirmed') bg-blue-100 text-blue-800
                            @elseif($konsultasi->status == 'completed') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            @if($konsultasi->status == 'pending') Menunggu Konfirmasi
                            @elseif($konsultasi->status == 'confirmed') Dikonfirmasi
                            @elseif($konsultasi->status == 'completed') Selesai
                            @else Dibatalkan
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Personal Info -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Personal</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-600">Nama:</span>
                                    <p class="font-medium">{{ $konsultasi->nama }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Email:</span>
                                    <p class="font-medium">{{ $konsultasi->email }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">No. Telepon:</span>
                                    <p class="font-medium">{{ $konsultasi->no_telp }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Jadwal Konsultasi</h3>
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="font-medium">{{ $konsultasi->tanggal_konsultasi->format('d F Y') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium">{{ date('H:i', strtotime($konsultasi->waktu_konsultasi)) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        @if($konsultasi->catatan_admin)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan dari Tim</h3>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-gray-700">{{ $konsultasi->catatan_admin }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Project Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Proyek</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-600">Jenis Ruangan:</span>
                                    <p class="font-medium">{{ ucwords(str_replace('_', ' ', $konsultasi->jenis_ruangan)) }}</p>
                                </div>
                                @if($konsultasi->luas_ruangan)
                                    <div>
                                        <span class="text-sm text-gray-600">Luas Ruangan:</span>
                                        <p class="font-medium">{{ $konsultasi->luas_ruangan }} m²</p>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-sm text-gray-600">Budget Range:</span>
                                    <p class="font-medium">{{ $konsultasi->getBudgetRangeLabel() }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Timeline:</span>
                                    <p class="font-medium">{{ $konsultasi->getTimelineLabel() }}</p>
                                </div>
                                @if($konsultasi->gaya_preferensi)
                                    <div>
                                        <span class="text-sm text-gray-600">Gaya Preferensi:</span>
                                        <p class="font-medium">{{ $konsultasi->gaya_preferensi }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Deskripsi Kebutuhan</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-700 leading-relaxed">{{ $konsultasi->deskripsi_kebutuhan }}</p>
                            </div>
                        </div>

                        <!-- Photos -->
                        @if($konsultasi->upload_foto && count($konsultasi->upload_foto) > 0)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Foto Ruangan</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    @foreach($konsultasi->upload_foto as $foto)
                                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                            <img src="{{ Storage::url($foto) }}" alt="Foto ruangan" 
                                                class="w-full h-full object-cover hover:scale-105 transition duration-300 cursor-pointer"
                                                onclick="openModal('{{ Storage::url($foto) }}')">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Status Timeline -->
                @if($konsultasi->status == 'confirmed' || $konsultasi->status == 'completed')
                    <div class="mt-8 pt-8 border-t">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Konsultasi</h3>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Konsultasi Dikonfirmasi</span>
                            </div>
                            
                            @if($konsultasi->status == 'completed')
                                <div class="w-4 h-0.5 bg-green-500"></div>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span class="ml-2 text-sm font-medium text-gray-700">Konsultasi Selesai</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="mt-8 pt-8 border-t">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Butuh bantuan? <a href="#" class="text-blue-600 hover:text-blue-800">Hubungi support</a></p>
                        </div>
                        
                        @if($konsultasi->status == 'pending')
                            <div class="text-right">
                                <p class="text-sm text-gray-600 mb-2">Tim kami akan menghubungi Anda segera</p>
                                <a href="{{ route('konsultasi.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                    Book Konsultasi Lagi
                                </a>
                            </div>
                        @elseif($konsultasi->status == 'completed')
                            <a href="{{ route('konsultasi.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                Book Konsultasi Lagi
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Image Preview -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center" onclick="closeModal()">
    <div class="max-w-4xl max-h-full p-4">
        <img id="modalImage" src="" alt="Preview" class="max-w-full max-h-full object-contain">
    </div>
</div>

<script>
function openModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('imageModal').classList.add('hidden');
}
</script>
@endsection

