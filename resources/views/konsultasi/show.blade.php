@extends('layouts.main')

@section('title', 'Detail Permintaan Desain - Daiku Interior')

@section('content')
@php
    $whatsappNumber = preg_replace('/\D+/', '', config('services.daiku.whatsapp_number'));
    $whatsappUrl = 'https://wa.me/'.$whatsappNumber.'?text='.rawurlencode('Halo Daiku, saya ingin menanyakan permintaan desain DI-'.$konsultasi->id.'.');
    [$statusLabel, $statusClass, $statusMessage] = match ($konsultasi->status) {
        'pending' => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-800', 'Permintaan sedang ditinjau oleh tim Daiku.'],
        'confirmed' => ['Dikonfirmasi', 'bg-blue-100 text-blue-800', 'Permintaan telah dikonfirmasi dan akan ditindaklanjuti oleh tim Daiku.'],
        'completed' => ['Selesai', 'bg-emerald-100 text-emerald-800', 'Peninjauan permintaan telah selesai.'],
        'cancelled' => ['Dibatalkan', 'bg-red-100 text-red-700', 'Permintaan ini tidak dapat dilanjutkan.'],
        default => [ucfirst($konsultasi->status), 'bg-slate-100 text-slate-700', 'Status permintaan sedang diperbarui.'],
    };
    if ($konsultasi->status === 'pending' && $konsultasi->accepted_at) {
        [$statusLabel, $statusClass, $statusMessage] = ['Diterima', 'bg-amber-100 text-amber-800', 'Permintaan telah diterima. Tim Daiku sedang menentukan jadwal konsultasi dan desainer.'];
    }
    $requestNote = trim((string) $konsultasi->deskripsi_kebutuhan);
    if (in_array(mb_strtolower($requestNote), ['', '-', 'gaada', 'tidak ada', 'n/a'], true)) {
        $requestNote = 'Belum ada catatan tambahan dari pelanggan.';
    }
    $isAssignedDesigner = auth()->user()->isDesigner()
        && $konsultasi->designer_id === auth()->id();
@endphp

<main class="min-h-screen bg-slate-50 py-10">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <a href="{{ $isAssignedDesigner ? route('designer.dashboard') : route('pesanan.saya') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-amber-700 transition hover:text-amber-800">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ $isAssignedDesigner ? 'Kembali ke Dashboard Desainer' : 'Kembali ke Pesanan Saya' }}
        </a>

        @if(session('success'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="request-title">
            <header class="flex flex-col gap-5 bg-slate-900 px-6 py-7 text-white sm:flex-row sm:items-start sm:justify-between sm:px-8">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-300">Permintaan desain #{{ $konsultasi->id }}</p>
                    <h1 id="request-title" class="mt-2 text-2xl font-bold sm:text-3xl">{{ $konsultasi->getJenisKonsultasiLabel() }}</h1>
                    <p class="mt-2 text-sm text-slate-300">{{ $konsultasi->getJenisRuanganLabel() }} · Dikirim {{ $konsultasi->created_at->translatedFormat('j F Y') }}</p>
                </div>
                <span class="w-fit rounded-full px-3 py-1.5 text-sm font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
            </header>

            <div class="grid gap-6 p-6 sm:p-8 lg:grid-cols-2">
                <section class="rounded-xl border border-slate-200 p-5" aria-labelledby="customer-title">
                    <h2 id="customer-title" class="text-lg font-bold text-slate-900">Informasi pelanggan</h2>
                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Nama</dt><dd class="mt-1 font-semibold text-slate-800">{{ $konsultasi->nama }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">No. WhatsApp</dt><dd class="mt-1 font-semibold text-slate-800">{{ $konsultasi->no_telp }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Email</dt><dd class="mt-1 break-all font-semibold text-slate-800">{{ $konsultasi->email ?: 'Tidak diisi' }}</dd></div>
                    </dl>
                </section>

                <section class="rounded-xl border border-slate-200 p-5" aria-labelledby="project-title">
                    <h2 id="project-title" class="text-lg font-bold text-slate-900">Ringkasan proyek</h2>
                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Jenis proyek</dt><dd class="mt-1 font-semibold text-slate-800">{{ $konsultasi->getJenisKonsultasiLabel() }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Jenis bangunan</dt><dd class="mt-1 font-semibold text-slate-800">{{ $konsultasi->getJenisRuanganLabel() }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Luas area</dt><dd class="mt-1 font-semibold text-slate-800">{{ $konsultasi->luas_ruangan ? $konsultasi->luas_ruangan.' m²' : 'Belum diisi' }}</dd></div>
                        <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Anggaran</dt><dd class="mt-1 font-semibold text-slate-800">{{ $konsultasi->getBudgetRangeLabel() }}</dd></div>
                    </dl>
                </section>

                <section class="rounded-xl bg-amber-50 p-5 lg:col-span-2" aria-labelledby="status-title">
                    <h2 id="status-title" class="text-lg font-bold text-slate-900">Status permintaan</h2>
                    <div class="mt-3 flex gap-3 text-sm leading-relaxed text-slate-700"><i class="fas fa-clock mt-0.5 text-amber-600" aria-hidden="true"></i><p>{{ $statusMessage }}</p></div>
                </section>

                <section class="lg:col-span-2" aria-labelledby="note-title">
                    <h2 id="note-title" class="text-lg font-bold text-slate-900">Catatan kebutuhan</h2>
                    <p class="mt-3 rounded-xl bg-slate-50 p-5 leading-relaxed text-slate-700">{{ $requestNote }}</p>
                </section>

                @if(!empty($konsultasi->attachments))
                    <section class="lg:col-span-2" aria-labelledby="attachment-title">
                        <h2 id="attachment-title" class="text-lg font-bold text-slate-900">Lampiran referensi</h2>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach($konsultasi->attachments as $index => $attachment)
                                <a href="{{ route('konsultasi.attachment.download', [$konsultasi, $index]) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-700 transition hover:border-amber-300 hover:bg-amber-50">
                                    <i class="fas fa-paperclip text-amber-600" aria-hidden="true"></i>
                                    <span class="truncate">{{ basename($attachment) }}</span>
                                    <i class="fas fa-download ml-auto text-slate-400" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($konsultasi->catatan_admin)
                    <section class="lg:col-span-2" aria-labelledby="team-note-title">
                        <h2 id="team-note-title" class="text-lg font-bold text-slate-900">Catatan dari tim Daiku</h2>
                        <p class="mt-3 rounded-xl border border-blue-100 bg-blue-50 p-5 leading-relaxed text-slate-700">{{ $konsultasi->catatan_admin }}</p>
                    </section>
                @endif

                @if($konsultasi->consultation_result)
                    <section class="lg:col-span-2" aria-labelledby="consultation-result-title">
                        <h2 id="consultation-result-title" class="text-lg font-bold text-slate-900">Hasil konsultasi</h2>
                        <p class="mt-3 whitespace-pre-line rounded-xl border border-emerald-100 bg-emerald-50 p-5 leading-relaxed text-slate-700">{{ $konsultasi->consultation_result }}</p>
                    </section>
                @endif

                @if($isAssignedDesigner && $konsultasi->status === 'confirmed' && ! $konsultasi->pemesanan_id)
                    <section class="rounded-xl border border-amber-200 bg-amber-50 p-5 lg:col-span-2" aria-labelledby="complete-consultation-title">
                        <h2 id="complete-consultation-title" class="text-lg font-bold text-slate-900">Catat hasil konsultasi</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Ringkas kebutuhan, keputusan, kendala, dan tindak lanjut yang disepakati. Setelah disimpan, sistem membuat proyek desain awal.</p>
                        <form method="POST" action="{{ route('designer.konsultasi.complete', $konsultasi) }}" class="mt-4 space-y-3">
                            @csrf
                            <textarea name="consultation_result" rows="6" maxlength="5000" required class="w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500" placeholder="Contoh: kebutuhan ruang, ukuran terverifikasi, preferensi, batas anggaran, dan keputusan konsultasi.">{{ old('consultation_result') }}</textarea>
                            <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Simpan hasil & mulai desain awal</button>
                        </form>
                    </section>
                @endif
            </div>

            <footer class="flex flex-col gap-4 border-t border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-semibold text-green-700 hover:text-green-800"><i class="fab fa-whatsapp text-base" aria-hidden="true"></i> Tanya melalui WhatsApp Daiku</a>
                @if(in_array($konsultasi->status, ['completed', 'cancelled'], true))
                    <a href="{{ route('konsultasi.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Buat Pesanan Baru <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></a>
                @endif
            </footer>
        </section>
    </div>
</main>
@endsection
