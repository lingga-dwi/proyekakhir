@extends('layouts.dashboard')

@section('title', 'Kelola Pemesanan - Admin Dashboard')
@section('page-title', 'Kelola Pemesanan')
@section('page-description', 'Tindak lanjuti konsultasi dan pesanan pelanggan dari satu tempat')

@section('content')
@include('admin._work_tabs')
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
                <div><p class="text-sm text-slate-500">{{ $card['label'] }}</p><p class="text-2xl font-bold text-slate-950">{{ $card['value'] }}</p></div>
            </div>
        </article>
    @endforeach
</div>

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-semibold text-slate-950">Permintaan Konsultasi</h2>
            <p class="text-xs text-slate-500">Diproses di halaman ini tanpa membuat menu admin terpisah.</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label for="consultation_status" class="sr-only">Filter konsultasi</label>
            <select id="consultation_status" name="consultation_status" class="rounded-xl border-slate-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="pending" @selected(request('consultation_status') === 'pending')>Menunggu</option>
                <option value="confirmed" @selected(request('consultation_status') === 'confirmed')>Dikonfirmasi</option>
                <option value="completed" @selected(request('consultation_status') === 'completed')>Selesai</option>
                <option value="cancelled" @selected(request('consultation_status') === 'cancelled')>Ditolak</option>
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[860px]">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Jadwal</th>
                    <th class="px-5 py-3 font-medium">Pelanggan</th>
                    <th class="px-5 py-3 font-medium">Kebutuhan</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($consultations as $consultation)
                    @php
                        $consultationStatus = match($consultation->status) {
                            'confirmed' => ['Dikonfirmasi', 'bg-blue-100 text-blue-700'],
                            'completed' => ['Selesai', 'bg-green-100 text-green-700'],
                            'cancelled' => ['Ditolak', 'bg-red-100 text-red-700'],
                            default => ['Menunggu', 'bg-amber-100 text-amber-700'],
                        };
                    @endphp
                    <tr class="align-top hover:bg-slate-50">
                        <td class="px-5 py-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $consultation->tanggal_konsultasi->translatedFormat('d M Y') }}</p>
                            <p class="text-xs text-slate-500">{{ $consultation->waktu_konsultasi->format('H:i') }} WIB</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $consultation->user?->nama ?? $consultation->nama }}</p>
                            <p class="text-xs text-slate-500">{{ $consultation->no_telp }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-slate-900">{{ $consultation->getJenisRuanganLabel() }}</p>
                            <p class="max-w-xs truncate text-xs text-slate-500">{{ $consultation->deskripsi_kebutuhan }}</p>
                        </td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $consultationStatus[1] }}">{{ $consultationStatus[0] }}</span></td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('konsultasi.show', $consultation) }}" class="text-sm font-semibold text-slate-700 hover:text-slate-950">Detail</a>
                                @if($consultation->status === 'pending')
                                    <form method="POST" action="{{ route('admin.pemesanan.konsultasi.update', $consultation) }}">
                                        @csrf @method('PUT')<input type="hidden" name="status" value="confirmed">
                                        <button class="text-sm font-semibold text-blue-700 hover:text-blue-800">Konfirmasi</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pemesanan.konsultasi.update', $consultation) }}" onsubmit="return confirm('Tolak permintaan konsultasi ini?')">
                                        @csrf @method('PUT')<input type="hidden" name="status" value="cancelled">
                                        <button class="text-sm font-semibold text-red-600 hover:text-red-700">Tolak</button>
                                    </form>
                                @elseif($consultation->status === 'confirmed' && ! $consultation->pemesanan_id)
                                    <form method="POST" action="{{ route('admin.pemesanan.konsultasi.convert', $consultation) }}">
                                        @csrf
                                        <button class="rounded-lg bg-amber-400 px-3 py-2 text-xs font-semibold text-slate-950 hover:bg-amber-300">Jadikan proyek</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pemesanan.konsultasi.update', $consultation) }}">
                                        @csrf @method('PUT')<input type="hidden" name="status" value="completed">
                                        <button class="text-sm font-semibold text-green-700 hover:text-green-800">Selesai tanpa proyek</button>
                                    </form>
                                @elseif($consultation->pemesanan_id)
                                    <a href="{{ route('pemesanan.show', $consultation->pemesanan_id) }}" class="text-sm font-semibold text-green-700 hover:text-green-800">Buka proyek</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">Belum ada permintaan konsultasi pada status ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($consultations->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $consultations->links() }}</div>@endif
</section>

<form method="GET" class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row">
    <label class="relative flex-1">
        <span class="sr-only">Cari pesanan</span>
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari ID, pelanggan, atau jenis proyek..." class="w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-4 focus:border-amber-500 focus:ring-amber-500">
    </label>
    <select name="status" class="rounded-xl border-slate-300 px-4 py-2.5 focus:border-amber-500 focus:ring-amber-500">
        <option value="">Semua status</option>
        <option value="pending" @selected(request('status') === 'pending')>Pesanan baru</option>
        <option value="dikonfirmasi" @selected(request('status') === 'dikonfirmasi')>Dikonfirmasi</option>
        <option value="sedang_dikerjakan" @selected(request('status') === 'sedang_dikerjakan')>Sedang dikerjakan</option>
        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
        <option value="dibatalkan" @selected(request('status') === 'dibatalkan')>Dibatalkan</option>
    </select>
    <button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Terapkan</button>
    @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.pemesanan.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Reset</a>
    @endif
</form>

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-semibold text-slate-950">Daftar Pemesanan</h2>
        <p class="text-xs text-slate-500">Menampilkan {{ $pemesanans->firstItem() ?? 0 }}–{{ $pemesanans->lastItem() ?? 0 }} dari {{ $pemesanans->total() }} pesanan</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px]">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Pesanan</th>
                    <th class="px-5 py-3 font-medium">Pelanggan</th>
                    <th class="px-5 py-3 font-medium">Ruang</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Nilai</th>
                    <th class="px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pemesanans as $pesanan)
                    @php
                        $statusLabel = match($pesanan->status_pemesanan) {
                            'pending' => 'Pesanan baru', 'dikonfirmasi' => 'Dikonfirmasi',
                            'sedang_dikerjakan' => 'Sedang dikerjakan', 'selesai' => 'Selesai',
                            'dibatalkan' => 'Dibatalkan', default => ucfirst($pesanan->status_pemesanan),
                        };
                        $statusClass = match($pesanan->status_pemesanan) {
                            'pending' => 'bg-orange-100 text-orange-800', 'dikonfirmasi' => 'bg-blue-100 text-blue-800',
                            'sedang_dikerjakan' => 'bg-purple-100 text-purple-800', 'selesai' => 'bg-green-100 text-green-800',
                            'dibatalkan' => 'bg-red-100 text-red-800', default => 'bg-slate-100 text-slate-700',
                        };
                        $orderValue = $pesanan->invoice?->total_tagihan ?: $pesanan->total_harga;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4">
                            <p class="text-sm font-semibold text-slate-900">DI-{{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-xs text-slate-500">{{ $pesanan->tanggal_pesan?->translatedFormat('d M Y') }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $pesanan->user->nama }}</p>
                            <p class="text-xs text-slate-500">{{ $pesanan->user->email }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-sm text-slate-900">{{ $pesanan->jenis_proyek ?: 'Interior' }}</p>
                            <p class="text-xs text-slate-500">{{ $pesanan->jenis_bangunan ?: '-' }} · {{ $pesanan->luas_area ?: 0 }} m²</p>
                        </td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td class="px-5 py-4 text-sm font-medium text-slate-700">{{ $orderValue > 0 ? 'Rp ' . number_format((float) $orderValue, 0, ',', '.') : 'Belum ditetapkan' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('pemesanan.show', $pesanan) }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800">Detail</a>
                                <button type="button" class="text-sm font-semibold text-blue-700 hover:text-blue-800" onclick="openStatusModal({{ $pesanan->id }}, @js($pesanan->status_pemesanan))">Ubah status</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-slate-500">Tidak ada pesanan yang sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pemesanans->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $pemesanans->links() }}</div>@endif
</section>

<div id="statusModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="status-title">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <h2 id="status-title" class="text-lg font-semibold text-slate-950">Ubah Status Pesanan</h2>
        <form id="statusForm" method="POST" class="mt-5">
            @csrf @method('PUT')
            <label for="statusSelect" class="text-sm font-medium text-slate-700">Status baru</label>
            <select name="status" id="statusSelect" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                <option value="pending">Pesanan baru</option><option value="dikonfirmasi">Dikonfirmasi</option>
                <option value="sedang_dikerjakan">Sedang dikerjakan</option><option value="selesai">Selesai</option><option value="dibatalkan">Dibatalkan</option>
            </select>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeStatusModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openStatusModal(id, status) {
    const modal = document.getElementById('statusModal');
    document.getElementById('statusForm').action = `{{ url('/admin/pemesanan') }}/${id}/status`;
    document.getElementById('statusSelect').value = status;
    modal.classList.remove('hidden'); modal.classList.add('flex');
}
function closeStatusModal() {
    const modal = document.getElementById('statusModal');
    modal.classList.add('hidden'); modal.classList.remove('flex');
}
document.getElementById('statusModal').addEventListener('click', event => { if (event.target.id === 'statusModal') closeStatusModal(); });
</script>
@endpush
