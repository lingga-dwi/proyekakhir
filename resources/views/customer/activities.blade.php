@extends('layouts.main')

@section('title', 'Pesanan Saya - Daiku Interior')

@section('content')
<main class="min-h-screen bg-slate-50 py-10">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <header class="mb-8">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-600">Area pelanggan</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900 sm:text-4xl">Pesanan Saya</h1>
            <p class="mt-2 text-slate-600">Pantau permintaan konsultasi dan pesanan desain Anda dalam satu riwayat.</p>
        </header>

        <section aria-labelledby="riwayat-title">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 id="riwayat-title" class="text-xl font-bold text-slate-900">Riwayat permintaan dan pesanan</h2>
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">{{ $activities->total() }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">Status konsultasi dan perkembangan proyek ditampilkan dalam urutan terbaru.</p>
                </div>
                <a href="{{ route('konsultasi.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                    <i class="fas fa-plus" aria-hidden="true"></i> Buat Permintaan
                </a>
            </div>

            @forelse($activities as $activity)
                @php
                    $isOrder = $activity->type === 'pemesanan';
                    $record = $activity->record;

                    if ($isOrder) {
                        $title = $record->katalog?->nama_desain ?? ucfirst(str_replace('_', ' ', $record->jenis_proyek));
                        [$statusLabel, $statusClass] = match ($record->status_pemesanan) {
                            'pending' => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-800'],
                            'dikonfirmasi' => ['Dikonfirmasi', 'bg-blue-100 text-blue-800'],
                            'sedang_dikerjakan' => ['Sedang Dikerjakan', 'bg-orange-100 text-orange-800'],
                            'selesai' => ['Selesai', 'bg-emerald-100 text-emerald-800'],
                            'dibatalkan' => ['Dibatalkan', 'bg-red-100 text-red-700'],
                            default => [ucfirst($record->status_pemesanan), 'bg-slate-100 text-slate-700'],
                        };
                        $typeLabel = 'Pesanan proyek';
                        $typeClass = 'bg-blue-50 text-blue-700';
                        $icon = 'fa-clipboard-list';
                        $detailUrl = route('pemesanan.show', $record);
                    } else {
                        $title = $record->getJenisKonsultasiLabel().' · '.$record->getJenisRuanganLabel();
                        [$statusLabel, $statusClass] = match ($record->status) {
                            'pending' => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-800'],
                            'confirmed' => ['Dikonfirmasi', 'bg-blue-100 text-blue-800'],
                            'completed' => ['Selesai', 'bg-emerald-100 text-emerald-800'],
                            'cancelled' => ['Dibatalkan', 'bg-red-100 text-red-700'],
                            default => [ucfirst($record->status), 'bg-slate-100 text-slate-700'],
                        };
                        $typeLabel = 'Permintaan konsultasi';
                        $typeClass = 'bg-violet-50 text-violet-700';
                        $icon = 'fa-comments';
                        $detailUrl = route('konsultasi.show', $record);
                        $requestSummary = trim((string) $record->deskripsi_kebutuhan);
                        if (in_array(mb_strtolower($requestSummary), ['', '-', 'gaada', 'tidak ada', 'n/a'], true)) {
                            $requestSummary = 'Belum ada catatan tambahan.';
                        }
                    }
                @endphp

                <article class="mb-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $typeClass }}"><i class="fas {{ $icon }}" aria-hidden="true"></i></span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold {{ $typeClass }} rounded-full px-2.5 py-1">{{ $typeLabel }}</span>
                                    <span class="text-xs text-slate-400">{{ $record->created_at->format('d M Y') }}</span>
                                </div>
                                <h3 class="mt-2 text-lg font-bold text-slate-900">{{ $title }}</h3>
                                @if($isOrder)
                                    <p class="mt-1 text-sm text-slate-500">{{ ucfirst(str_replace('_', ' ', $record->jenis_bangunan)) }} · {{ $record->luas_area }} m²</p>
                                @else
                                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $requestSummary }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex w-fit shrink-0 items-center gap-3 sm:flex-col sm:items-end">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            <a href="{{ $detailUrl }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 transition hover:text-amber-700">Lihat Detail <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></a>
                        </div>
                    </div>

                    @if($isOrder)
                        <div class="mt-5 border-t border-slate-100 pt-4">
                            <div class="mb-2 flex justify-between text-xs font-semibold text-slate-500"><span>Progres proyek</span><span>{{ $record->progress }}%</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-amber-400" style="width: {{ $record->progress }}%"></div></div>
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                    <i class="fas fa-clipboard-list text-4xl text-slate-300" aria-hidden="true"></i>
                    <h3 class="mt-5 text-lg font-bold text-slate-800">Belum ada permintaan atau pesanan</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Mulailah dengan mengirim permintaan desain. Tim Daiku akan meninjau kebutuhan ruang Anda.</p>
                    <a href="{{ route('konsultasi.create') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-amber-300">Buat Permintaan</a>
                </div>
            @endforelse

            @if($activities->hasPages())
                <div class="mt-7">{{ $activities->links() }}</div>
            @endif
        </section>
    </div>
</main>
@endsection
