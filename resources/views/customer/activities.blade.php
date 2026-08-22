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
                    <table class="w-full min-w-275 text-left">
                        <thead class="border-b border-slate-200 bg-white text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-6 py-4">Referensi</th>
                                <th scope="col" class="px-5 py-4">Detail Proyek</th>
                                <th scope="col" class="px-5 py-4">Catatan Konsultasi</th>
                                <th scope="col" class="px-5 py-4">Status</th>
                                <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($activities as $activity)
                                @php
                                    [$statusLabel, $statusClass] = $statusStyles[$activity->normalized_status];
                                @endphp
                                <tr class="group transition hover:bg-slate-50/80">
                                    <td class="px-6 py-5">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                                <span class="font-bold text-slate-700">#{{ $activity->reference }}</span>
                                                <span class="text-slate-400">{{ $activity->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                            </div>
                                            <p class="mt-1 text-[11px] font-semibold text-slate-500">{{ $activity->type_label }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-5">
                                        <p class="text-sm font-medium text-slate-900">{{ $activity->title ?: 'Proyek Interior' }}</p>
                                        <p class="text-xs text-slate-500">{{ $activity->building ?: 'Jenis bangunan belum ditentukan' }}</p>
                                        @if($activity->area)
                                            <p class="mt-1 text-[11px] text-slate-400">{{ $activity->area }}</p>
                                        @endif
                                        @if($activity->budgetLabel)
                                            <p class="mt-0.5 text-[11px] text-slate-400">{{ $activity->budgetLabel }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-5">
                                        <p class="max-w-60 truncate text-xs text-slate-600" title="{{ $activity->requirementNote ?: 'Belum ada catatan' }}">{{ $activity->requirementNote ?: 'Belum ada catatan' }}</p>
                                    </td>
                                    <td class="px-5 py-5">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">{{ $statusLabel }}</span>
                                        @if($activity->stage_label && $activity->stage_label !== $statusLabel)
                                            <p class="mt-1 text-[11px] font-medium text-slate-600">{{ $activity->stage_label }}</p>
                                        @endif
                                        @if($activity->actor)
                                            <p class="mt-0.5 text-[11px] text-slate-400">Oleh: {{ $activity->actor }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                class="inline-flex w-44 items-center justify-center gap-2 rounded-lg bg-slate-950 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
                                                data-review="{{ $activity->review ? json_encode($activity->review, JSON_THROW_ON_ERROR) : 'null' }}"
                                                onclick="openReviewModal(this)"
                                            >
                                                Tinjau Penawaran
                                                <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 px-3.5 text-sm font-semibold text-slate-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700"
                                                data-history="{{ json_encode(['reference' => $activity->reference, 'history' => $activity->history], JSON_THROW_ON_ERROR) }}"
                                                onclick="openHistoryModal(this)"
                                            >Riwayat</button>
                                        </div>
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
                        @endphp
                        <article class="p-4 sm:p-5">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="font-bold text-slate-700">#{{ $activity->reference }}</span>
                                    <span class="text-slate-400">{{ $activity->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                </div>
                                <h3 class="mt-1 font-bold text-slate-950">{{ $activity->title }}</h3>
                                <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $activity->meta }}</p>
                                <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $activity->requirementNote ?: 'Belum ada catatan' }}</p>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $activity->type_label }}</span>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </div>
                                    @if($activity->stage_label && $activity->stage_label !== $statusLabel)
                                        <p class="mt-1 text-[11px] font-medium text-slate-600">{{ $activity->stage_label }}</p>
                                    @endif
                                    @if($activity->actor)
                                        <p class="mt-0.5 text-[11px] text-slate-400">Oleh: {{ $activity->actor }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex w-44 items-center justify-center gap-2 rounded-lg bg-slate-950 px-3 py-1.5 text-sm font-semibold text-white"
                                        data-review="{{ $activity->review ? json_encode($activity->review, JSON_THROW_ON_ERROR) : 'null' }}"
                                        onclick="openReviewModal(this)"
                                    >
                                        Tinjau Penawaran
                                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 px-3.5 text-sm font-semibold text-slate-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700"
                                        data-history="{{ json_encode(['reference' => $activity->reference, 'history' => $activity->history], JSON_THROW_ON_ERROR) }}"
                                        onclick="openHistoryModal(this)"
                                    >Riwayat</button>
                                </div>
                            </div>
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

@include('customer._review_modal')
@include('customer._history_modal')
@endsection

@push('scripts')
<script>
let currentReviewData = null;
let reviewModalNeedsRefresh = false;

function reviewCsrfToken() {
    return document.querySelector('#reviewCsrfForm input[name="_token"]').value;
}

function openReviewModal(button) {
    const review = JSON.parse(button.dataset.review);
    renderReviewModal(review);

    const modal = document.getElementById('reviewModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function renderReviewModal(review) {
    currentReviewData = review;

    const hasReview = review !== null;
    document.getElementById('reviewModalEmptyState').classList.toggle('hidden', hasReview);
    document.getElementById('reviewModalContent').classList.toggle('hidden', !hasReview);
    document.getElementById('reviewModalApprovalNotice').classList.add('hidden');
    document.getElementById('reviewModalApprovalFooter').classList.add('hidden');
    document.getElementById('reviewModalStatusFooter').classList.remove('hidden');
    document.getElementById('reviewModalStatusFooter').classList.add('flex');

    if (!hasReview) {
        document.getElementById('reviewModalReference').textContent = '';
        document.getElementById('reviewModalSubtitle').textContent = 'Belum ada penawaran untuk ditinjau.';
        return;
    }

    document.getElementById('reviewModalReference').textContent = `#${review.reference}`;
    document.getElementById('reviewModalReferenceValue').textContent = review.reference;
    const subtitles = {
        draft: 'Tinjau desain awal dan draft RAB sebelum melanjutkan ke pembayaran DP.',
        final: 'Tinjau desain dan RAB final sebelum proyek masuk ke tahap pengerjaan.',
        status: review.stageLabel ? `Tahap saat ini: ${review.stageLabel}.` : 'Pantau perkembangan proyek Anda.',
    };
    document.getElementById('reviewModalSubtitle').textContent = subtitles[review.stage] || subtitles.status;
    document.getElementById('reviewModalTitle').textContent = review.title || 'Belum ditentukan';
    document.getElementById('reviewModalBuilding').textContent = review.building || 'Belum ditentukan';
    document.getElementById('reviewModalArea').textContent = review.area || 'Belum diisi';
    document.getElementById('reviewModalBudget').textContent = review.budgetLabel || 'Belum ditentukan';

    const docTypeMeta = {
        'draft-design': { label: 'Desain Awal', icon: 'fa-file-image', color: 'bg-red-50 text-red-500' },
        'draft-rab': { label: 'Draft RAB', icon: 'fa-file-lines', color: 'bg-emerald-50 text-emerald-600' },
        'final-design': { label: 'Desain Final', icon: 'fa-file-image', color: 'bg-red-50 text-red-500' },
        'final-rab': { label: 'RAB Final', icon: 'fa-file-lines', color: 'bg-emerald-50 text-emerald-600' },
    };
    const stageHeadings = { draft: 'Desain Awal & Draft RAB', final: 'Desain & RAB Final' };
    const documentList = document.getElementById('reviewModalDocuments');
    documentList.replaceChildren();

    const documentsByStage = { draft: [], final: [] };
    (review.documents || []).forEach(document_ => {
        (documentsByStage[document_.stage] || documentsByStage.draft).push(document_);
    });

    ['draft', 'final'].forEach(stage => {
        const docs = documentsByStage[stage];
        if (!docs.length) return;

        const heading = document.createElement('p');
        heading.className = 'text-xs font-semibold uppercase tracking-wide text-slate-500';
        heading.textContent = stageHeadings[stage];
        documentList.appendChild(heading);

        docs.forEach(document_ => {
            const meta = docTypeMeta[`${stage}-${document_.type}`] || { label: document_.type, icon: 'fa-file', color: 'bg-slate-100 text-slate-400' };
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2.5 rounded-lg border border-slate-200 px-3 py-2';
            row.innerHTML = `
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md ${meta.color}"><i class="fas ${meta.icon} text-xs" aria-hidden="true"></i></span>
                <p class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800">${meta.label}</p>
                <a href="${document_.downloadUrl}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" title="Unduh ${meta.label}">
                    <i class="fas fa-download text-xs" aria-hidden="true"></i>
                </a>`;
            documentList.appendChild(row);
        });
    });

    if (!(review.documents || []).length) {
        documentList.innerHTML = '<p class="text-xs text-slate-400">Belum ada dokumen yang diunggah.</p>';
    }

    document.getElementById('reviewModalTotalHarga').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(review.totalHarga || 0);

    const invoiceStatusMeta = {
        pending: { label: 'Belum Dibayar', tone: 'bg-amber-100 text-amber-700' },
        submitted: { label: 'Menunggu Verifikasi', tone: 'bg-blue-100 text-blue-700' },
        paid: { label: 'Lunas', tone: 'bg-emerald-100 text-emerald-700' },
    };
    const invoiceList = document.getElementById('reviewModalInvoiceList');
    invoiceList.replaceChildren();
    if (!(review.invoices || []).length) {
        invoiceList.innerHTML = '<p class="text-xs text-slate-400">Belum ada tagihan untuk proyek ini.</p>';
    } else {
        review.invoices.forEach(invoice => {
            const statusMeta = invoiceStatusMeta[invoice.status] || { label: invoice.status, tone: 'bg-slate-100 text-slate-600' };
            const amountFormatted = new Intl.NumberFormat('id-ID').format(invoice.amount);
            const card = document.createElement('div');
            card.className = 'rounded-xl border border-slate-200 bg-amber-50/60 p-3';
            card.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <p class="flex items-center gap-2 text-sm font-semibold text-slate-900"><i class="fas fa-file-invoice text-amber-500" aria-hidden="true"></i> ${invoice.name}</p>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ${statusMeta.tone}">${statusMeta.label}</span>
                </div>
                <dl class="mt-2 space-y-1 text-xs">
                    <div class="flex items-center justify-between"><dt class="text-slate-500">Nominal</dt><dd class="font-semibold text-amber-700">Rp ${amountFormatted}</dd></div>
                    ${invoice.dueDate ? `<div class="flex items-center justify-between"><dt class="text-slate-500">Jatuh Tempo</dt><dd class="font-medium text-slate-700">${invoice.dueDate}</dd></div>` : ''}
                    <div class="flex items-center justify-between"><dt class="text-slate-500">Metode Pembayaran</dt><dd class="font-medium text-slate-700">Transfer Bank</dd></div>
                </dl>
                ${invoice.note ? `<p class="mt-2 text-xs text-slate-600">${invoice.note}</p>` : ''}
                <div class="mt-3 space-y-1 rounded-lg bg-white p-2.5 text-xs">
                    <div class="flex items-center justify-between"><span class="text-slate-500">Bank Tujuan</span><span class="font-semibold text-slate-800">${review.bankDisplayName || ''}</span></div>
                    <div class="flex items-center justify-between"><span class="text-slate-500">Nomor Rekening</span><span class="font-semibold text-slate-800">${review.bankAccountNumber || ''}</span></div>
                    <div class="flex items-center justify-between"><span class="text-slate-500">Atas Nama</span><span class="font-semibold text-slate-800">${review.bankAccountHolder || ''}</span></div>
                </div>`;
            if (invoice.uploadUrl) {
                const uploadBtn = document.createElement('button');
                uploadBtn.type = 'button';
                uploadBtn.className = 'mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-slate-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800';
                uploadBtn.innerHTML = invoice.status === 'submitted'
                    ? '<i class="fas fa-upload" aria-hidden="true"></i> Ganti Bukti Pembayaran'
                    : '<i class="fas fa-upload" aria-hidden="true"></i> Upload Bukti Pembayaran';
                uploadBtn.onclick = () => triggerInvoiceUpload(invoice);
                card.appendChild(uploadBtn);
            }
            invoiceList.appendChild(card);
        });
    }

    document.getElementById('reviewModalStageLabel').textContent = review.stageLabel || '';
    document.getElementById('reviewModalProgressNote').textContent = review.progressNote || '';

    const isDecision = review.stage === 'draft' || review.stage === 'final';

    document.getElementById('reviewModalStatusBox').classList.toggle('hidden', isDecision);
    document.getElementById('reviewModalDocumentsNoticeText').textContent = isDecision
        ? 'Pastikan desain dan RAB sudah sesuai dengan kebutuhan Anda sebelum menyetujui penawaran.'
        : 'Jika ada tagihan yang belum dibayar, unggah bukti pembayaran pada daftar tagihan di samping.';

    document.getElementById('reviewModalApprovalNotice').classList.toggle('hidden', !isDecision);
    document.getElementById('reviewModalApprovalFooter').classList.toggle('hidden', !isDecision);

    document.getElementById('reviewModalStatusFooter').classList.toggle('hidden', isDecision);
    document.getElementById('reviewModalStatusFooter').classList.toggle('flex', !isDecision);

    hideRevisionPanel();
}

let pendingInvoiceUpload = null;

function triggerInvoiceUpload(invoice) {
    pendingInvoiceUpload = invoice;
    document.getElementById('reviewModalInvoiceUploadInput').click();
}

function submitInvoiceEvidence(file) {
    const invoice = pendingInvoiceUpload;
    if (!invoice) return;

    const formData = new FormData();
    formData.append('bukti_pembayaran', file);

    fetch(invoice.uploadUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': reviewCsrfToken() },
        body: formData,
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                showReviewToast(json.message || 'Gagal mengirim bukti pembayaran.', 'error');
                return;
            }
            showReviewToast(json.message || 'Bukti pembayaran berhasil dikirim.');
            reviewModalNeedsRefresh = true;
            const target = (currentReviewData.invoices || []).find(item => item.id === invoice.id);
            if (target) {
                target.status = 'submitted';
                renderReviewModal(currentReviewData);
            }
        })
        .catch(() => showReviewToast('Gagal mengirim bukti pembayaran. Periksa koneksi Anda.', 'error'))
        .finally(() => { pendingInvoiceUpload = null; });
}

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    if (reviewModalNeedsRefresh) window.location.reload();
}

function openHistoryModal(button) {
    const data = JSON.parse(button.dataset.history);
    document.getElementById('historyModalReference').textContent = `#${data.reference}`;
    const list = document.getElementById('historyModalList');
    list.replaceChildren();
    (data.history || []).forEach(entry => {
        const el = document.createElement('div');
        el.className = 'rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm';
        el.innerHTML = `<p class="font-semibold text-slate-800">${entry.note || 'Status diperbarui'}</p>
            <p class="mt-0.5 text-slate-500">${entry.date}</p>`;
        list.appendChild(el);
    });
    if (!(data.history || []).length) {
        list.innerHTML = '<p class="py-8 text-center text-sm text-slate-400">Belum ada riwayat tercatat untuk pesanan ini.</p>';
    }
    const modal = document.getElementById('historyModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeHistoryModal() {
    const modal = document.getElementById('historyModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function showRevisionPanel() {
    document.getElementById('reviewModalRevisionPanel').classList.remove('hidden');
    document.getElementById('reviewModalRevisionBtn').classList.add('hidden');
    document.getElementById('reviewModalApproveBtn').classList.add('hidden');
    document.getElementById('reviewModalCancelRevisionBtn').classList.remove('hidden');
    document.getElementById('reviewModalSubmitRevisionBtn').classList.remove('hidden');
    document.getElementById('reviewModalFeedback').focus();
}

function hideRevisionPanel() {
    document.getElementById('reviewModalRevisionPanel').classList.add('hidden');
    document.getElementById('reviewModalFeedback').value = '';
    document.getElementById('reviewModalRevisionBtn').classList.remove('hidden');
    document.getElementById('reviewModalApproveBtn').classList.remove('hidden');
    document.getElementById('reviewModalCancelRevisionBtn').classList.add('hidden');
    document.getElementById('reviewModalSubmitRevisionBtn').classList.add('hidden');
}

function showReviewToast(message, tone = 'success') {
    showNotificationCard(message, tone);
}

function confirmApproveReviewDecision() {
    const stageLabel = currentReviewData.stage === 'draft' ? 'desain awal' : 'desain final';
    window.dispatchEvent(new CustomEvent('open-confirmation', {
        detail: {
            title: 'Setujui ' + stageLabel + '?',
            message: 'Setelah disetujui, keputusan ini tidak dapat dibatalkan dan proyek akan lanjut ke tahap berikutnya.',
            confirmLabel: 'Ya, setujui',
            tone: 'success',
            onConfirm: () => submitReviewDecision('approved'),
        },
    }));
}

function submitReviewDecision(decision) {
    const feedback = document.getElementById('reviewModalFeedback').value.trim();
    if (decision === 'revision_requested' && !feedback) {
        showReviewToast('Catatan revisi wajib diisi.', 'error');
        return;
    }

    const buttons = ['reviewModalApproveBtn', 'reviewModalSubmitRevisionBtn'].map(id => document.getElementById(id));
    buttons.forEach(button => button.disabled = true);

    fetch(currentReviewData.decisionUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': reviewCsrfToken(), 'Content-Type': 'application/json' },
        body: JSON.stringify({
            stage: currentReviewData.stage,
            decision,
            feedback: feedback || null,
        }),
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                const errorMessage = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal mengirim keputusan.');
                showReviewToast(errorMessage, 'error');
                return;
            }
            showReviewToast(json.message || 'Keputusan berhasil dikirim.');
            if (json.review) {
                renderReviewModal(json.review);
                reviewModalNeedsRefresh = true;
            } else {
                setTimeout(() => window.location.reload(), 1200);
            }
        })
        .catch(() => showReviewToast('Gagal mengirim keputusan. Periksa koneksi Anda.', 'error'))
        .finally(() => buttons.forEach(button => button.disabled = false));
}

document.getElementById('reviewModal').addEventListener('click', event => {
    if (event.target.id === 'reviewModal') closeReviewModal();
});

document.getElementById('reviewModalInvoiceUploadInput').addEventListener('change', event => {
    if (event.target.files.length) submitInvoiceEvidence(event.target.files[0]);
    event.target.value = '';
});
</script>
@endpush
