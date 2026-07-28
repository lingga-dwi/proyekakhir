@extends('layouts.dashboard')

@section('title', 'Kelola Katalog - Admin Dashboard')
@section('page-title', 'Kelola Katalog')
@section('page-description', 'Kelola portofolio, kelengkapan, dan status publikasi desain')

@section('content')
@php
    $activeStatus = request('status', 'all');
    $statusTabs = [
        'all' => ['label' => 'Semua', 'count' => $stats['all']],
        'published' => ['label' => 'Publik', 'count' => $stats['published']],
        'draft' => ['label' => 'Draft', 'count' => $stats['draft']],
        'archived' => ['label' => 'Arsip', 'count' => $stats['archived']],
    ];
@endphp

@if($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <p class="font-semibold">Aksi tidak dapat diproses.</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-sm font-medium text-slate-600"><span class="font-semibold text-slate-950">{{ $stats['all'] }}</span> katalog aktif</p>
        <p class="mt-1 text-xs text-slate-400">Terakhir diubah ditampilkan lebih dahulu.</p>
    </div>
    <a href="{{ route('admin.katalog.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-400 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
        <i class="fas fa-plus mr-2"></i>Tambah Katalog
    </a>
</div>

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <nav class="flex gap-1 overflow-x-auto border-b border-slate-200 px-4 pt-3" aria-label="Status katalog">
        @foreach($statusTabs as $status => $tab)
            @php
                $tabQuery = request()->except(['page', 'status']);
                if ($status !== 'all') {
                    $tabQuery['status'] = $status;
                }
            @endphp
            <a href="{{ route('admin.katalog.index', $tabQuery) }}"
               class="whitespace-nowrap border-b-2 px-3 py-3 text-sm font-semibold transition {{ $activeStatus === $status ? 'border-amber-400 text-slate-950' : 'border-transparent text-slate-500 hover:border-slate-200 hover:text-slate-800' }}">
                {{ $tab['label'] }}
                <span class="ml-1 rounded-full px-2 py-0.5 text-xs {{ $activeStatus === $status ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500' }}">{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </nav>

    <form method="GET" class="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row lg:items-center">
        @if($activeStatus !== 'all')
            <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif
        <label class="relative min-w-0 flex-1">
            <span class="sr-only">Cari katalog</span>
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau deskripsi..." class="w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-4 text-sm focus:border-amber-500 focus:ring-amber-500">
        </label>
        <select name="category" class="rounded-xl border-slate-300 px-3 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500 lg:w-48" aria-label="Filter kategori">
            <option value="">Semua kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="completeness" class="rounded-xl border-slate-300 px-3 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500 lg:w-52" aria-label="Filter kelengkapan katalog">
            <option value="">Semua kelengkapan</option>
            <option value="complete" @selected(request('completeness') === 'complete')>Lengkap ({{ $stats['complete'] }})</option>
            <option value="incomplete" @selected(request('completeness') === 'incomplete')>Perlu dilengkapi ({{ $stats['incomplete'] }})</option>
        </select>
        <select name="sort" class="rounded-xl border-slate-300 px-3 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500 lg:w-44" aria-label="Urutkan katalog">
            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Terakhir diubah</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Paling lama diubah</option>
            <option value="name_asc" @selected(request('sort') === 'name_asc')>Nama A-Z</option>
            <option value="name_desc" @selected(request('sort') === 'name_desc')>Nama Z-A</option>
        </select>
        <div class="flex gap-2">
            <button class="inline-flex flex-1 items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Terapkan</button>
            @if(request()->hasAny(['search', 'category', 'completeness', 'sort', 'status']))
                <a href="{{ route('admin.katalog.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50" title="Reset filter"><i class="fas fa-redo-alt"></i></a>
            @endif
        </div>
    </form>

    <form id="bulkCatalogForm" action="{{ route('admin.katalog.bulk-action') }}" method="POST" class="hidden border-b border-amber-200 bg-amber-50 px-4 py-3">
        @csrf
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <p class="min-w-36 text-sm font-semibold text-amber-950"><span id="selectedCatalogCount">0</span> katalog dipilih</p>
            <select id="bulkAction" name="action" class="rounded-lg border-amber-300 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" required>
                <option value="">Pilih aksi</option>
                <option value="publish">Publikasikan</option>
                <option value="draft">Jadikan draft</option>
                <option value="archive">Arsipkan</option>
                <option value="change_category">Ubah kategori</option>
            </select>
            <select id="bulkCategory" name="category_id" class="hidden rounded-lg border-amber-300 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Pilih kategori baru</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">Terapkan Aksi</button>
            <button id="clearCatalogSelection" type="button" class="text-sm font-medium text-slate-600 hover:text-slate-950">Batalkan pilihan</button>
        </div>
    </form>

    <div class="overflow-x-auto lg:overflow-visible">
        <table class="w-full min-w-[860px] text-left">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="w-12 px-4 py-3">
                        <input id="selectAllCatalogs" type="checkbox" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500" aria-label="Pilih semua katalog di halaman ini">
                    </th>
                    <th class="px-3 py-3">Desain</th>
                    <th class="px-3 py-3">Kategori</th>
                    <th class="px-3 py-3">Kelengkapan</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Terakhir Diubah</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($katalogs as $katalog)
                    @php
                        $categoryName = $katalog->category?->name ?: 'Tanpa kategori';
                        $imageCount = ($katalog->gambar_utama ? 1 : 0) + count($katalog->galeri_gambar ?? []);
                        $isComplete = $katalog->isCompleteForPublication();
                    @endphp
                    <tr class="group transition hover:bg-slate-50/80">
                        <td class="px-4 py-3 align-middle">
                            <input type="checkbox" name="ids[]" value="{{ $katalog->id }}" form="bulkCatalogForm" class="catalog-checkbox rounded border-slate-300 text-amber-500 focus:ring-amber-500" aria-label="Pilih {{ $katalog->nama_desain }}">
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex min-w-64 items-center gap-3">
                                @if($katalog->gambar_utama && $katalog->gambar_utama_url)
                                    <img src="{{ $katalog->gambar_utama_url }}" alt="" class="h-14 w-20 shrink-0 rounded-lg border border-slate-200 object-cover" loading="lazy">
                                @else
                                    <span class="flex h-14 w-20 shrink-0 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-slate-300"><i class="fas fa-image"></i></span>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.katalog.edit', $katalog) }}" class="line-clamp-1 font-semibold text-slate-950 hover:text-amber-700">{{ $katalog->nama_desain }}</a>
                                    <p class="mt-1 text-xs text-slate-400">ID #{{ $katalog->id }} <span class="mx-1">&middot;</span> {{ $imageCount }} gambar</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-600">{{ $categoryName }}</td>
                        <td class="px-3 py-3">
                            @if($isComplete)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"><i class="fas fa-check mr-1.5"></i>Lengkap</span>
                            @else
                                <a href="{{ route('admin.katalog.edit', $katalog) }}" class="inline-flex items-center rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 hover:bg-orange-100"><i class="fas fa-exclamation-circle mr-1.5"></i>Perlu dilengkapi</a>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            @if($katalog->status === 'published')
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700"><span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-blue-500"></span>Dipublikasikan</span>
                            @elseif($katalog->status === 'archived')
                                <span class="inline-flex items-center rounded-full bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-600"><i class="fas fa-archive mr-1.5 text-[10px]"></i>Diarsipkan</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"><span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-slate-400"></span>Draft</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-500">
                            <span class="block">{{ $katalog->updated_at->format('d M Y') }}</span>
                            <span class="text-xs text-slate-400">{{ $katalog->updated_at->format('H:i') }} WIB</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5" x-data="{ menuOpen: false }">
                                @if($katalog->status === 'published')
                                    <a href="{{ route('katalog.detail', $katalog) }}" target="_blank" rel="noopener" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Lihat di website" aria-label="Lihat {{ $katalog->nama_desain }} di website"><i class="fas fa-eye text-xs"></i></a>
                                @endif
                                <a href="{{ route('admin.katalog.edit', $katalog) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800" title="Edit katalog" aria-label="Edit {{ $katalog->nama_desain }}"><i class="fas fa-pencil-alt text-xs"></i></a>
                                <div class="relative">
                                    <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-100" aria-label="Tindakan lainnya untuk {{ $katalog->nama_desain }}"><i class="fas fa-ellipsis-v text-xs"></i></button>
                                    <div x-cloak x-show="menuOpen" x-transition.origin.top.right @click.outside="menuOpen = false" class="absolute right-0 z-30 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                                        @if($katalog->status === 'published')
                                            <form action="{{ route('admin.katalog.bulk-action') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="ids[]" value="{{ $katalog->id }}">
                                                <input type="hidden" name="action" value="draft">
                                                <button class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50"><i class="fas fa-file-alt w-4 text-slate-400"></i>Jadikan draft</button>
                                            </form>
                                        @elseif($katalog->status === 'archived')
                                            <form action="{{ route('admin.katalog.bulk-action') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="ids[]" value="{{ $katalog->id }}">
                                                <input type="hidden" name="action" value="draft">
                                                <button class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50"><i class="fas fa-undo-alt w-4 text-slate-400"></i>Pulihkan sebagai draft</button>
                                            </form>
                                        @elseif($isComplete)
                                            <form action="{{ route('admin.katalog.bulk-action') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="ids[]" value="{{ $katalog->id }}">
                                                <input type="hidden" name="action" value="publish">
                                                <button class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-blue-700 hover:bg-blue-50"><i class="fas fa-upload w-4"></i>Publikasikan</button>
                                            </form>
                                        @endif

                                        @if($katalog->status !== 'archived')
                                            <form action="{{ route('admin.katalog.destroy', $katalog) }}" method="POST" onsubmit="return confirm('Arsipkan katalog ini? Katalog tidak akan tampil kepada pengunjung, tetapi riwayatnya tetap tersimpan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50"><i class="fas fa-archive w-4 text-slate-400"></i>Arsipkan</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400"><i class="fas fa-folder-open text-xl"></i></span>
                            <h3 class="mt-4 text-base font-semibold text-slate-900">Tidak ada katalog yang sesuai</h3>
                            <p class="mt-1 text-sm text-slate-500">Ubah filter atau tambahkan katalog baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">Menampilkan {{ $katalogs->firstItem() ?? 0 }}-{{ $katalogs->lastItem() ?? 0 }} dari {{ $katalogs->total() }} katalog</p>
        @if($katalogs->hasPages())
            {{ $katalogs->links() }}
        @endif
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = Array.from(document.querySelectorAll('.catalog-checkbox'));
    const selectAll = document.getElementById('selectAllCatalogs');
    const bulkForm = document.getElementById('bulkCatalogForm');
    const selectedCount = document.getElementById('selectedCatalogCount');
    const clearSelection = document.getElementById('clearCatalogSelection');
    const actionSelect = document.getElementById('bulkAction');
    const categorySelect = document.getElementById('bulkCategory');

    function updateSelection() {
        const checkedCount = checkboxes.filter((checkbox) => checkbox.checked).length;
        selectedCount.textContent = checkedCount;
        bulkForm.classList.toggle('hidden', checkedCount === 0);
        selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach((checkbox) => checkbox.checked = selectAll.checked);
        updateSelection();
    });

    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelection));

    clearSelection.addEventListener('click', function () {
        checkboxes.forEach((checkbox) => checkbox.checked = false);
        updateSelection();
    });

    actionSelect.addEventListener('change', function () {
        const changesCategory = actionSelect.value === 'change_category';
        categorySelect.classList.toggle('hidden', !changesCategory);
        categorySelect.required = changesCategory;
    });

    bulkForm.addEventListener('submit', function (event) {
        if (actionSelect.value === 'archive' && !confirm('Arsipkan semua katalog yang dipilih? Katalog tidak akan tampil kepada pengunjung.')) {
            event.preventDefault();
        }
    });
});
</script>
@endsection
