@extends('layouts.dashboard')

@section('title', 'Status Proyek - Admin Dashboard')
@section('page-title', 'Status Proyek')
@section('page-description', 'Kelola desainer, target, progres, dan status proyek interior')

@section('content')
@include('admin._work_tabs')
@php
    $cards = [
        ['label' => 'Persiapan', 'value' => $projectStats['persiapan'], 'note' => 'Pending atau dikonfirmasi', 'tone' => 'bg-orange-100 text-orange-700', 'icon' => 'fa-clipboard-list'],
        ['label' => 'Desain / Produksi', 'value' => $projectStats['tahap_desain_produksi'], 'note' => 'Sedang dikerjakan', 'tone' => 'bg-blue-100 text-blue-700', 'icon' => 'fa-drafting-compass'],
        ['label' => 'Proyek Selesai', 'value' => $projectStats['selesai'], 'note' => 'Pekerjaan dituntaskan', 'tone' => 'bg-green-100 text-green-700', 'icon' => 'fa-check-circle'],
        ['label' => 'Dibatalkan', 'value' => $projectStats['dibatalkan'], 'note' => 'Tidak dilanjutkan', 'tone' => 'bg-red-100 text-red-700', 'icon' => 'fa-ban'],
    ];
    $statusLabels = [
        'pending' => 'Persiapan',
        'dikonfirmasi' => 'Dikonfirmasi',
        'sedang_dikerjakan' => 'Desain / Produksi',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($cards as $card)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $card['tone'] }}"><i class="fas {{ $card['icon'] }}"></i></span>
                <div><p class="text-sm text-slate-500">{{ $card['label'] }}</p><p class="text-2xl font-bold text-slate-950">{{ $card['value'] }}</p><p class="text-xs text-slate-400">{{ $card['note'] }}</p></div>
            </div>
        </article>
    @endforeach
</div>

<form method="GET" class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[minmax(0,1fr)_220px_220px_auto_auto]">
    <label class="relative"><span class="sr-only">Cari proyek</span><i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari proyek atau pelanggan..." class="w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-4 focus:border-amber-500 focus:ring-amber-500">
    </label>
    <select name="status" class="rounded-xl border-slate-300 px-4 py-2.5 focus:border-amber-500 focus:ring-amber-500">
        <option value="">Semua status</option>
        @foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
    </select>
    <select name="designer" class="rounded-xl border-slate-300 px-4 py-2.5 focus:border-amber-500 focus:ring-amber-500">
        <option value="">Semua desainer</option>
        @foreach($designers as $designer)<option value="{{ $designer->id }}" @selected((string) request('designer') === (string) $designer->id)>{{ $designer->nama }}</option>@endforeach
    </select>
    <button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Terapkan</button>
    @if(request()->hasAny(['search', 'status', 'designer']))<a href="{{ route('admin.proyek.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Reset</a>@endif
</form>

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="font-semibold text-slate-950">Semua Proyek</h2>
        <p class="text-xs text-slate-500">Menampilkan {{ $proyek->firstItem() ?? 0 }}-{{ $proyek->lastItem() ?? 0 }} dari {{ $proyek->total() }} proyek</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1120px]">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr>
                <th class="px-5 py-3 font-medium">Proyek</th><th class="px-5 py-3 font-medium">Klien</th><th class="px-5 py-3 font-medium">Budget</th>
                <th class="px-5 py-3 font-medium">Desainer</th><th class="px-5 py-3 font-medium">Target</th><th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Progres</th><th class="px-5 py-3 font-medium">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($proyek as $project)
                    @php
                        $statusClass = match($project->status_pemesanan) {
                            'pending' => 'bg-orange-100 text-orange-800', 'dikonfirmasi' => 'bg-cyan-100 text-cyan-800',
                            'sedang_dikerjakan' => 'bg-blue-100 text-blue-800', 'selesai' => 'bg-green-100 text-green-800',
                            'dibatalkan' => 'bg-red-100 text-red-800', default => 'bg-slate-100 text-slate-700'
                        };
                        $barClass = match($project->status_pemesanan) {
                            'selesai' => 'bg-green-500', 'dibatalkan' => 'bg-red-400', 'sedang_dikerjakan' => 'bg-blue-500', default => 'bg-amber-400'
                        };
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4"><p class="text-sm font-semibold text-slate-900">{{ $project->jenis_proyek ?: 'Proyek Interior' }}</p><p class="text-xs text-slate-500">PRJ-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}{{ $project->jenis_bangunan ? ' - '.$project->jenis_bangunan : '' }}</p></td>
                        <td class="px-5 py-4"><p class="text-sm font-medium text-slate-900">{{ $project->user->nama }}</p><p class="text-xs text-slate-500">{{ $project->user->email }}</p></td>
                        <td class="px-5 py-4 text-sm text-slate-700">{{ (float) $project->total_harga > 0 ? 'Rp '.number_format((float) $project->total_harga, 0, ',', '.') : 'Belum ditentukan' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700">{{ $project->designer?->nama ?? 'Belum ditetapkan' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-700">{{ $project->target_selesai?->translatedFormat('d M Y') ?? 'Belum ditetapkan' }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ $statusLabels[$project->status_pemesanan] ?? $project->status_pemesanan }}</span></td>
                        <td class="px-5 py-4"><div class="flex items-center gap-2"><div class="h-2 w-24 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full {{ $barClass }}" style="width: {{ $project->progress }}%"></div></div><span class="text-xs font-semibold text-slate-600">{{ $project->progress }}%</span></div></td>
                        <td class="px-5 py-4"><div class="flex items-center gap-3">
                            <a href="{{ route('pemesanan.show', $project->id) }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800">Detail</a>
                            <button type="button" class="text-sm font-semibold text-blue-700 hover:text-blue-800" onclick='openProjectModal({{ $project->id }}, @js($project->status_pemesanan), {{ $project->progress }}, @js(optional($project->target_selesai)->format('Y-m-d')), @js($project->designer_id), @js((float) $project->total_harga), @js($project->catatan_progres))'>Update</button>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-14 text-center text-sm text-slate-500">Tidak ada proyek yang sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($proyek->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $proyek->links() }}</div>@endif
</section>

<div id="projectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="project-modal-title">
    <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between"><h2 id="project-modal-title" class="text-lg font-semibold text-slate-950">Update Progres Proyek</h2><button type="button" onclick="closeProjectModal()" class="h-9 w-9 rounded-full text-slate-500 hover:bg-slate-100"><i class="fas fa-times"></i></button></div>
        <form id="projectForm" method="POST" class="mt-5 grid gap-4 sm:grid-cols-2">@csrf @method('PUT')
            <label class="block"><span class="text-sm font-medium text-slate-700">Status</span><select id="projectStatus" name="status_pemesanan" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">@foreach($statusLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
            <label class="block"><span class="text-sm font-medium text-slate-700">Progres (%)</span><input id="projectProgress" name="progress" type="number" min="0" max="100" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"></label>
            <label class="block"><span class="text-sm font-medium text-slate-700">Target selesai</span><input id="projectTarget" name="target_selesai" type="date" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"></label>
            <label class="block"><span class="text-sm font-medium text-slate-700">Desainer</span><select id="projectDesigner" name="designer_id" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"><option value="">Belum ditetapkan</option>@foreach($designers as $designer)<option value="{{ $designer->id }}">{{ $designer->nama }}</option>@endforeach</select></label>
            <label class="block sm:col-span-2"><span class="text-sm font-medium text-slate-700">Budget</span><input id="projectBudget" name="total_harga" type="number" min="0" step="1000" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"></label>
            <label class="block sm:col-span-2"><span class="text-sm font-medium text-slate-700">Catatan progres</span><textarea id="projectNote" name="catatan_progres" rows="4" maxlength="2000" class="mt-2 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500" placeholder="Contoh: Tahap desain awal telah disetujui klien"></textarea></label>
            <div class="flex justify-end gap-3 sm:col-span-2"><button type="button" onclick="closeProjectModal()" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button><button class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">Simpan Perubahan</button></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openProjectModal(id, status, progress, target, designer, budget, note) {
    document.getElementById('projectForm').action = `{{ url('/admin/proyek') }}/${id}`;
    document.getElementById('projectStatus').value = status;
    document.getElementById('projectProgress').value = progress;
    document.getElementById('projectTarget').value = target || '';
    document.getElementById('projectDesigner').value = designer || '';
    document.getElementById('projectBudget').value = budget > 0 ? budget : '';
    document.getElementById('projectNote').value = note || '';
    const modal = document.getElementById('projectModal'); modal.classList.remove('hidden'); modal.classList.add('flex');
}
function closeProjectModal() { const modal = document.getElementById('projectModal'); modal.classList.add('hidden'); modal.classList.remove('flex'); }
document.getElementById('projectModal').addEventListener('click', event => { if (event.target.id === 'projectModal') closeProjectModal(); });
document.getElementById('projectStatus').addEventListener('change', event => { if (event.target.value === 'selesai') document.getElementById('projectProgress').value = 100; });
</script>
@endpush
