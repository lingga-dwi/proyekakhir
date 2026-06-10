@extends('layouts.dashboard')

@section('title', 'Dashboard Admin - Daiku Interior')
@section('page-title', 'Dashboard')
@section('page-description', 'Selamat Datang Dashboard Information')


@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Proyek Aktif -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Proyek Aktif</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['total_projects'] }}</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>12%
                    <span class="text-gray-500">Update: July 14, 2023</span>
                </p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-project-diagram text-xl text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Revenue -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-800">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>3%
                    <span class="text-gray-500">From completed projects</span>
                </p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-xl text-orange-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Konsultasi Terjadwal -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Konsultasi Terjadwal</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['consultations'] }}</p>
                <p class="text-sm text-red-600 mt-1">
                    <i class="fas fa-arrow-down mr-1"></i>8%
                    <span class="text-gray-500">Update: July 10, 2023</span>
                </p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-check text-xl text-red-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Pelanggan Baru -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pelanggan Baru</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['new_customers'] }}</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>12%
                    <span class="text-gray-500">Update: July 10, 2023</span>
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-xl text-green-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Aktivitas Terbaru -->
<div class="bg-white rounded-lg shadow-sm mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Aktivitas Terbaru</h3>
            <span class="text-sm text-gray-500">Wednesday, 06 July 2023</span>
        </div>
    </div>
    <div class="p-6">
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-4"></div>
                    <div>
                        <p class="font-medium text-gray-800">09:00</p>
                        <p class="text-sm text-gray-600">Konsultasi Ruang - Maya Indira</p>
                        <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">On Time</span>
                    </div>
                </div>
                <span class="text-sm text-gray-500">Tim Meeting</span>
            </div>
            
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-4"></div>
                    <div>
                        <p class="font-medium text-gray-800">12:00</p>
                        <p class="text-sm text-gray-600">Review Progress Proyek Mingguan</p>
                        <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">Low</span>
                    </div>
                </div>
                <span class="text-sm text-gray-500">Quality Check</span>
            </div>
            
            <div class="flex items-center justify-between py-3">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-4"></div>
                    <div>
                        <p class="font-medium text-gray-800">01:30</p>
                        <p class="text-sm text-gray-600">Inspeksi Furniture - Budi Santoso</p>
                        <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full">Pending</span>
                    </div>
                </div>
                <span class="text-sm text-gray-500">Quality Check</span>
            </div>
        </div>
    </div>
</div>

<!-- Proyek Terbaru -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Proyek Terbaru</h3>
            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designation</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($recent_projects as $project)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ $project->user->nama }}&background=fbbf24&color=fff" alt="{{ $project->user->nama }}">
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $project->user->nama }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $project->katalog ? $project->katalog->nama_desain : $project->jenis_proyek }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($project->jenis_proyek) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $project->created_at->format('d M Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @switch($project->status_pemesanan)
                            @case('pending')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @break
                            @case('dikonfirmasi')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Dikonfirmasi</span>
                                @break
                            @case('sedang_dikerjakan')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Dalam Progress</span>
                                @break
                            @case('selesai')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                @break
                            @default
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($project->status_pemesanan) }}</span>
                        @endswitch
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('pemesanan.show', $project->id) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="#" class="text-yellow-600 hover:text-yellow-900 ml-3">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" class="text-red-600 hover:text-red-900 ml-3">
                            <i class="fas fa-trash"></i>
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
                Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">{{ $stats['total_projects'] }}</span> results
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
