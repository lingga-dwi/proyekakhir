@extends('layouts.main')

@section('title', $katalog->nama_desain . ' - Daiku Interior')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="{{ route('home') }}" class="hover:text-yellow-600">Beranda</a></li>
                <li><i class="fas fa-chevron-right"></i></li>
                <li><a href="{{ route('katalog') }}" class="hover:text-yellow-600">Katalog</a></li>
                <li><i class="fas fa-chevron-right"></i></li>
                <li class="text-gray-800">{{ $katalog->nama_desain }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Image Gallery -->
            <div class="space-y-4">
                <!-- Main Image with Product Spots -->
                <div class="relative aspect-w-16 aspect-h-10 bg-gray-200 rounded-lg overflow-hidden">
                    @if($katalog->gambar_utama_url)
                        <img src="{{ $katalog->gambar_utama_url }}" 
                             alt="{{ $katalog->nama_desain }}" 
                             class="w-full h-96 object-cover">
                    @else
                        <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-6xl"></i>
                        </div>
                    @endif
                    
                    <!-- Product Spots -->
                    @if($katalog->product_spots && count($katalog->product_spots) > 0)
                        @foreach($katalog->product_spots as $index => $spot)
                            <div class="absolute cursor-pointer product-spot" 
                                 style="left: {{ $spot['x'] }}%; top: {{ $spot['y'] }}%;"
                                 data-product="{{ $spot['product'] }}"
                                 data-price="{{ $spot['price'] }}"
                                 data-index="{{ $index }}">
                                <!-- Spot Indicator -->
                                <div class="w-8 h-8 bg-white rounded-full shadow-lg border-2 border-blue-500 flex items-center justify-center transform -translate-x-1/2 -translate-y-1/2 hover:scale-110 transition-transform duration-200">
                                    <div class="w-4 h-4 bg-blue-500 rounded-full pulse-animation"></div>
                                </div>
                                
                                <!-- Product Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 opacity-0 hover:opacity-100 transition-opacity duration-200 z-10 product-tooltip">
                                    <div class="bg-white rounded-lg shadow-xl p-4 w-64 border">
                                        <h4 class="font-semibold text-gray-800 text-sm mb-1">{{ $spot['product'] }}</h4>
                                        <p class="text-blue-600 font-bold text-lg">{{ $spot['price'] }}</p>
                                        <div class="mt-2">
                                            <button class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                                Lihat Detail
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Arrow -->
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-l-8 border-r-8 border-t-8 border-transparent border-t-white"></div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                
                <!-- Gallery Thumbnails -->
                @if($katalog->galeri_gambar_urls && count($katalog->galeri_gambar_urls) > 0)
                <div class="grid grid-cols-4 gap-4">
                    @foreach($katalog->galeri_gambar_urls as $image)
                    <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg overflow-hidden cursor-pointer hover:opacity-75">
                        <img src="{{ $image }}" 
                             alt="{{ $katalog->nama_desain }}" 
                             class="w-full h-20 object-cover">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Product Info -->
            <div class="space-y-6">
                <!-- Category Badge -->
                <div>
                    <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                        {{ $katalog->category ? $katalog->category->name : $katalog->kategori }}
                    </span>
                </div>

                <!-- Title & Description -->
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ $katalog->nama_desain }}</h1>
                    <p class="text-gray-600 leading-relaxed">{{ $katalog->deskripsi }}</p>
                </div>

                <!-- Price -->
                <div class="border-t border-b border-gray-200 py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Estimasi Harga</p>
                            <p class="text-3xl font-bold text-yellow-600">{{ $katalog->getFormattedHargaAttribute() }}</p>
                            <p class="text-sm text-gray-500">*Harga dapat berubah sesuai spesifikasi</p>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Yang Anda Dapatkan:</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Konsultasi gratis dengan designer
                        </li>
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Desain 3D visualization
                        </li>
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Material recommendation
                        </li>
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Project management
                        </li>
                        <li class="flex items-center text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Garansi hasil kerja
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-4">
                    @auth
                        <a href="{{ route('pemesanan.create', ['katalog_id' => $katalog->id]) }}" 
                           class="w-full bg-yellow-500 text-white py-4 rounded-lg text-center font-semibold hover:bg-yellow-600 transition duration-200 block">
                            <i class="fas fa-comments mr-2"></i>Mulai Konsultasi
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="w-full bg-yellow-500 text-white py-4 rounded-lg text-center font-semibold hover:bg-yellow-600 transition duration-200 block">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login untuk Konsultasi
                        </a>
                    @endauth
                    
                    <div class="grid grid-cols-2 gap-4">
                        <button class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-heart mr-2"></i>Simpan
                        </button>
                        <button class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-share mr-2"></i>Bagikan
                        </button>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-gray-100 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Butuh Bantuan?</h3>
                    <div class="space-y-2">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-phone text-yellow-500 w-5 mr-3"></i>
                            <span>+62 761-123456</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-envelope text-yellow-500 w-5 mr-3"></i>
                            <span>info@daikuinterior.com</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-map-marker-alt text-yellow-500 w-5 mr-3"></i>
                            <span>Pekanbaru, Riau</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product List Section -->
        @if($katalog->product_spots && count($katalog->product_spots) > 0)
        <div class="mt-16 bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Produk dalam Desain Ini</h2>
            <p class="text-gray-600 mb-8">Klik pada titik di gambar untuk melihat detail produk atau lihat semua produk di bawah</p>
            
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($katalog->product_spots as $index => $spot)
                    <div class="border rounded-lg p-4 hover:shadow-md transition duration-300 product-item" data-index="{{ $index }}">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-blue-600 font-bold">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800 mb-1">{{ $spot['product'] }}</h3>
                                <p class="text-blue-600 font-bold text-lg mb-2">{{ $spot['price'] }}</p>
                                <button class="text-sm bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition duration-200">
                                    <i class="fas fa-shopping-cart mr-2"></i>Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-8 text-center">
                <button class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-list mr-2"></i>Lihat Semua Produk dalam Set
                </button>
            </div>
        </div>
        @endif

        <!-- Room Details -->
        @if($katalog->style_tags || $katalog->room_size || $katalog->inspiration_story)
        <div class="mt-16 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Detail Ruangan</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                @if($katalog->style_tags)
                <div>
                    <h3 class="font-semibold text-gray-700 mb-3">Style Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(', ', $katalog->style_tags) as $tag)
                            <span class="bg-white px-3 py-1 rounded-full text-sm text-gray-700 border">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($katalog->room_size)
                <div>
                    <h3 class="font-semibold text-gray-700 mb-3">Ukuran Ruangan</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $katalog->room_size }} m²</p>
                </div>
                @endif
                
                @if($katalog->inspiration_story)
                <div>
                    <h3 class="font-semibold text-gray-700 mb-3">Inspirasi</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $katalog->inspiration_story }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Related Designs -->
        @if($relatedKatalogs->count() > 0)
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-800 mb-8">Desain Serupa</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($relatedKatalogs as $related)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden group hover:shadow-lg transition duration-300">
                    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                        @if($related->gambar_utama_url)
                            <img src="{{ $related->gambar_utama_url }}" 
                                 alt="{{ $related->nama_desain }}" 
                                 class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-3xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $related->nama_desain }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($related->deskripsi, 100) }}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-yellow-600">{{ $related->getFormattedHargaAttribute() }}</span>
                            <a href="{{ route('katalog.detail', $related->id) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* Product Spot Animations */
.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.7;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.product-spot:hover .product-tooltip {
    opacity: 1 !important;
}

.product-spot:hover .pulse-animation {
    animation-play-state: paused;
    transform: scale(1.1);
}

/* Smooth tooltip transitions */
.product-tooltip {
    transition: opacity 0.3s ease, transform 0.3s ease;
    pointer-events: none;
}

.product-spot:hover .product-tooltip {
    pointer-events: auto;
}

/* Highlight effect for product items */
.product-item.highlighted {
    border-color: #3B82F6;
    background-color: #EFF6FF;
    transform: scale(1.02);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Product spot interactions
    const productSpots = document.querySelectorAll('.product-spot');
    const productItems = document.querySelectorAll('.product-item');
    
    // Hover effects for product spots
    productSpots.forEach(spot => {
        spot.addEventListener('mouseenter', function() {
            const index = this.getAttribute('data-index');
            highlightProductItem(index);
        });
        
        spot.addEventListener('mouseleave', function() {
            removeHighlights();
        });
        
        // Click to show detailed info
        spot.addEventListener('click', function() {
            const product = this.getAttribute('data-product');
            const price = this.getAttribute('data-price');
            showProductModal(product, price);
        });
    });
    
    // Hover effects for product items
    productItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const index = this.getAttribute('data-index');
            highlightProductSpot(index);
        });
        
        item.addEventListener('mouseleave', function() {
            removeSpotHighlights();
        });
    });
    
    function highlightProductItem(index) {
        const item = document.querySelector(`.product-item[data-index="${index}"]`);
        if (item) {
            item.classList.add('highlighted');
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
    
    function removeHighlights() {
        productItems.forEach(item => {
            item.classList.remove('highlighted');
        });
    }
    
    function highlightProductSpot(index) {
        const spot = document.querySelector(`.product-spot[data-index="${index}"]`);
        if (spot) {
            spot.style.transform = 'scale(1.2)';
            spot.style.zIndex = '1000';
        }
    }
    
    function removeSpotHighlights() {
        productSpots.forEach(spot => {
            spot.style.transform = '';
            spot.style.zIndex = '';
        });
    }
    
    function showProductModal(product, price) {
        // Simple alert for now - can be enhanced with proper modal
        alert(`${product}\n\nHarga: ${price}\n\nFitur ini dapat dikembangkan lebih lanjut untuk menampilkan detail produk lengkap.`);
    }
});
</script>
@endsection
