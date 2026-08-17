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

@if($assignedConsultations->isNotEmpty())
    <section class="mt-6 overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm" aria-labelledby="consultation-assignments-title">
        <div class="border-b border-amber-100 bg-amber-50 px-5 py-4">
            <h2 id="consultation-assignments-title" class="font-semibold text-slate-950">Jadwal Konsultasi</h2>
            <p class="mt-0.5 text-xs text-slate-600">Catat hasil konsultasi agar proyek dapat masuk ke tahap desain awal dan RAB.</p>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($assignedConsultations as $consultation)
                <article class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-slate-950">{{ $consultation->nama }} · {{ $consultation->getJenisKonsultasiLabel() }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $consultation->tanggal_konsultasi->translatedFormat('d M Y') }} pukul {{ $consultation->waktu_konsultasi->format('H:i') }} · {{ $consultation->getJenisRuanganLabel() }}</p>
                    </div>
                    <a href="{{ route('konsultasi.show', $consultation) }}" class="inline-flex w-fit items-center gap-2 text-sm font-semibold text-amber-700 hover:text-amber-800">Buka konsultasi <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></a>
                </article>
            @endforeach
        </div>
    </section>
@endif

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="assigned-projects-title">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 id="assigned-projects-title" class="font-semibold text-slate-950">Daftar Penugasan</h2>
        <p class="mt-0.5 text-xs text-slate-500">Perbarui progres proyek yang ditugaskan kepada Anda.</p>
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
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('pemesanan.show', $project) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-950">Detail</a>
                                @if(!in_array($project->status_pemesanan, ['selesai', 'dibatalkan'], true))
                                    <button type="button" onclick="document.getElementById('designer-project-{{ $project->id }}').showModal()" class="text-sm font-semibold text-amber-700 hover:text-amber-800">
                                        Update
                                    </button>
                                @endif
                            </div>
                            @if(!in_array($project->status_pemesanan, ['selesai', 'dibatalkan'], true))
                                <dialog id="designer-project-{{ $project->id }}" class="w-[min(92vw,32rem)] rounded-2xl p-0 shadow-2xl backdrop:bg-slate-950/50">
                            <form action="{{ route('designer.proyek.update', $project) }}" method="POST" class="p-6 text-left">
                                @csrf
                                @method('PUT')
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-950">Update Progres</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $project->jenis_proyek ?: 'Proyek Interior' }}</p>
                                    </div>
                                    <button type="button" onclick="this.closest('dialog').close()" class="text-slate-400 hover:text-slate-700" aria-label="Tutup">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <label class="text-sm font-medium text-slate-700">
                                        Status
                                        <select name="status_pemesanan" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                                            @if($project->status_pemesanan === 'pending')
                                                <option value="dikonfirmasi">Persiapan</option>
                                            @elseif($project->status_pemesanan === 'dikonfirmasi')
                                                <option value="dikonfirmasi">Persiapan</option>
                                                <option value="sedang_dikerjakan">Sedang dikerjakan</option>
                                            @else
                                                <option value="sedang_dikerjakan">Sedang dikerjakan</option>
                                                <option value="selesai">Selesai</option>
                                            @endif
                                        </select>
                                    </label>
                                    <label class="text-sm font-medium text-slate-700">
                                        Progres (%)
                                        <input type="number" name="progress" value="{{ $project->progress }}" min="0" max="100" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                                    </label>
                                    <label class="text-sm font-medium text-slate-700 sm:col-span-2">
                                        Target selesai
                                        <input type="date" name="target_selesai" value="{{ $project->target_selesai?->format('Y-m-d') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                                    </label>
                                    <label class="text-sm font-medium text-slate-700 sm:col-span-2">
                                        Catatan progres
                                        <textarea name="catatan_progres" rows="3" required maxlength="2000" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500" placeholder="Jelaskan pekerjaan yang sudah diselesaikan...">{{ $project->catatan_progres }}</textarea>
                                    </label>
                                </div>

                                <div class="mt-6 flex justify-end gap-3">
                                    <button type="button" onclick="this.closest('dialog').close()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                                    <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-400">Simpan Progres</button>
                                </div>
                            </form>
                                </dialog>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">Belum ada proyek yang ditugaskan kepada Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
