@php
    $editing = isset($katalog);
    $fieldClass = 'mt-2 w-full rounded-xl border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500';
@endphp

@if($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <p class="font-semibold">Periksa kembali data katalog.</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-5 flex items-center justify-between gap-4">
    <a href="{{ route('admin.katalog.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-950">
        <i class="fas fa-arrow-left text-xs"></i>Kembali ke katalog
    </a>
    @if($editing)
        <span class="text-xs font-medium text-slate-400">ID #{{ $katalog->id }}</span>
    @endif
</div>

<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="font-semibold text-slate-950">Informasi Katalog</h2>
                <p class="mt-1 text-sm text-slate-500">Isi informasi inti dan media portofolio. Field bertanda <span class="text-red-500">*</span> wajib diisi.</p>
            </div>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="block md:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Nama desain <span class="text-red-500">*</span></span>
                    <input type="text" name="nama_desain" value="{{ old('nama_desain', $katalog->nama_desain ?? '') }}" maxlength="255" required class="{{ $fieldClass }} @error('nama_desain') border-red-400 @enderror" placeholder="Contoh: Kitchen Set Minimalis">
                    @error('nama_desain')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Kategori <span class="text-slate-400">(opsional untuk draft)</span></span>
                    <select name="category_id" class="{{ $fieldClass }} @error('category_id') border-red-400 @enderror">
                        <option value="">Belum ditentukan</option>
                        @foreach($categoryGroups as $parent)
                            <optgroup label="{{ $parent->name }}">
                                @foreach($parent->children as $child)
                                    <option value="{{ $child->id }}" @selected((string) old('category_id', $katalog->category_id ?? '') === (string) $child->id)>{{ $child->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('category_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Gaya desain <span class="text-slate-400">(opsional)</span></span>
                    <input type="text" name="style_tags" value="{{ old('style_tags', $katalog->style_tags ?? '') }}" maxlength="500" class="{{ $fieldClass }} @error('style_tags') border-red-400 @enderror" placeholder="Modern, Minimalis, Skandinavia">
                    <span class="mt-1 block text-xs text-slate-400">Pisahkan beberapa gaya dengan koma.</span>
                    @error('style_tags')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="block md:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Deskripsi <span class="text-red-500">*</span></span>
                    <textarea name="deskripsi" rows="5" maxlength="5000" required class="{{ $fieldClass }} @error('deskripsi') border-red-400 @enderror" placeholder="Jelaskan konsep, fungsi, dan karakter utama desain.">{{ old('deskripsi', $katalog->deskripsi ?? '') }}</textarea>
                    @error('deskripsi')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="block md:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Cerita inspirasi <span class="text-slate-400">(opsional)</span></span>
                    <textarea name="inspiration_story" rows="3" maxlength="5000" class="{{ $fieldClass }} @error('inspiration_story') border-red-400 @enderror" placeholder="Ceritakan kebutuhan atau ide di balik desain ini.">{{ old('inspiration_story', $katalog->inspiration_story ?? '') }}</textarea>
                    @error('inspiration_story')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <div class="md:col-span-2 border-t border-slate-100 pt-5">
                    <h3 class="font-semibold text-slate-950">Media</h3>
                    <p class="mt-1 text-sm text-slate-500">Gunakan gambar portofolio asli dengan pencahayaan dan orientasi yang konsisten.</p>
                </div>

                @if($editing && $katalog->gambar_utama && $katalog->gambar_utama_url)
                    <div class="md:col-span-2">
                        <p class="text-sm font-medium text-slate-700">Gambar utama saat ini</p>
                        <img src="{{ $katalog->gambar_utama_url }}" alt="{{ $katalog->nama_desain }}" class="mt-2 h-48 w-full rounded-xl border border-slate-200 object-cover sm:w-80">
                    </div>
                @endif

                <label class="block md:col-span-2">
                    <span class="text-sm font-medium text-slate-700">{{ $editing && $katalog->gambar_utama ? 'Ganti gambar utama' : 'Gambar utama' }} <span class="text-slate-400">(wajib untuk publikasi)</span></span>
                    <input type="file" name="gambar_utama" accept=".jpg,.jpeg,.png,.webp" class="{{ $fieldClass }} file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                    <span class="mt-1 block text-xs text-slate-400">JPG, PNG, atau WebP. Maksimal 5 MB.</span>
                    @error('gambar_utama')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                @if($editing && count($katalog->galeri_gambar ?? []) > 0)
                    <fieldset class="md:col-span-2">
                        <legend class="text-sm font-medium text-slate-700">Galeri saat ini <span class="text-slate-400">(opsional)</span></legend>
                        <p class="mt-1 text-xs text-slate-400">Centang gambar yang ingin dihapus ketika perubahan disimpan.</p>
                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach($katalog->galeri_gambar as $galleryPath)
                                @php($galleryUrl = $katalog->galleryImageUrl($galleryPath))
                                <label class="group relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    @if($galleryUrl)
                                        <img src="{{ $galleryUrl }}" alt="" class="h-28 w-full object-cover">
                                    @else
                                        <span class="flex h-28 items-center justify-center text-slate-300"><i class="fas fa-image"></i></span>
                                    @endif
                                    <span class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-600">
                                        <input type="checkbox" name="remove_gallery[]" value="{{ $galleryPath }}" class="rounded border-slate-300 text-red-500 focus:ring-red-400">Hapus
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                <label class="block md:col-span-2">
                    <span class="text-sm font-medium text-slate-700">{{ $editing ? 'Tambah gambar galeri' : 'Galeri tambahan' }} <span class="text-slate-400">(opsional)</span></span>
                    <input type="file" name="galeri_gambar[]" multiple accept=".jpg,.jpeg,.png,.webp" class="{{ $fieldClass }} file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                    <span class="mt-1 block text-xs text-slate-400">Maksimal 12 gambar dalam satu galeri, masing-masing maksimal 5 MB.</span>
                    @error('galeri_gambar')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    @error('galeri_gambar.*')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <div class="md:col-span-2 border-t border-slate-100 pt-5">
                    <h3 class="font-semibold text-slate-950">Publikasi</h3>
                    <p class="mt-1 text-sm text-slate-500">Draft dan arsip tidak ditampilkan kepada pengunjung.</p>
                </div>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></span>
                    <select name="status" required class="{{ $fieldClass }} @error('status') border-red-400 @enderror">
                        <option value="draft" @selected(old('status', $katalog->status ?? 'draft') === 'draft')>Draft</option>
                        <option value="published" @selected(old('status', $katalog->status ?? '') === 'published')>Dipublikasikan</option>
                        @if($editing)
                            <option value="archived" @selected(old('status', $katalog->status) === 'archived')>Diarsipkan</option>
                        @endif
                    </select>
                    @error('status')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                <div class="flex items-end md:justify-end">
                    <div class="grid w-full gap-2 sm:w-auto sm:grid-cols-2">
                        <a href="{{ route('admin.katalog.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
                            <i class="fas fa-save mr-2"></i>{{ $submitLabel }}
                        </button>
                    </div>
                </div>
            </div>
</section>
