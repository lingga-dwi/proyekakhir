@extends('layouts.dashboard')

@section('title', 'Kelola Katalog - Admin Dashboard')
@section('page-title', 'Kelola Katalog')
@section('page-description', 'Manage portfolio and create design')


@section('content')
<!-- Actions Bar -->
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('admin.katalog.create') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200">
        <i class="fas fa-plus mr-2"></i>Tambah Katalog
    </a>
    
    <div class="flex items-center space-x-4">
        <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option>Semua Kategori</option>
            <option>Ruang Tamu</option>
            <option>Kamar Tidur</option>
            <option>Kamar Mandi</option>
        </select>
        <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option>Semua Status</option>
            <option>Aktif</option>
            <option>Non-Aktif</option>
        </select>
    </div>
</div>

<!-- Katalog Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @forelse($katalogs as $katalog)
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition duration-300">
        <div class="relative bg-gray-200">
            @if($katalog->gambar_utama_url)
                <img src="{{ $katalog->gambar_utama_url }}" 
                     alt="{{ $katalog->nama_desain }}" 
                     class="w-full h-48 object-cover">
            @else
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-image text-gray-400 text-3xl"></i>
                </div>
            @endif

            <span class="absolute top-4 left-4 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-400 text-white shadow-sm">
                {{ $katalog->category ? $katalog->category->name : $katalog->kategori }}
            </span>
        </div>

        <div class="p-5">
            <h3 class="text-2xl font-semibold text-slate-800 mb-3">{{ $katalog->nama_desain }}</h3>
            <p class="text-gray-600 text-sm leading-6 mb-5 line-clamp-3">{{ Str::limit($katalog->deskripsi, 110) }}</p>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.katalog.edit', $katalog) }}" class="w-9 h-9 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center hover:bg-orange-100 transition" title="Edit Katalog">
                        <i class="fas fa-pencil-alt text-sm"></i>
                    </a>
                    <form action="{{ route('admin.katalog.destroy', $katalog) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                onclick="return confirm('Apakah Anda yakin ingin menghapus katalog ini?')"
                                class="w-9 h-9 rounded-lg bg-red-50 text-red-400 flex items-center justify-center hover:bg-red-100 transition"
                                title="Hapus Katalog">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12">
        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum ada katalog</h3>
        <p class="text-gray-500 mb-4">Mulai tambahkan desain pertama Anda</p>
        <a href="{{ route('admin.katalog.create') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200">
            <i class="fas fa-plus mr-2"></i>Tambah Katalog
        </a>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($katalogs->hasPages())
<div class="mt-8">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">
            Showing {{ $katalogs->firstItem() }} to {{ $katalogs->lastItem() }} of {{ $katalogs->total() }} results
        </div>
        {{ $katalogs->links() }}
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush
