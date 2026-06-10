@extends('layouts.dashboard')

@section('title', 'Dashboard Designer - Daiku Interior')
@section('page-title', 'Dashboard')
@section('page-description', 'Selamat Datang Dashboard Designer')


@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Assigned Projects -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Proyek Ditugaskan</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['assigned_projects'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-tasks text-xl text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <!-- In Progress -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Sedang Dikerjakan</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['in_progress'] }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-cog text-xl text-orange-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Completed This Month -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Selesai Bulan Ini</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['completed_this_month'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-xl text-green-600"></i>
            </div>
        </div>
    </div>
    
    <!-- Pending Reviews -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Menunggu Review</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_reviews'] }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-eye text-xl text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- My Projects -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Proyek Saya</h3>
            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klien</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($my_projects as $project)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $project->katalog ? $project->katalog->nama_desain : ($project->rfq && $project->rfq->katalog ? $project->rfq->katalog->nama_desain : $project->jenis_proyek) }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $project->jenis_bangunan }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ $project->user->nama }}&background=fbbf24&color=fff" alt="{{ $project->user->nama }}">
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $project->user->nama }}</div>
                            </div>
                        </div>
                    </td>
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $project->tanggal_pesan->addDays(30)->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $progress = match($project->status_pemesanan) {
                                'pending' => 25,
                                'dikonfirmasi' => 50,
                                'sedang_dikerjakan' => 75,
                                'selesai' => 100,
                                default => 0
                            };
                        @endphp
                        <div class="flex items-center">
                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600">{{ $progress }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('pemesanan.show', $project->id) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="#" class="text-yellow-600 hover:text-yellow-900 ml-3">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

