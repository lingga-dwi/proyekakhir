@extends('layouts.main')

@section('title', 'Katalog Desain Interior - Daiku Interior')

@push('head-scripts')
<script>
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function sanitizeImageUrl(value) {
    if (!value) {
        return '';
    }

    try {
        const url = new URL(value, window.location.origin);

        if (!['http:', 'https:'].includes(url.protocol)) {
            return '';
        }

        return escapeHtml(url.toString());
    } catch (error) {
        return '';
    }
}

// Define functions in head to ensure they're available immediately
function openSidebar(katalogId) {
    console.log('Opening sidebar for ID:', katalogId);
    
    const sidebar = document.getElementById('detailSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar && overlay) {
        // Show sidebar
        sidebar.style.transform = 'translateX(0)';
        overlay.style.opacity = '1';
        overlay.style.pointerEvents = 'auto';
        
        // Load content
        loadSidebarContent(katalogId);
    } else {
        console.error('Sidebar elements not found!');
    }
}

function closeSidebar() {
    console.log('Closing sidebar');
    
    const sidebar = document.getElementById('detailSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar && overlay) {
        sidebar.style.transform = 'translateX(100%)';
        overlay.style.opacity = '0';
        overlay.style.pointerEvents = 'none';
    }
}

async function loadSidebarContent(katalogId) {
    const content = document.getElementById('sidebarContent');
    
    // Show loading
    content.innerHTML = `
        <div class="flex items-center justify-center h-64">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-600"></div>
            <span class="ml-3 text-gray-600">Memuat detail...</span>
        </div>
    `;
    
    try {
        const response = await fetch('/api/katalog/' + katalogId);
        if (!response.ok) {
            throw new Error('Gagal memuat data katalog');
        }

        const data = await response.json();
        const productSpots = Array.isArray(data.product_spots) ? data.product_spots : [];
        const productListHtml = productSpots.length
            ? productSpots.map((item) => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white rounded-md mr-3 overflow-hidden border border-gray-200 flex items-center justify-center">
                            <i class="fas fa-couch text-gray-400"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">${escapeHtml(item.product)}</h4>
                            <p class="text-xs text-gray-500">Spot: ${escapeHtml(item.x)}% , ${escapeHtml(item.y)}%</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">${escapeHtml(item.price)}</p>
                    </div>
                </div>
            `).join('')
            : '<p class="text-sm text-gray-500">Belum ada daftar produk untuk desain ini.</p>';

        const styleTags = data.style_tags
            ? data.style_tags.split(',').map(tag => tag.trim()).filter(Boolean)
            : [];
        const styleTagsHtml = styleTags.length
            ? styleTags.map(tag => `<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">${escapeHtml(tag)}</span>`).join(' ')
            : '<span class="text-sm text-gray-500">Belum tersedia</span>';
        const safeImageUrl = sanitizeImageUrl(data.gambar_utama_url);
        const safeName = escapeHtml(data.nama_desain);
        const safeCategory = escapeHtml(data.category);
        const safeDescription = escapeHtml(data.deskripsi);
        const safeFormattedPrice = escapeHtml(data.formatted_price);

        content.innerHTML = `
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Detail Desain</h2>
                <button onclick="closeSidebar()" class="text-gray-400 hover:text-gray-600 transition duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Image -->
            <div class="relative">
                ${safeImageUrl
                    ? `<img src="${safeImageUrl}" alt="${safeName}" class="w-full h-64 object-cover">`
                    : `<div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-3xl"></i>
                       </div>`
                }
                ${safeCategory
                    ? `<div class="absolute top-4 left-4">
                        <span class="bg-yellow-500 text-gray-900 px-3 py-1 rounded text-sm font-medium">${safeCategory}</span>
                       </div>`
                    : ''
                }
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                <!-- Title -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">${safeName}</h1>
                    <div class="flex flex-wrap gap-2">${styleTagsHtml}</div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Deskripsi</h3>
                    <p class="text-gray-600 leading-relaxed">${safeDescription}</p>
                </div>

                <!-- Product List -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Produk & Material</h3>
                    <div class="space-y-3">
                        ${productListHtml}
                    </div>

                    <!-- Total Price -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-900">Total Estimasi:</span>
                            <span class="font-bold text-lg text-yellow-600">${safeFormattedPrice}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">*Belum termasuk jasa instalasi dan pengiriman</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3 pt-4 border-t border-gray-200">
                    <button class="block w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200 text-center font-medium">
                        <i class="fas fa-shopping-bag mr-2"></i>Lanjut ke Keranjang Belanja
                    </button>
                    <a href="/pemesanan/create?katalog_id=` + katalogId + `" class="block w-full bg-yellow-500 text-gray-900 py-3 px-4 rounded-lg hover:bg-yellow-600 transition duration-200 text-center font-medium">
                        <i class="fas fa-clipboard-list mr-2"></i>Konsultasi & Pesan Desain
                    </a>
                    <button class="block w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition duration-200 text-center font-medium">
                        <i class="fas fa-heart mr-2"></i>Simpan ke Wishlist
                    </button>
                </div>

                <!-- Additional Products -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Pelanggan yang melihat desain ini juga melihat</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <div class="w-full h-16 bg-gray-200 rounded-md mb-2 flex items-center justify-center">
                                <i class="fas fa-bookshelf text-gray-500 text-2xl"></i>
                            </div>
                            <h4 class="text-sm font-medium text-gray-900">HEMNES</h4>
                            <p class="text-xs text-gray-600">Rak buku, putih</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">Rp 1.599.000</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <div class="w-full h-16 bg-gray-200 rounded-md mb-2 flex items-center justify-center">
                                <i class="fas fa-grip-lines text-gray-500 text-2xl"></i>
                            </div>
                            <h4 class="text-sm font-medium text-gray-900">LACK</h4>
                            <p class="text-xs text-gray-600">Rak dinding, putih</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">Rp 199.000</p>
                        </div>
                    </div>
                </div>

                <!-- Services Info -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2">Layanan Daiku Interior</h4>
                    <div class="space-y-2 text-sm text-blue-800">
                        <div class="flex items-center">
                            <i class="fas fa-truck mr-2"></i>
                            <span>Pengiriman & instalasi gratis area Jakarta</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-tools mr-2"></i>
                            <span>Garansi instalasi 1 tahun</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-headset mr-2"></i>
                            <span>Konsultasi desain gratis</span>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-yellow-50 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-900 mb-2">Butuh Bantuan?</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center text-yellow-700">
                            <i class="fab fa-whatsapp mr-2"></i>
                            <span>WhatsApp: +62 812-3456-7890</span>
                        </div>
                        <div class="flex items-center text-yellow-700">
                            <i class="fas fa-phone mr-2"></i>
                            <span>Telepon: (021) 1234-5678</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        content.innerHTML = `
                <div class="p-6">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
                        <p class="font-semibold mb-1">Gagal memuat detail desain.</p>
                    <p class="text-sm">${escapeHtml(error.message)}</p>
                </div>
                <button onclick="closeSidebar()" class="mt-4 text-sm text-gray-600 hover:text-gray-800">
                    Tutup
                </button>
            </div>
        `;
    }
}

console.log('Sidebar functions loaded in head');
</script>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-3xl font-bold text-gray-900">Katalog Desain Interior</h1>
            <p class="mt-2 text-gray-600">Temukan inspirasi desain terbaik untuk hunian impian Anda</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Search -->
                <div class="flex-1 max-w-md">
                    <form action="{{ route('katalog') }}" method="GET" class="relative">
                        <input type="hidden" name="category" value="{{ request('category') }}">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Cari desain interior..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-arrow-right text-yellow-600 hover:text-yellow-700"></i>
                        </button>
                    </form>
                </div>

                <!-- Category Filter -->
                <div class="flex gap-4">
                    <form action="{{ route('katalog') }}" method="GET" class="flex gap-4">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <select name="category" 
                                onchange="this.form.submit()"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <option value="">Semua Kategori</option>
                            @foreach($parentCategories as $parent)
                                <optgroup label="{{ $parent->name }}">
                                    @foreach($parent->children as $child)
                                        <option value="{{ $child->slug }}" {{ request('category') == $child->slug ? 'selected' : '' }}>
                                            {{ $child->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <!-- Sort -->
                        <select name="sort" 
                                onchange="this.form.submit()"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nama A-Z</option>
                            <option value="category" {{ request('sort') == 'category' ? 'selected' : '' }}>Kategori</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Katalog Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($katalogs->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($katalogs as $katalog)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                        <!-- Image -->
                        <div class="relative h-48 overflow-hidden">
                            @if($katalog->gambar_utama_url)
                                <img src="{{ $katalog->gambar_utama_url }}" 
                                     alt="{{ $katalog->nama_desain }}" 
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                                </div>
                            @endif
                            
                            <!-- Category Badge -->
                            @if($katalog->category)
                                <div class="absolute top-3 left-3">
                                    <span class="bg-yellow-500 text-gray-900 px-2 py-1 rounded text-xs font-medium">
                                        {{ $katalog->category->name }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ $katalog->nama_desain }}</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $katalog->deskripsi }}</p>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button onclick="openSidebar({{ $katalog->id }})" 
                                        class="flex-1 bg-gray-100 text-gray-700 py-2 px-3 rounded-md hover:bg-gray-200 transition duration-200 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>Lihat Detail
                                </button>
                                
                                @auth
                                    <a href="{{ route('pemesanan.create', ['katalog_id' => $katalog->id]) }}" 
                                       class="flex-1 bg-yellow-500 text-gray-900 py-2 px-3 rounded-md hover:bg-yellow-600 transition duration-200 text-sm font-medium text-center">
                                        <i class="fas fa-shopping-cart mr-1"></i>Buat Pesanan
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" 
                                       class="flex-1 bg-yellow-500 text-gray-900 py-2 px-3 rounded-md hover:bg-yellow-600 transition duration-200 text-sm font-medium text-center">
                                        <i class="fas fa-sign-in-alt mr-1"></i>Login
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $katalogs->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-search text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada desain ditemukan</h3>
                <p class="text-gray-600 mb-4">Coba ubah kata kunci pencarian atau filter kategori</p>
                <a href="{{ route('katalog') }}" class="bg-yellow-500 text-gray-900 px-6 py-2 rounded-lg hover:bg-yellow-600 transition duration-200">
                    Lihat Semua Desain
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Sidebar Detail (IKEA Style) -->
<div id="detailSidebar" class="fixed inset-y-0 right-0 w-96 bg-white shadow-2xl transform translate-x-full transition-all duration-500 ease-in-out z-50 overflow-y-auto">
    <div id="sidebarContent">
        <!-- Content will be loaded here -->
        <div class="flex items-center justify-center h-64">
            <div class="text-gray-500 text-center">
                <i class="fas fa-info-circle text-4xl mb-4"></i>
                <p>Klik "Lihat Detail" untuk melihat informasi lengkap</p>
            </div>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 opacity-0 pointer-events-none transition-opacity duration-500"></div>
@endsection

@section('scripts')
<script>
// Initialize DOM events
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, sidebar ready');
    
    // Close sidebar when clicking overlay
    const overlay = document.getElementById('sidebarOverlay');
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    
    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
});
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
