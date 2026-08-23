<article class="px-5 py-4 sm:px-6 {{ $depth ? 'bg-slate-50/70' : '' }}">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex min-w-0 items-start gap-3 {{ $depth ? 'sm:pl-8' : '' }}">
            <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $depth ? 'bg-white text-slate-500 ring-1 ring-slate-200' : 'bg-amber-50 text-amber-700' }}">
                <i class="fas {{ $depth ? 'fa-turn-up rotate-90' : 'fa-folder' }}"></i>
            </span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-bold text-slate-950">{{ $category->name }}</h3>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $category->is_active ? 'Tampil' : 'Disembunyikan' }}</span>
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $category->katalogs_count }} katalog</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">{{ $depth ? 'Subkategori dari '.$parentName : 'Kategori utama' }} · Urutan {{ $category->sort_order }}</p>
                @if($category->description)<p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ $category->description }}</p>@endif
            </div>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2 sm:pl-12 lg:pl-0">
            <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                @csrf
                <button class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    <i class="fas {{ $category->is_active ? 'fa-eye-slash' : 'fa-eye' }} mr-2 text-xs"></i>{{ $category->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                </button>
            </form>
            <details class="relative">
                <summary class="inline-flex h-9 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ubah</summary>
                <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4 lg:absolute lg:right-0 lg:top-full lg:z-20 lg:w-[430px] lg:bg-white lg:shadow-xl">
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="grid gap-3 sm:grid-cols-2">
                        @csrf @method('PUT')
                        <label class="block sm:col-span-2"><span class="mb-1 block text-xs font-semibold text-slate-600">Nama</span><input name="name" value="{{ $category->name }}" maxlength="100" required class="w-full rounded-lg border-slate-300 text-sm"></label>
                        <label class="block"><span class="mb-1 block text-xs font-semibold text-slate-600">Induk</span><select name="parent_id" class="w-full rounded-lg border-slate-300 text-sm" @disabled($category->allChildren->isNotEmpty())><option value="">Kategori utama</option>@foreach($parents as $possibleParent)<option value="{{ $possibleParent->id }}" @selected($category->parent_id === $possibleParent->id)>{{ $possibleParent->name }}</option>@endforeach</select>@if($category->allChildren->isNotEmpty())<input type="hidden" name="parent_id" value="">@endif</label>
                        <label class="block"><span class="mb-1 block text-xs font-semibold text-slate-600">Urutan</span><input type="number" name="sort_order" value="{{ $category->sort_order }}" min="0" max="9999" required class="w-full rounded-lg border-slate-300 text-sm"></label>
                        <label class="block sm:col-span-2"><span class="mb-1 block text-xs font-semibold text-slate-600">Deskripsi</span><textarea name="description" rows="2" maxlength="500" class="w-full rounded-lg border-slate-300 text-sm">{{ $category->description }}</textarea></label>
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-600"><input type="checkbox" name="is_active" value="1" @checked($category->is_active) class="rounded border-slate-300 text-amber-500">Tampilkan</label>
                        <button class="rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan perubahan</button>
                    </form>
                </div>
            </details>
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" @submit.prevent="$dispatch('open-confirmation', { form: $el, title: 'Hapus kategori?', message: 'Kategori hanya dapat dihapus jika tidak memiliki subkategori dan tidak digunakan oleh katalog.', confirmLabel: 'Ya, hapus', tone: 'danger' })">
                @csrf @method('DELETE')
                <button class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-white px-3 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus</button>
            </form>
        </div>
    </div>
</article>
