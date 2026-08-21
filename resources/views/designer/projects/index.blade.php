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
        <table class="w-full min-w-[1160px] text-left">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Referensi</th>
                    <th class="px-5 py-3">Pelanggan</th>
                    <th class="px-5 py-3">Detail Proyek</th>
                    <th class="px-5 py-3">Catatan Konsultasi</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($projects as $project)
                    @php
                        $statusLabel = \App\Support\ProjectStageLabel::forPemesanan($project);
                        $statusClass = match(true) {
                            $project->status_pemesanan === 'dibatalkan' => 'bg-red-50 text-red-700',
                            $project->status_pemesanan === 'selesai' => 'bg-emerald-50 text-emerald-700',
                            $project->workflow_stage === 'approved' => 'bg-purple-50 text-purple-700',
                            $project->workflow_stage === 'awaiting_admin_validation' => 'bg-orange-50 text-orange-700',
                            default => 'bg-blue-50 text-blue-700',
                        };
                        $canUpdate = !in_array($project->status_pemesanan, ['selesai', 'dibatalkan'], true);

                        $konsultasi = $project->konsultasi;
                        $budgetLabel = match($konsultasi?->budget_range) {
                            'under_10m' => 'Di bawah Rp 10 Juta',
                            '10m_25m' => 'Rp 10 - 25 Juta',
                            '25m_50m' => 'Rp 25 - 50 Juta',
                            '50m_100m' => 'Rp 50 - 100 Juta',
                            'above_100m' => 'Di atas Rp 100 Juta',
                            default => null,
                        };
                        $rawAttachments = $konsultasi?->attachments ?? [];
                        $attachments = collect($rawAttachments)->map(function ($attachment, $index) use ($konsultasi) {
                            $path = is_array($attachment) ? ($attachment['path'] ?? '') : $attachment;
                            $name = is_array($attachment) ? ($attachment['name'] ?? basename($path)) : basename($path);

                            return [
                                'name' => $name,
                                'url' => $konsultasi ? route('konsultasi.attachment.download', [$konsultasi->id, $index]) : null,
                            ];
                        })->filter(fn ($attachment) => $attachment['url'])->values()->all();

                        $canManageDocuments = in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'survey_scheduled', 'final_design'], true);
                        $isSendableStage = in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'survey_scheduled', 'final_design'], true);
                        $isAwaitingDecision = in_array($project->workflow_stage, ['awaiting_admin_validation', 'awaiting_draft_approval', 'awaiting_final_approval'], true);
                        $docStage = null;
                        $docRound = null;
                        if (in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'awaiting_admin_validation', 'awaiting_draft_approval'], true)) {
                            $docStage = 'draft';
                            $docRound = (int) $project->draft_round;
                        } elseif (in_array($project->workflow_stage, ['survey_scheduled', 'final_design', 'awaiting_final_approval'], true)) {
                            $docStage = 'final';
                            $docRound = (int) $project->final_round;
                        }
                        $roundDocuments = $docStage
                            ? $project->documents->where('stage', $docStage)->where('submission_round', $docRound)
                            : collect();
                        $canSend = $isSendableStage
                            && $roundDocuments->firstWhere('document_type', 'design')
                            && $roundDocuments->firstWhere('document_type', 'rab');
                        $formatDoc = function ($document) use ($project) {
                            if (! $document) {
                                return null;
                            }

                            return [
                                'id' => $document->id,
                                'name' => $document->original_name,
                                'size' => \Illuminate\Support\Facades\Storage::disk('local')->exists($document->path)
                                    ? \Illuminate\Support\Facades\Storage::disk('local')->size($document->path)
                                    : null,
                                'downloadUrl' => route('pemesanan.document.download', [$project->id, $document->id]),
                                'deleteUrl' => route('designer.proyek.document.delete', [$project->id, $document->id]),
                            ];
                        };
                        $decisionHistory = $project->documentDecisions->map(fn ($decision) => [
                            'stage' => $decision->stage,
                            'decision' => $decision->decision,
                            'feedback' => $decision->feedback,
                            'customer' => $decision->customer?->nama,
                            'date' => $decision->created_at->translatedFormat('d M Y, H:i'),
                            'timestamp' => $decision->created_at->toIso8601String(),
                        ])->values()->all();
                        $statusHistory = $project->statusTrackings->map(fn ($tracking) => [
                            'note' => $tracking->catatan,
                            'date' => $tracking->created_at->translatedFormat('d M Y, H:i'),
                            'timestamp' => $tracking->created_at->toIso8601String(),
                        ])->values()->all();

                        $projectPayload = [
                            'id' => $project->id,
                            'reference' => 'DI-'.str_pad((string) $project->id, 3, '0', STR_PAD_LEFT),
                            'status' => $project->status_pemesanan,
                            'stageLabel' => \App\Support\ProjectStageLabel::forPemesanan($project),
                            'docStage' => $docStage,
                            'target' => $project->target_selesai?->format('Y-m-d'),
                            'note' => $project->catatan_progres,
                            'customer' => $project->user?->nama,
                            'email' => $konsultasi?->email ?? $project->user?->email,
                            'phone' => $konsultasi?->no_telp ?? $project->user?->no_telp,
                            'address' => $konsultasi?->alamat ?? $project->user?->alamat,
                            'title' => $project->jenis_proyek,
                            'building' => $project->jenis_bangunan,
                            'area' => $project->luas_area !== null ? (float) $project->luas_area : null,
                            'budgetLabel' => $budgetLabel,
                            'description' => $project->deskripsi_keinginan_desain,
                            'attachments' => $attachments,
                            'showUrl' => route('pemesanan.show', $project->id),
                            'canManageDocuments' => $canManageDocuments,
                            'isAwaitingDecision' => $isAwaitingDecision,
                            'canSend' => $canSend,
                            'totalHarga' => (float) $project->total_harga,
                            'design' => $formatDoc($roundDocuments->firstWhere('document_type', 'design')),
                            'rab' => $formatDoc($roundDocuments->firstWhere('document_type', 'rab')),
                            'uploadUrl' => route('designer.proyek.document.upload', $project->id),
                            'sendUrl' => route('designer.proyek.document.send', $project->id),
                            'decisions' => $decisionHistory,
                            'statusHistory' => $statusHistory,
                        ];
                    @endphp
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-5 py-4">
                            <p class="text-xs font-semibold text-amber-700">DI-{{ str_pad($project->id, 3, '0', STR_PAD_LEFT) }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $project->created_at?->translatedFormat('d M Y, H:i') }} WIB</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $project->user?->nama ?? 'Pelanggan tidak tersedia' }}</p>
                            <p class="max-w-[190px] truncate text-xs text-slate-500">{{ $project->user?->email }}</p>
                            @if($konsultasi?->no_telp)<p class="mt-1 text-[11px] text-slate-400">{{ $konsultasi->no_telp }}</p>@endif
                            <p class="mt-1 max-w-[240px] truncate text-[11px] text-slate-500" title="{{ $konsultasi?->alamat ?: 'Alamat proyek belum diisi' }}">
                                <span class="font-medium text-slate-400">Alamat:</span> {{ $konsultasi?->alamat ?: 'Belum diisi' }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $project->jenis_proyek ?: 'Proyek Interior' }}</p>
                            <p class="text-xs text-slate-500">{{ $project->jenis_bangunan ?: 'Jenis bangunan belum ditentukan' }}</p>
                            @if($project->luas_area !== null)
                                <p class="mt-1 text-[11px] text-slate-400">{{ rtrim(rtrim(number_format((float) $project->luas_area, 2, ',', '.'), '0'), ',') }} m&sup2;</p>
                            @endif
                            @if($budgetLabel)
                                <p class="mt-0.5 text-[11px] text-slate-400">{{ $budgetLabel }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="max-w-[220px] truncate text-xs text-slate-600" title="{{ $project->deskripsi_keinginan_desain ?: 'Belum ada catatan' }}">{{ $project->deskripsi_keinginan_desain ?: 'Belum ada catatan' }}</p>
                            @if(count($attachments))
                                @php
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                                    $firstAttachment = $attachments[0];
                                    $firstIsImage = in_array(strtolower(pathinfo($firstAttachment['name'], PATHINFO_EXTENSION)), $imageExtensions, true);
                                @endphp
                                <button
                                    type="button"
                                    data-project="{{ json_encode($projectPayload, JSON_THROW_ON_ERROR) }}"
                                    onclick="openDpModal(this)"
                                    class="mt-1.5 flex items-center gap-1.5 rounded-lg transition hover:opacity-80"
                                    title="Lihat {{ count($attachments) }} lampiran"
                                >
                                    <span class="block h-9 w-9 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        @if($firstIsImage)
                                            <img src="{{ $firstAttachment['url'] }}" alt="{{ $firstAttachment['name'] }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="flex h-full w-full items-center justify-center text-slate-300"><i class="fas fa-file-lines" aria-hidden="true"></i></span>
                                        @endif
                                    </span>
                                    @if(count($attachments) > 1)
                                        <span class="text-[11px] font-semibold text-slate-500">+{{ count($attachments) - 1 }}</span>
                                    @endif
                                </button>
                            @endif
                        </td>
                        <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                @if($canUpdate)
                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center rounded-lg bg-slate-950 px-3.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                                        data-project="{{ json_encode($projectPayload, JSON_THROW_ON_ERROR) }}"
                                        onclick="openDpModal(this)"
                                    >Unggah Desain &amp; RAB</button>
                                @endif
                            </div>
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

@include('designer.projects._project_modal')
@endsection

@push('scripts')
<script>
let currentDpModalData = null;
let currentDpModalButton = null;

function syncDpModalButton() {
    if (currentDpModalButton) {
        currentDpModalButton.dataset.project = JSON.stringify(currentDpModalData);
    }
}

function openDpModal(button) {
    const project = JSON.parse(button.dataset.project);
    currentDpModalData = project;
    currentDpModalButton = button;

    document.getElementById('dpReferenceTitle').textContent = `#${project.reference}`;
    document.getElementById('dpReference').textContent = project.reference;

    const stageMeta = {
        draft: {
            pill: 'Tahap 1 dari 2',
            title: 'Desain Awal & Draft RAB',
            subtitle: 'Unggah desain awal dan draft RAB untuk dikirim ke pelanggan agar dapat divalidasi.',
            designLabel: 'Desain Awal', designDrop: 'desain awal',
            rabLabel: 'Draft RAB', rabDrop: 'draft RAB',
        },
        final: {
            pill: 'Tahap 2 dari 2',
            title: 'Desain Final & RAB Final',
            subtitle: 'Unggah desain final dan RAB final untuk dikirim kepada pelanggan.',
            designLabel: 'Desain Final', designDrop: 'desain final',
            rabLabel: 'RAB Final', rabDrop: 'RAB final',
        },
    };
    const currentStageMeta = stageMeta[project.docStage] || {
        pill: 'Belum dimulai',
        title: 'Desain & RAB',
        subtitle: 'Desain dan RAB belum dapat diunggah pada tahap ini.',
        designLabel: 'Desain', designDrop: 'desain',
        rabLabel: 'RAB', rabDrop: 'RAB',
    };
    document.getElementById('dpStagePill').textContent = currentStageMeta.pill;
    document.getElementById('dpStageTitle').textContent = currentStageMeta.title;
    document.getElementById('dpStageSubtitle').textContent = currentStageMeta.subtitle;
    document.getElementById('dpDesignFieldLabel').textContent = currentStageMeta.designLabel;
    document.getElementById('dpDesignDropLabel').textContent = currentStageMeta.designDrop;
    document.getElementById('dpRabFieldLabel').textContent = currentStageMeta.rabLabel;
    document.getElementById('dpRabDropLabel').textContent = currentStageMeta.rabDrop;

    renderDpDocumentSlot('Design', 'design');
    renderDpDocumentSlot('Rab', 'rab');
    updateDpSendButtonState();
    renderDpHistoryPanel(project);

    const modal = document.getElementById('dpModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function renderDpHistoryPanel(project) {
    const decisionLabels = { approved: 'Disetujui', revision_requested: 'Minta revisi' };
    const stageLabels = { draft: 'Desain awal', final: 'Desain final' };

    const decisionRows = (project.decisions || []).map(decision => ({
        timestamp: decision.timestamp,
        html: `<p class="font-semibold text-slate-700">${stageLabels[decision.stage] || decision.stage} · ${decisionLabels[decision.decision] || decision.decision}</p>
            <p class="mt-0.5 text-slate-500">${decision.customer || 'Pelanggan'} · ${decision.date}</p>
            ${decision.feedback ? `<p class="mt-1 text-slate-600">${decision.feedback}</p>` : ''}`,
    }));
    const statusRows = (project.statusHistory || []).map(entry => ({
        timestamp: entry.timestamp,
        html: `<p class="font-semibold text-slate-700">${entry.note || 'Status diperbarui'}</p>
            <p class="mt-0.5 text-slate-500">${entry.date}</p>`,
    }));

    const historyPanel = document.getElementById('dpHistoryPanel');
    historyPanel.replaceChildren();
    const allRows = decisionRows.concat(statusRows).sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
    allRows.forEach(row => {
        const el = document.createElement('div');
        el.className = 'rounded-lg bg-slate-50 p-2.5 text-xs';
        el.innerHTML = row.html;
        historyPanel.appendChild(el);
    });
    if (!allRows.length) {
        historyPanel.innerHTML = '<p class="text-xs text-slate-400">Belum ada riwayat tercatat.</p>';
    }
    historyPanel.classList.add('hidden');
    document.getElementById('dpHistoryToggle').onclick = () => historyPanel.classList.toggle('hidden');
}

function closeDpModal() {
    const modal = document.getElementById('dpModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}


function updateDpSendButtonState() {
    const project = currentDpModalData;
    const sendBtn = document.getElementById('dpSendBtn');

    sendBtn.classList.toggle('hidden', project.isAwaitingDecision || !project.canManageDocuments);
    sendBtn.disabled = !project.canSend;
}

function confirmSendDpDocuments() {
    window.dispatchEvent(new CustomEvent('open-confirmation', {
        detail: {
            title: 'Kirim dokumen ke pelanggan?',
            message: 'Desain dan RAB akan dikirim ke pelanggan untuk ditinjau. Pastikan berkas sudah benar sebelum melanjutkan.',
            confirmLabel: 'Ya, kirim',
            tone: 'primary',
            onConfirm: () => sendDpDocuments(),
        },
    }));
}

function sendDpDocuments() {
    const project = currentDpModalData;
    const sendBtn = document.getElementById('dpSendBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> Mengirim...';

    fetch(project.sendUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': dpCsrfToken() },
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                showDpToast(json.message || 'Gagal mengirim ke pelanggan.', 'error');
                return;
            }
            applyDpDocumentResponse(json, true);
        })
        .catch(() => showDpToast('Gagal mengirim ke pelanggan. Periksa koneksi Anda.', 'error'))
        .finally(() => {
            sendBtn.disabled = !project.canSend;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim';
            updateDpSendButtonState();
        });
}

function renderDpDocumentSlot(cap, type) {
    const uploadForm = document.getElementById(`dp${cap}UploadForm`);
    const dropzone = document.getElementById(`dp${cap}Dropzone`);
    const input = document.getElementById(`dp${cap}Input`);
    const fileRow = document.getElementById(`dp${cap}FileRow`);
    const deleteBtn = document.getElementById(`dp${cap}DeleteBtn`);
    const project = currentDpModalData;

    uploadForm.action = project.uploadUrl;
    input.onchange = () => { if (input.files.length) submitDpDocument(uploadForm, input.files[0], type); };
    dropzone.ondragover = event => { event.preventDefault(); dropzone.classList.add('border-amber-400', 'bg-amber-50'); };
    dropzone.ondragleave = () => dropzone.classList.remove('border-amber-400', 'bg-amber-50');
    dropzone.ondrop = event => {
        event.preventDefault();
        dropzone.classList.remove('border-amber-400', 'bg-amber-50');
        if (event.dataTransfer.files.length) submitDpDocument(uploadForm, event.dataTransfer.files[0], type);
    };

    const document_ = project[type];
    const canUpload = project.canManageDocuments;
    const canDelete = project.canManageDocuments || project.isAwaitingDecision;
    dropzone.parentElement.classList.toggle('hidden', !canUpload);

    if (document_) {
        document.getElementById(`dp${cap}FileName`).textContent = document_.name;
        document.getElementById(`dp${cap}FileSize`).textContent = formatDpFileSize(document_.size);
        document.getElementById(`dp${cap}Download`).href = document_.downloadUrl;
        deleteBtn.classList.toggle('hidden', !canDelete);
        deleteBtn.onclick = () => {
            window.dispatchEvent(new CustomEvent('open-confirmation', {
                detail: {
                    title: 'Hapus dokumen ini?',
                    message: `File "${document_.name}" akan dihapus permanen dan tidak dapat dikembalikan.`,
                    confirmLabel: 'Ya, hapus',
                    tone: 'danger',
                    onConfirm: () => deleteDpDocumentRequest(document_.deleteUrl),
                },
            }));
        };
        fileRow.classList.remove('hidden');
        fileRow.classList.add('flex');
    } else {
        fileRow.classList.add('hidden');
        fileRow.classList.remove('flex');
    }
}

function dpCsrfToken() {
    return document.querySelector('#dpDesignUploadForm input[name="_token"]').value;
}

function showDpToast(message, tone = 'success') {
    showNotificationCard(message, tone);
}

function applyDpDocumentResponse(json, showToast = false) {
    currentDpModalData[json.documentType] = json.document;
    currentDpModalData.canManageDocuments = json.canManageDocuments;
    currentDpModalData.isAwaitingDecision = json.isAwaitingDecision;
    currentDpModalData.canSend = json.canSend;
    currentDpModalData.totalHarga = json.totalHarga;
    renderDpDocumentSlot('Design', 'design');
    renderDpDocumentSlot('Rab', 'rab');
    updateDpSendButtonState();
    syncDpModalButton();
    if (showToast) showDpToast(json.message || 'Berhasil disimpan.');
}

function submitDpDocument(form, file, type) {
    const formData = new FormData();
    formData.append('_token', dpCsrfToken());
    formData.append('document_type', type);
    formData.append('document', file);

    fetch(form.action, { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                showDpToast(json.message || 'Gagal mengunggah dokumen.', 'error');
                return;
            }
            applyDpDocumentResponse(json);
        })
        .catch(() => showDpToast('Gagal mengunggah dokumen. Periksa koneksi Anda.', 'error'));
}

function deleteDpDocumentRequest(url) {
    return fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': dpCsrfToken() } })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                showDpToast(json.message || 'Gagal menghapus dokumen.', 'error');
                return;
            }
            applyDpDocumentResponse(json);
        })
        .catch(() => showDpToast('Gagal menghapus dokumen. Periksa koneksi Anda.', 'error'));
}

function formatDpFileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

document.getElementById('dpModal').addEventListener('click', event => {
    if (event.target.id === 'dpModal') closeDpModal();
});

(function pollForUpdates() {
    const heartbeatUrl = '{{ route('designer.projects.heartbeat') }}';
    let lastSignal = null;
    let isFirstCheck = true;

    function anyModalOpen() {
        const modal = document.getElementById('dpModal');
        return modal && !modal.classList.contains('hidden');
    }

    function check() {
        fetch(heartbeatUrl, { headers: { 'Accept': 'application/json' } })
            .then(response => response.ok ? response.json() : null)
            .then(payload => {
                if (!payload) return;

                if (isFirstCheck) {
                    lastSignal = payload.signal;
                    isFirstCheck = false;
                    return;
                }

                if (payload.signal !== lastSignal && !anyModalOpen()) {
                    window.location.reload();
                }
            })
            .catch(() => {});
    }

    check();
    setInterval(check, 5000);
})();
</script>
@endpush
