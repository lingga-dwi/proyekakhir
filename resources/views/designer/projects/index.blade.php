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
                        $statusLabel = \App\Support\ProjectStageLabel::forPemesanan($project);
                        $statusClass = match(true) {
                            $project->status_pemesanan === 'dibatalkan' => 'bg-red-50 text-red-700',
                            $project->status_pemesanan === 'selesai' => 'bg-emerald-50 text-emerald-700',
                            $project->workflow_stage === 'approved' => 'bg-purple-50 text-purple-700',
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

                        $canManageDocuments = in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'final_design'], true);
                        $isSendableStage = in_array($project->workflow_stage, ['draft_design', 'revision_requested', 'final_design'], true);
                        $isAwaitingDecision = in_array($project->workflow_stage, ['awaiting_draft_approval', 'awaiting_final_approval'], true);
                        $docStage = null;
                        $docRound = null;
                        if (in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'awaiting_draft_approval'], true)) {
                            $docStage = 'draft';
                            $docRound = (int) $project->draft_round;
                        } elseif (in_array($project->workflow_stage, ['final_design', 'awaiting_final_approval'], true)) {
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
                        ])->values()->all();

                        $projectPayload = [
                            'id' => $project->id,
                            'reference' => 'DI-'.str_pad((string) $project->id, 3, '0', STR_PAD_LEFT),
                            'status' => $project->status_pemesanan,
                            'stageLabel' => \App\Support\ProjectStageLabel::forPemesanan($project),
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
                        ];
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
                                <a href="{{ route('pemesanan.show', $project) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                                @if($canUpdate)
                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center rounded-lg bg-slate-950 px-3 text-sm font-semibold text-white hover:bg-slate-800"
                                        data-project="{{ json_encode($projectPayload, JSON_THROW_ON_ERROR) }}"
                                        onclick="openDpModal(this)"
                                    >Kelola</button>
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

    document.getElementById('dpForm').action = `{{ url('/designer/proyek') }}/${project.id}`;
    document.getElementById('dpReferenceTitle').textContent = `#${project.reference}`;
    document.getElementById('dpReference').textContent = project.reference;
    document.getElementById('dpCustomer').textContent = project.customer || 'Belum diisi';
    document.getElementById('dpPhone').textContent = project.phone || 'Belum diisi';
    document.getElementById('dpEmail').textContent = project.email || 'Belum diisi';
    document.getElementById('dpAddress').textContent = project.address || 'Belum diisi';
    document.getElementById('dpTitle').textContent = project.title || 'Belum ditentukan';
    document.getElementById('dpBuilding').textContent = project.building || 'Belum ditentukan';
    document.getElementById('dpArea').textContent = project.area !== null ? `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(project.area)} m²` : 'Belum diisi';
    document.getElementById('dpBudgetLabel').textContent = project.budgetLabel || 'Belum ditentukan';
    document.getElementById('dpDescription').textContent = project.description || 'Belum ada catatan kebutuhan.';

    const attachmentSection = document.getElementById('dpAttachments');
    const attachmentList = document.getElementById('dpAttachmentList');
    attachmentList.replaceChildren();
    const imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    (project.attachments || []).forEach(attachment => {
        const extension = (attachment.name.split('.').pop() || '').toLowerCase();
        const isImage = imageExtensions.includes(extension);
        const link = document.createElement('a');
        link.href = attachment.url;
        link.className = 'group block overflow-hidden rounded-xl border border-slate-200 bg-slate-50 transition hover:border-amber-300';
        link.innerHTML = isImage
            ? `<img src="${attachment.url}" alt="${attachment.name}" class="aspect-square w-full object-cover">`
            : '<div class="flex aspect-square w-full items-center justify-center"><i class="fas fa-file-lines text-3xl text-slate-300" aria-hidden="true"></i></div>';
        const caption = document.createElement('p');
        caption.className = 'truncate px-2 py-1.5 text-[11px] font-medium text-slate-600 group-hover:text-amber-700';
        caption.textContent = attachment.name;
        link.appendChild(caption);
        attachmentList.appendChild(link);
    });
    attachmentSection.classList.toggle('hidden', !project.attachments?.length);

    document.getElementById('dpStageLabel').textContent = project.stageLabel || 'Proses Proyek';
    document.getElementById('dpStatus').value = project.status;
    document.getElementById('dpTarget').value = project.target || '';
    document.getElementById('dpNote').value = project.note || '';
    document.getElementById('dpDetailLink').href = project.showUrl;

    document.getElementById('dpTotalHarga').textContent = project.totalHarga > 0
        ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(project.totalHarga)
        : 'Belum ditetapkan oleh admin';

    renderDpDocumentSlot('Design', 'design');
    renderDpDocumentSlot('Rab', 'rab');
    updateDpDocStatus();
    updateDpSendButtonState();

    const historyPanel = document.getElementById('dpHistoryPanel');
    historyPanel.replaceChildren();
    const decisionLabels = { approved: 'Disetujui', revision_requested: 'Minta revisi' };
    const stageLabels = { draft: 'Desain awal', final: 'Desain final' };
    (project.decisions || []).forEach(decision => {
        const row = document.createElement('div');
        row.className = 'rounded-lg bg-slate-50 p-2.5 text-xs';
        row.innerHTML = `<p class="font-semibold text-slate-700">${stageLabels[decision.stage] || decision.stage} · ${decisionLabels[decision.decision] || decision.decision}</p>
            <p class="mt-0.5 text-slate-500">${decision.customer || 'Pelanggan'} · ${decision.date}</p>
            ${decision.feedback ? `<p class="mt-1 text-slate-600">${decision.feedback}</p>` : ''}`;
        historyPanel.appendChild(row);
    });
    if (!project.decisions?.length) {
        historyPanel.innerHTML = '<p class="text-xs text-slate-400">Belum ada keputusan dari pelanggan.</p>';
    }
    historyPanel.classList.add('hidden');
    document.getElementById('dpHistoryToggle').onclick = () => historyPanel.classList.toggle('hidden');

    clearTimeout(dpToastTimer);
    document.getElementById('dpToast').classList.add('hidden');

    const modal = document.getElementById('dpModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDpModal() {
    const modal = document.getElementById('dpModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function updateDpDocStatus() {
    const project = currentDpModalData;
    let docStatus = 'Belum ada desain & RAB pada tahap ini.';
    if (project.isAwaitingDecision) {
        docStatus = 'Terkirim ke pelanggan, menunggu keputusan.';
    } else if (project.design && project.rab) {
        docStatus = 'Desain & RAB lengkap. Belum dikirim ke pelanggan.';
    } else if (project.design || project.rab) {
        docStatus = `Belum dikirim ke pelanggan. Unggah ${project.design ? 'RAB' : 'desain'} untuk melengkapi.`;
    } else if (!project.canManageDocuments) {
        docStatus = 'Desain & RAB tidak dapat dikelola pada tahap ini.';
    }
    document.getElementById('dpDocStatus').textContent = docStatus;
}

function updateDpSendButtonState() {
    const project = currentDpModalData;
    const sendBtn = document.getElementById('dpSendBtn');
    const infoBox = document.getElementById('dpSendInfo');
    const infoText = document.getElementById('dpSendInfoText');

    sendBtn.classList.toggle('hidden', project.isAwaitingDecision || !project.canManageDocuments);

    const isDraftStage = !project.stageLabel || project.stageLabel === 'Menunggu Desain Awal & Draft RAB';
    const priceMissing = isDraftStage && !(project.totalHarga > 0);
    sendBtn.disabled = !project.canSend || priceMissing;

    if (project.isAwaitingDecision) {
        infoBox.classList.add('hidden');
        infoBox.classList.remove('flex');
    } else if (project.stageLabel === 'Konsultasi') {
        infoText.textContent = 'Selesaikan konsultasi terlebih dahulu (isi hasil konsultasi) sebelum dokumen dapat dikirim ke pelanggan. File yang diunggah di sini tetap tersimpan.';
        infoBox.classList.remove('hidden');
        infoBox.classList.add('flex');
    } else if (!project.canSend) {
        infoText.textContent = 'Unggah desain dan RAB terlebih dahulu sebelum mengirim ke pelanggan.';
        infoBox.classList.remove('hidden');
        infoBox.classList.add('flex');
    } else if (priceMissing) {
        infoText.textContent = 'Admin belum menetapkan nilai penawaran. Hubungi admin sebelum mengirim ke pelanggan.';
        infoBox.classList.remove('hidden');
        infoBox.classList.add('flex');
    } else {
        infoText.textContent = 'Setelah pelanggan menyetujui desain ini, sistem akan menampilkan pembayaran DP 20%.';
        infoBox.classList.remove('hidden');
        infoBox.classList.add('flex');
    }
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
            applyDpDocumentResponse(json);
        })
        .catch(() => showDpToast('Gagal mengirim ke pelanggan. Periksa koneksi Anda.', 'error'))
        .finally(() => {
            sendBtn.disabled = !project.canSend;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim ke Pelanggan';
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
    return document.querySelector('#dpForm input[name="_token"]').value;
}

let dpToastTimer = null;
function showDpToast(message, tone = 'success') {
    const toast = document.getElementById('dpToast');
    toast.textContent = message;
    toast.className = tone === 'success'
        ? 'absolute left-1/2 top-4 z-10 flex w-[min(90%,26rem)] -translate-x-1/2 items-center justify-center rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-center text-sm font-semibold text-green-800 shadow-lg'
        : 'absolute left-1/2 top-4 z-10 flex w-[min(90%,26rem)] -translate-x-1/2 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-center text-sm font-semibold text-red-800 shadow-lg';
    clearTimeout(dpToastTimer);
    dpToastTimer = setTimeout(() => toast.classList.add('hidden'), 4000);
}

function applyDpDocumentResponse(json) {
    currentDpModalData[json.documentType] = json.document;
    currentDpModalData.canManageDocuments = json.canManageDocuments;
    currentDpModalData.isAwaitingDecision = json.isAwaitingDecision;
    currentDpModalData.canSend = json.canSend;
    currentDpModalData.totalHarga = json.totalHarga;
    renderDpDocumentSlot('Design', 'design');
    renderDpDocumentSlot('Rab', 'rab');
    updateDpDocStatus();
    updateDpSendButtonState();
    syncDpModalButton();
    showDpToast(json.message || 'Berhasil disimpan.');
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

function saveDpChanges() {
    const saveBtn = document.getElementById('dpSaveBtn');
    const form = document.getElementById('dpForm');
    const note = document.getElementById('dpNote').value.trim();
    if (!note) {
        showDpToast('Catatan progres wajib diisi.', 'error');
        return;
    }
    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';

    fetch(form.action, {
        method: 'PUT',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': dpCsrfToken(), 'Content-Type': 'application/json' },
        body: JSON.stringify({
            status_pemesanan: document.getElementById('dpStatus').value,
            target_selesai: document.getElementById('dpTarget').value || null,
            catatan_progres: note,
        }),
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                const errorMessage = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal menyimpan perubahan.');
                showDpToast(errorMessage, 'error');
                return;
            }
            currentDpModalData.target = document.getElementById('dpTarget').value || null;
            currentDpModalData.note = note;
            syncDpModalButton();
            showDpToast(json.message || 'Perubahan berhasil disimpan.');
        })
        .catch(() => showDpToast('Gagal menyimpan perubahan. Periksa koneksi Anda.', 'error'))
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan Perubahan';
        });
}

document.getElementById('dpModal').addEventListener('click', event => {
    if (event.target.id === 'dpModal') closeDpModal();
});
</script>
@endpush
