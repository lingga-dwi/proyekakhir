@extends('layouts.dashboard')

@section('title', 'Edit Katalog - Admin Panel')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Katalog</h1>
            <p class="text-gray-600">Edit informasi katalog desain interior</p>
        </div>
        <a href="{{ route('admin.katalog.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <form action="{{ route('admin.katalog.update', $katalog) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Desain -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Desain</label>
                        <input type="text" 
                               name="nama_desain" 
                               value="{{ old('nama_desain', $katalog->nama_desain) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('nama_desain') border-red-500 @enderror"
                               required>
                        @error('nama_desain')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <select name="category_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('category_id') border-red-500 @enderror">
                            <option value="">Pilih Kategori</option>
                            @foreach(\App\Models\Category::with('children')->parents()->active()->orderBy('sort_order')->get() as $parent)
                                <optgroup label="{{ $parent->name }}">
                                    @foreach($parent->children as $child)
                                        <option value="{{ $child->id }}" {{ old('category_id', $katalog->category_id) == $child->id ? 'selected' : '' }}>
                                            {{ $child->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kategori Lama (untuk backward compatibility) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori Lama</label>
                        <input type="text" 
                               name="kategori" 
                               value="{{ old('kategori', $katalog->kategori) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('kategori') border-red-500 @enderror">
                        @error('kategori')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Harga Estimasi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga Estimasi</label>
                        <input type="number" 
                               name="harga_estimasi" 
                               value="{{ old('harga_estimasi', $katalog->harga_estimasi) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('harga_estimasi') border-red-500 @enderror"
                               min="0"
                               step="1000">
                        @error('harga_estimasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Room Size -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ukuran Ruangan (m²)</label>
                        <input type="number" 
                               name="room_size" 
                               value="{{ old('room_size', $katalog->room_size) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('room_size') border-red-500 @enderror"
                               min="1">
                        @error('room_size')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Style Tags -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Style Tags</label>
                        <input type="text" 
                               name="style_tags" 
                               value="{{ old('style_tags', $katalog->style_tags) }}"
                               placeholder="Modern, Minimalis, Scandinavian (pisahkan dengan koma)"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('style_tags') border-red-500 @enderror">
                        @error('style_tags')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="deskripsi" 
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('deskripsi') border-red-500 @enderror"
                                  required>{{ old('deskripsi', $katalog->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Inspiration Story -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cerita Inspirasi</label>
                        <textarea name="inspiration_story" 
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('inspiration_story') border-red-500 @enderror">{{ old('inspiration_story', $katalog->inspiration_story) }}</textarea>
                        @error('inspiration_story')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Current Image -->
                    @if($katalog->gambar_utama_url)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Saat Ini</label>
                        <div class="flex items-center space-x-4">
                            <img src="{{ $katalog->gambar_utama_url }}" 
                                 alt="{{ $katalog->nama_desain }}" 
                                 class="w-32 h-24 object-cover rounded-lg border">
                            <div>
                                <p class="text-sm text-gray-600">{{ basename($katalog->gambar_utama) }}</p>
                                <p class="text-xs text-gray-500">Upload gambar baru untuk mengganti</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Upload Gambar Baru -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $katalog->gambar_utama ? 'Ganti Gambar Utama' : 'Upload Gambar Utama' }}
                        </label>
                        <input type="file" 
                               name="gambar_utama" 
                               accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent @error('gambar_utama') border-red-500 @enderror">
                        <p class="text-sm text-gray-500 mt-1">Format: JPG, PNG, JPEG. Maksimal 5MB.</p>
                        @error('gambar_utama')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.katalog.index') }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-yellow-500 text-gray-900 rounded-lg hover:bg-yellow-600 transition duration-200 font-medium">
                        <i class="fas fa-save mr-2"></i>Update Katalog
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
