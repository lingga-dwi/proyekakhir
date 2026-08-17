@extends('layouts.dashboard')

@section('title', 'Kelola Kategori - Admin Dashboard')
@section('page-title', 'Kelola Kategori')
@section('page-description', 'Atur kategori induk dan subkategori katalog')

@section('content')
<div class="mx-auto max-w-[1500px]">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-slate-600">Kategori aktif akan tersedia pada formulir dan filter katalog.</p>
            <p class="mt-1 text-xs text-slate-400">Kategori yang masih digunakan sebaiknya dinonaktifkan, bukan dihapus.</p>
        </div>
        <a href="{{ route('admin.katalog.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            <i class="fas fa-arrow-left text-xs"></i>Kembali ke Katalog
        </a>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Total kategori', 'value' => $stats['total'], 'icon' => 'fa-layer-group', 'color' => 'bg-slate-100 text-slate-600'],
            ['label' => 'Kategori aktif', 'value' => $stats['active'], 'icon' => 'fa-eye', 'color' => 'bg-emerald-50 text-emerald-600'],
            ['label' => 'Kategori induk', 'value' => $stats['parents'], 'icon' => 'fa-folder-tree', 'color' => 'bg-blue-50 text-blue-600'],
            ['label' => 'Digunakan katalog', 'value' => $stats['used'], 'icon' => 'fa-images', 'color' => 'bg-amber-50 text-amber-700'],
        ] as $stat)
            <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $stat['color'] }}"><i class="fas {{ $stat['icon'] }}"></i></span>
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p><p class="mt-1 text-2xl font-bold text-slate-950">{{ $stat['value'] }}</p></div>
            </article>
        @endforeach
    </div>

    <div class="grid items-start gap-6 xl:grid-cols-[380px_minmax(0,1fr)]">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:sticky xl:top-0">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-950">Tambah kategori</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Pilih kategori induk bila ingin membuat subkategori.</p>
            </div>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold text-slate-700">Nama kategori</span>
                    <input name="name" value="{{ old('name') }}" maxlength="100" required class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Contoh: Ruang Ibadah">
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold text-slate-700">Kategori induk <span class="font-normal text-slate-400">(opsional)</span></span>
                    <select name="parent_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Jadikan kategori utama</option>
                        @foreach($categories as $parent)
                            <option value="{{ $parent->id }}" @selected((string) old('parent_id') === (string) $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold text-slate-700">Deskripsi <span class="font-normal text-slate-400">(opsional)</span></span>
                    <textarea name="description" rows="3" maxlength="500" class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Keterangan singkat kategori">{{ old('description') }}</textarea>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold text-slate-700">Urutan tampil</span>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999" required class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
                </label>
                <label class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <span class="text-sm font-medium text-slate-700">Langsung tampilkan kategori</span>
                </label>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
                    <i class="fas fa-plus"></i>Tambah Kategori
                </button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-slate-950">Struktur kategori</h2>
                <p class="mt-1 text-sm text-slate-500">Subkategori ditampilkan menjorok di bawah kategori induknya.</p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($categories as $parent)
                    @include('admin.categories.partials.row', ['category' => $parent, 'parents' => $categories, 'depth' => 0, 'parentName' => null])
                    @foreach($parent->allChildren as $child)
                        @include('admin.categories.partials.row', ['category' => $child, 'parents' => $categories, 'depth' => 1, 'parentName' => $parent->name])
                    @endforeach
                @empty
                    <div class="px-6 py-16 text-center">
                        <i class="fas fa-layer-group text-4xl text-slate-300"></i>
                        <p class="mt-4 font-semibold text-slate-700">Belum ada kategori</p>
                        <p class="mt-1 text-sm text-slate-500">Tambahkan kategori pertama melalui formulir di samping.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
