@extends('layouts.dashboard')

@section('title', 'Data Pelanggan - Admin Dashboard')
@section('page-title', 'Data Pelanggan')
@section('page-description', 'Kelola informasi dan riwayat pelanggan Daiku Interior')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Total Pelanggan -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Pelanggan</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pelanggan->total() }}</p>
            </div>
        </div>
    </div>

    <!-- Pelanggan Aktif -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-user-check text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pelanggan Aktif</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pelanggan->where('pemesanans_count', '>', 0)->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Pelanggan Baru (30 hari)</p>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
                <i class="fas fa-user-plus text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pelanggan Baru</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pelanggan->where('created_at', '>=', now()->subDays(30))->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Total Proyek -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
                <i class="fas fa-project-diagram text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Proyek</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pelanggan->sum('pemesanans_count') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" placeholder="Cari pelanggan..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>
        </div>
        <div class="flex gap-3">
            <button class="daiku-yellow text-white px-4 py-2 rounded-lg daiku-yellow-hover flex items-center">
                <i class="fas fa-plus mr-2"></i>Tambah Pelanggan
            </button>
            <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 flex items-center">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
        </div>
    </div>
</div>

<!-- Pelanggan Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Daftar Pelanggan</h3>
        <p class="text-sm text-gray-500">Showing {{ $pelanggan->firstItem() }} to {{ $pelanggan->lastItem() }} of {{ $pelanggan->total() }} results</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email & Telepon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Proyek</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Belanja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($pelanggan as $customer)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ $customer->nama }}&background=fbbf24&color=fff" alt="{{ $customer->nama }}">
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $customer->nama }}</div>
                                <div class="text-sm text-gray-500">ID: {{ $customer->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $customer->email }}</div>
                        <div class="text-sm text-gray-500">{{ $customer->no_telp ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ Str::limit($customer->alamat ?? 'Alamat belum diisi', 50) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $customer->pemesanans_count }} Proyek
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        Rp {{ number_format(0, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $customer->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button class="text-yellow-600 hover:text-yellow-900">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                        <p>Belum ada data pelanggan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $pelanggan->links() }}
    </div>
</div>
@endsection
