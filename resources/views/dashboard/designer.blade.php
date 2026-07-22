@extends('layouts.dashboard')

@section('title', 'Dashboard Desainer - Daiku Interior')
@section('page-title', 'Proyek Saya')
@section('page-description', 'Penugasan dan progres proyek yang menjadi tanggung jawab Anda')

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

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="assigned-projects-title">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 id="assigned-projects-title" class="font-semibold text-slate-950">Daftar Penugasan</h2>
        <p class="mt-0.5 text-xs text-slate-500">Data target dan progres ditetapkan oleh admin.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Proyek</th>
                    <th class="px-5 py-3">Pelanggan</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Target</th>
                    <th class="px-5 py-3">Progres</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($my_projects as $project)
                    @php
                        [$statusLabel, $statusClass] = match($project->status_pemesanan) {
                            'pending' => ['Pesanan baru', 'bg-orange-50 text-orange-700'],
                            'dikonfirmasi' => ['Persiapan', 'bg-blue-50 text-blue-700'],
                            'sedang_dikerjakan' => ['Dikerjakan', 'bg-purple-50 text-purple-700'],
                            'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700'],
                            'dibatalkan' => ['Dibatalkan', 'bg-red-50 text-red-700'],
                            default => [ucfirst($project->status_pemesanan), 'bg-slate-100 text-slate-600'],
                        };
                    @endphp
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-5 py-4">
                            <p class="text-sm font-semibold text-slate-950">{{ $project->jenis_proyek ?: 'Proyek Interior' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">PRJ-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $project->user?->nama ?? 'Pelanggan tidak tersedia' }}</td>
                        <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $project->target_selesai?->format('d M Y') ?? 'Belum ditentukan' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-blue-500" style="width: {{ $project->progress }}%"></div></div>
                                <span class="text-xs text-slate-500">{{ $project->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('pemesanan.show', $project) }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">Belum ada proyek yang ditugaskan kepada Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
