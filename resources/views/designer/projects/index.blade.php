@extends('layouts.dashboard')

@section('title', 'Proyek Saya - Daiku Interior')
@section('page-title', 'Proyek Saya')
@section('page-description', 'Lihat dan kelola seluruh proyek yang ditugaskan kepada Anda')

@section('content')
@php
    $statusOptions = [
        'pending' => 'Pesanan baru',
        'dikonfirmasi' => 'Persiapan',
        'sedang_dikerjakan' => 'Sedang dikerjakan',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];
@endphp

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="designer-project-list-title">
    <div class="border-b border-slate-100 p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 id="designer-project-list-title" class="text-lg font-semibold text-slate-950">Daftar Proyek</h2>
                <p class="mt-1 text-sm text-slate-500">Hanya proyek yang ditugaskan kepada akun Anda yang ditampilkan.</p>
            </div>
            <form method="GET" action="{{ route('designer.projects.index') }}" class="grid w-full gap-3 sm:grid-cols-[minmax(240px,1fr)_200px_auto] lg:max-w-3xl">
                <label class="sr-only" for="designer-project-search">Cari proyek</label>
                <div class="relative">
                    <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
                    <input id="designer-project-search" name="search" value="{{ request('search') }}" class="w-full rounded-xl border-slate-300 py-2.5 pl-11 pr-4 text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Cari ID, proyek, atau pelanggan">
                </div>
                <label class="sr-only" for="designer-project-status">Filter status</label>
                <select id="designer-project-status" name="status" class="rounded-xl border-slate-300 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Semua status</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Terapkan</button>
                    @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('designer.projects.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[850px] text-left">
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
                @forelse($projects as $project)
                    @php
                        [$statusLabel, $statusClass] = match($project->status_pemesanan) {
                            'pending' => ['Pesanan baru', 'bg-orange-50 text-orange-700'],
                            'dikonfirmasi' => ['Persiapan', 'bg-blue-50 text-blue-700'],
                            'sedang_dikerjakan' => ['Dikerjakan', 'bg-purple-50 text-purple-700'],
                            'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700'],
                            'dibatalkan' => ['Dibatalkan', 'bg-red-50 text-red-700'],
                            default => [ucfirst($project->status_pemesanan), 'bg-slate-100 text-slate-600'],
                        };
                        $canUpdate = !in_array($project->status_pemesanan, ['selesai', 'dibatalkan'], true);
                    @endphp
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-5 py-4">
                            <p class="text-xs font-semibold text-amber-700">DI-{{ str_pad($project->id, 3, '0', STR_PAD_LEFT) }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-950">{{ $project->jenis_proyek ?: 'Proyek Interior' }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $project->jenis_bangunan ?: 'Jenis bangunan belum ditentukan' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-slate-700">{{ $project->user?->nama ?? 'Pelanggan tidak tersedia' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $project->user?->email }}</p>
                        </td>
                        <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $project->target_selesai?->translatedFormat('d M Y') ?? 'Belum ditentukan' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-blue-500" style="width: {{ $project->progress }}%"></div></div>
                                <span class="text-xs font-semibold text-slate-500">{{ $project->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('pemesanan.show', $project) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kelola</a>
                                @if($canUpdate)
                                    <button type="button" onclick="document.getElementById('designer-list-project-{{ $project->id }}').showModal()" class="inline-flex h-9 items-center rounded-lg bg-slate-950 px-3 text-sm font-semibold text-white hover:bg-slate-800">Update progres</button>
                                @endif
                            </div>

                            @if($canUpdate)
                                <dialog id="designer-list-project-{{ $project->id }}" class="w-[min(92vw,32rem)] rounded-2xl p-0 shadow-2xl backdrop:bg-slate-950/50">
                                    <form action="{{ route('designer.proyek.update', $project) }}" method="POST" class="p-6 text-left">
                                        @csrf @method('PUT')
                                        <div class="flex items-start justify-between gap-4">
                                            <div><h3 class="text-lg font-semibold text-slate-950">Update Progres</h3><p class="mt-1 text-sm text-slate-500">{{ $project->jenis_proyek ?: 'Proyek Interior' }}</p></div>
                                            <button type="button" onclick="this.closest('dialog').close()" class="text-slate-400 hover:text-slate-700" aria-label="Tutup"><i class="fas fa-times"></i></button>
                                        </div>
                                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                            <label class="text-sm font-medium text-slate-700">Status
                                                <select name="status_pemesanan" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                                                    @if($project->status_pemesanan === 'pending')
                                                        <option value="dikonfirmasi">Persiapan</option>
                                                    @elseif($project->status_pemesanan === 'dikonfirmasi')
                                                        <option value="dikonfirmasi">Persiapan</option><option value="sedang_dikerjakan">Sedang dikerjakan</option>
                                                    @else
                                                        <option value="sedang_dikerjakan">Sedang dikerjakan</option><option value="selesai">Selesai</option>
                                                    @endif
                                                </select>
                                            </label>
                                            <label class="text-sm font-medium text-slate-700">Progres (%)
                                                <input type="number" name="progress" value="{{ $project->progress }}" min="0" max="100" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                                            </label>
                                            <label class="text-sm font-medium text-slate-700 sm:col-span-2">Target selesai
                                                <input type="date" name="target_selesai" value="{{ $project->target_selesai?->format('Y-m-d') }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                                            </label>
                                            <label class="text-sm font-medium text-slate-700 sm:col-span-2">Catatan progres
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
                    <tr><td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">Tidak ada proyek yang sesuai pencarian atau filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($projects->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $projects->links() }}</div>
    @endif
</section>
@endsection
