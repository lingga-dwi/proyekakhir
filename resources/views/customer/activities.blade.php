@extends('layouts.main')

@section('title', 'Pesanan Saya - Daiku Interior')

@section('content')
@php
    $statusTabs = [
        'all' => 'Semua',
        'pending' => 'Menunggu Konfirmasi',
        'confirmed' => 'Dikonfirmasi',
        'in_progress' => 'Sedang Dikerjakan',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $statusStyles = [
        'pending' => ['Menunggu Konfirmasi', 'bg-amber-50 text-amber-700 ring-amber-200'],
        'confirmed' => ['Dikonfirmasi', 'bg-blue-50 text-blue-700 ring-blue-200'],
        'in_progress' => ['Sedang Dikerjakan', 'bg-violet-50 text-violet-700 ring-violet-200'],
        'completed' => ['Selesai', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'cancelled' => ['Dibatalkan', 'bg-red-50 text-red-700 ring-red-200'],
    ];
@endphp

<main class="min-h-screen bg-slate-50 py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-600">Area Pelanggan</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950 sm:text-4xl">Pesanan Saya</h1>
                <p class="mt-2 text-sm text-slate-600 sm:text-base">Pantau konsultasi dan perkembangan proyek Daiku dalam satu halaman.</p>
            </div>
            <a href="{{ route('konsultasi.create') }}" class="inline-flex w-fit items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                Buat Pesanan
            </a>
        </header>

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="daftar-pesanan-title">
            <div class="border-b border-slate-200 px-4 pt-1 sm:px-6">
                <h2 id="daftar-pesanan-title" class="sr-only">Daftar pesanan dan konsultasi</h2>
                <nav class="-mb-px flex gap-7 overflow-x-auto" aria-label="Status pesanan">
                    @foreach($statusTabs as $statusKey => $statusLabel)
                        @php
                            $tabQuery = array_filter([
                                'status' => $statusKey === 'all' ? null : $statusKey,
                                'type' => $filters['type'] === 'all' ? null : $filters['type'],
                                'sort' => $filters['sort'] === 'latest' ? null : $filters['sort'],
                                'q' => $filters['search'] ?: null,
                            ], fn ($value) => $value !== null && $value !== '');
                            $isActive = $filters['status'] === $statusKey;
                        @endphp
                        <a href="{{ route('pesanan.saya', $tabQuery) }}"
                           class="flex shrink-0 items-center gap-2 border-b-2 px-1 py-4 text-sm font-semibold transition {{ $isActive ? 'border-amber-500 text-amber-700' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}"
                           @if($isActive) aria-current="page" @endif>
                            {{ $statusLabel }}
                            <span class="rounded-full px-2 py-0.5 text-[11px] {{ $isActive ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500' }}">{{ $statusCounts[$statusKey] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <form method="GET" action="{{ route('pesanan.saya') }}" class="grid gap-3 border-b border-slate-200 bg-slate-50/70 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-[minmax(280px,1fr)_220px_180px_auto]">
                @if($filters['status'] !== 'all')
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                @endif

                <label class="relative block">
                    <span class="sr-only">Cari pesanan</span>
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400" aria-hidden="true"></i>
                    <input type="search" name="q" value="{{ $filters['search'] }}" placeholder="Cari nomor atau nama pesanan"
                           class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                </label>

                <label>
                    <span class="sr-only">Jenis aktivitas</span>
                    <select name="type" aria-label="Jenis aktivitas" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                        <option value="all" @selected($filters['type'] === 'all')>Semua jenis</option>
                        <option value="konsultasi" @selected($filters['type'] === 'konsultasi')>Konsultasi</option>
                        <option value="pemesanan" @selected($filters['type'] === 'pemesanan')>Pesanan proyek</option>
                    </select>
                </label>

                <label>
                    <span class="sr-only">Urutan pesanan</span>
                    <select name="sort" aria-label="Urutan pesanan" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                        <option value="latest" @selected($filters['sort'] === 'latest')>Terbaru</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Terlama</option>
                    </select>
                </label>

                <div class="flex gap-2">
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-slate-950 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 lg:flex-none">Terapkan</button>
                    @if($filters['status'] !== 'all' || $filters['type'] !== 'all' || $filters['sort'] !== 'latest' || $filters['search'] !== '')
                        <a href="{{ route('pesanan.saya') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">Reset</a>
                    @endif
                </div>
            </form>

            @if($activities->count())
                <div class="hidden overflow-x-auto lg:block">
                    <table class="w-full min-w-[960px] text-left">
                        <thead class="border-b border-slate-200 bg-white text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-6 py-4">Pesanan</th>
                                <th scope="col" class="px-5 py-4">Jenis</th>
                                <th scope="col" class="px-5 py-4">Status</th>
                                <th scope="col" class="px-5 py-4">Progres</th>
                                <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($activities as $activity)
                                @php
                                    [$statusLabel, $statusClass] = $statusStyles[$activity->normalized_status];
                                    $progress = max(0, min(100, (int) ($activity->progress ?? 0)));
                                @endphp
                                <tr class="group transition hover:bg-slate-50/80">
                                    <td class="px-6 py-5">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                                <span class="font-bold text-slate-700">#{{ $activity->reference }}</span>
                                                <span class="text-slate-400">{{ $activity->created_at->translatedFormat('d M Y') }}</span>
                                            </div>
                                            <p class="mt-1 font-bold text-slate-950">{{ $activity->title }}</p>
                                            <p class="mt-1 max-w-xl truncate text-sm text-slate-500">{{ $activity->meta }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-5 text-sm font-medium text-slate-700">{{ $activity->type_label }}</td>
                                    <td class="px-5 py-5">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-5 py-5">
                                        @if($activity->type === 'pemesanan')
                                            <div class="flex items-center gap-3">
                                                <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full bg-amber-400" style="width: {{ $progress }}%"></div>
                                                </div>
                                                <span class="text-xs font-semibold text-slate-600">{{ $progress }}%</span>
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-400">Tahap konsultasi</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <a href="{{ $activity->detail_url }}" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-amber-50 hover:text-amber-700">
                                            Lihat Detail
                                            <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-slate-100 lg:hidden">
                    @foreach($activities as $activity)
                        @php
                            [$statusLabel, $statusClass] = $statusStyles[$activity->normalized_status];
                            $progress = max(0, min(100, (int) ($activity->progress ?? 0)));
                        @endphp
                        <article class="p-4 sm:p-5">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="font-bold text-slate-700">#{{ $activity->reference }}</span>
                                    <span class="text-slate-400">{{ $activity->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                                <h3 class="mt-1 font-bold text-slate-950">{{ $activity->title }}</h3>
                                <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $activity->meta }}</p>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $activity->type_label }}</span>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                                <a href="{{ $activity->detail_url }}" class="inline-flex items-center gap-2 text-sm font-semibold text-amber-700">
                                    Lihat Detail
                                    <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                                </a>
                            </div>

                            @if($activity->type === 'pemesanan')
                                <div class="mt-4">
                                    <div class="mb-2 flex justify-between text-xs font-semibold text-slate-500"><span>Progres proyek</span><span>{{ $progress }}%</span></div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-amber-400" style="width: {{ $progress }}%"></div></div>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>

                @if($activities->hasPages())
                    <div class="border-t border-slate-200 px-4 py-5 sm:px-6">{{ $activities->links() }}</div>
                @endif
            @else
                <div class="px-6 py-16 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-400"><i class="fas fa-clipboard-list" aria-hidden="true"></i></span>
                    <h3 class="mt-5 text-lg font-bold text-slate-900">{{ $statusCounts['all'] > 0 ? 'Pesanan tidak ditemukan' : 'Belum ada pesanan' }}</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                        {{ $statusCounts['all'] > 0 ? 'Coba ubah kata pencarian atau filter yang digunakan.' : 'Mulailah dengan mengirim kebutuhan ruang Anda kepada tim Daiku.' }}
                    </p>
                    @if($statusCounts['all'] > 0)
                        <a href="{{ route('pesanan.saya') }}" class="mt-5 inline-flex rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset filter</a>
                    @else
                        <a href="{{ route('konsultasi.create') }}" class="mt-5 inline-flex rounded-xl bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-amber-300">Buat Pesanan</a>
                    @endif
                </div>
            @endif
        </section>
    </div>
</main>
@endsection
