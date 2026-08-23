@extends('layouts.dashboard')

@section('title', 'Dashboard Admin - Daiku Interior')
@section('page-title', 'Dashboard')
@section('page-description', 'Prioritas pekerjaan dan perkembangan proyek terbaru')

@section('content')
@php
    $cards = [
        ['label' => 'Permintaan Baru', 'value' => $stats['new_requests'], 'hint' => 'Menunggu tindak lanjut', 'icon' => 'fa-inbox', 'tone' => 'bg-orange-50 text-orange-700'],
        ['label' => 'Proyek Aktif', 'value' => $stats['active_projects'], 'hint' => 'Sedang dipersiapkan atau dikerjakan', 'icon' => 'fa-drafting-compass', 'tone' => 'bg-blue-50 text-blue-700'],
        ['label' => 'Deadline ≤ 7 Hari', 'value' => $stats['deadlines_soon'], 'hint' => 'Termasuk proyek yang terlambat', 'icon' => 'fa-calendar-day', 'tone' => 'bg-red-50 text-red-700'],
        ['label' => 'Pelanggan Baru', 'value' => $stats['new_customers'], 'hint' => 'Terdaftar dalam 30 hari', 'icon' => 'fa-user-plus', 'tone' => 'bg-emerald-50 text-emerald-700'],
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($cards as $card)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-400">{{ $card['hint'] }}</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $card['tone'] }}">
                    <i class="fas {{ $card['icon'] }}"></i>
                </span>
            </div>
        </article>
    @endforeach
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2" aria-labelledby="latest-status-title">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 id="latest-status-title" class="font-semibold text-slate-950">Status Pesanan Terbaru</h2>
            <p class="mt-0.5 text-xs text-slate-500">Tahap terkini dari permintaan dan proyek yang sedang berjalan.</p>
        </div>
        <div class="space-y-3 px-5 py-4">
            @forelse($latestOrderStatuses as $order)
                <a href="{{ $order['url'] }}" class="block border-l-4 border-amber-400 bg-slate-50/70 py-2 pl-4 pr-3 transition hover:bg-amber-50">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="shrink-0 text-xs font-bold text-slate-400">{{ $order['reference'] }}</span>
                            <span class="truncate text-sm font-semibold text-slate-900">{{ $order['customer'] }}</span>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $order['tone'] }}">{{ $order['stage'] }}</span>
                    </div>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ $order['project_type'] }}</p>
                </a>
            @empty
                <div class="py-12 text-center text-sm text-slate-500">Belum ada pesanan aktif saat ini.</div>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="activity-title">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 id="activity-title" class="font-semibold text-slate-950">Riwayat Aktivitas</h2>
            <p class="mt-0.5 text-xs text-slate-500">Log kejadian terbaru di seluruh proyek.</p>
        </div>
        <div class="space-y-3 px-5 py-4">
            @forelse($recentActivities as $activity)
                <a href="{{ $activity['url'] }}" class="block border-l-4 border-blue-400 bg-slate-50/70 py-2 pl-4 pr-3 transition hover:bg-blue-50">
                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $activity['title'] }}</span>
                    <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $activity['description'] }}</span>
                    <span class="mt-1 block text-[11px] text-slate-400">{{ $activity['occurred_at']->translatedFormat('d M Y, H:i') }} WIB</span>
                </a>
            @empty
                <div class="py-12 text-center text-sm text-slate-500">Belum ada aktivitas tercatat.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
