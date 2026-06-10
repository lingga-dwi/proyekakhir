@extends('layouts.dashboard')

@section('title', 'Status Proyek - Admin Dashboard')
@section('page-title', 'Status Proyek')
@section('page-description', 'Tracking progress dan status proyek interior')

@section('content')
@php
    $statusMeta = [
        'pending' => [
            'label' => 'Konsultasi',
            'color' => 'orange',
            'progress' => 15,
            'note' => 'Menunggu tindak lanjut awal',
        ],
        'dikonfirmasi' => [
            'label' => 'Konsultasi',
            'color' => 'orange',
            'progress' => 30,
            'note' => 'Sudah disetujui, siap masuk tahap kerja',
        ],
        'sedang_dikerjakan' => [
            'label' => 'Tahap Desain / Produksi',
            'color' => 'blue',
            'progress' => 70,
            'note' => 'Sedang dalam pengerjaan aktif',
        ],
        'selesai' => [
            'label' => 'Proyek selesai',
            'color' => 'green',
            'progress' => 100,
            'note' => 'Pekerjaan sudah dituntaskan',
        ],
        'dibatalkan' => [
            'label' => 'Persetujuan / Revisi',
            'color' => 'purple',
            'progress' => 45,
            'note' => 'Butuh keputusan atau revisi lanjutan',
        ],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg">
                <i class="fas fa-comments text-orange-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Konsultasi</p>
                <p class="text-2xl font-bold text-gray-900">{{ $projectStats['konsultasi'] }}</p>
                <p class="text-xs text-gray-500">Menunggu atau baru dikonfirmasi</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-drafting-compass text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Tahap Desain / Produksi</p>
                <p class="text-2xl font-bold text-gray-900">{{ $projectStats['tahap_desain_produksi'] }}</p>
                <p class="text-xs text-gray-500">Sedang dikerjakan</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
                <i class="fas fa-user-check text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Persetujuan / Revisi</p>
                <p class="text-2xl font-bold text-gray-900">{{ $projectStats['persetujuan'] }}</p>
                <p class="text-xs text-gray-500">Butuh keputusan admin atau klien</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Proyek selesai</p>
                <p class="text-2xl font-bold text-gray-900">{{ $projectStats['selesai'] }}</p>
                <p class="text-xs text-gray-500">Pekerjaan selesai dituntaskan</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" placeholder="Cari proyek..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>
        </div>
        <div class="flex gap-3">
            <button class="daiku-yellow text-white px-4 py-2 rounded-lg daiku-yellow-hover flex items-center">
                <i class="fas fa-pen mr-2"></i>Update Progres
            </button>
            <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 flex items-center">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Semua Proyek</h3>
        <p class="text-sm text-gray-500">Showing {{ $proyek->firstItem() ?? 0 }} to {{ $proyek->lastItem() ?? 0 }} of {{ $proyek->total() }} results</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyek</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klien</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desainer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target Selesai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progres</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($proyek as $project)
                    @php
                        $meta = $statusMeta[$project->status_pemesanan] ?? [
                            'label' => ucfirst(str_replace('_', ' ', $project->status_pemesanan)),
                            'color' => 'gray',
                            'progress' => 35,
                            'note' => 'Status proyek belum dikategorikan',
                        ];
                        $projectCode = 'PRJ-' . str_pad($project->id, 4, '0', STR_PAD_LEFT);
                        $budget = $project->total_harga ? 'Rp ' . number_format((float) $project->total_harga, 0, ',', '.') : 'Belum ditentukan';
                        $designer = data_get($project, 'role_desainer', 'Belum ditetapkan');
                        $targetDate = $project->tanggal_pesan
                            ? $project->tanggal_pesan->copy()->addDays(30)->format('d M Y')
                            : $project->created_at->copy()->addDays(30)->format('d M Y');
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $project->jenis_proyek ?? 'Proyek Interior' }}</div>
                            <div class="text-sm text-gray-500">{{ $projectCode }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($project->user->nama) }}&background=fbbf24&color=fff" alt="{{ $project->user->nama }}">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $project->user->nama }}</div>
                                    <div class="text-sm text-gray-500">{{ $project->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $budget }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $designer }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $targetDate }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $meta['color'] }}-100 text-{{ $meta['color'] }}-800">
                                {{ $meta['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-{{ $meta['color'] }}-500 h-2 rounded-full" style="width: {{ $meta['progress'] }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ $meta['progress'] }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-3">
                                <button class="text-yellow-600 hover:text-yellow-900" title="Lihat Progres">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="text-blue-600 hover:text-blue-900" title="Update Progres">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-project-diagram text-4xl mb-4 text-gray-300"></i>
                            <p>Belum ada data proyek</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $proyek->links() }}
    </div>
</div>
@endsection
