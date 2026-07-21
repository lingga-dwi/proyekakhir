@extends('layouts.main')

@section('title', 'Aktivitas Saya - Daiku Interior')

@section('content')
<main class="min-h-screen bg-slate-50 py-10">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <header class="mb-8">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-600">Area pelanggan</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900 sm:text-4xl">Aktivitas Saya</h1>
            <p class="mt-2 text-slate-600">Pantau pesanan desain dan jadwal konsultasi Anda dalam satu tempat.</p>
        </header>

        <div class="mb-7 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm">
            <nav class="grid grid-cols-2 gap-1.5" aria-label="Jenis aktivitas">
                <a href="{{ route('aktivitas.saya', ['tab' => 'pesanan']) }}"
                   class="flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeTab === 'pesanan' ? 'bg-amber-400 text-slate-950 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                   @if($activeTab === 'pesanan') aria-current="page" @endif>
                    <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                    Pesanan
                    <span class="rounded-full px-2 py-0.5 text-xs {{ $activeTab === 'pesanan' ? 'bg-white/70' : 'bg-slate-100' }}">{{ $pemesanans->total() }}</span>
                </a>
                <a href="{{ route('aktivitas.saya', ['tab' => 'konsultasi']) }}"
                   class="flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeTab === 'konsultasi' ? 'bg-amber-400 text-slate-950 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                   @if($activeTab === 'konsultasi') aria-current="page" @endif>
                    <i class="fas fa-comments" aria-hidden="true"></i>
                    Konsultasi
                    <span class="rounded-full px-2 py-0.5 text-xs {{ $activeTab === 'konsultasi' ? 'bg-white/70' : 'bg-slate-100' }}">{{ $konsultasis->total() }}</span>
                </a>
            </nav>
        </div>

        @if($activeTab === 'pesanan')
            <section aria-labelledby="pesanan-title">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 id="pesanan-title" class="text-xl font-bold text-slate-900">Pesanan desain</h2>
                        <p class="mt-1 text-sm text-slate-500">Lihat status dan perkembangan proyek interior Anda.</p>
                    </div>
                    <a href="{{ route('pemesanan.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-plus" aria-hidden="true"></i> Buat Pesanan
                    </a>
                </div>

                @forelse($pemesanans as $pemesanan)
                    @php
                        $orderTitle = $pemesanan->katalog?->nama_desain
                            ?? $pemesanan->rfq?->katalog?->nama_desain
                            ?? ucfirst(str_replace('_', ' ', $pemesanan->jenis_proyek));
                        [$statusLabel, $statusClass, $progress] = match ($pemesanan->status_pemesanan) {
                            'pending' => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-800', 25],
                            'dikonfirmasi' => ['Dikonfirmasi', 'bg-blue-100 text-blue-800', 50],
                            'sedang_dikerjakan' => ['Sedang Dikerjakan', 'bg-orange-100 text-orange-800', 75],
                            'selesai' => ['Selesai', 'bg-emerald-100 text-emerald-800', 100],
                            'dibatalkan' => ['Dibatalkan', 'bg-red-100 text-red-700', 0],
                            default => [ucfirst($pemesanan->status_pemesanan), 'bg-slate-100 text-slate-700', 0],
                        };
                    @endphp
                    <article class="mb-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pesanan #{{ $pemesanan->id }}</p>
                                <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $orderTitle }}</h3>
                                <p class="mt-1 text-sm text-slate-500">Dibuat {{ $pemesanan->created_at->format('d M Y') }}</p>
                            </div>
                            <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <dl class="mt-5 grid gap-4 border-y border-slate-100 py-4 sm:grid-cols-3">
                            <div><dt class="text-xs text-slate-400">Jenis proyek</dt><dd class="mt-1 text-sm font-semibold text-slate-700">{{ ucfirst(str_replace('_', ' ', $pemesanan->jenis_proyek)) }}</dd></div>
                            <div><dt class="text-xs text-slate-400">Bangunan</dt><dd class="mt-1 text-sm font-semibold text-slate-700">{{ ucfirst(str_replace('_', ' ', $pemesanan->jenis_bangunan)) }}</dd></div>
                            <div><dt class="text-xs text-slate-400">Luas area</dt><dd class="mt-1 text-sm font-semibold text-slate-700">{{ $pemesanan->luas_area }} m²</dd></div>
                        </dl>

                        <div class="mt-4">
                            <div class="mb-2 flex justify-between text-xs font-semibold text-slate-500"><span>Progres</span><span>{{ $progress }}%</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-amber-400" style="width: {{ $progress }}%"></div></div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <a href="{{ route('pemesanan.show', $pemesanan->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Lihat Detail <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                        <i class="fas fa-clipboard-list text-4xl text-slate-300" aria-hidden="true"></i>
                        <h3 class="mt-5 text-lg font-bold text-slate-800">Belum ada pesanan</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Pilih inspirasi dari katalog atau buat pesanan sesuai kebutuhan ruang Anda.</p>
                        <a href="{{ route('katalog') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-amber-300">Lihat Katalog</a>
                    </div>
                @endforelse

                @if($pemesanans->hasPages())
                    <div class="mt-7">{{ $pemesanans->links() }}</div>
                @endif
            </section>
        @else
            <section aria-labelledby="konsultasi-title">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 id="konsultasi-title" class="text-xl font-bold text-slate-900">Jadwal konsultasi</h2>
                        <p class="mt-1 text-sm text-slate-500">Pantau jadwal dan hasil peninjauan kebutuhan ruang Anda.</p>
                    </div>
                    <a href="{{ route('konsultasi.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-plus" aria-hidden="true"></i> Buat Konsultasi
                    </a>
                </div>

                @forelse($konsultasis as $konsultasi)
                    @php
                        [$statusLabel, $statusClass] = match ($konsultasi->status) {
                            'pending' => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-800'],
                            'confirmed' => ['Dikonfirmasi', 'bg-blue-100 text-blue-800'],
                            'completed' => ['Selesai', 'bg-emerald-100 text-emerald-800'],
                            'cancelled' => ['Dibatalkan', 'bg-red-100 text-red-700'],
                            default => [ucfirst($konsultasi->status), 'bg-slate-100 text-slate-700'],
                        };
                    @endphp
                    <article class="mb-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Konsultasi #{{ $konsultasi->id }}</p>
                                <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $konsultasi->getJenisKonsultasiLabel() }}</h3>
                            </div>
                            <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <dl class="mt-5 grid gap-4 border-y border-slate-100 py-4 sm:grid-cols-3">
                            <div><dt class="text-xs text-slate-400">Tanggal</dt><dd class="mt-1 text-sm font-semibold text-slate-700">{{ $konsultasi->tanggal_konsultasi->format('d M Y') }}</dd></div>
                            <div><dt class="text-xs text-slate-400">Waktu</dt><dd class="mt-1 text-sm font-semibold text-slate-700">{{ $konsultasi->waktu_konsultasi->format('H:i') }} WIB</dd></div>
                            <div><dt class="text-xs text-slate-400">Ruangan</dt><dd class="mt-1 text-sm font-semibold text-slate-700">{{ ucwords(str_replace('_', ' ', $konsultasi->jenis_ruangan)) }}</dd></div>
                        </dl>
                        <p class="mt-4 line-clamp-2 text-sm leading-relaxed text-slate-600">{{ $konsultasi->deskripsi_kebutuhan }}</p>

                        <div class="mt-5 flex justify-end">
                            <a href="{{ route('konsultasi.show', $konsultasi->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Lihat Detail <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                        <i class="fas fa-comments text-4xl text-slate-300" aria-hidden="true"></i>
                        <h3 class="mt-5 text-lg font-bold text-slate-800">Belum ada konsultasi</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Ceritakan kebutuhan ruang Anda agar tim Daiku dapat menentukan langkah berikutnya.</p>
                        <a href="{{ route('konsultasi.create') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-amber-300">Jadwalkan Konsultasi</a>
                    </div>
                @endforelse

                @if($konsultasis->hasPages())
                    <div class="mt-7">{{ $konsultasis->links() }}</div>
                @endif
            </section>
        @endif
    </div>
</main>
@endsection
