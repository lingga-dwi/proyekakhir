@extends('layouts.dashboard')

@section('title', 'Dashboard Admin - Daiku Interior')
@section('page-title', 'Dashboard')
@section('page-description', 'Prioritas pekerjaan dan perkembangan proyek terbaru')

@section('content')
@php
    $cards = [
        ['label' => 'Pesanan Baru', 'value' => $stats['pending_orders'], 'hint' => 'Menunggu tindak lanjut', 'icon' => 'fa-inbox', 'tone' => 'bg-orange-50 text-orange-700'],
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

<section class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="activity-title">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 id="activity-title" class="font-semibold text-slate-950">Aktivitas Proyek Terbaru</h2>
        <p class="mt-0.5 text-xs text-slate-500">Riwayat perubahan status dan progres yang benar-benar tercatat.</p>
    </div>
    <div class="grid divide-y divide-slate-100 px-5 md:grid-cols-2 md:divide-y-0 xl:grid-cols-3">
        @forelse($recentActivities as $activity)
            <a href="{{ $activity['url'] }}" class="flex gap-3 border-slate-100 py-4 transition hover:bg-slate-50 md:border-b md:px-3 xl:border-r">
                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                    <i class="fas {{ $activity['icon'] }} text-sm"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $activity['title'] }}</span>
                    <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $activity['description'] }}</span>
                    <span class="mt-1 block text-[11px] text-slate-400">{{ $activity['occurred_at']->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <div class="col-span-full py-12 text-center text-sm text-slate-500">Belum ada perubahan status proyek.</div>
        @endforelse
    </div>
</section>
@endsection
