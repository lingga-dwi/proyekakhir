@extends('layouts.dashboard')

@section('title', 'Kelola Pemesanan - Admin Dashboard')
@section('page-title', 'Kelola Pemesanan')
@section('page-description', 'Tindak lanjuti konsultasi dan pesanan pelanggan dari satu tempat')

@section('content')
@php
    $orderIds = $workItems->where('item_type', 'order')->pluck('id');
    $projectDocumentsById = \App\Models\Pemesanan::whereIn('id', $orderIds)
        ->with([
            'documents' => fn ($query) => $query->orderByDesc('version'),
            'documentDecisions' => fn ($query) => $query->with('customer')->orderByDesc('created_at'),
            'konsultasi:id,pemesanan_id',
            'dpInvoice',
            'invoices' => fn ($query) => $query->orderByDesc('created_at'),
            'statusTrackings' => fn ($query) => $query->orderByDesc('created_at'),
        ])
        ->get()
        ->keyBy('id');
@endphp

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="font-semibold text-slate-950">Daftar Permintaan &amp; Pesanan</h2>
            <p class="text-xs text-slate-500">Konsultasi dan pesanan ditangani dalam satu antrean tanpa data ganda.</p>
        </div>
        <button type="button" onclick="openOrderModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
            <i class="fas fa-plus" aria-hidden="true"></i>
            Tambah Pesanan
        </button>
    </div>

    <form method="GET" class="grid gap-3 border-b border-slate-100 bg-slate-50/60 p-4 md:grid-cols-[minmax(0,1fr)_230px_auto]">
        <label class="relative">
            <span class="sr-only">Cari permintaan atau pesanan</span>
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari ID, pelanggan, atau kebutuhan..." class="w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-4 focus:border-amber-500 focus:ring-amber-500">
        </label>
        <label>
            <span class="sr-only">Filter tahap</span>
            <select name="stage" class="w-full rounded-xl border-slate-300 px-4 py-2.5 focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua tahap</option>
                <optgroup label="Konsultasi">
                    <option value="consultation_pending" @selected(request('stage') === 'consultation_pending')>Konsultasi masuk</option>
                    <option value="consultation_confirmed" @selected(request('stage') === 'consultation_confirmed')>Desainer ditugaskan</option>
                    <option value="consultation_completed" @selected(request('stage') === 'consultation_completed')>Konsultasi selesai</option>
                    <option value="consultation_cancelled" @selected(request('stage') === 'consultation_cancelled')>Konsultasi ditolak</option>
                </optgroup>
                <optgroup label="Pesanan &amp; Proyek">
                    <option value="order_pending" @selected(request('stage') === 'order_pending')>Pesanan baru</option>
                    <option value="order_confirmed" @selected(request('stage') === 'order_confirmed')>Dikonfirmasi</option>
                    <option value="order_active" @selected(request('stage') === 'order_active')>Sedang dikerjakan</option>
                    <option value="order_completed" @selected(request('stage') === 'order_completed')>Selesai</option>
                    <option value="order_cancelled" @selected(request('stage') === 'order_cancelled')>Dibatalkan</option>
                </optgroup>
            </select>
        </label>
        <div class="flex gap-2">
            <button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Terapkan</button>
            @if(request()->hasAny(['search', 'stage']))
                <a href="{{ route('admin.pemesanan.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Reset</a>
            @endif
        </div>
    </form>

    <div class="border-b border-slate-100 px-5 py-3 text-xs text-slate-500">
        Menampilkan {{ $workItems->firstItem() ?? 0 }}-{{ $workItems->lastItem() ?? 0 }} dari {{ $workItems->total() }} data
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1320px]">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Referensi</th>
                    <th class="px-5 py-3 font-medium">Pelanggan</th>
                    <th class="px-5 py-3 font-medium">Detail Proyek</th>
                    <th class="px-5 py-3 font-medium">Catatan Konsultasi</th>
                    <th class="px-5 py-3 font-medium">Penanggung Jawab</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($workItems as $item)
                    @php
                        $isConsultation = $item->item_type === 'consultation';
                        $reference = ($isConsultation ? 'KS-' : 'DI-').str_pad((string) $item->id, 3, '0', STR_PAD_LEFT);
                        $title = $isConsultation ? match($item->title) {
                            'free_consultation' => 'Desain Interior Baru',
                            'virtual_design' => 'Renovasi Interior',
                            'in_home_visit' => 'Custom Furniture',
                            'chat_support' => 'Konsultasi Desain',
                            default => 'Permintaan Konsultasi',
                        } : ($item->title ?: 'Interior');
                        $building = $isConsultation ? match($item->space) {
                            'living_room' => 'Rumah Tinggal',
                            'bedroom' => 'Apartemen',
                            'kitchen' => 'Ruko',
                            'bathroom' => 'Kantor',
                            'office' => 'Kafe / Restoran',
                            'whole_house' => 'Lainnya',
                            default => 'Belum ditentukan',
                        } : ($item->space ?: 'Belum ditentukan');
                        $budgetLabel = match($item->budget_range) {
                            'under_10m' => 'Di bawah Rp 10 Juta',
                            '10m_25m' => 'Rp 10 - 25 Juta',
                            '25m_50m' => 'Rp 25 - 50 Juta',
                            '50m_100m' => 'Rp 50 - 100 Juta',
                            'above_100m' => 'Di atas Rp 100 Juta',
                            default => null,
                        };
                        $rawAttachments = is_string($item->attachments)
                            ? (json_decode($item->attachments, true) ?: [])
                            : ($item->attachments ?: []);
                        $attachments = collect($rawAttachments)->map(function ($attachment, $index) use ($item) {
                            $path = is_array($attachment) ? ($attachment['path'] ?? '') : $attachment;
                            $name = is_array($attachment) ? ($attachment['name'] ?? basename($path)) : basename($path);

                            return [
                                'name' => $name,
                                'url' => $item->consultation_id
                                    ? route('konsultasi.attachment.download', [$item->consultation_id, $index])
                                    : null,
                            ];
                        })->filter(fn ($attachment) => $attachment['url'])->values()->all();
                        $statusActor = null;
                        if ($isConsultation) {
                            [$statusLabel, $statusClass] = match($item->status) {
                                'pending' => [\App\Support\ProjectStageLabel::forKonsultasi(new \App\Models\Konsultasi(['status' => 'pending'])), 'bg-violet-100 text-violet-700'],
                                'confirmed' => [\App\Support\ProjectStageLabel::forKonsultasi(new \App\Models\Konsultasi(['status' => 'confirmed'])), 'bg-blue-100 text-blue-700'],
                                'completed' => ['Konsultasi selesai', 'bg-green-100 text-green-700'],
                                'cancelled' => ['Konsultasi ditolak', 'bg-red-100 text-red-700'],
                                default => ['Belum diketahui', 'bg-slate-100 text-slate-700'],
                            };
                            $statusActor = match($item->status) {
                                'pending' => 'Admin',
                                'confirmed' => 'Desainer',
                                default => null,
                            };
                        } else {
                            $tempPemesanan = new \App\Models\Pemesanan([
                                'status_pemesanan' => $item->status,
                                'workflow_stage' => $item->workflow_stage,
                            ]);
                            $statusLabel = $item->workflow_stage === 'awaiting_admin_validation'
                                ? 'Perlu Ditinjau'
                                : \App\Support\ProjectStageLabel::forPemesanan($tempPemesanan);
                            $statusClass = match(true) {
                                $item->status === 'dibatalkan' => 'bg-red-100 text-red-800',
                                $item->status === 'selesai' => 'bg-green-100 text-green-800',
                                $item->workflow_stage === 'approved' => 'bg-purple-100 text-purple-800',
                                $item->workflow_stage === 'konsultasi' => 'bg-violet-100 text-violet-700',
                                $item->workflow_stage === 'awaiting_admin_validation' => 'bg-orange-100 text-orange-800',
                                default => 'bg-blue-100 text-blue-800',
                            };
                            $statusActor = \App\Support\ProjectStageLabel::actorFor($tempPemesanan);
                        }
                        if ($isConsultation && $item->status === 'pending' && $item->accepted_at) {
                            [$statusLabel, $statusClass] = ['Diterima · pilih desainer', 'bg-amber-100 text-amber-800'];
                            $statusActor = 'Admin';
                        }
                        if (! $isConsultation && ! $item->designer_id && ! in_array($item->status, ['selesai', 'dibatalkan'], true)) {
                            [$statusLabel, $statusClass] = ['Menunggu penugasan', 'bg-amber-100 text-amber-800'];
                            $statusActor = 'Admin';
                        }
                        $itemDate = \Illuminate\Support\Carbon::parse($item->scheduled_date);
                        $detailPayload = [
                            'reference' => $reference,
                            'type' => $isConsultation ? 'Permintaan konsultasi' : 'Pesanan desain',
                            'status' => $statusLabel,
                            'statusClass' => $statusClass,
                            'date' => $itemDate->translatedFormat('d M Y').($item->scheduled_time ? ' · '.substr($item->scheduled_time, 0, 5).' WIB' : ''),
                            'customer' => $item->customer_name,
                            'email' => $item->customer_email,
                            'phone' => $item->customer_phone,
                            'address' => $item->customer_address,
                            'title' => $title,
                            'building' => $building,
                            'area' => $item->area !== null ? (float) $item->area : null,
                            'budgetLabel' => $budgetLabel,
                            'description' => $item->detail,
                            'attachments' => $attachments,
                            'isProject' => ! $isConsultation,
                            'progress' => (int) $item->progress,
                            'designer' => $item->designer_name,
                            'target' => $item->target_selesai ? \Illuminate\Support\Carbon::parse($item->target_selesai)->translatedFormat('d M Y') : null,
                            'budget' => (float) $item->total_harga,
                            'note' => $item->note,
                        ];
                    @endphp
                    <tr class="align-top hover:bg-slate-50">
                        <td class="px-5 py-4">
                            <button
                                type="button"
                                data-detail="{{ json_encode($detailPayload, JSON_THROW_ON_ERROR) }}"
                                onclick="openDetailModal(this)"
                                class="text-left text-sm font-bold text-amber-700 underline-offset-4 transition hover:text-amber-800 hover:underline focus:outline-none focus-visible:rounded focus-visible:ring-2 focus-visible:ring-amber-500"
                                aria-label="Buka detail {{ $reference }}"
                            >{{ $reference }}</button>
                            <p class="text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i') }} WIB</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $item->customer_name }}</p>
                            <p class="max-w-[190px] truncate text-xs text-slate-500">{{ $item->customer_email }}</p>
                            @if($item->customer_phone)<p class="mt-1 text-[11px] text-slate-400">{{ $item->customer_phone }}</p>@endif
                            <p class="mt-1 max-w-[240px] truncate text-[11px] text-slate-500" title="{{ $item->customer_address ?: 'Alamat proyek belum diisi' }}">
                                <span class="font-medium text-slate-400">Alamat:</span> {{ $item->customer_address ?: 'Belum diisi' }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $title }}</p>
                            <p class="text-xs text-slate-500">{{ $building }}</p>
                            @if($item->area !== null)
                                <p class="mt-1 text-[11px] text-slate-400">{{ rtrim(rtrim(number_format((float) $item->area, 2, ',', '.'), '0'), ',') }} m&sup2;</p>
                            @endif
                            @if($budgetLabel)
                                <p class="mt-0.5 text-[11px] text-slate-400">{{ $budgetLabel }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="max-w-[220px] truncate text-xs text-slate-600" title="{{ $item->detail ?: 'Belum ada catatan' }}">{{ $item->detail ?: 'Belum ada catatan' }}</p>
                            @if(count($attachments))
                                @php
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                                    $firstAttachment = $attachments[0];
                                    $firstIsImage = in_array(strtolower(pathinfo($firstAttachment['name'], PATHINFO_EXTENSION)), $imageExtensions, true);
                                @endphp
                                <button
                                    type="button"
                                    data-detail="{{ json_encode($detailPayload, JSON_THROW_ON_ERROR) }}"
                                    onclick="openDetailModal(this)"
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
                        <td class="px-5 py-4">
                            @if($isConsultation && $item->accepted_at && in_array($item->status, ['pending', 'confirmed'], true))
                                <form method="POST" action="{{ route('admin.pemesanan.konsultasi.assign', $item->id) }}" class="flex min-w-[210px] items-center gap-2">
                                    @csrf @method('PUT')
                                    <label class="sr-only" for="consultation-designer-{{ $item->id }}">Pilih desainer konsultasi</label>
                                    <select id="consultation-designer-{{ $item->id }}" name="designer_id" required onchange="this.form.requestSubmit()" class="min-w-0 flex-1 rounded-lg border-slate-300 py-2 text-xs focus:border-amber-500 focus:ring-amber-500">
                                        <option value="">Pilih desainer</option>
                                        @foreach($designers as $designer)
                                            <option value="{{ $designer->id }}" @selected((int) $item->designer_id === $designer->id)>{{ $designer->nama }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <p class="mt-1 text-[11px] text-slate-400">Konsultasi melalui WhatsApp</p>
                            @elseif($isConsultation)
                                <p class="text-sm font-medium text-slate-600">{{ $item->designer_name ?: 'Belum ditugaskan' }}</p>
                            @else
                                <p class="text-sm font-medium text-slate-700">{{ $item->designer_name ?: 'Belum ditugaskan' }}</p>
                                <p class="mt-1 text-[11px] text-slate-400">Desainer proyek</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if($statusActor)
                                <p class="mt-1 text-[11px] text-slate-400">Oleh: {{ $statusActor }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($isConsultation)
                                <div class="flex max-w-[260px] flex-wrap items-center gap-2">
                                    @if($item->status === 'pending')
                                        @if(!$item->accepted_at)
                                            <button
                                                type="button"
                                                class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-3.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                                                @disabled($designers->isEmpty())
                                                title="{{ $designers->isEmpty() ? 'Tambahkan akun desainer terlebih dahulu' : 'Terima dan tugaskan desainer' }}"
                                                @click="$dispatch('open-accept-consultation', {
                                                    action: '{{ route('admin.pemesanan.konsultasi.accept', $item->id) }}',
                                                    reference: '{{ $reference }}',
                                                    customer: @js($item->customer_name),
                                                    requirement: @js($title)
                                                })"
                                            >Terima</button>
                                        @endif
                                        @if(!$item->accepted_at)
                                            <button
                                                type="button"
                                                class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-white px-3.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                                @click="$dispatch('open-reject-consultation', {
                                                    action: '{{ route('admin.pemesanan.konsultasi.update', $item->id) }}',
                                                    reference: '{{ $reference }}',
                                                    customer: @js($item->customer_name),
                                                    requirement: @js($title)
                                                })"
                                            >Tolak</button>
                                        @endif
                                    @endif
                                    @if($item->status === 'confirmed')
                                        <span class="text-sm font-medium text-slate-500">Menunggu hasil konsultasi</span>
                                    @endif
                                    @if(in_array($item->status, ['completed', 'cancelled'], true))
                                        <button
                                            type="button"
                                            class="inline-flex h-9 items-center justify-center rounded-lg bg-slate-950 px-3.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                                            @click="$dispatch('open-consultation-modal', {
                                                reference: '{{ $reference }}',
                                                customer: @js($item->customer_name),
                                                email: @js($item->customer_email),
                                                phone: @js($item->customer_phone),
                                                address: @js($item->customer_address),
                                                requirement: @js($title),
                                                statusLabel: @js($statusLabel),
                                                statusClass: @js($statusClass),
                                                status: '{{ $item->status }}',
                                                convertAction: '{{ route('admin.pemesanan.konsultasi.convert', $item->id) }}',
                                                deleteAction: '{{ route('admin.pemesanan.konsultasi.destroy', $item->id) }}'
                                            })"
                                        >Kelola Pesanan</button>
                                    @endif
                                </div>
                            @else
                                @php
                                    $project = $projectDocumentsById[$item->id] ?? null;
                                    $docStage = null;
                                    $docRound = null;
                                    if ($project) {
                                        if (in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'awaiting_admin_validation', 'awaiting_draft_approval'], true)) {
                                            $docStage = 'draft';
                                            $docRound = (int) $project->draft_round;
                                        } elseif (in_array($project->workflow_stage, ['final_design', 'awaiting_final_approval'], true)) {
                                            $docStage = 'final';
                                            $docRound = (int) $project->final_round;
                                        }
                                    }
                                    $canManageDocuments = $project && in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'final_design'], true);
                                    $isSendableStage = $project && in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'final_design'], true);
                                    $isAwaitingDecision = $project && in_array($project->workflow_stage, ['awaiting_admin_validation', 'awaiting_draft_approval', 'awaiting_final_approval'], true);
                                    $needsAdminValidation = $project && $project->workflow_stage === 'awaiting_admin_validation';
                                    $roundDocuments = $project && $docStage
                                        ? $project->documents->where('stage', $docStage)->where('submission_round', $docRound)
                                        : collect();
                                    $currentDesign = $roundDocuments->firstWhere('document_type', 'design');
                                    $currentRab = $roundDocuments->firstWhere('document_type', 'rab');
                                    $canSend = $isSendableStage && $currentDesign && $currentRab;
                                    $draftDocuments = $project
                                        ? $project->documents->where('stage', 'draft')->where('submission_round', (int) $project->draft_round)
                                        : collect();
                                    $finalDocuments = $project
                                        ? $project->documents->where('stage', 'final')->where('submission_round', (int) $project->final_round)
                                        : collect();
                                    $draftDesign = $draftDocuments->firstWhere('document_type', 'design');
                                    $draftRab = $draftDocuments->firstWhere('document_type', 'rab');
                                    $finalDesign = $finalDocuments->firstWhere('document_type', 'design');
                                    $finalRab = $finalDocuments->firstWhere('document_type', 'rab');
                                    $formatDoc = function ($document) use ($item) {
                                        if (! $document) {
                                            return null;
                                        }

                                        return [
                                            'id' => $document->id,
                                            'name' => $document->original_name,
                                            'size' => \Illuminate\Support\Facades\Storage::disk('local')->exists($document->path)
                                                ? \Illuminate\Support\Facades\Storage::disk('local')->size($document->path)
                                                : null,
                                            'downloadUrl' => route('pemesanan.document.download', [$item->id, $document->id]),
                                            'deleteUrl' => route('admin.pemesanan.document.delete', [$item->id, $document->id]),
                                        ];
                                    };
                                    $decisionHistory = $project
                                        ? $project->documentDecisions->map(fn ($decision) => [
                                            'stage' => $decision->stage,
                                            'round' => $decision->submission_round,
                                            'decision' => $decision->decision,
                                            'feedback' => $decision->feedback,
                                            'customer' => $decision->customer?->nama,
                                            'date' => $decision->created_at->translatedFormat('d M Y, H:i'),
                                            'timestamp' => $decision->created_at->toIso8601String(),
                                        ])->values()->all()
                                        : [];
                                @endphp
                                @php
                                    $projectPayload = [
                                        'id' => $item->id,
                                        'reference' => $reference,
                                        'status' => $item->status,
                                        'stageLabel' => $project
                                            ? ($project->workflow_stage === 'awaiting_admin_validation' ? 'Perlu Ditinjau' : \App\Support\ProjectStageLabel::forPemesanan($project))
                                            : null,
                                        'workflowStage' => $project?->workflow_stage,
                                        'needsValidation' => $needsAdminValidation,
                                        'canFinalize' => $project && $project->workflow_stage === 'approved',
                                        'completeConsultationUrl' => $project?->konsultasi
                                            ? route('admin.pemesanan.konsultasi.complete', $project->konsultasi->id)
                                            : null,
                                        'dpInvoice' => $project?->dpInvoice ? [
                                            'number' => $project->dpInvoice->number,
                                            'amount' => (float) $project->dpInvoice->amount,
                                            'status' => $project->dpInvoice->status,
                                            'dueDate' => $project->dpInvoice->due_date?->translatedFormat('d M Y'),
                                            'evidenceUrl' => $project->dpInvoice->proof_path
                                                ? route('admin.pemesanan.invoice.evidence.download', [$item->id, $project->dpInvoice->id])
                                                : null,
                                        ] : null,
                                        'dpVerifyUrl' => route('admin.pemesanan.dp.verify', $item->id),
                                        'validateUrl' => route('admin.pemesanan.validate.send', $item->id),
                                        'revisionUrl' => route('admin.pemesanan.validate.revision', $item->id),
                                        'bankDisplayName' => config('company.bank.display_name'),
                                        'bankName' => config('company.bank.name'),
                                        'bankAccountNumber' => config('company.bank.account_number'),
                                        'bankAccountHolder' => config('company.bank.account_holder'),
                                        'invoices' => $project ? $project->invoices->map(fn ($invoice) => [
                                            'id' => $invoice->id,
                                            'number' => $invoice->number,
                                            'name' => $invoice->name,
                                            'amount' => (float) $invoice->amount,
                                            'status' => $invoice->status,
                                            'dueDate' => $invoice->due_date?->translatedFormat('d M Y'),
                                            'note' => $invoice->note,
                                            'evidenceUrl' => $invoice->proof_path
                                                ? route('admin.pemesanan.invoice.evidence.download', [$item->id, $invoice->id])
                                                : null,
                                        ])->values()->all() : [],
                                        'invoiceStoreUrl' => route('admin.pemesanan.invoice.store', $item->id),
                                        'invoiceMarkPaidUrlBase' => url('/admin/pemesanan/'.$item->id.'/tagihan'),
                                        'designer' => $item->designer_id,
                                        'note' => $item->note,
                                        'customer' => $item->customer_name,
                                        'email' => $item->customer_email,
                                        'phone' => $item->customer_phone,
                                        'address' => $item->customer_address,
                                        'title' => $title,
                                        'building' => $building,
                                        'area' => $item->area !== null ? (float) $item->area : null,
                                        'budgetLabel' => $budgetLabel,
                                        'description' => $item->detail,
                                        'attachments' => $attachments,
                                        'canManageDocuments' => $canManageDocuments,
                                        'isAwaitingDecision' => $isAwaitingDecision,
                                        'canSend' => $canSend,
                                        'totalHarga' => $project ? (float) $project->total_harga : 0,
                                        'design' => $formatDoc($currentDesign),
                                        'rab' => $formatDoc($currentRab),
                                        'draftDesign' => $formatDoc($draftDesign),
                                        'draftRab' => $formatDoc($draftRab),
                                        'finalDesign' => $formatDoc($finalDesign),
                                        'finalRab' => $formatDoc($finalRab),
                                        'uploadUrl' => route('admin.pemesanan.document.upload', $item->id),
                                        'sendUrl' => route('admin.pemesanan.document.send', $item->id),
                                        'decisions' => $decisionHistory,
                                        'deleteUrl' => url('/admin/pemesanan/'.$item->id),
                                        'targetSelesai' => $project?->target_selesai?->format('Y-m-d'),
                                        'statusHistory' => $project
                                            ? $project->statusTrackings->map(fn ($tracking) => [
                                                'note' => $tracking->catatan,
                                                'date' => $tracking->created_at->translatedFormat('d M Y, H:i'),
                                                'timestamp' => $tracking->created_at->toIso8601String(),
                                            ])->values()->all()
                                            : [],
                                    ];
                                @endphp
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center justify-center rounded-lg bg-slate-950 px-3.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                                        data-project="{{ json_encode($projectPayload, JSON_THROW_ON_ERROR) }}"
                                        onclick="openProjectModal(this)"
                                    >Tinjau Penawaran</button>
                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 px-3.5 text-sm font-semibold text-slate-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700"
                                        data-project="{{ json_encode($projectPayload, JSON_THROW_ON_ERROR) }}"
                                        onclick="openOrderReviewModal(this)"
                                    >Tinjau Pemesanan</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-14 text-center text-sm text-slate-500">Tidak ada permintaan atau pesanan yang sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($workItems->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $workItems->links() }}</div>@endif
</section>

@include('admin.pemesanan._manual_order_modal')
@include('admin.pemesanan._project_modal')
@include('admin.pemesanan._detail_modal')
@include('admin.pemesanan._accept_consultation_modal')
@include('admin.pemesanan._reject_consultation_modal')
@include('admin.pemesanan._consultation_modal')
@endsection

@push('scripts')
<script>
window.customerPicker = function (customers, initialMode, initialId) {
    const selectedCustomer = customers.find(customer => customer.id === String(initialId));

    return {
        customers,
        open: false,
        mode: initialMode === 'new' ? 'new' : 'existing',
        selectedId: selectedCustomer?.id ?? '',
        query: initialMode === 'new'
            ? 'Pelanggan baru'
            : (selectedCustomer ? [selectedCustomer.name, selectedCustomer.email].filter(Boolean).join(' - ') : ''),

        get filteredCustomers() {
            const keyword = this.query.trim().toLowerCase();

            if (!keyword || this.mode === 'new' || this.selectedId) {
                return this.customers.slice(0, 8);
            }

            return this.customers.filter(customer =>
                [customer.name, customer.email, customer.phone]
                    .filter(Boolean)
                    .some(value => value.toLowerCase().includes(keyword))
            ).slice(0, 8);
        },

        clearSelection() {
            this.selectedId = '';
            this.mode = 'existing';
        },

        selectCustomer(customer) {
            this.selectedId = customer.id;
            this.mode = 'existing';
            this.query = [customer.name, customer.email].filter(Boolean).join(' - ');
            this.open = false;
        },

        selectNewCustomer() {
            this.selectedId = '';
            this.mode = 'new';
            this.query = 'Pelanggan baru';
            this.open = false;
        },

        reset() {
            this.selectedId = '';
            this.mode = 'existing';
            this.query = '';
            this.open = true;
        },
    };
};

function openOrderModal() {
    const modal = document.getElementById('orderModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeOrderModal() {
    const modal = document.getElementById('orderModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function formatRupiahInput(input) {
    const digitsOnly = input.value.replace(/\D/g, '');
    input.value = digitsOnly ? new Intl.NumberFormat('id-ID').format(parseInt(digitsOnly, 10)) : '';
}

function getRupiahInputValue(input) {
    return input.value.replace(/\D/g, '') || '0';
}

function setRupiahInputValue(input, amount) {
    input.value = amount > 0 ? new Intl.NumberFormat('id-ID').format(amount) : '';
}


let currentProjectModalData = null;
let currentProjectModalButton = null;

function syncProjectModalButton() {
    if (currentProjectModalButton) {
        currentProjectModalButton.dataset.project = JSON.stringify(currentProjectModalData);
    }
}

function openProjectModal(button) {
    const project = JSON.parse(button.dataset.project);
    currentProjectModalData = project;
    currentProjectModalButton = button;

    document.getElementById('projectForm').action = `{{ url('/admin/proyek') }}/${project.id}`;
    document.getElementById('projectModalReferenceTitle').textContent = `#${project.reference}`;

    const useCardLayout = project.workflowStage && project.workflowStage !== 'konsultasi';
    const needsValidation = project.workflowStage === 'awaiting_admin_validation';
    const canVerifyDp = project.workflowStage === 'dp_verification' && project.dpInvoice;

    const validationView = document.getElementById('projectModalValidationView');
    const standardView = document.getElementById('projectModalStandardView');
    const panel = document.getElementById('projectModalPanel');
    validationView.classList.toggle('hidden', !useCardLayout);
    panel.classList.toggle('max-w-2xl', !useCardLayout);
    panel.classList.toggle('max-w-5xl', useCardLayout);
    document.getElementById('projectModalSubtitle').textContent = needsValidation
        ? 'Tinjau desain awal dan draft RAB, tetapkan penawaran dan kirim ke pelanggan.'
        : useCardLayout
            ? 'Tinjau dokumen, penawaran, dan tagihan proyek.'
            : 'Kelola tahap proses, penugasan desainer, serta desain & RAB.';

    document.getElementById('projectModalValidationInfoBanner').classList.toggle('hidden', !needsValidation);

    const dpVerifyForm = document.getElementById('projectModalDpVerifyForm');
    dpVerifyForm.classList.toggle('hidden', !canVerifyDp);
    if (canVerifyDp) {
        dpVerifyForm.action = project.dpVerifyUrl;
        document.getElementById('projectModalDpInvoiceInfo').textContent =
            `${project.dpInvoice.number} · Rp ${new Intl.NumberFormat('id-ID').format(project.dpInvoice.amount)}${project.dpInvoice.dueDate ? ' · Jatuh tempo ' + project.dpInvoice.dueDate : ''}`;

        const evidenceLink = document.getElementById('projectModalDpEvidenceLink');
        evidenceLink.classList.toggle('hidden', !project.dpInvoice.evidenceUrl);
        evidenceLink.classList.toggle('flex', !!project.dpInvoice.evidenceUrl);
        if (project.dpInvoice.evidenceUrl) evidenceLink.href = project.dpInvoice.evidenceUrl;
    }

    const validationFooter = document.getElementById('projectModalValidationFooter');
    validationFooter.classList.toggle('hidden', !needsValidation);
    validationFooter.classList.toggle('flex', needsValidation);

    if (useCardLayout) {
        renderProjectValidationDocuments(project);

        const revisionFeedback = document.getElementById('projectModalRevisionFeedback');
        const revisionBtn = document.getElementById('projectModalRevisionBtn');
        revisionFeedback.value = '';
        revisionFeedback.classList.add('hidden');
        revisionBtn.dataset.armed = 'false';
        revisionBtn.innerHTML = '<i class="fas fa-comment-dots" aria-hidden="true"></i> Minta Revisi';
    }

    renderProjectBilling();

    const completeForm = document.getElementById('projectCompleteConsultationForm');
    const canCompleteConsultation = project.stageLabel === 'Konsultasi' && project.completeConsultationUrl;
    completeForm.classList.toggle('hidden', !canCompleteConsultation);
    if (canCompleteConsultation) completeForm.action = project.completeConsultationUrl;
    document.getElementById('projectConsultationResult').value = '';

    const historyPanel = document.getElementById('projectModalHistoryPanel');
    historyPanel.replaceChildren();
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
    document.getElementById('projectModalHistoryToggle').onclick = () => historyPanel.classList.toggle('hidden');

    const modal = document.getElementById('projectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function confirmValidateProjectDraft() {
    const totalHarga = getCurrentTotalHarga();
    if (totalHarga <= 0) {
        showModalToast('Buat minimal satu tagihan terlebih dahulu sebelum memvalidasi.', 'error');
        return;
    }

    window.dispatchEvent(new CustomEvent('open-confirmation', {
        detail: {
            title: 'Validasi dan kirim ke pelanggan?',
            message: 'Desain awal dan RAB akan dikirim ke pelanggan untuk ditinjau. Pastikan nilai penawaran dan dokumen sudah benar.',
            confirmLabel: 'Ya, validasi & kirim',
            tone: 'primary',
            onConfirm: () => validateProjectDraft(),
        },
    }));
}

function validateProjectDraft() {
    const project = currentProjectModalData;
    const totalHarga = getCurrentTotalHarga();

    if (totalHarga <= 0) {
        showModalToast('Buat minimal satu tagihan terlebih dahulu sebelum memvalidasi.', 'error');
        return;
    }

    const btn = document.getElementById('projectModalValidateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> Memvalidasi...';

    fetch(project.validateUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json' },
        body: JSON.stringify({ total_harga: totalHarga }),
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                const errorMessage = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal memvalidasi.');
                showModalToast(errorMessage, 'error');
                return;
            }
            showModalToast(json.message || 'Desain divalidasi dan berhasil dikirim.');
            closeProjectModal();
            setTimeout(() => window.location.reload(), 600);
        })
        .catch(() => showModalToast('Gagal memvalidasi. Periksa koneksi Anda.', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane" aria-hidden="true"></i> Validasi &amp; Kirim';
        });
}

function requestProjectValidationRevision() {
    const project = currentProjectModalData;
    const textarea = document.getElementById('projectModalRevisionFeedback');
    const btn = document.getElementById('projectModalRevisionBtn');

    if (btn.dataset.armed !== 'true') {
        textarea.classList.remove('hidden');
        btn.dataset.armed = 'true';
        btn.innerHTML = '<i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim Permintaan Revisi';
        return;
    }

    btn.disabled = true;
    fetch(project.revisionUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json' },
        body: JSON.stringify({ feedback: textarea.value }),
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                showModalToast(json.message || 'Gagal mengirim revisi.', 'error');
                return;
            }
            showModalToast(json.message || 'Permintaan revisi terkirim.');
            closeProjectModal();
            setTimeout(() => window.location.reload(), 600);
        })
        .catch(() => showModalToast('Gagal mengirim revisi. Periksa koneksi Anda.', 'error'))
        .finally(() => { btn.disabled = false; });
}

let invoiceModalContext = 'standard';

function openInvoiceModal(context) {
    const project = currentProjectModalData;
    invoiceModalContext = context;

    document.getElementById('invoiceModalName').value = '';
    document.getElementById('invoiceModalAmount').value = '';
    document.getElementById('invoiceModalDueDate').value = '';
    document.getElementById('invoiceModalNote').value = '';
    document.getElementById('invoiceModalError').classList.add('hidden');

    document.getElementById('invoiceModalBankSelect').innerHTML = `<option>${project.bankName || 'BSI'}</option>`;
    document.getElementById('invoiceModalAccountNumber').value = project.bankAccountNumber || '';
    document.getElementById('invoiceModalAccountHolder').value = project.bankAccountHolder || '';

    const modal = document.getElementById('invoiceModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeInvoiceModal() {
    const modal = document.getElementById('invoiceModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function submitInvoiceModal() {
    const project = currentProjectModalData;
    const name = document.getElementById('invoiceModalName').value.trim();
    const amount = parseInt(getRupiahInputValue(document.getElementById('invoiceModalAmount')), 10) || 0;
    const dueDate = document.getElementById('invoiceModalDueDate').value;
    const note = document.getElementById('invoiceModalNote').value;
    const errorEl = document.getElementById('invoiceModalError');
    errorEl.classList.add('hidden');

    if (!name) {
        errorEl.textContent = 'Nama tagihan wajib diisi.';
        errorEl.classList.remove('hidden');
        return;
    }
    if (amount <= 0) {
        errorEl.textContent = 'Nominal tagihan wajib diisi.';
        errorEl.classList.remove('hidden');
        return;
    }

    const btn = document.getElementById('invoiceModalSubmitBtn');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    fetch(project.invoiceStoreUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, amount, due_date: dueDate || null, note: note || null }),
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                errorEl.textContent = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal membuat tagihan.');
                errorEl.classList.remove('hidden');
                return;
            }
            project.invoices = [json.invoice, ...(project.invoices || [])];
            if (project.needsValidation) {
                project.totalHarga = project.invoices.reduce((sum, invoice) => sum + invoice.amount, 0);
            }
            syncProjectModalButton();
            closeInvoiceModal();
            renderProjectBilling();
            showModalToast(json.message || 'Tagihan berhasil dibuat.');
        })
        .catch(() => { errorEl.textContent = 'Gagal membuat tagihan. Periksa koneksi Anda.'; errorEl.classList.remove('hidden'); })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Tambahkan Tagihan';
        });
}

function confirmMarkProjectInvoicePaid(invoice) {
    window.dispatchEvent(new CustomEvent('open-confirmation', {
        detail: {
            title: 'Tandai tagihan lunas?',
            message: `Tagihan "${invoice.name}" sebesar Rp ${new Intl.NumberFormat('id-ID').format(invoice.amount)} akan ditandai lunas. Pastikan pembayaran benar-benar sudah diterima.`,
            confirmLabel: 'Ya, tandai lunas',
            tone: 'success',
            onConfirm: () => markProjectInvoicePaid(invoice.id),
        },
    }));
}

function markProjectInvoicePaid(invoiceId) {
    const project = currentProjectModalData;

    fetch(`${project.invoiceMarkPaidUrlBase}/${invoiceId}/lunas`, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                showModalToast(json.message || 'Gagal menandai tagihan lunas.', 'error');
                return;
            }
            const invoice = (project.invoices || []).find(item => item.id === invoiceId);
            if (invoice) invoice.status = 'paid';
            syncProjectModalButton();
            renderProjectBilling();
            showModalToast(json.message || 'Tagihan ditandai lunas.');
        })
        .catch(() => showModalToast('Gagal menandai tagihan lunas. Periksa koneksi Anda.', 'error'));
}

function getCurrentTotalHarga() {
    const project = currentProjectModalData;
    if (project.needsValidation) {
        return (project.invoices || []).reduce((sum, invoice) => sum + invoice.amount, 0);
    }
    return project.totalHarga || 0;
}

function invoiceStatusMeta(status) {
    const labels = { pending: 'Belum Dibayar', submitted: 'Menunggu Verifikasi', paid: 'Lunas' };
    const classes = { pending: 'bg-slate-100 text-slate-600', submitted: 'bg-amber-100 text-amber-700', paid: 'bg-emerald-100 text-emerald-700' };
    return { label: labels[status] || status, tone: classes[status] || 'bg-slate-100 text-slate-600' };
}

function renderInvoiceListInto(containerId, invoices, mode) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.replaceChildren();

    if (!invoices.length) {
        container.innerHTML = mode === 'table'
            ? '<tr><td colspan="4" class="px-3 py-4 text-center text-slate-400">Belum ada tagihan.</td></tr>'
            : '<p class="text-xs text-slate-400">Belum ada tagihan untuk proyek ini.</p>';
        return;
    }

    invoices.forEach(invoice => {
        const { label, tone } = invoiceStatusMeta(invoice.status);
        const amountFormatted = new Intl.NumberFormat('id-ID').format(invoice.amount);

        if (mode === 'table') {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-3 py-2">
                    <p class="font-semibold text-slate-800">${invoice.name}</p>
                    <p class="mt-0.5 text-[10px] text-slate-400">${invoice.number}${invoice.dueDate ? ' &middot; ' + invoice.dueDate : ''}</p>
                </td>
                <td class="px-3 py-2 font-semibold text-slate-800">Rp ${amountFormatted}</td>
                <td class="px-3 py-2">
                    <span class="inline-flex rounded-full px-2 py-0.5 font-semibold ${tone}">${label}</span>
                </td>
                <td class="px-3 py-2"></td>`;
            const actionsCell = row.lastElementChild;
            if (invoice.evidenceUrl) {
                const evidenceLink = document.createElement('a');
                evidenceLink.href = invoice.evidenceUrl;
                evidenceLink.target = '_blank';
                evidenceLink.rel = 'noopener';
                evidenceLink.className = 'block whitespace-nowrap text-[11px] font-semibold text-slate-600 hover:underline';
                evidenceLink.innerHTML = '<i class="fas fa-paperclip" aria-hidden="true"></i> Lihat Bukti';
                actionsCell.appendChild(evidenceLink);
            }
            if (invoice.status !== 'paid') {
                const payBtn = document.createElement('button');
                payBtn.type = 'button';
                payBtn.className = 'mt-1 block whitespace-nowrap text-[11px] font-semibold text-emerald-700 hover:underline';
                payBtn.textContent = 'Tandai Lunas';
                payBtn.onclick = () => confirmMarkProjectInvoicePaid(invoice);
                actionsCell.appendChild(payBtn);
            }
            container.appendChild(row);
            return;
        }

        const row = document.createElement('div');
        row.className = 'rounded-lg border border-slate-200 p-2.5 text-xs';
        row.innerHTML = `
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-slate-800">${invoice.name}</p>
                    <p class="mt-0.5 text-slate-400">${invoice.number}${invoice.dueDate ? ' &middot; Jatuh tempo ' + invoice.dueDate : ''}</p>
                    ${invoice.note ? `<p class="mt-1 text-slate-600">${invoice.note}</p>` : ''}
                </div>
                <div class="shrink-0 text-right">
                    <p class="font-semibold text-slate-800">Rp ${amountFormatted}</p>
                    <span class="mt-1 inline-flex rounded-full px-2 py-0.5 font-semibold ${tone}">${label}</span>
                </div>
            </div>`;
        if (invoice.evidenceUrl || invoice.status !== 'paid') {
            const actionsRow = document.createElement('div');
            actionsRow.className = 'mt-2 flex flex-wrap items-center gap-2';
            if (invoice.evidenceUrl) {
                const evidenceLink = document.createElement('a');
                evidenceLink.href = invoice.evidenceUrl;
                evidenceLink.target = '_blank';
                evidenceLink.rel = 'noopener';
                evidenceLink.className = 'inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50';
                evidenceLink.innerHTML = '<i class="fas fa-paperclip" aria-hidden="true"></i> Lihat Bukti';
                actionsRow.appendChild(evidenceLink);
            }
            if (invoice.status !== 'paid') {
                const payBtn = document.createElement('button');
                payBtn.type = 'button';
                payBtn.className = 'inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-50';
                payBtn.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i> Tandai Lunas';
                payBtn.onclick = () => confirmMarkProjectInvoicePaid(invoice);
                actionsRow.appendChild(payBtn);
            }
            row.appendChild(actionsRow);
        }
        container.appendChild(row);
    });
}

function renderProjectValidationDocuments(project) {
    const docTypeMeta = {
        draftDesign: { label: 'Desain Awal', icon: 'fa-file-image', color: 'bg-red-50 text-red-500' },
        draftRab: { label: 'Draft RAB', icon: 'fa-file-lines', color: 'bg-emerald-50 text-emerald-600' },
        finalDesign: { label: 'Desain Final', icon: 'fa-file-image', color: 'bg-red-50 text-red-500' },
        finalRab: { label: 'RAB Final', icon: 'fa-file-lines', color: 'bg-emerald-50 text-emerald-600' },
    };
    const container = document.getElementById('projectModalValidationDocuments');
    container.replaceChildren();

    const entries = Object.entries(docTypeMeta).filter(([key]) => project[key]);
    if (!entries.length) {
        container.innerHTML = '<p class="text-xs text-slate-400">Belum ada dokumen yang diunggah.</p>';
        return;
    }

    entries.forEach(([key, meta]) => {
        const document_ = project[key];
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2.5 rounded-lg border border-slate-200 px-3 py-2';
        row.innerHTML = `
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md ${meta.color}"><i class="fas ${meta.icon} text-xs" aria-hidden="true"></i></span>
            <p class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800">${meta.label}</p>
            <a href="${document_.downloadUrl}" target="_blank" rel="noopener" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" title="Unduh ${meta.label}">
                <i class="fas fa-download text-xs" aria-hidden="true"></i>
            </a>`;
        container.appendChild(row);
    });
}

function renderProjectBilling() {
    const project = currentProjectModalData;
    const invoices = project.invoices || [];
    const totalHarga = getCurrentTotalHarga();
    const totalBilled = invoices.reduce((sum, invoice) => sum + invoice.amount, 0);
    const totalPaid = invoices.filter(invoice => invoice.status === 'paid').reduce((sum, invoice) => sum + invoice.amount, 0);
    const remaining = Math.max(totalHarga - totalPaid, 0);
    const fmt = amount => new Intl.NumberFormat('id-ID').format(amount);

    if (document.getElementById('projectModalValidationBilled')) {
        document.getElementById('projectModalValidationBilled').textContent = 'Rp ' + fmt(totalBilled);
        document.getElementById('projectModalValidationPaid').textContent = 'Rp ' + fmt(totalPaid);
        document.getElementById('projectModalValidationRemaining').textContent = 'Rp ' + fmt(remaining);
        renderInvoiceListInto('projectModalValidationInvoiceList', invoices, 'table');
    }
}

function csrfToken() {
    return document.querySelector('#projectForm input[name="_token"]').value;
}

function showModalToast(message, tone = 'success') {
    showNotificationCard(message, tone);
}

function closeProjectModal() {
    const modal = document.getElementById('projectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    closeInvoiceModal();
}

let currentOrderReviewData = null;

function orderReviewCsrfToken() {
    return document.querySelector('#orderReviewCsrfForm input[name="_token"]').value;
}

function openOrderReviewModal(button) {
    const project = JSON.parse(button.dataset.project);
    currentOrderReviewData = project;

    document.getElementById('orderReviewReference').textContent = `#${project.reference}`;
    document.getElementById('orderReviewStageLabel').textContent = project.stageLabel || 'Proses Proyek';
    document.getElementById('orderReviewDesigner').value = project.designer || '';
    document.getElementById('orderReviewTargetSelesai').value = project.targetSelesai || '';
    document.getElementById('orderReviewError').classList.add('hidden');

    const finalizeField = document.getElementById('orderReviewFinalizeField');
    document.getElementById('orderReviewStatus').value = project.status;
    finalizeField.classList.toggle('hidden', !project.canFinalize);

    const completeBtn = document.getElementById('orderReviewCompleteBtn');
    completeBtn.disabled = project.status === 'selesai';
    completeBtn.innerHTML = project.status === 'selesai'
        ? '<i class="fas fa-circle-check" aria-hidden="true"></i> Proyek Selesai'
        : '<i class="fas fa-circle-check" aria-hidden="true"></i> Selesaikan Proyek';

    const modal = document.getElementById('orderReviewModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeOrderReviewModal() {
    const modal = document.getElementById('orderReviewModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function setOrderReviewStatus(status) {
    document.getElementById('orderReviewStatus').value = status;

    if (status === 'selesai') {
        const reference = currentOrderReviewData?.reference || '';
        window.dispatchEvent(new CustomEvent('open-confirmation', {
            detail: {
                title: 'Selesaikan proyek ini?',
                message: `Proyek ${reference} akan ditandai selesai. Pastikan seluruh pengerjaan dan pembayaran sudah rampung sebelum melanjutkan.`,
                confirmLabel: 'Ya, selesaikan',
                tone: 'success',
                onConfirm: () => saveOrderReview(),
            },
        }));
        return;
    }

    saveOrderReview();
}

function saveOrderReview() {
    const project = currentOrderReviewData;
    const errorEl = document.getElementById('orderReviewError');
    errorEl.classList.add('hidden');

    const saveBtn = document.getElementById('orderReviewSaveBtn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';

    fetch(`{{ url('/admin/proyek') }}/${project.id}`, {
        method: 'PUT',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': orderReviewCsrfToken(), 'Content-Type': 'application/json' },
        body: JSON.stringify({
            status_pemesanan: document.getElementById('orderReviewStatus').value,
            designer_id: document.getElementById('orderReviewDesigner').value || null,
            target_selesai: document.getElementById('orderReviewTargetSelesai').value || null,
        }),
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                errorEl.textContent = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal menyimpan perubahan.');
                errorEl.classList.remove('hidden');
                return;
            }
            showModalToast(json.message || 'Perubahan berhasil disimpan.');
            closeOrderReviewModal();
            setTimeout(() => window.location.reload(), 600);
        })
        .catch(() => { errorEl.textContent = 'Gagal menyimpan perubahan. Periksa koneksi Anda.'; errorEl.classList.remove('hidden'); })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan Perubahan';
        });
}

function confirmDeleteOrderReview() {
    const project = currentOrderReviewData;
    const reference = project?.reference || '';
    window.dispatchEvent(new CustomEvent('open-confirmation', {
        detail: {
            title: 'Hapus pesanan ini?',
            message: `Seluruh data proyek ${reference}, dokumen, dan riwayat konsultasi terkait akan dihapus permanen dan tidak dapat dikembalikan.`,
            confirmLabel: 'Ya, hapus',
            tone: 'danger',
            onConfirm: () => {
                fetch(project.deleteUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': orderReviewCsrfToken(), 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: '_method=DELETE',
                }).then(() => window.location.reload());
            },
        },
    }));
}

function openDetailModal(button) {
    const detail = JSON.parse(button.dataset.detail);
    const contact = [detail.email, detail.phone].filter(Boolean).join(' · ') || 'Belum ada kontak';
    const formatRupiah = value => value > 0 ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value) : 'Belum ditentukan';

    document.getElementById('detailModalType').textContent = `${detail.type} · ${detail.reference}`;
    document.getElementById('detail-modal-title').textContent = detail.title || 'Permintaan desain';
    document.getElementById('detailModalDate').textContent = detail.date;
    document.getElementById('detailModalStatus').textContent = detail.status;
    document.getElementById('detailModalStatus').className = `rounded-full px-3 py-1 text-xs font-semibold ${detail.statusClass}`;
    document.getElementById('detailModalCustomer').textContent = detail.customer || 'Belum diisi';
    document.getElementById('detailModalContact').textContent = contact;
    document.getElementById('detailModalAddress').textContent = detail.address || 'Belum diisi';
    document.getElementById('detailModalProjectType').textContent = detail.title || 'Belum ditentukan';
    document.getElementById('detailModalBuilding').textContent = detail.building || 'Belum ditentukan';
    document.getElementById('detailModalArea').textContent = detail.area !== null ? `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(detail.area)} m²` : 'Belum diisi';
    document.getElementById('detailModalRequestBudget').textContent = detail.budgetLabel || formatRupiah(detail.budget);
    document.getElementById('detailModalDescription').textContent = detail.description || 'Belum ada catatan kebutuhan.';
    const attachmentSection = document.getElementById('detailModalAttachments');
    const attachmentList = document.getElementById('detailModalAttachmentList');
    attachmentList.replaceChildren();
    (detail.attachments || []).forEach(attachment => {
        const link = document.createElement('a');
        link.href = attachment.url;
        link.className = 'flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm font-semibold text-slate-700 transition hover:border-amber-300 hover:bg-amber-50';
        link.innerHTML = '<i class="fas fa-paperclip text-amber-600" aria-hidden="true"></i><span class="min-w-0 flex-1 truncate"></span><i class="fas fa-download text-slate-400" aria-hidden="true"></i>';
        link.querySelector('span').textContent = attachment.name;
        attachmentList.appendChild(link);
    });
    attachmentSection.classList.toggle('hidden', !detail.attachments?.length);
    document.getElementById('detailModalProject').classList.toggle('hidden', !detail.isProject);
    document.getElementById('detailModalProgress').textContent = `${detail.progress}%`;
    document.getElementById('detailModalDesigner').textContent = detail.designer || 'Belum ditetapkan';
    document.getElementById('detailModalTarget').textContent = detail.target || 'Belum ditentukan';
    document.getElementById('detailModalBudget').textContent = formatRupiah(detail.budget);
    document.getElementById('detailModalNoteSection').classList.toggle('hidden', !detail.note);
    document.getElementById('detailModalNote').textContent = detail.note || '';

    const modal = document.getElementById('detailModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('projectModal').addEventListener('click', event => {
    if (event.target.id === 'projectModal') closeProjectModal();
});

document.getElementById('orderModal').addEventListener('click', event => {
    if (event.target.id === 'orderModal') closeOrderModal();
});

document.getElementById('detailModal').addEventListener('click', event => {
    if (event.target.id === 'detailModal') closeDetailModal();
});

@if($errors->manualOrder->any())
openOrderModal();
@endif

(function pollForUpdates() {
    const heartbeatUrl = '{{ route('admin.pemesanan.heartbeat') }}';
    let lastSignal = null;
    let isFirstCheck = true;

    function anyModalOpen() {
        const jsModals = ['projectModal', 'orderModal', 'detailModal', 'invoiceModal', 'orderReviewModal'];
        if (jsModals.some(id => {
            const el = document.getElementById(id);
            return el && !el.classList.contains('hidden');
        })) {
            return true;
        }

        return Array.from(document.querySelectorAll('[x-data]')).some(el => {
            return el._x_dataStack && el._x_dataStack.some(data => data.open === true);
        });
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

    setInterval(check, 15000);
})();
</script>
@endpush
