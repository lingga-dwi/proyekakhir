@extends('layouts.dashboard')

@section('title', 'Kelola Pemesanan - Admin Dashboard')
@section('page-title', 'Kelola Pemesanan')
@section('page-description', 'Monitor pesanan dan status pelanggan Daiku Interior')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Total Pesanan -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Pesanan</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pemesanans->total() }}</p>
            </div>
        </div>
    </div>

    <!-- Pending -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg">
                <i class="fas fa-clock text-orange-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pemesanans->where('status_pemesanan', 'pending')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Dikonfirmasi -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-check-circle text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Dikonfirmasi</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pemesanans->where('status_pemesanan', 'dikonfirmasi')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Selesai -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-check-double text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Selesai</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pemesanans->where('status_pemesanan', 'selesai')->count() }}</p>
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
                <input type="text" placeholder="Cari pesanan..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>
        </div>
        <div class="flex gap-3">
            <button class="daiku-yellow text-white px-4 py-2 rounded-lg daiku-yellow-hover flex items-center">
                <i class="fas fa-plus mr-2"></i>Tambah Pesanan
            </button>
            <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 flex items-center">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
        </div>
    </div>
</div>

<!-- Pemesanan Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Daftar Pemesanan</h3>
        <p class="text-sm text-gray-500">Showing {{ $pemesanans->firstItem() }} to {{ $pemesanans->lastItem() }} of {{ $pemesanans->total() }} results</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Ruangan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nilai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($pemesanans as $pesanan)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">DI-{{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}</div>
                        <div class="text-sm text-gray-500">{{ $pesanan->tanggal_pesan->format('d M Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ $pesanan->user->nama }}&background=fbbf24&color=fff" alt="{{ $pesanan->user->nama }}">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">{{ $pesanan->user->nama }}</div>
                                <div class="text-sm text-gray-500">{{ $pesanan->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $pesanan->jenis_proyek ?? 'Interior Design' }}</div>
                        <div class="text-sm text-gray-500">{{ $pesanan->jenis_bangunan ?? 'Residential' }} • {{ $pesanan->luas_area ?? '0' }} m²</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $pesanan->tanggal_pesan->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'pending' => 'orange',
                                'dikonfirmasi' => 'blue', 
                                'sedang_dikerjakan' => 'purple',
                                'selesai' => 'green',
                                'dibatalkan' => 'red'
                            ];
                            $color = $statusColors[$pesanan->status_pemesanan] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ ucfirst(str_replace('_', ' ', $pesanan->status_pemesanan)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        Rp {{ number_format(rand(25000000, 100000000), 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button class="text-yellow-600 hover:text-yellow-900" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="text-blue-600 hover:text-blue-900" title="Edit Status" onclick="openStatusModal({{ $pesanan->id }}, '{{ $pesanan->status_pemesanan }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-900" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        <i class="fas fa-shopping-cart text-4xl mb-4 text-gray-300"></i>
                        <p>Belum ada data pemesanan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $pemesanans->links() }}
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Update Status Pesanan</h3>
            <form id="statusForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                    <select name="status" id="statusSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="pending">Pending</option>
                        <option value="dikonfirmasi">Dikonfirmasi</option>
                        <option value="sedang_dikerjakan">Sedang Dikerjakan</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openStatusModal(id, currentStatus) {
    document.getElementById('statusModal').classList.remove('hidden');
    document.getElementById('statusForm').action = `/admin/pemesanan/${id}/status`;
    document.getElementById('statusSelect').value = currentStatus;
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});
</script>
@endsection
