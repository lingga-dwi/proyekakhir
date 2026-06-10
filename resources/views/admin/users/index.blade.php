@extends('layouts.dashboard')

@section('title', 'Manajemen User - Admin Dashboard')
@section('page-title', 'Manajemen User')
@section('page-description', 'Kelola akun pengguna dan hak akses')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Pengguna</p>
                <p class="text-3xl font-bold text-gray-800">{{ $userStats['total'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-xl text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Admin</p>
                <p class="text-3xl font-bold text-gray-800">{{ $userStats['admin'] }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-shield text-xl text-red-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Desainer</p>
                <p class="text-3xl font-bold text-gray-800">{{ $userStats['designer'] }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-pencil-ruler text-xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pelanggan</p>
                <p class="text-3xl font-bold text-gray-800">{{ $userStats['pelanggan'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-user text-xl text-green-600"></i>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Daftar Pengguna</h3>
        <p class="text-sm text-gray-500">
            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
        </p>
    </div>

    <button type="button" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200">
        <i class="fas fa-plus mr-2"></i>Tambah Pengguna
    </button>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pengguna</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    @php
                        $roleColors = [
                            'admin' => 'red',
                            'designer' => 'purple',
                            'pelanggan' => 'green',
                        ];
                        $roleLabels = [
                            'admin' => 'Admin',
                            'designer' => 'Desainer',
                            'pelanggan' => 'Pelanggan',
                        ];
                        $roleColor = $roleColors[$user->role] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img
                                    class="h-10 w-10 rounded-full"
                                    src="https://ui-avatars.com/api/?name={{ urlencode($user->nama) }}&background=fbbf24&color=fff"
                                    alt="{{ $user->nama }}"
                                >
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->nama }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $roleColor }}-100 text-{{ $roleColor }}-800">
                                {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $user->created_at ? $user->created_at->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-3">
                                <button type="button" class="text-yellow-600 hover:text-yellow-900" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="text-blue-600 hover:text-blue-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($user->role !== 'admin' || $userStats['admin'] > 1)
                                    <button type="button" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                            <p>Belum ada data pengguna</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $users->links() }}
    </div>
</div>
@endsection
