@extends($adminView ? 'layouts.dashboard' : 'layouts.main')

@section('title', 'Detail Pesanan - Daiku Interior')
@section('page-title', 'Detail Pesanan')
@section('page-description', 'Tinjau kebutuhan pelanggan, pembayaran, dan progres proyek')

@section('content')
<div class="{{ $adminView ? '' : 'min-h-screen bg-gray-50 py-8' }}">
    <div class="{{ $adminView ? 'mx-auto max-w-6xl' : 'mx-auto max-w-4xl px-4 sm:px-6 lg:px-8' }}">
        @if(session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Detail Pesanan #{{ $pemesanan->id }}</h1>
                    <p class="text-gray-600">Dibuat pada {{ $pemesanan->created_at->format('d M Y H:i') }}</p>
                </div>
                <div class="text-right">
                    @switch($pemesanan->status_pemesanan)
                        @case('pending')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-2"></i>Menunggu Konfirmasi
                            </span>
                            @break
                        @case('dikonfirmasi')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-check mr-2"></i>Dikonfirmasi
                            </span>
                            @break
                        @case('sedang_dikerjakan')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                <i class="fas fa-cog mr-2"></i>Sedang Dikerjakan
                            </span>
                            @break
                        @case('selesai')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>Selesai
                            </span>
                            @break
                        @default
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($pemesanan->status_pemesanan) }}
                            </span>
                    @endswitch
                </div>
            </div>
        </div>

        @php
            $workflowLabels = [
                'draft_design' => 'Desainer menyiapkan desain awal & RAB',
                'awaiting_draft_approval' => 'Menunggu review desain awal pelanggan',
                'revision_requested' => 'Pelanggan meminta revisi desain awal',
                'awaiting_dp' => 'Menunggu pembayaran DP 20%',
                'dp_verification' => 'Bukti DP menunggu verifikasi admin',
                'survey_pending' => 'Menunggu admin menjadwalkan survei lokasi',
                'survey_scheduled' => 'Survei lokasi telah dijadwalkan',
                'final_design' => 'Desainer menyiapkan desain & RAB final',
                'awaiting_final_approval' => 'Menunggu persetujuan desain final pelanggan',
                'approved' => 'Desain final disetujui, pengerjaan berjalan',
            ];
            $draftDocuments = $pemesanan->documents
                ->where('stage', 'draft')
                ->where('submission_round', $pemesanan->draft_round);
            $finalDocuments = $pemesanan->documents
                ->where('stage', 'final')
                ->where('submission_round', $pemesanan->final_round)
                ->whereIn('document_type', ['design', 'rab']);
            $surveyDocuments = $pemesanan->documents->where('document_type', 'survey');
            $dpInvoice = $pemesanan->dpInvoice;
        @endphp

        <section class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Tahap proses saat ini</p>
            <h2 class="mt-1 text-lg font-semibold text-slate-950">{{ $workflowLabels[$pemesanan->workflow_stage] ?? 'Proses proyek' }}</h2>
            @if($pemesanan->catatan_progres)<p class="mt-2 text-sm leading-6 text-slate-700">{{ $pemesanan->catatan_progres }}</p>@endif
        </section>

        @if((auth()->user()->isAdmin() || $pemesanan->designer_id === auth()->id()) && in_array($pemesanan->workflow_stage, ['draft_design', 'revision_requested', 'final_design'], true))
            @php
                $activeDocumentStage = $pemesanan->workflow_stage === 'final_design' ? 'final' : 'draft';
                $activeDocuments = $activeDocumentStage === 'final' ? $finalDocuments : $draftDocuments;
                $activeRound = $activeDocumentStage === 'final' ? $pemesanan->final_round : $pemesanan->draft_round;
                $hasDesign = $activeDocuments->contains('document_type', 'design');
                $hasRab = $activeDocuments->contains('document_type', 'rab');
            @endphp
            <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-950">Kirim dokumen {{ $activeDocumentStage === 'final' ? 'final' : 'awal' }} · Putaran {{ $activeRound }}</h2>
                <p class="mt-1 text-sm text-slate-500">Desain dan RAB wajib tersedia pada putaran yang sama sebelum sistem mengirimkannya untuk ditinjau pelanggan.</p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-full px-3 py-1 {{ $hasDesign ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $hasDesign ? '✓' : '○' }} Desain</span>
                    <span class="rounded-full px-3 py-1 {{ $hasRab ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $hasRab ? '✓' : '○' }} RAB</span>
                </div>
                <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.pemesanan.document.upload', $pemesanan) : route('designer.proyek.document.upload', $pemesanan) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-[180px_1fr_auto]">
                    @csrf
                    <select name="document_type" class="rounded-lg border-slate-300" required><option value="design">Desain</option><option value="rab">RAB</option></select>
                    <input type="file" name="document" accept=".jpg,.jpeg,.png,.webp,.pdf" required class="rounded-lg border border-slate-300 p-2 text-sm">
                    <button class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Kirim dokumen</button>
                </form>
            </section>
        @endif

        @foreach(['draft' => ['Dokumen desain awal & RAB', $draftDocuments], 'final' => ['Dokumen desain & RAB final', $finalDocuments]] as $stage => [$title, $documents])
            @if($documents->isNotEmpty())
                <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-slate-950">{{ $title }} · Putaran {{ $stage === 'draft' ? $pemesanan->draft_round : $pemesanan->final_round }}</h2>
                    <div class="mt-4 space-y-2">
                        @foreach($documents as $document)
                            <a href="{{ route('pemesanan.document.download', [$pemesanan, $document]) }}" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm transition hover:border-amber-300 hover:bg-amber-50">
                                <span><i class="fas fa-file-arrow-down mr-2 text-amber-600" aria-hidden="true"></i>{{ $document->document_type === 'rab' ? 'RAB' : 'Desain' }} — {{ $document->original_name }}</span>
                                <span class="text-xs text-slate-400">v{{ $document->version }}</span>
                            </a>
                        @endforeach
                    </div>
                    @if(auth()->id() === $pemesanan->id_user && (($stage === 'draft' && $pemesanan->workflow_stage === 'awaiting_draft_approval') || ($stage === 'final' && $pemesanan->workflow_stage === 'awaiting_final_approval')))
                        <form method="POST" action="{{ route('pemesanan.document.decision', $pemesanan) }}" class="mt-5 grid gap-3 rounded-xl bg-slate-50 p-4">
                            @csrf
                            <input type="hidden" name="stage" value="{{ $stage }}">
                            <textarea name="feedback" rows="3" maxlength="2000" class="rounded-lg border-slate-300" placeholder="Catatan revisi (isi bila meminta revisi)"></textarea>
                            <div class="flex flex-wrap gap-3">
                                <button name="decision" value="approved" class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">{{ $stage === 'draft' ? 'Setujui & lanjut ke DP' : 'Setujui desain final' }}</button>
                                <button name="decision" value="revision_requested" class="rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-semibold text-amber-800 hover:bg-amber-50">Minta revisi</button>
                            </div>
                        </form>
                    @endif
                </section>
            @endif
        @endforeach

        @if($dpInvoice)
            <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-950">Invoice DP 20%</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $dpInvoice->number }} · Rp {{ number_format($dpInvoice->amount, 0, ',', '.') }} · {{ strtoupper($dpInvoice->status) }}</p>
                @if($dpInvoice->due_date)<p class="mt-1 text-xs text-slate-500">Jatuh tempo: {{ $dpInvoice->due_date->format('d M Y') }}</p>@endif
                @if(auth()->id() === $pemesanan->id_user && $pemesanan->workflow_stage === 'awaiting_dp')
                    <form method="POST" action="{{ route('pemesanan.dp-evidence.upload', $pemesanan) }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 sm:flex-row">
                        @csrf <input type="file" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.webp,.pdf" required class="min-w-0 flex-1 rounded-lg border border-slate-300 p-2 text-sm"><button class="rounded-lg bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950">Kirim bukti DP</button>
                    </form>
                @endif
                @if(auth()->user()->isAdmin() && $pemesanan->workflow_stage === 'dp_verification')
                    <form method="POST" action="{{ route('admin.pemesanan.dp.verify', $pemesanan) }}" class="mt-4">@csrf <button class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white">Verifikasi DP</button></form>
                @endif
            </section>
        @endif

        @if(auth()->user()->isAdmin() && $pemesanan->workflow_stage === 'survey_pending')
            <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-950">Tugaskan desainer & jadwalkan survei</h2>
                <form method="POST" action="{{ route('admin.pemesanan.survey.schedule', $pemesanan) }}" class="mt-4 grid gap-3 lg:grid-cols-[1fr_1fr_1.4fr_auto]">
                    @csrf @method('PUT')
                    <select name="designer_id" required class="rounded-lg border-slate-300">
                        <option value="">Pilih desainer</option>
                        @foreach($designers as $designer)
                            <option value="{{ $designer->id }}" @selected(old('designer_id', $pemesanan->designer_id) == $designer->id)>{{ $designer->nama }}</option>
                        @endforeach
                    </select>
                    <input type="datetime-local" name="survey_scheduled_at" min="{{ now()->format('Y-m-d\\TH:i') }}" required class="rounded-lg border-slate-300">
                    <input type="text" name="survey_notes" maxlength="2000" placeholder="Catatan survei" class="rounded-lg border-slate-300">
                    <button class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Simpan jadwal</button>
                </form>
            </section>
        @endif

        @if($pemesanan->workflow_stage === 'survey_scheduled')
            <section class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <h2 class="font-semibold text-slate-950">Jadwal survei lokasi</h2>
                <p class="mt-2 text-sm text-slate-700">{{ $pemesanan->survey_scheduled_at?->translatedFormat('j F Y, H:i') }}{{ $pemesanan->designer ? ' · '.$pemesanan->designer->nama : '' }}</p>
                @if($pemesanan->survey_notes)<p class="mt-1 text-sm leading-6 text-slate-600">{{ $pemesanan->survey_notes }}</p>@endif
                @if($pemesanan->designer_id === auth()->id())
                    <form method="POST" action="{{ route('designer.proyek.survey.complete', $pemesanan) }}" enctype="multipart/form-data" class="mt-4 grid gap-3">
                        @csrf
                        <textarea name="survey_result" rows="4" maxlength="5000" required class="rounded-lg border-slate-300" placeholder="Catat ukuran aktual, kondisi lokasi, titik listrik/mekanikal, dan temuan survei.">{{ old('survey_result') }}</textarea>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <input type="file" name="survey_document" accept=".jpg,.jpeg,.png,.webp,.pdf" class="min-w-0 rounded-lg border border-slate-300 bg-white p-2 text-sm">
                            <button class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Selesaikan survei</button>
                        </div>
                    </form>
                @endif
            </section>
        @endif

        @if($pemesanan->survey_result)
            <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-950">Hasil survei lokasi</h2>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $pemesanan->survey_result }}</p>
                @foreach($surveyDocuments as $document)
                    <a href="{{ route('pemesanan.document.download', [$pemesanan, $document]) }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-amber-700"><i class="fas fa-paperclip"></i>{{ $document->original_name }}</a>
                @endforeach
            </section>
        @endif

        @if($pemesanan->documentDecisions->isNotEmpty())
            <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-950">Riwayat keputusan pelanggan</h2>
                <div class="mt-3 divide-y divide-slate-100">
                    @foreach($pemesanan->documentDecisions->sortByDesc('created_at') as $decision)
                        <div class="py-3 text-sm">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="font-semibold text-slate-800">{{ $decision->stage === 'draft' ? 'Desain awal' : 'Desain final' }} · Putaran {{ $decision->submission_round }}</p>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $decision->decision === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">{{ $decision->decision === 'approved' ? 'Disetujui' : 'Revisi diminta' }}</span>
                            </div>
                            @if($decision->feedback)<p class="mt-1 text-slate-600">{{ $decision->feedback }}</p>@endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Pelanggan -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pelanggan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Nama</label>
                            <p class="text-gray-800">{{ $pemesanan->user->nama }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Email</label>
                            <p class="text-gray-800">{{ $pemesanan->user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">No. Telepon</label>
                            <p class="text-gray-800">{{ $pemesanan->user->no_telp }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Alamat</label>
                            <p class="text-gray-800">{{ $pemesanan->user->alamat }}</p>
                        </div>
                    </div>
                </div>

                <!-- Detail Proyek -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Proyek</h2>
                    @php($selectedKatalog = $pemesanan->katalog)
                    @if($selectedKatalog)
                    <div class="mb-4 p-4 bg-yellow-50 rounded-lg">
                        <h3 class="font-medium text-gray-800">Desain Terpilih</h3>
                        <p class="text-gray-600">{{ $selectedKatalog->nama_desain }}</p>
                        <p class="text-sm text-gray-500">{{ $selectedKatalog->category?->name ?? 'Tanpa kategori' }}</p>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jenis Proyek</label>
                            <p class="text-gray-800 capitalize">{{ str_replace('_', ' ', $pemesanan->jenis_proyek) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jenis Bangunan</label>
                            <p class="text-gray-800 capitalize">{{ str_replace('_', ' ', $pemesanan->jenis_bangunan) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Luas Area</label>
                            <p class="text-gray-800">{{ $pemesanan->luas_area }} m²</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jumlah Ruangan</label>
                            <p class="text-gray-800">{{ $pemesanan->jumlah_ruangan }} ruangan</p>
                        </div>
                        @if($pemesanan->total_harga > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Estimasi Biaya</label>
                            <p class="text-gray-800">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Preferensi Desain -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Preferensi Desain</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Gaya Desain</label>
                            <p class="text-gray-800 capitalize">{{ $pemesanan->gaya_desain_preferensi }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Warna Dominan</label>
                            <p class="text-gray-800">{{ $pemesanan->warna_dominan }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Deskripsi Keinginan</label>
                            <p class="text-gray-800">{{ $pemesanan->deskripsi_keinginan_desain }}</p>
                        </div>
                    </div>
                </div>

                <!-- File Upload -->
                @if($pemesanan->upload_denah_foto && count($pemesanan->upload_denah_foto) > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">File Denah/Referensi</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($pemesanan->upload_denah_foto as $file)
                        <div class="border border-gray-200 rounded-lg p-4 text-center">
                            <i class="fas fa-file-image text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600">{{ basename($file) }}</p>
                            <a href="{{ route('pemesanan.attachment', [$pemesanan, $loop->index]) }}" target="_blank" rel="noopener" class="text-blue-600 text-sm hover:text-blue-800">
                                Lihat File
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Progress Timeline -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Progress Proyek</h2>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $pemesanan->status_pemesanan == 'pending' || $pemesanan->status_pemesanan == 'dikonfirmasi' || $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Pesanan Diterima</p>
                                <p class="text-sm text-gray-500">{{ $pemesanan->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $pemesanan->status_pemesanan == 'dikonfirmasi' || $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Konsultasi Selesai</p>
                                <p class="text-sm text-gray-500">{{ $pemesanan->status_pemesanan == 'dikonfirmasi' || $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? $pemesanan->updated_at->format('d M Y H:i') : 'Belum selesai' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Pengerjaan Dimulai</p>
                                <p class="text-sm text-gray-500">{{ $pemesanan->status_pemesanan == 'sedang_dikerjakan' || $pemesanan->status_pemesanan == 'selesai' ? 'Sedang dikerjakan' : 'Menunggu konfirmasi' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $pemesanan->status_pemesanan == 'selesai' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Proyek Selesai</p>
                                <p class="text-sm text-gray-500">{{ $pemesanan->status_pemesanan == 'selesai' ? $pemesanan->updated_at->format('d M Y H:i') : 'Belum selesai' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Hubungi Kami</h2>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-yellow-500 w-5"></i>
                            <span class="ml-3 text-gray-700">Pekanbaru, Riau</span>
                        </div>
                        <a href="{{ route('konsultasi.index') }}" class="flex items-center text-gray-700 hover:text-yellow-700">
                            <i class="fas fa-comments text-yellow-500 w-5"></i>
                            <span class="ml-3">Buka layanan konsultasi</span>
                        </a>
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                <!-- Admin Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Admin Actions</h2>
                    <form action="{{ route('admin.pemesanan.updateStatus', $pemesanan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-2">Update Status</label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500">
                                    <option value="pending" {{ $pemesanan->status_pemesanan == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="dikonfirmasi" {{ $pemesanan->status_pemesanan == 'dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
                                    <option value="sedang_dikerjakan" {{ $pemesanan->status_pemesanan == 'sedang_dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                    <option value="selesai" {{ $pemesanan->status_pemesanan == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="dibatalkan" {{ $pemesanan->status_pemesanan == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
