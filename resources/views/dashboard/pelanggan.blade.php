@extends('layouts.dashboard')

@section('title', 'Dashboard Pelanggan - Daiku Interior')
@section('page-title', 'Dashboard')
@section('page-description', 'Selamat Datang Dashboard Information')


@section('content')
<!-- Quick Actions -->
<div class="mb-6">
    <a href="{{ route('pemesanan.create') }}" class="inline-flex items-center px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200">
        <i class="fas fa-plus mr-2"></i>Konsultasi
    </a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Pendapatan -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                <p class="text-3xl font-bold text-gray-800">Rp 485M</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="text-gray-500">All-time total pendapatan</span>
                </p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-xl text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Proyek Selesai -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Proyek Selesai</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['completed_projects'] }}</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="text-gray-500">All time proyek selesai</span>
                </p>
            </div>
            <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-xl text-pink-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Pelanggan Baru -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pelanggan Baru</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['active_projects'] }}</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="text-gray-500">this data penjualan</span>
                </p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-xl text-cyan-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Produk Terjual -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Produk Terjual</p>
                <p class="text-3xl font-bold text-gray-800">18%</p>
                <p class="text-sm text-orange-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="text-gray-500">vs last month</span>
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shopping-cart text-xl text-green-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Trend Pendapatan & Distribusi Proyek -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Trend Pendapatan -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Trend Pendapatan</h3>
            <span class="text-sm text-gray-500">Jan 2025</span>
        </div>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <p class="text-gray-500">Chart placeholder - Grafik trend pendapatan bulanan</p>
        </div>
    </div>
    
    <!-- Distribusi Proyek -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Distribusi Proyek</h3>
            <span class="text-sm text-gray-500">Berdasarkan Jenis</span>
        </div>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <p class="text-gray-500">Chart placeholder - Distribusi jenis proyek</p>
        </div>
    </div>
</div>

<!-- Laporan Detail -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Laporan Detail</h3>
            <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option>10</option>
                <option>25</option>
                <option>50</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Kontrak</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimasi Selesai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($my_orders as $order)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $order->katalog ? $order->katalog->nama_desain : ($order->rfq && $order->rfq->katalog ? $order->rfq->katalog->nama_desain : $order->jenis_proyek) }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $order->jenis_bangunan }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->user->nama }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @switch($order->status_pemesanan)
                            @case('pending')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Konsultasi</span>
                                @break
                            @case('dikonfirmasi')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Desain</span>
                                @break
                            @case('sedang_dikerjakan')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Progress</span>
                                @break
                            @case('selesai')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                @break
                            @default
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($order->status_pemesanan) }}</span>
                        @endswitch
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($order->katalog)
                            {{ $order->katalog->getFormattedHargaAttribute() }}
                        @elseif($order->rfq && $order->rfq->katalog)
                            {{ $order->rfq->katalog->getFormattedHargaAttribute() }}
                        @else
                            <span class="text-gray-400">Belum ditentukan</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $progress = match($order->status_pemesanan) {
                                'pending' => 25,
                                'dikonfirmasi' => 50,
                                'sedang_dikerjakan' => 75,
                                'selesai' => 100,
                                default => 0
                            };
                        @endphp
                        <div class="flex items-center">
                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600">{{ $progress }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->tanggal_pesan->format('d M Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->tanggal_pesan->addDays(30)->format('d M Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('pemesanan.show', $order->id) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing <span class="font-medium">1</span> to <span class="font-medium">{{ count($my_orders) }}</span> of <span class="font-medium">{{ $stats['total_projects'] }}</span> results
            </div>
            <div class="flex space-x-1">
                <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">1</button>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">2</button>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">3</button>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">4</button>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
