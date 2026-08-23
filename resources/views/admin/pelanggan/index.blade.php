@extends('layouts.dashboard')

@section('title', 'Kelola Pelanggan - Admin Dashboard')
@section('page-title', 'Kelola Pelanggan')
@section('page-description', 'Lihat informasi akun dan riwayat aktivitas pelanggan')

@section('content')
@php
    $cards = [
        ['label' => 'Total Pelanggan', 'value' => $stats['total'], 'tone' => 'bg-blue-100 text-blue-700', 'icon' => 'fa-users'],
        ['label' => 'Pelanggan Aktif', 'value' => $stats['active'], 'tone' => 'bg-green-100 text-green-700', 'icon' => 'fa-user-check'],
        ['label' => 'Pelanggan Baru', 'value' => $stats['new'], 'tone' => 'bg-amber-100 text-amber-700', 'icon' => 'fa-user-plus'],
        ['label' => 'Total Proyek', 'value' => $stats['projects'], 'tone' => 'bg-purple-100 text-purple-700', 'icon' => 'fa-project-diagram'],
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($cards as $card)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center gap-4"><span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $card['tone'] }}"><i class="fas {{ $card['icon'] }}"></i></span><div><p class="text-sm text-slate-500">{{ $card['label'] }}</p><p class="text-2xl font-bold text-slate-950">{{ $card['value'] }}</p></div></div></article>
    @endforeach
</div>

<form method="GET" class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row">
    <label class="relative flex-1"><span class="sr-only">Cari pelanggan</span><i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau telepon..." class="w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-4 focus:border-amber-500 focus:ring-amber-500"></label>
    <button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Cari</button>
    @if(request('search'))<a href="{{ route('admin.pelanggan.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Reset</a>@endif
</form>

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-slate-950">Daftar Pelanggan</h2><p class="text-xs text-slate-500">Menampilkan {{ $pelanggan->firstItem() ?? 0 }}-{{ $pelanggan->lastItem() ?? 0 }} dari {{ $pelanggan->total() }} pelanggan</p></div>
    <div class="overflow-x-auto"><table class="w-full min-w-[1080px]">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3 font-medium">Pelanggan</th><th class="px-5 py-3 font-medium">Kontak</th><th class="px-5 py-3 font-medium">Total Proyek</th><th class="px-5 py-3 font-medium">Total Belanja</th><th class="px-5 py-3 font-medium">Terdaftar</th><th class="px-5 py-3 font-medium">Aktivitas</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($pelanggan as $customer)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-sm font-bold text-amber-800">{{ strtoupper(substr($customer->nama, 0, 1)) }}</span><p class="text-sm font-semibold text-slate-900">{{ $customer->nama }}</p></div></td>
                    <td class="px-5 py-4"><p class="text-sm text-slate-700">{{ $customer->email }}</p><p class="text-xs text-slate-500">{{ $customer->no_telp ?: 'Telepon belum diisi' }}</p></td>
                    <td class="px-5 py-4"><span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $customer->pemesanans_count }} Proyek</span></td>
                    <td class="px-5 py-4 text-sm font-bold text-slate-900">Rp {{ number_format((float) $customer->pemesanans_sum_total_harga, 0, ',', '.') }}</td>
                    <td class="px-5 py-4 text-sm text-slate-600">{{ $customer->created_at->translatedFormat('d M Y') }}</td>
                    <td class="px-5 py-4"><a href="{{ route('admin.pemesanan.index', ['search' => $customer->email]) }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800">Lihat Pesanan</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-slate-500">Tidak ada pelanggan yang sesuai pencarian.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    @if($pelanggan->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $pelanggan->links() }}</div>@endif
</section>
@endsection
