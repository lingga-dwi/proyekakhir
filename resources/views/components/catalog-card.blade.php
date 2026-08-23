@props([
    'katalog',
    'sidebar' => false,
])

<article
    @if($sidebar)
        onclick="openSidebar({{ $katalog->id }})"
        tabindex="0"
        role="button"
        aria-label="Lihat detail {{ $katalog->nama_desain }}"
        onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); openSidebar({{ $katalog->id }}); }"
    @endif
    class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 transition duration-300 hover:-translate-y-1 hover:shadow-xl focus-within:ring-2 focus-within:ring-amber-500 focus-within:ring-offset-2 {{ $sidebar ? 'cursor-pointer focus:ring-2 focus:ring-amber-500 focus:ring-offset-2' : '' }}"
>

    <div class="relative h-56 shrink-0 overflow-hidden bg-gray-100">
        @if($katalog->gambar_utama_url)
            <img
                src="{{ $katalog->gambar_utama_url }}"
                alt="{{ $katalog->nama_desain }}"
                class="h-full w-full cursor-zoom-in object-cover transition duration-500 group-hover:scale-105"
                onclick="event.stopPropagation(); openImageLightbox(@js($katalog->gambar_utama_url), @js($katalog->nama_desain))"
                tabindex="0"
                onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); event.stopPropagation(); openImageLightbox(@js($katalog->gambar_utama_url), @js($katalog->nama_desain)); }"
                loading="lazy"
                decoding="async"
                width="640"
                height="448"
            >
        @else
            <div class="flex h-full items-center justify-center text-gray-400">
                <i class="fas fa-image text-4xl" aria-hidden="true"></i>
            </div>
        @endif
        <div class="pointer-events-none absolute inset-0 bg-black/0 transition group-hover:bg-black/10"></div>
        @if($katalog->category)
            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-gray-800 shadow-sm backdrop-blur">
                {{ $katalog->category->name }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-lg font-bold text-slate-900">{{ $katalog->nama_desain }}</h3>
        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-600">{{ $katalog->deskripsi }}</p>
        @if($sidebar)
            <span class="mt-auto inline-flex items-center gap-2 self-start pt-4 text-sm font-semibold text-amber-700 transition group-hover:gap-3">
                Lihat desain <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
            </span>
        @else
            <a href="{{ route('katalog', ['design' => $katalog->id]) }}" class="mt-auto inline-flex items-center gap-2 self-start pt-4 text-sm font-semibold text-amber-700 transition hover:gap-3 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                Lihat desain <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
            </a>
        @endif
    </div>
</article>
