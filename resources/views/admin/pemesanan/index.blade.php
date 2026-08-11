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
                    <option value="consultation_confirmed" @selected(request('stage') === 'consultation_confirmed')>Konsultasi dikonfirmasi</option>
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
                    <th class="px-5 py-3 font-medium">Sumber</th>
                    <th class="px-5 py-3 font-medium">Tahap</th>
                    <th class="px-5 py-3 font-medium">Progres</th>
                    <th class="px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($workItems as $item)
                    @php
                        $isConsultation = $item->item_type === 'consultation';
                        $reference = ($isConsultation ? 'KS-' : 'DI-').str_pad((string) $item->id, 3, '0', STR_PAD_LEFT);
                        $title = $isConsultation ? match($item->title) {
                            'living_room' => 'Rumah Tinggal',
                            'bedroom' => 'Apartemen',
                            'kitchen' => 'Ruko',
                            'bathroom' => 'Kantor',
                            'office' => 'Kafe / Restoran',
                            'whole_house' => 'Lainnya',
                            default => 'Permintaan Konsultasi',
                        } : ($item->title ?: 'Interior');
                        [$statusLabel, $statusClass] = match($item->item_type.':'.$item->status) {
                            'consultation:pending' => ['Konsultasi masuk', 'bg-violet-100 text-violet-700'],
                            'consultation:confirmed' => ['Konsultasi dikonfirmasi', 'bg-blue-100 text-blue-700'],
                            'consultation:completed' => ['Konsultasi selesai', 'bg-green-100 text-green-700'],
                            'consultation:cancelled' => ['Konsultasi ditolak', 'bg-red-100 text-red-700'],
                            'order:pending' => ['Pesanan baru', 'bg-orange-100 text-orange-800'],
                            'order:dikonfirmasi' => ['Dikonfirmasi', 'bg-blue-100 text-blue-800'],
                            'order:sedang_dikerjakan' => ['Sedang dikerjakan', 'bg-purple-100 text-purple-800'],
                            'order:selesai' => ['Selesai', 'bg-green-100 text-green-800'],
                            'order:dibatalkan' => ['Dibatalkan', 'bg-red-100 text-red-800'],
                            default => ['Belum diketahui', 'bg-slate-100 text-slate-700'],
                        };
                        $sourceLabel = match($item->source) {
                            'kantor' => 'Kantor',
                            'whatsapp' => 'WhatsApp',
                            'telepon' => 'Telepon',
                            'instagram' => 'Instagram',
                            default => $isConsultation ? 'Form konsultasi' : 'Website',
                        };
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
                            'title' => $title,
                            'space' => $item->space,
                            'source' => $sourceLabel,
                            'description' => $item->detail,
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
                            <p class="text-sm font-semibold text-slate-900">{{ $reference }}</p>
                            <p class="text-xs text-slate-500">{{ $itemDate->translatedFormat('d M Y') }}</p>
                            @if($isConsultation && $item->scheduled_time)
                                <p class="mt-1 text-[11px] font-medium text-slate-400">{{ substr($item->scheduled_time, 0, 5) }} WIB</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $item->customer_name }}</p>
                            <p class="max-w-[190px] truncate text-xs text-slate-500">{{ $item->customer_email }}</p>
                            @if($item->customer_phone)<p class="mt-1 text-[11px] text-slate-400">{{ $item->customer_phone }}</p>@endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $title }}</p>
                            <p class="max-w-xs truncate text-xs text-slate-500">{{ $item->detail ?: ($item->space ?: 'Belum ada catatan kebutuhan') }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $sourceLabel }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td class="px-5 py-4">
                            @if($isConsultation)
                                <span class="text-xs font-medium text-slate-400">Pra-pesanan</span>
                            @else
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-20 overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full rounded-full bg-amber-400" style="width: {{ $item->progress }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500">{{ $item->progress }}%</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-400">{{ $item->designer_name ?: 'Belum ada desainer' }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($isConsultation)
                                <div class="flex max-w-[230px] flex-wrap items-center gap-3">
                                    <button type="button" data-detail="{{ json_encode($detailPayload, JSON_THROW_ON_ERROR) }}" onclick="openDetailModal(this)" class="text-sm font-semibold text-slate-700 hover:text-slate-950">Detail</button>
                                    @if($item->status === 'pending')
                                        <details class="w-full">
                                            <summary class="cursor-pointer text-sm font-semibold text-blue-700 hover:text-blue-800">Jadwalkan konsultasi</summary>
                                            <form method="POST" action="{{ route('admin.pemesanan.konsultasi.schedule', $item->id) }}" class="mt-3 grid gap-2 rounded-xl bg-slate-50 p-3">
                                                @csrf @method('PUT')
                                                <input type="date" name="tanggal_konsultasi" min="{{ now()->format('Y-m-d') }}" required class="rounded-lg border-slate-300 text-sm">
                                                <input type="time" name="waktu_konsultasi" required class="rounded-lg border-slate-300 text-sm">
                                                <select name="designer_id" required class="rounded-lg border-slate-300 text-sm">
                                                    <option value="">Pilih desainer</option>
                                                    @foreach($designers as $designer)<option value="{{ $designer->id }}">{{ $designer->nama }}</option>@endforeach
                                                </select>
                                                <textarea name="catatan_admin" rows="2" maxlength="2000" placeholder="Catatan jadwal (opsional)" class="rounded-lg border-slate-300 text-sm"></textarea>
                                                <button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">Simpan jadwal</button>
                                            </form>
                                        </details>
                                        <form method="POST" action="{{ route('admin.pemesanan.konsultasi.update', $item->id) }}" onsubmit="return confirm('Tolak permintaan konsultasi ini?')">
                                            @csrf @method('PUT')<input type="hidden" name="status" value="cancelled">
                                            <button class="text-sm font-semibold text-red-600 hover:text-red-700">Tolak</button>
                                        </form>
                                    @endif
                                    @if($item->status === 'confirmed')
                                        <form method="POST" action="{{ route('admin.pemesanan.konsultasi.update', $item->id) }}" onsubmit="return confirm('Konsultasi telah selesai dan siap masuk ke tahap desain awal?')">
                                            @csrf @method('PUT')<input type="hidden" name="status" value="completed">
                                            <button class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Selesai &amp; mulai desain</button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center gap-3">
                                    <button type="button" data-detail="{{ json_encode($detailPayload, JSON_THROW_ON_ERROR) }}" onclick="openDetailModal(this)" class="text-sm font-semibold text-amber-700 hover:text-amber-800">Detail</button>
                                    <button
                                        type="button"
                                        class="text-sm font-semibold text-blue-700 hover:text-blue-800"
                                        data-project="{{ json_encode([
                                            'id' => $item->id,
                                            'status' => $item->status,
                                            'progress' => (int) $item->progress,
                                            'target' => $item->target_selesai,
                                            'designer' => $item->designer_id,
                                            'budget' => (float) $item->total_harga,
                                            'note' => $item->note,
                                        ], JSON_THROW_ON_ERROR) }}"
                                        onclick="openProjectModal(this)"
                                    >Kelola</button>
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

function openProjectModal(button) {
    const project = JSON.parse(button.dataset.project);
    document.getElementById('projectForm').action = `{{ url('/admin/proyek') }}/${project.id}`;
    document.getElementById('projectStatus').value = project.status;
    document.getElementById('projectProgress').value = project.progress ?? 0;
    document.getElementById('projectTarget').value = project.target || '';
    document.getElementById('projectDesigner').value = project.designer || '';
    document.getElementById('projectBudget').value = project.budget > 0 ? project.budget : '';
    document.getElementById('projectNote').value = project.note || '';

    const modal = document.getElementById('projectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeProjectModal() {
    const modal = document.getElementById('projectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openDetailModal(button) {
    const detail = JSON.parse(button.dataset.detail);
    const contact = [detail.email, detail.phone].filter(Boolean).join(' · ') || 'Belum ada kontak';
    const need = [detail.title, detail.space].filter(Boolean).join(' · ') || 'Belum ditentukan';
    const formatRupiah = value => value > 0 ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value) : 'Belum ditentukan';

    document.getElementById('detailModalType').textContent = `${detail.type} · ${detail.reference}`;
    document.getElementById('detail-modal-title').textContent = detail.title || 'Permintaan desain';
    document.getElementById('detailModalDate').textContent = detail.date;
    document.getElementById('detailModalStatus').textContent = detail.status;
    document.getElementById('detailModalStatus').className = `rounded-full px-3 py-1 text-xs font-semibold ${detail.statusClass}`;
    document.getElementById('detailModalCustomer').textContent = detail.customer || 'Belum diisi';
    document.getElementById('detailModalContact').textContent = contact;
    document.getElementById('detailModalNeed').textContent = need;
    document.getElementById('detailModalSource').textContent = detail.source || 'Website';
    document.getElementById('detailModalDescription').textContent = detail.description || 'Belum ada catatan kebutuhan.';
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
