@extends('layouts.dashboard')

@section('title', 'Kelola Pemesanan - Admin Dashboard')
@section('page-title', 'Kelola Pemesanan')
@section('page-description', 'Tindak lanjuti konsultasi dan pesanan pelanggan dari satu tempat')

@section('content')
@php
    $summaryCards = [
        ['label' => 'Konsultasi Menunggu', 'value' => $stats['consultations_pending'], 'tone' => 'bg-violet-100 text-violet-700', 'icon' => 'fa-comments'],
        ['label' => 'Pesanan Baru', 'value' => $stats['pending'], 'tone' => 'bg-orange-100 text-orange-700', 'icon' => 'fa-clock'],
        ['label' => 'Proyek Aktif', 'value' => $stats['active'], 'tone' => 'bg-blue-100 text-blue-700', 'icon' => 'fa-drafting-compass'],
        ['label' => 'Selesai', 'value' => $stats['completed'], 'tone' => 'bg-green-100 text-green-700', 'icon' => 'fa-check-double'],
    ];

    $orderIds = $workItems->where('item_type', 'order')->pluck('id');
    $projectDocumentsById = \App\Models\Pemesanan::whereIn('id', $orderIds)
        ->with(['documents' => fn ($query) => $query->orderByDesc('version'), 'documentDecisions' => fn ($query) => $query->with('customer')->orderByDesc('created_at')])
        ->get()
        ->keyBy('id');
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($summaryCards as $card)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $card['tone'] }}"><i class="fas {{ $card['icon'] }}"></i></span>
                <div>
                    <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-slate-950">{{ $card['value'] }}</p>
                </div>
            </div>
        </article>
    @endforeach
</div>

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
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
        <table class="w-full min-w-[1120px]">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Referensi</th>
                    <th class="px-5 py-3 font-medium">Pelanggan</th>
                    <th class="px-5 py-3 font-medium">Kebutuhan</th>
                    <th class="px-5 py-3 font-medium">Penanggung Jawab</th>
                    <th class="px-5 py-3 font-medium">Tahap</th>
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
                        if ($isConsultation) {
                            [$statusLabel, $statusClass] = match($item->status) {
                                'pending' => [\App\Support\ProjectStageLabel::forKonsultasi(new \App\Models\Konsultasi(['status' => 'pending'])), 'bg-violet-100 text-violet-700'],
                                'confirmed' => [\App\Support\ProjectStageLabel::forKonsultasi(new \App\Models\Konsultasi(['status' => 'confirmed'])), 'bg-blue-100 text-blue-700'],
                                'completed' => ['Konsultasi selesai', 'bg-green-100 text-green-700'],
                                'cancelled' => ['Konsultasi ditolak', 'bg-red-100 text-red-700'],
                                default => ['Belum diketahui', 'bg-slate-100 text-slate-700'],
                            };
                        } else {
                            $statusLabel = \App\Support\ProjectStageLabel::forPemesanan(new \App\Models\Pemesanan([
                                'status_pemesanan' => $item->status,
                                'workflow_stage' => $item->workflow_stage,
                            ]));
                            $statusClass = match(true) {
                                $item->status === 'dibatalkan' => 'bg-red-100 text-red-800',
                                $item->status === 'selesai' => 'bg-green-100 text-green-800',
                                $item->workflow_stage === 'approved' => 'bg-purple-100 text-purple-800',
                                $item->workflow_stage === 'konsultasi' => 'bg-violet-100 text-violet-700',
                                default => 'bg-blue-100 text-blue-800',
                            };
                        }
                        if ($isConsultation && $item->status === 'pending' && $item->accepted_at) {
                            [$statusLabel, $statusClass] = ['Diterima · pilih desainer', 'bg-amber-100 text-amber-800'];
                        }
                        if (! $isConsultation && ! $item->designer_id && ! in_array($item->status, ['selesai', 'dibatalkan'], true)) {
                            [$statusLabel, $statusClass] = ['Menunggu penugasan', 'bg-amber-100 text-amber-800'];
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
                            <p class="text-xs text-slate-500">{{ $itemDate->translatedFormat('d M Y') }}</p>
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
                            <p class="max-w-xs truncate text-xs text-slate-500">{{ $item->detail ?: ($item->space ?: 'Belum ada catatan kebutuhan') }}</p>
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
                                            <form
                                                method="POST"
                                                action="{{ route('admin.pemesanan.konsultasi.update', $item->id) }}"
                                                @submit.prevent="$dispatch('open-confirmation', {
                                                    form: $el,
                                                    title: 'Tolak permintaan konsultasi?',
                                                    message: 'Permintaan ini akan ditandai ditolak dan proses konsultasi tidak dapat dilanjutkan.',
                                                    confirmLabel: 'Ya, tolak',
                                                    tone: 'danger'
                                                })"
                                            >
                                                @csrf @method('PUT')<input type="hidden" name="status" value="cancelled">
                                                <button class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-white px-3.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">Tolak</button>
                                            </form>
                                        @endif
                                    @endif
                                    @if($item->status === 'confirmed')
                                        <span class="text-sm font-medium text-slate-500">Menunggu hasil konsultasi</span>
                                    @endif
                                    @if($item->status === 'completed')
                                        <form
                                            method="POST"
                                            action="{{ route('admin.pemesanan.konsultasi.convert', $item->id) }}"
                                            @submit.prevent="$dispatch('open-confirmation', {
                                                form: $el,
                                                title: 'Lanjutkan menjadi pesanan?',
                                                message: 'Data konsultasi akan diteruskan menjadi pesanan proyek dan dapat dikelola oleh admin.',
                                                confirmLabel: 'Ya, lanjutkan',
                                                tone: 'success'
                                            })"
                                        >
                                            @csrf
                                            <button class="inline-flex h-9 items-center justify-center rounded-lg bg-slate-950 px-3.5 text-sm font-semibold text-white transition hover:bg-slate-800">Lanjutkan ke Pesanan</button>
                                        </form>
                                    @endif
                                    @if($item->status === 'cancelled')
                                        <span class="text-sm text-slate-400">Tidak dilanjutkan</span>
                                    @endif
                                </div>
                            @else
                                @php
                                    $project = $projectDocumentsById[$item->id] ?? null;
                                    $docStage = null;
                                    $docRound = null;
                                    if ($project) {
                                        if (in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'awaiting_draft_approval'], true)) {
                                            $docStage = 'draft';
                                            $docRound = (int) $project->draft_round;
                                        } elseif (in_array($project->workflow_stage, ['final_design', 'awaiting_final_approval'], true)) {
                                            $docStage = 'final';
                                            $docRound = (int) $project->final_round;
                                        }
                                    }
                                    $canManageDocuments = $project && in_array($project->workflow_stage, ['konsultasi', 'draft_design', 'revision_requested', 'final_design'], true);
                                    $isSendableStage = $project && in_array($project->workflow_stage, ['draft_design', 'revision_requested', 'final_design'], true);
                                    $isAwaitingDecision = $project && in_array($project->workflow_stage, ['awaiting_draft_approval', 'awaiting_final_approval'], true);
                                    $roundDocuments = $project && $docStage
                                        ? $project->documents->where('stage', $docStage)->where('submission_round', $docRound)
                                        : collect();
                                    $currentDesign = $roundDocuments->firstWhere('document_type', 'design');
                                    $currentRab = $roundDocuments->firstWhere('document_type', 'rab');
                                    $canSend = $isSendableStage && $currentDesign && $currentRab;
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
                                        ])->values()->all()
                                        : [];
                                @endphp
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center justify-center rounded-lg bg-slate-950 px-3.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                                        data-project="{{ json_encode([
                                            'id' => $item->id,
                                            'reference' => $reference,
                                            'status' => $item->status,
                                            'stageLabel' => $project ? \App\Support\ProjectStageLabel::forPemesanan($project) : null,
                                            'canFinalize' => $project && $project->workflow_stage === 'approved',
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
                                            'showUrl' => route('admin.pemesanan.show', $item->id),
                                            'canManageDocuments' => $canManageDocuments,
                                            'isAwaitingDecision' => $isAwaitingDecision,
                                            'canSend' => $canSend,
                                            'totalHarga' => $project ? (float) $project->total_harga : 0,
                                            'design' => $formatDoc($currentDesign),
                                            'rab' => $formatDoc($currentRab),
                                            'uploadUrl' => route('admin.pemesanan.document.upload', $item->id),
                                            'sendUrl' => route('admin.pemesanan.document.send', $item->id),
                                            'decisions' => $decisionHistory,
                                        ], JSON_THROW_ON_ERROR) }}"
                                        onclick="openProjectModal(this)"
                                    >Kelola Pesanan</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-slate-500">Tidak ada permintaan atau pesanan yang sesuai filter.</td></tr>
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
    document.getElementById('projectModalReference').textContent = project.reference;
    document.getElementById('projectModalCustomer').textContent = project.customer || 'Belum diisi';
    document.getElementById('projectModalPhone').textContent = project.phone || 'Belum diisi';
    document.getElementById('projectModalEmail').textContent = project.email || 'Belum diisi';
    document.getElementById('projectModalAddress').textContent = project.address || 'Belum diisi';
    document.getElementById('projectModalTitle').textContent = project.title || 'Belum ditentukan';
    document.getElementById('projectModalBuilding').textContent = project.building || 'Belum ditentukan';
    document.getElementById('projectModalArea').textContent = project.area !== null ? `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(project.area)} m²` : 'Belum diisi';
    document.getElementById('projectModalBudgetLabel').textContent = project.budgetLabel || 'Belum ditentukan';
    document.getElementById('projectModalDescription').textContent = project.description || 'Belum ada catatan kebutuhan.';

    const attachmentSection = document.getElementById('projectModalAttachments');
    const attachmentList = document.getElementById('projectModalAttachmentList');
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

    document.getElementById('projectModalStageLabel').textContent = project.stageLabel || 'Proses Proyek';
    const finalizeOptions = { dikonfirmasi: 'Pengerjaan', sedang_dikerjakan: 'Sedang dikerjakan', selesai: 'Selesai' };
    const finalizeField = document.getElementById('projectFinalizeField');
    const statusSelect = document.getElementById('projectStatus');
    finalizeField.classList.toggle('hidden', !project.canFinalize);
    if (project.canFinalize) {
        statusSelect.replaceChildren();
        Object.entries(finalizeOptions).forEach(([value, label]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            statusSelect.appendChild(option);
        });
        statusSelect.value = project.status;
    } else {
        statusSelect.replaceChildren(new Option('', project.status));
        statusSelect.value = project.status;
    }
    document.getElementById('projectDesigner').value = project.designer || '';
    document.getElementById('projectModalReferenceTitle').textContent = `#${project.reference}`;
    document.getElementById('projectModalDetailLink').href = project.showUrl;
    setRupiahInputValue(document.getElementById('projectTotalHarga'), project.totalHarga);

    renderDocumentSlot('Design', 'design');
    renderDocumentSlot('Rab', 'rab');
    updateDocStatus();
    updateSendButtonState();

    const historyPanel = document.getElementById('projectModalHistoryPanel');
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
    document.getElementById('projectModalHistoryToggle').onclick = () => historyPanel.classList.toggle('hidden');

    clearTimeout(projectModalToastTimer);
    document.getElementById('projectModalToast').classList.add('hidden');

    const modal = document.getElementById('projectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function updateDocStatus() {
    const project = currentProjectModalData;
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
    document.getElementById('projectModalDocStatus').textContent = docStatus;
}

function updateSendButtonState() {
    const project = currentProjectModalData;
    const sendBtn = document.getElementById('projectModalSendBtn');
    const infoBox = document.getElementById('projectModalSendInfo');
    const infoText = document.getElementById('projectModalSendInfoText');

    sendBtn.classList.toggle('hidden', project.isAwaitingDecision || !project.canManageDocuments);

    const isDraftStage = !project.stageLabel || project.stageLabel === 'Menunggu Desain Awal & Draft RAB';
    const priceMissing = isDraftStage && !(project.totalHarga > 0);
    sendBtn.disabled = !project.canSend || priceMissing;

    if (project.isAwaitingDecision) {
        infoBox.classList.add('hidden');
        infoBox.classList.remove('flex');
    } else if (project.stageLabel === 'Konsultasi') {
        infoText.textContent = 'Selesaikan konsultasi terlebih dahulu sebelum dokumen dapat dikirim ke pelanggan. File yang diunggah di sini tetap tersimpan sebagai cadangan untuk desainer.';
        infoBox.classList.remove('hidden');
        infoBox.classList.add('flex');
    } else if (!project.canSend) {
        infoText.textContent = 'Unggah desain dan RAB terlebih dahulu sebelum mengirim ke pelanggan.';
        infoBox.classList.remove('hidden');
        infoBox.classList.add('flex');
    } else if (priceMissing) {
        infoText.textContent = 'Isi Nilai Penawaran terlebih dahulu sebelum mengirim ke pelanggan.';
        infoBox.classList.remove('hidden');
        infoBox.classList.add('flex');
    } else {
        infoText.textContent = 'Setelah pelanggan menyetujui desain ini, sistem akan menampilkan pembayaran DP 20%.';
        infoBox.classList.remove('hidden');
        infoBox.classList.add('flex');
    }
}

function sendProjectDocuments() {
    const project = currentProjectModalData;
    const sendBtn = document.getElementById('projectModalSendBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> Mengirim...';

    fetch(project.sendUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                showModalToast(json.message || 'Gagal mengirim ke pelanggan.', 'error');
                return;
            }
            applyDocumentResponse(json);
        })
        .catch(() => showModalToast('Gagal mengirim ke pelanggan. Periksa koneksi Anda.', 'error'))
        .finally(() => {
            sendBtn.disabled = !project.canSend;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim ke Pelanggan';
            updateSendButtonState();
        });
}

function renderDocumentSlot(cap, type) {
    const uploadForm = document.getElementById(`project${cap}UploadForm`);
    const dropzone = document.getElementById(`project${cap}Dropzone`);
    const input = document.getElementById(`project${cap}Input`);
    const fileRow = document.getElementById(`project${cap}FileRow`);
    const deleteBtn = document.getElementById(`project${cap}DeleteBtn`);
    const project = currentProjectModalData;

    uploadForm.action = project.uploadUrl;
    input.onchange = () => { if (input.files.length) submitDocument(uploadForm, input.files[0], type); };
    dropzone.ondragover = event => { event.preventDefault(); dropzone.classList.add('border-amber-400', 'bg-amber-50'); };
    dropzone.ondragleave = () => dropzone.classList.remove('border-amber-400', 'bg-amber-50');
    dropzone.ondrop = event => {
        event.preventDefault();
        dropzone.classList.remove('border-amber-400', 'bg-amber-50');
        if (event.dataTransfer.files.length) submitDocument(uploadForm, event.dataTransfer.files[0], type);
    };

    const document_ = project[type];
    const canUpload = project.canManageDocuments;
    const canDelete = project.canManageDocuments || project.isAwaitingDecision;
    dropzone.parentElement.classList.toggle('hidden', !canUpload);

    if (document_) {
        document.getElementById(`project${cap}FileName`).textContent = document_.name;
        document.getElementById(`project${cap}FileSize`).textContent = formatFileSize(document_.size);
        document.getElementById(`project${cap}Download`).href = document_.downloadUrl;
        deleteBtn.classList.toggle('hidden', !canDelete);
        deleteBtn.onclick = () => {
            window.dispatchEvent(new CustomEvent('open-confirmation', {
                detail: {
                    title: 'Hapus dokumen ini?',
                    message: `File "${document_.name}" akan dihapus permanen dan tidak dapat dikembalikan.`,
                    confirmLabel: 'Ya, hapus',
                    tone: 'danger',
                    onConfirm: () => deleteDocumentRequest(document_.deleteUrl, cap, type),
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

function csrfToken() {
    return document.querySelector('#projectForm input[name="_token"]').value;
}

let projectModalToastTimer = null;
function showModalToast(message, tone = 'success') {
    const toast = document.getElementById('projectModalToast');
    toast.textContent = message;
    toast.className = tone === 'success'
        ? 'absolute left-1/2 top-4 z-10 flex w-[min(90%,26rem)] -translate-x-1/2 items-center justify-center rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-center text-sm font-semibold text-green-800 shadow-lg'
        : 'absolute left-1/2 top-4 z-10 flex w-[min(90%,26rem)] -translate-x-1/2 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-center text-sm font-semibold text-red-800 shadow-lg';
    clearTimeout(projectModalToastTimer);
    projectModalToastTimer = setTimeout(() => toast.classList.add('hidden'), 4000);
}

function applyDocumentResponse(json) {
    currentProjectModalData[json.documentType] = json.document;
    currentProjectModalData.canManageDocuments = json.canManageDocuments;
    currentProjectModalData.isAwaitingDecision = json.isAwaitingDecision;
    currentProjectModalData.canSend = json.canSend;
    currentProjectModalData.totalHarga = json.totalHarga;
    renderDocumentSlot('Design', 'design');
    renderDocumentSlot('Rab', 'rab');
    updateDocStatus();
    updateSendButtonState();
    syncProjectModalButton();
    showModalToast(json.message || 'Berhasil disimpan.');
}

function submitDocument(form, file, type) {
    const formData = new FormData();
    formData.append('_token', csrfToken());
    formData.append('document_type', type);
    formData.append('document', file);

    fetch(form.action, { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                showModalToast(json.message || 'Gagal mengunggah dokumen.', 'error');
                return;
            }
            applyDocumentResponse(json);
        })
        .catch(() => showModalToast('Gagal mengunggah dokumen. Periksa koneksi Anda.', 'error'));
}

function deleteDocumentRequest(url, cap, type) {
    return fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() } })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                showModalToast(json.message || 'Gagal menghapus dokumen.', 'error');
                return;
            }
            applyDocumentResponse(json);
        })
        .catch(() => showModalToast('Gagal menghapus dokumen. Periksa koneksi Anda.', 'error'));
}

function saveProjectChanges() {
    const saveBtn = document.getElementById('projectModalSaveBtn');
    const form = document.getElementById('projectForm');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';

    fetch(form.action, {
        method: 'PUT',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json' },
        body: JSON.stringify({
            status_pemesanan: document.getElementById('projectStatus').value,
            designer_id: document.getElementById('projectDesigner').value || null,
            total_harga: getRupiahInputValue(document.getElementById('projectTotalHarga')),
        }),
    })
        .then(response => response.json().then(json => ({ ok: response.ok, json })))
        .then(({ ok, json }) => {
            if (!ok) {
                const errorMessage = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Gagal menyimpan perubahan.');
                showModalToast(errorMessage, 'error');
                return;
            }
            currentProjectModalData.status = document.getElementById('projectStatus').value;
            currentProjectModalData.designer = document.getElementById('projectDesigner').value || null;
            currentProjectModalData.totalHarga = parseInt(getRupiahInputValue(document.getElementById('projectTotalHarga')), 10) || 0;
            updateSendButtonState();
            syncProjectModalButton();
            showModalToast(json.message || 'Perubahan berhasil disimpan.');
        })
        .catch(() => showModalToast('Gagal menyimpan perubahan. Periksa koneksi Anda.', 'error'))
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan Perubahan';
        });
}

function formatFileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

function closeProjectModal() {
    const modal = document.getElementById('projectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
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

document.getElementById('projectStatus').addEventListener('change', event => {
    if (event.target.value === 'selesai') document.getElementById('projectProgress').value = 100;
});

@if($errors->manualOrder->any())
openOrderModal();
@endif
</script>
@endpush
