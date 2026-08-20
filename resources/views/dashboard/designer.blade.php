@extends('layouts.dashboard')

@section('title', 'Dashboard Desainer - Daiku Interior')
@section('page-title', 'Dashboard Desainer')
@section('page-description', 'Ringkasan konsultasi dan proyek yang menjadi tanggung jawab Anda')

@section('content')
@php
    $cards = [
        ['label' => 'Ditugaskan', 'value' => $stats['assigned_projects'], 'icon' => 'fa-briefcase', 'tone' => 'bg-blue-50 text-blue-700'],
        ['label' => 'Sedang Dikerjakan', 'value' => $stats['in_progress'], 'icon' => 'fa-drafting-compass', 'tone' => 'bg-orange-50 text-orange-700'],
        ['label' => 'Selesai Bulan Ini', 'value' => $stats['completed_this_month'], 'icon' => 'fa-circle-check', 'tone' => 'bg-emerald-50 text-emerald-700'],
        ['label' => 'Menunggu Persiapan', 'value' => $stats['pending_reviews'], 'icon' => 'fa-clock', 'tone' => 'bg-purple-50 text-purple-700'],
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($cards as $card)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950">{{ $card['value'] }}</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $card['tone'] }}"><i class="fas {{ $card['icon'] }}"></i></span>
            </div>
        </article>
    @endforeach
</div>

@if($assignedConsultations->isNotEmpty())
    <section class="mt-6 overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm" aria-labelledby="consultation-assignments-title">
        <div class="border-b border-amber-100 bg-amber-50 px-5 py-4">
            <h2 id="consultation-assignments-title" class="font-semibold text-slate-950">Konsultasi Ditugaskan</h2>
            <p class="mt-0.5 text-xs text-slate-600">Hubungi pelanggan melalui WhatsApp, lalu catat hasil konsultasi agar proyek dapat masuk ke tahap desain awal dan RAB.</p>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($assignedConsultations as $consultation)
                <article class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-slate-950">{{ $consultation->nama }} · {{ $consultation->getJenisKonsultasiLabel() }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $consultation->getJenisRuanganLabel() }} · WhatsApp {{ $consultation->no_telp }}</p>
                    </div>
                    <a href="{{ route('konsultasi.show', $consultation) }}" class="inline-flex w-fit items-center gap-2 text-sm font-semibold text-amber-700 hover:text-amber-800">Buka konsultasi <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></a>
                </article>
            @endforeach
        </div>
    </section>
@endif

<section class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="activity-title">
    <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
        <div>
            <h2 id="activity-title" class="font-semibold text-slate-950">Aktivitas Proyek Terbaru</h2>
            <p class="mt-0.5 text-xs text-slate-500">Riwayat perubahan status dan progres proyek yang ditugaskan kepada Anda.</p>
        </div>
        <a href="{{ route('designer.projects.index') }}" class="shrink-0 text-sm font-semibold text-amber-700 hover:text-amber-800">Lihat semua proyek <i class="fas fa-arrow-right ml-1 text-xs" aria-hidden="true"></i></a>
    </div>
    <div class="divide-y divide-slate-100 px-5">
        @forelse($recentActivities as $activity)
            <a href="{{ $activity['url'] }}" class="flex items-center gap-3 px-3 py-4 transition hover:bg-slate-50">
                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                    <i class="fas {{ $activity['icon'] }} text-sm"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $activity['title'] }}</span>
                    <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $activity['description'] }}</span>
                    <span class="mt-1 block text-[11px] text-slate-400">{{ $activity['occurred_at']->diffForHumans() }}</span>
                </span>
                <i class="fas fa-chevron-right text-xs text-slate-300" aria-hidden="true"></i>
            </a>
        @empty
            <div class="py-12 text-center text-sm text-slate-500">Belum ada perubahan status proyek.</div>
        @endforelse
    </div>
</section>
@endsection
