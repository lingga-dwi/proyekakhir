@extends('layouts.main')

@section('title', 'Konsultasi Interior')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-blue-600 to-purple-700 text-white">
        <div class="container mx-auto px-4 py-16">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">Konsultasi Interior Gratis</h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">Wujudkan rumah impian dengan bantuan designer profesional</p>
                <a href="{{ route('konsultasi.create') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition duration-300 inline-block">
                    Book Konsultasi Sekarang
                </a>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class="container mx-auto px-4 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Pilihan Layanan Konsultasi</h2>
            <p class="text-gray-600 text-lg">Berbagai paket konsultasi sesuai kebutuhan Anda</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Free Consultation -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-green-200 relative">
                <div class="absolute -top-4 left-4 bg-green-500 text-white px-4 py-1 rounded-full text-sm font-semibold">
                    GRATIS
                </div>
                <div class="text-center mt-4">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Konsultasi Gratis</h3>
                    <p class="text-gray-600 mb-4">30 menit video call dengan designer untuk diskusi awal</p>
                    <ul class="text-sm text-gray-600 text-left space-y-2">
                        <li>✓ Konsep desain dasar</li>
                        <li>✓ Saran layout ruangan</li>
                        <li>✓ Estimasi budget</li>
                        <li>✓ Rekomendasi style</li>
                    </ul>
                </div>
            </div>

            <!-- Virtual Design -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition duration-300">
                <div class="text-center">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Virtual Design</h3>
                    <p class="text-gray-600 mb-4">Desain 3D + mockup digital untuk visualisasi</p>
                    <ul class="text-sm text-gray-600 text-left space-y-2">
                        <li>✓ 3D visualization</li>
                        <li>✓ Material selection</li>
                        <li>✓ Shopping list</li>
                        <li>✓ Multiple revisions</li>
                    </ul>
                </div>
            </div>

            <!-- In-Home Visit -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition duration-300">
                <div class="text-center">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Kunjungan Rumah</h3>
                    <p class="text-gray-600 mb-4">Designer datang langsung untuk survey detail</p>
                    <ul class="text-sm text-gray-600 text-left space-y-2">
                        <li>✓ Survey langsung</li>
                        <li>✓ Pengukuran akurat</li>
                        <li>✓ Analisis ruang detail</li>
                        <li>✓ Konsultasi on-site</li>
                    </ul>
                </div>
            </div>

            <!-- Chat Support -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition duration-300">
                <div class="text-center">
                    <div class="bg-yellow-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Chat Support</h3>
                    <p class="text-gray-600 mb-4">Real-time chat dengan expert untuk pertanyaan cepat</p>
                    <ul class="text-sm text-gray-600 text-left space-y-2">
                        <li>✓ Instant response</li>
                        <li>✓ File sharing</li>
                        <li>✓ Quick solutions</li>
                        <li>✓ Follow-up support</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="bg-white py-16">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Cara Kerja Konsultasi</h2>
                <p class="text-gray-600 text-lg">Proses simple untuk mendapatkan desain impian</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="bg-blue-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-xl">1</div>
                    <h3 class="font-semibold text-gray-800 mb-2">Book Konsultasi</h3>
                    <p class="text-gray-600 text-sm">Pilih jenis konsultasi dan jadwal yang sesuai</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-xl">2</div>
                    <h3 class="font-semibold text-gray-800 mb-2">Isi Form Detail</h3>
                    <p class="text-gray-600 text-sm">Ceritakan kebutuhan dan upload foto ruangan</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-xl">3</div>
                    <h3 class="font-semibold text-gray-800 mb-2">Konsultasi</h3>
                    <p class="text-gray-600 text-sm">Diskusi dengan designer sesuai jadwal</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-xl">4</div>
                    <h3 class="font-semibold text-gray-800 mb-2">Terima Hasil</h3>
                    <p class="text-gray-600 text-sm">Dapatkan rekomendasi dan rencana desain</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Siap Mulai Transformasi Ruangan?</h2>
            <p class="text-xl mb-8 opacity-90">Konsultasi gratis 30 menit dengan designer profesional</p>
            <a href="{{ route('konsultasi.create') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition duration-300 inline-block">
                Book Konsultasi Gratis
            </a>
        </div>
    </div>
</div>
@endsection

