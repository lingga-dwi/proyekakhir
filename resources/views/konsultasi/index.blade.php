@extends('layouts.main')

@section('title', 'Layanan Desain Interior - Daiku Interior')

@section('content')
<!-- Hero Section - IKEA Style -->
<section class="bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-2 min-h-screen">
            <!-- Left Content -->
            <div class="flex items-center px-8 lg:px-16 py-16">
                <div class="max-w-lg">
                    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                        Layanan desain interior
                    </h1>
                    
                    <div class="space-y-6 mb-8">
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">
                            Wujudkan interior impian Anda dengan Layanan Desain Interior Daiku
                        </h2>
                        
                        <div class="space-y-4 text-gray-700 leading-relaxed">
                            <p>
                                Dapatkan inspirasi gaya melalui konsultasi bersama ahli dan 
                                temukan pilihan warna, pencahayaan, penyimpanan, serta 
                                perabot yang pas untuk ruangan Anda.
                            </p>
                            <p>
                                Dengan pengalaman panjang di bidang perabot rumah, Daiku 
                                menghadirkan produk dan solusi yang mengutamakan gaya dan 
                                fungsi. Gunakan kesempatan ini untuk merasakan keunggulan 
                                pengetahuan Daiku yang mendalam.
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        @auth
                            <a href="{{ route('konsultasi.create') }}" 
                               class="inline-flex items-center justify-center px-8 py-4 bg-green-500 text-white font-medium rounded-full hover:bg-green-600 transition duration-200">
                                <i class="fab fa-whatsapp mr-3 text-xl"></i>
                                Konsultasi Sekarang
                            </a>
                            <a href="{{ route('pemesanan.create') }}" 
                               class="inline-flex items-center justify-center px-8 py-4 bg-yellow-500 text-gray-900 font-medium rounded-full hover:bg-yellow-600 transition duration-200">
                                Buat Pesanan
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="inline-flex items-center justify-center px-8 py-4 bg-green-500 text-white font-medium rounded-full hover:bg-green-600 transition duration-200">
                                <i class="fab fa-whatsapp mr-3 text-xl"></i>
                                Konsultasi Sekarang
                            </a>
                            <a href="{{ route('login') }}" 
                               class="inline-flex items-center justify-center px-8 py-4 bg-yellow-500 text-gray-900 font-medium rounded-full hover:bg-yellow-600 transition duration-200">
                                Buat Pesanan
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
            
            <!-- Right Image -->
            <div class="relative bg-blue-100">
                <div class="absolute inset-0 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Interior Design Consultation" 
                         class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="layanan" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Pilihan Layanan Konsultasi</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Kami menyediakan berbagai jenis konsultasi yang disesuaikan dengan kebutuhan dan budget Anda
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Konsultasi Gratis -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 hover:shadow-md transition duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-comments text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Konsultasi Gratis</h3>
                    <div class="text-3xl font-bold text-green-600 mb-4">GRATIS</div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Diskusi awal untuk memahami kebutuhan dan memberikan arahan dasar desain interior
                    </p>
                    <ul class="text-sm text-gray-600 space-y-3 mb-8 text-left">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                            <span>30 menit konsultasi online via video call</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                            <span>Tips dan saran dasar penataan ruang</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                            <span>Referensi gaya desain yang sesuai</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-3 mt-0.5"></i>
                            <span>Rekomendasi produk dasar</span>
                        </li>
                    </ul>
                    @auth
                        <a href="{{ route('konsultasi.create') }}?type=free_consultation" 
                           class="block w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-200 font-medium">
                            Pilih Layanan
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="block w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-200 font-medium">
                            Login untuk Memilih
                        </a>
                    @endauth
                </div>
            </div>
            
            <!-- Virtual Design -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 hover:shadow-md transition duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-laptop text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Virtual Design</h3>
                    <div class="text-3xl font-bold text-blue-600 mb-4">Rp 2.500.000</div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Desain 3D dan layout ruangan lengkap dengan rekomendasi produk dan warna
                    </p>
                    <ul class="text-sm text-gray-600 space-y-3 mb-8 text-left">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mr-3 mt-0.5"></i>
                            <span>Desain 3D profesional dengan rendering berkualitas tinggi</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mr-3 mt-0.5"></i>
                            <span>Shopping list produk dengan link pembelian</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mr-3 mt-0.5"></i>
                            <span>2x revisi gratis untuk penyesuaian</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-500 mr-3 mt-0.5"></i>
                            <span>Panduan implementasi step-by-step</span>
                        </li>
                    </ul>
                    @auth
                        <a href="{{ route('konsultasi.create') }}?type=virtual_design" 
                           class="block w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                            Pilih Layanan
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="block w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                            Login untuk Memilih
                        </a>
                    @endauth
                </div>
            </div>
            
            <!-- In-Home Visit -->
            <div class="bg-white rounded-lg shadow-sm border-2 border-yellow-400 p-8 hover:shadow-md transition duration-300 relative">
                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                    <span class="bg-yellow-400 text-gray-900 text-sm font-bold px-4 py-1 rounded-full">POPULER</span>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-home text-2xl text-yellow-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Kunjungan Rumah</h3>
                    <div class="text-3xl font-bold text-yellow-600 mb-4">Rp 5.000.000</div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Konsultasi langsung di lokasi dengan pengukuran dan analisis detail ruangan
                    </p>
                    <ul class="text-sm text-gray-600 space-y-3 mb-8 text-left">
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-3 mt-0.5"></i>
                            <span>Kunjungan & survei lokasi oleh desainer profesional</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-3 mt-0.5"></i>
                            <span>Konsep desain lengkap dengan mood board</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-3 mt-0.5"></i>
                            <span>Estimasi biaya detail dan timeline proyek</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-3 mt-0.5"></i>
                            <span>Follow-up support selama 1 bulan</span>
                        </li>
                    </ul>
                    @auth
                        <a href="{{ route('konsultasi.create') }}?type=in_home_visit" 
                           class="block w-full bg-yellow-500 text-gray-900 py-3 rounded-lg hover:bg-yellow-600 transition duration-200 font-medium">
                            Pilih Layanan
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="block w-full bg-yellow-500 text-gray-900 py-3 rounded-lg hover:bg-yellow-600 transition duration-200 font-medium">
                            Login untuk Memilih
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Bagaimana Cara Kerjanya?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Proses konsultasi yang mudah dan terstruktur untuk hasil yang maksimal
            </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-12">
            <div class="text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-blue-600">1</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Pilih Layanan</h3>
                <p class="text-gray-600 leading-relaxed">
                    Pilih jenis konsultasi yang sesuai dengan kebutuhan dan budget Anda. 
                    Mulai dari konsultasi gratis hingga kunjungan rumah.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-green-600">2</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Konsultasi</h3>
                <p class="text-gray-600 leading-relaxed">
                    Diskusikan impian dan kebutuhan ruangan Anda dengan desainer ahli kami. 
                    Dapatkan insight dan rekomendasi yang tepat.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-yellow-600">3</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Implementasi</h3>
                <p class="text-gray-600 leading-relaxed">
                    Dapatkan desain, shopping list, dan panduan implementasi. 
                    Tim kami siap membantu hingga ruangan impian Anda terwujud.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Preview -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Hasil Konsultasi Kami</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Lihat transformasi ruangan yang telah kami bantu wujudkan melalui layanan konsultasi
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition duration-300">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Living Room Design" 
                         class="w-full h-48 object-cover">
                    <div class="absolute top-4 left-4">
                        <span class="bg-white text-gray-900 px-3 py-1 rounded-full text-sm font-medium shadow">Ruang Keluarga</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Modern Minimalis</h3>
                    <p class="text-gray-600 text-sm mb-4">Transformasi ruang keluarga dengan konsep minimalis yang nyaman dan fungsional.</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Jakarta • 45m²
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition duration-300">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Kitchen Design" 
                         class="w-full h-48 object-cover">
                    <div class="absolute top-4 left-4">
                        <span class="bg-white text-gray-900 px-3 py-1 rounded-full text-sm font-medium shadow">Dapur</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Scandinavian Kitchen</h3>
                    <p class="text-gray-600 text-sm mb-4">Desain dapur bergaya Skandinavia yang clean, bright, dan sangat fungsional.</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Bandung • 20m²
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition duration-300">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1540932239986-30128078f3c5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Bedroom Design" 
                         class="w-full h-48 object-cover">
                    <div class="absolute top-4 left-4">
                        <span class="bg-white text-gray-900 px-3 py-1 rounded-full text-sm font-medium shadow">Kamar Tidur</span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Cozy Bedroom</h3>
                    <p class="text-gray-600 text-sm mb-4">Kamar tidur yang hangat dan nyaman dengan sentuhan modern yang elegan.</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Surabaya • 25m²
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-12">
            <a href="{{ route('katalog') }}" 
               class="inline-flex items-center px-8 py-3 bg-gray-900 text-white font-medium rounded-lg hover:bg-gray-800 transition duration-200">
                Lihat Semua Portfolio
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-blue-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold text-gray-900 mb-6">Siap Memulai Konsultasi?</h2>
        <p class="text-xl text-gray-600 mb-8">
            Konsultasi gratis tersedia! Mari wujudkan impian interior Anda bersama tim ahli desainer Daiku Interior.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @auth
                <a href="{{ route('konsultasi.create') }}" 
                   class="inline-flex items-center justify-center px-8 py-4 bg-gray-900 text-white font-medium rounded-full hover:bg-gray-800 transition duration-200">
                    <i class="fas fa-calendar-check mr-3"></i>
                    Booking Konsultasi Sekarang
                </a>
            @else
                <a href="{{ route('register') }}" 
                   class="inline-flex items-center justify-center px-8 py-4 bg-gray-900 text-white font-medium rounded-full hover:bg-gray-800 transition duration-200">
                    <i class="fas fa-user-plus mr-3"></i>
                    Daftar & Mulai Konsultasi
                </a>
            @endauth
            
            <a href="https://wa.me/6281234567890" 
               class="inline-flex items-center justify-center px-8 py-4 bg-green-500 text-white font-medium rounded-full hover:bg-green-600 transition duration-200">
                <i class="fab fa-whatsapp mr-3 text-xl"></i>
                Konsultasi Sekarang
            </a>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>
@endsection