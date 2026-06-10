@extends('layouts.main')

@section('title', 'Katalog Desain - Daiku Interior')

@section('content')
<!-- Header Section -->
<section class="bg-gradient-to-br from-blue-600 to-purple-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Katalog Desain Interior</h1>
            <p class="text-xl md:text-2xl opacity-90">Temukan inspirasi desain terbaik untuk hunian impian Anda</p>
        </div>
    </div>
</section>

<!-- Categories Navigation -->
<section class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search Bar -->
        <div class="mb-12">
            <form action="{{ route('katalog') }}" method="GET" class="flex max-w-2xl mx-auto">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari desain interior..." 
                       class="flex-1 px-6 py-4 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg">
                <button type="submit" class="px-8 py-4 bg-blue-600 text-white border border-blue-600 rounded-r-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-search text-xl"></i>
                </button>
            </form>
        </div>

        <!-- Category Navigation -->
        @if(!request('category'))
        <div class="space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Jelajahi Berdasarkan Kategori</h2>
                <p class="text-gray-600">Pilih kategori yang sesuai dengan kebutuhan Anda</p>
            </div>
            
            <!-- Parent Categories -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($parentCategories as $parentCategory)
                    <div class="bg-gray-50 rounded-xl p-6 hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="{{ $parentCategory->icon }} text-3xl text-white"></i>
                            </div>
                            <h3 class="font-bold text-xl text-gray-800 mb-2">{{ $parentCategory->name }}</h3>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $parentCategory->description }}</p>
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $parentCategory->getAllCatalogCountAttribute() }} desain tersedia
                            </div>
                        </div>
                        
                        <!-- Sub Categories -->
                        @if($parentCategory->children->count() > 0)
                            <div class="space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700 border-b border-gray-200 pb-2">Kategori Populer:</h4>
                                @foreach($parentCategory->children->take(4) as $child)
                                    <a href="{{ route('katalog', ['category' => $child->slug]) }}" 
                                       class="flex items-center justify-between p-3 rounded-lg text-sm hover:bg-white hover:shadow-md transition duration-200 group">
                                        <div class="flex items-center">
                                            <i class="{{ $child->icon }} w-4 mr-3 text-gray-500 group-hover:text-blue-600"></i>
                                            <span class="text-gray-700 group-hover:text-gray-900 font-medium">{{ $child->name }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $child->katalogs->count() }}</span>
                                    </a>
                                @endforeach
                                
                                @if($parentCategory->children->count() > 4)
                                    <a href="{{ route('katalog', ['category' => $parentCategory->slug]) }}" 
                                       class="block p-3 text-sm text-blue-600 hover:text-blue-800 font-medium text-center hover:bg-blue-50 rounded-lg transition duration-200">
                                        + {{ $parentCategory->children->count() - 4 }} kategori lainnya
                                    </a>
                                @endif
                                
                                <a href="{{ route('katalog', ['category' => $parentCategory->slug]) }}" 
                                   class="block mt-4 w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white text-center py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition duration-200 font-medium shadow-md">
                                    Jelajahi {{ $parentCategory->name }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Breadcrumb and Filter for Category Pages -->
        @if(request('category'))
        <div class="mb-8">
            @php
                $currentCategory = $parentCategories->flatMap(function($parent) {
                    return $parent->children;
                })->firstWhere('slug', request('category')) ?? $parentCategories->firstWhere('slug', request('category'));
            @endphp
            
            @if($currentCategory)
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li><a href="{{ route('katalog') }}" class="hover:text-blue-600">Katalog</a></li>
                    @if($currentCategory->parent)
                        <li><i class="fas fa-chevron-right mx-2"></i></li>
                        <li><a href="{{ route('katalog', ['category' => $currentCategory->parent->slug]) }}" class="hover:text-blue-600">{{ $currentCategory->parent->name }}</a></li>
                    @endif
                    <li><i class="fas fa-chevron-right mx-2"></i></li>
                    <li class="text-gray-800 font-medium">{{ $currentCategory->name }}</li>
                </ol>
            </nav>

            <!-- Category Header -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-8 mb-8">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-6">
                        <i class="{{ $currentCategory->icon }} text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $currentCategory->name }}</h1>
                        <p class="text-gray-600">{{ $currentCategory->description }}</p>
                        <p class="text-sm text-blue-600 mt-2 font-medium">{{ $katalogs->total() }} desain ditemukan</p>
                    </div>
                </div>
            </div>

            <!-- Sub Categories Navigation (if parent category) -->
            @if($currentCategory->hasChildren())
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Sub Kategori:</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($currentCategory->children as $child)
                        <a href="{{ route('katalog', ['category' => $child->slug]) }}" 
                           class="p-4 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition duration-200 text-center group">
                            <i class="{{ $child->icon }} text-2xl text-gray-400 group-hover:text-blue-600 mb-2"></i>
                            <h4 class="text-sm font-medium text-gray-700 group-hover:text-gray-900">{{ $child->name }}</h4>
                            <p class="text-xs text-gray-500 mt-1">{{ $child->katalogs->count() }} desain</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
            @endif
        </div>
        @endif
    </div>
</section>

<!-- Katalog Grid -->
@if($katalogs->count() > 0)
<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Results Header -->
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">
                @if(request('search'))
                    Hasil Pencarian "{{ request('search') }}"
                @elseif(request('category'))
                    @php
                        $currentCategory = $parentCategories->flatMap(function($parent) {
                            return $parent->children;
                        })->firstWhere('slug', request('category')) ?? $parentCategories->firstWhere('slug', request('category'));
                    @endphp
                    {{ $currentCategory ? $currentCategory->name : 'Katalog Desain' }}
                @else
                    Semua Desain
                @endif
            </h2>
            <p class="text-gray-600">{{ $katalogs->total() }} desain ditemukan</p>
        </div>

        <!-- Katalog Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            @foreach($katalogs as $katalog)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden group hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                <div class="relative aspect-w-16 aspect-h-9 bg-gray-200">
                    @if($katalog->gambar_utama_url)
                        <img src="{{ $katalog->gambar_utama_url }}" 
                             alt="{{ $katalog->nama_desain }}" 
                             class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                        </div>
                    @endif
                    
                    <!-- Category Badge -->
                    @if($katalog->category)
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="{{ $katalog->category->icon }} mr-1"></i>
                            {{ $katalog->category->name }}
                        </span>
                    </div>
                    @endif
                    
                    <!-- Price Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="bg-white bg-opacity-90 text-gray-800 px-2 py-1 rounded-lg text-sm font-semibold">
                            {{ $katalog->getFormattedHargaAttribute() }}
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-blue-600 transition duration-200">{{ $katalog->nama_desain }}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $katalog->deskripsi }}</p>
                    
                    <!-- Tags and Room Size -->
                    <div class="flex items-center justify-between mb-4">
                        @if($katalog->room_size)
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                            <i class="fas fa-expand-arrows-alt mr-1"></i>{{ $katalog->room_size }}m²
                        </span>
                        @endif
                        
                        @if($katalog->style_tags)
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice(explode(', ', $katalog->style_tags), 0, 2) as $tag)
                            <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    
                    <a href="{{ route('katalog.detail', $katalog->id) }}" 
                       class="block w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white text-center py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition duration-200 font-medium">
                        Lihat Detail
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            {{ $katalogs->links() }}
        </div>
    </div>
</section>
@else
<!-- Empty State -->
<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-md mx-auto">
            <i class="fas fa-search text-6xl text-gray-300 mb-6"></i>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Tidak Ada Desain Ditemukan</h3>
            <p class="text-gray-600 mb-6">
                @if(request('search'))
                    Tidak ada hasil untuk pencarian "{{ request('search') }}". Coba kata kunci lain.
                @else
                    Belum ada desain untuk kategori ini. Kembali ke halaman utama untuk melihat kategori lain.
                @endif
            </p>
            <a href="{{ route('katalog') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                Jelajahi Semua Kategori
            </a>
        </div>
    </div>
</section>
@endif
@endsection
