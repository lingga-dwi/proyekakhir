@extends('layouts.main')

@section('title', 'Katalog Desain Interior - Daiku Interior Pekanbaru')
@section('meta_description', 'Jelajahi katalog desain interior Daiku untuk hunian, kantor, dan ruang usaha di Pekanbaru.')

@push('head-scripts')
<script>
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function sanitizeImageUrl(value) {
    if (!value) return '';

    try {
        const url = new URL(value, window.location.origin);
        return ['http:', 'https:'].includes(url.protocol) ? escapeHtml(url.toString()) : '';
    } catch (_) {
        return '';
    }
}

function openSidebar(katalogId) {
    const sidebar = document.getElementById('detailSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    sidebar.classList.remove('translate-x-full');
    overlay.classList.remove('pointer-events-none', 'opacity-0');
    document.body.classList.add('overflow-hidden');
    loadSidebarContent(katalogId);
}

function closeSidebar() {
    const sidebar = document.getElementById('detailSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    sidebar.classList.add('translate-x-full');
    overlay.classList.add('pointer-events-none', 'opacity-0');
    document.body.classList.remove('overflow-hidden');
}

async function loadSidebarContent(katalogId) {
    const content = document.getElementById('sidebarContent');
    content.innerHTML = `
        <div class="flex min-h-80 items-center justify-center" role="status">
            <div class="h-8 w-8 animate-spin rounded-full border-2 border-gray-200 border-b-amber-500"></div>
            <span class="ml-3 text-gray-600">Memuat desain...</span>
        </div>`;

    try {
        const response = await fetch(`/api/katalog/${encodeURIComponent(katalogId)}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) throw new Error('Detail desain tidak dapat dimuat.');

        const data = await response.json();
        const imageUrl = sanitizeImageUrl(data.gambar_utama_url);
        const gallery = Array.isArray(data.galeri_gambar_urls)
            ? data.galeri_gambar_urls.map(sanitizeImageUrl).filter(Boolean)
            : [];
        const tags = data.style_tags
            ? data.style_tags.split(',').map(tag => tag.trim()).filter(Boolean)
            : [];
        const galleryHtml = gallery.length
            ? `<div class="grid grid-cols-3 gap-2 px-6 pt-4">
                ${gallery.map((image, index) => `
                    <img src="${image}" alt="Galeri ${escapeHtml(data.nama_desain)} ${index + 1}" class="h-24 w-full rounded-lg object-cover" loading="lazy" decoding="async">
                `).join('')}
               </div>`
            : '';
        const tagsHtml = tags.length
            ? `<div class="flex flex-wrap gap-2">${tags.map(tag => `<span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-gray-700">${escapeHtml(tag)}</span>`).join('')}</div>`
            : '';

        content.innerHTML = `
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white/95 px-6 py-4 backdrop-blur">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Detail desain</p>
                    <h2 class="mt-1 font-semibold text-slate-900">${escapeHtml(data.nama_desain)}</h2>
                </div>
                <button type="button" onclick="closeSidebar()" class="flex h-10 w-10 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-800" aria-label="Tutup detail desain">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>

            ${imageUrl
                ? `<img src="${imageUrl}" alt="${escapeHtml(data.nama_desain)}" class="h-72 w-full object-cover" decoding="async">`
                : `<div class="flex h-72 items-center justify-center bg-gray-100 text-gray-400"><i class="fas fa-image text-4xl" aria-hidden="true"></i></div>`}
            ${galleryHtml}

            <div class="space-y-7 p-6">
                <div>
                    ${data.category ? `<p class="text-sm font-bold uppercase tracking-wider text-amber-700">${escapeHtml(data.category)}</p>` : ''}
                    <h1 class="mt-2 text-2xl font-bold text-slate-900">${escapeHtml(data.nama_desain)}</h1>
                    <p class="mt-4 leading-relaxed text-gray-600">${escapeHtml(data.deskripsi)}</p>
                </div>

                ${tagsHtml}

                ${(data.room_size || data.inspiration_story) ? `
                    <div class="rounded-xl bg-stone-50 p-5">
                        ${data.room_size ? `<p class="text-sm text-gray-500">Referensi ukuran</p><p class="mt-1 font-semibold text-slate-900">${escapeHtml(data.room_size)} m&sup2;</p>` : ''}
                        ${data.inspiration_story ? `<p class="${data.room_size ? 'mt-4 border-t border-gray-200 pt-4' : ''} leading-relaxed text-gray-600">${escapeHtml(data.inspiration_story)}</p>` : ''}
                    </div>` : ''}

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <h3 class="font-bold text-slate-900">Tertarik dengan arah desain ini?</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">Gunakan desain ini sebagai referensi awal. Ruang lingkup dan kebutuhan akhir dibahas bersama tim Daiku.</p>
                    <a href="{{ url('/pemesanan/create') }}?katalog_id=${encodeURIComponent(katalogId)}" class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-amber-400 px-5 py-3 font-semibold text-slate-950 transition hover:bg-amber-300">
                        Konsultasikan Desain Ini
                    </a>
                </div>
            </div>`;
    } catch (error) {
        content.innerHTML = `
            <div class="p-6">
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                    <p class="font-semibold">Gagal memuat detail desain</p>
                    <p class="mt-1 text-sm">${escapeHtml(error.message)}</p>
                </div>
                <button type="button" onclick="closeSidebar()" class="mt-5 text-sm font-semibold text-gray-700 hover:text-gray-900">Tutup</button>
            </div>`;
    }
}
</script>
@endpush

@section('content')
<div class="min-h-screen bg-stone-50">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Portofolio Daiku</p>
            <h1 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Katalog Desain Interior</h1>
            <p class="mt-3 max-w-2xl text-lg text-gray-600">Temukan arah visual untuk hunian, kantor, atau ruang usaha Anda.</p>
        </div>
    </header>

    <section class="sticky top-16 z-30 border-b border-gray-200 bg-white/95 backdrop-blur" aria-label="Filter katalog">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <form action="{{ route('katalog') }}" method="GET" class="relative w-full md:max-w-md">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <label for="catalog-search" class="sr-only">Cari desain interior</label>
                    <input id="catalog-search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari desain interior..." class="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-12 focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    <i class="fas fa-search pointer-events-none absolute left-3 top-3.5 text-gray-400" aria-hidden="true"></i>
                    <button type="submit" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-amber-700" aria-label="Cari">
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <form action="{{ route('katalog') }}" method="GET" class="flex w-full gap-3 md:w-auto">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <label for="catalog-category" class="sr-only">Kategori</label>
                    <select id="catalog-category" name="category" onchange="this.form.submit()" class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2.5 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 md:min-w-48">
                        <option value="">Semua Kategori</option>
                        @foreach($parentCategories as $parent)
                            <optgroup label="{{ $parent->name }}">
                                @foreach($parent->children as $child)
                                    <option value="{{ $child->slug }}" @selected(request('category') == $child->slug)>{{ $child->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                    <label for="catalog-sort" class="sr-only">Urutkan</label>
                    <select id="catalog-sort" name="sort" onchange="this.form.submit()" class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2.5 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 md:min-w-36">
                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>Terbaru</option>
                        <option value="name" @selected(request('sort') === 'name')>Nama A-Z</option>
                        <option value="category" @selected(request('sort') === 'category')>Kategori</option>
                    </select>
                </form>
            </div>
        </div>
    </section>

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if($katalogs->count() > 0)
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($katalogs as $katalog)
                    <article onclick="openSidebar({{ $katalog->id }})"
                             tabindex="0"
                             role="button"
                             aria-label="Lihat detail {{ $katalog->nama_desain }}"
                             onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); openSidebar({{ $katalog->id }}); }"
                             class="group cursor-pointer overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 transition duration-300 hover:-translate-y-1 hover:shadow-xl focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        <div class="relative h-56 overflow-hidden bg-gray-100">
                            @if($katalog->gambar_utama_url)
                                <img src="{{ $katalog->gambar_utama_url }}"
                                     alt="{{ $katalog->nama_desain }}"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                     loading="lazy"
                                     decoding="async"
                                     width="640"
                                     height="448">
                            @else
                                <div class="flex h-full items-center justify-center text-gray-400"><i class="fas fa-image text-4xl" aria-hidden="true"></i></div>
                            @endif
                            <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/10"></div>
                            @if($katalog->category)
                                <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-gray-800 shadow-sm backdrop-blur">{{ $katalog->category->name }}</span>
                            @endif
                        </div>
                        <div class="p-5">
                            <h2 class="text-lg font-bold text-slate-900">{{ $katalog->nama_desain }}</h2>
                            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-600">{{ $katalog->deskripsi }}</p>
                            <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-amber-700">Lihat desain <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></span>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $katalogs->appends(request()->query())->links() }}</div>
        @else
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <i class="fas fa-search text-4xl text-gray-300" aria-hidden="true"></i>
                <h2 class="mt-5 text-xl font-bold text-slate-900">Tidak ada desain ditemukan</h2>
                <p class="mt-2 text-gray-600">Coba ubah kata kunci atau filter kategori.</p>
                <a href="{{ route('katalog') }}" class="mt-6 inline-flex rounded-lg bg-amber-400 px-6 py-3 font-semibold text-slate-950 hover:bg-amber-300">Lihat Semua Desain</a>
            </div>
        @endif
    </main>
</div>

<aside id="detailSidebar" class="fixed inset-y-0 right-0 z-50 w-full max-w-lg translate-x-full overflow-y-auto bg-white shadow-2xl transition-transform duration-300 ease-out" aria-label="Detail desain">
    <div id="sidebarContent">
        <div class="flex min-h-80 items-center justify-center text-gray-500">Pilih desain untuk melihat detail.</div>
    </div>
</aside>
<div id="sidebarOverlay" onclick="closeSidebar()" class="pointer-events-none fixed inset-0 z-40 bg-black/55 opacity-0 transition-opacity duration-300" aria-hidden="true"></div>
@endsection

@push('scripts')
<script>
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeSidebar();
});
</script>
@endpush
