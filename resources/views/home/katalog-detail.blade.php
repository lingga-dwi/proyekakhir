@extends('layouts.main')

@section('title', $katalog->nama_desain . ' - Daiku Interior')
@section('meta_description', Str::limit(strip_tags($katalog->deskripsi), 155))
@section('meta_image', $katalog->gambar_utama_url ?? asset('images/logo/image.png'))

@section('content')
<div class="min-h-screen bg-stone-50 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="mb-8" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                <li><a href="{{ route('home') }}" class="hover:text-amber-700">Beranda</a></li>
                <li><i class="fas fa-chevron-right text-xs" aria-hidden="true"></i></li>
                <li><a href="{{ route('katalog') }}" class="hover:text-amber-700">Katalog</a></li>
                <li><i class="fas fa-chevron-right text-xs" aria-hidden="true"></i></li>
                <li class="font-medium text-gray-800" aria-current="page">{{ $katalog->nama_desain }}</li>
            </ol>
        </nav>

        <div class="grid gap-12 lg:grid-cols-[1.2fr_0.8fr]">
            <section aria-label="Galeri desain">
                <div class="overflow-hidden rounded-2xl bg-gray-100 ring-1 ring-gray-200">
                    @if($katalog->gambar_utama_url)
                        <img id="catalog-main-image"
                             src="{{ $katalog->gambar_utama_url }}"
                             alt="{{ $katalog->nama_desain }}"
                             class="h-[520px] w-full object-cover"
                             fetchpriority="high"
                             decoding="async">
                    @else
                        <div class="flex h-[520px] items-center justify-center text-gray-400">
                            <i class="fas fa-image text-5xl" aria-hidden="true"></i>
                        </div>
                    @endif
                </div>

                @if($katalog->galeri_gambar_urls)
                    <div class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-4">
                        @foreach($katalog->galeri_gambar_urls as $index => $image)
                            <button type="button"
                                    class="overflow-hidden rounded-xl bg-gray-100 ring-1 ring-gray-200 transition hover:ring-2 hover:ring-amber-400"
                                    onclick="changeCatalogImage(@js($image), @js($katalog->nama_desain . ' - galeri ' . ($index + 1)))"
                                    aria-label="Tampilkan gambar galeri {{ $index + 1 }}">
                                <img src="{{ $image }}"
                                     alt="{{ $katalog->nama_desain }} - galeri {{ $index + 1 }}"
                                     class="h-24 w-full object-cover"
                                     loading="lazy"
                                     decoding="async"
                                     width="320"
                                     height="240">
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>

            <article class="self-start rounded-2xl bg-white p-7 shadow-sm ring-1 ring-gray-200 lg:sticky lg:top-24">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">
                    {{ $katalog->category?->name ?? $katalog->kategori }}
                </p>
                <h1 class="mt-3 text-3xl font-bold text-slate-900">{{ $katalog->nama_desain }}</h1>
                <p class="mt-5 leading-relaxed text-gray-600">{{ $katalog->deskripsi }}</p>

                @if($katalog->style_tags || $katalog->room_size || $katalog->inspiration_story)
                    <div class="mt-7 space-y-5 border-t border-gray-200 pt-6">
                        @if($katalog->style_tags)
                            <div>
                                <h2 class="text-sm font-semibold text-gray-500">Gaya</h2>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach(array_filter(array_map('trim', explode(',', $katalog->style_tags))) as $tag)
                                        <span class="rounded-full bg-stone-100 px-3 py-1 text-sm text-gray-700">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($katalog->room_size)
                            <div>
                                <h2 class="text-sm font-semibold text-gray-500">Referensi ukuran</h2>
                                <p class="mt-1 font-semibold text-slate-900">{{ $katalog->room_size }} m&sup2;</p>
                            </div>
                        @endif

                        @if($katalog->inspiration_story)
                            <div>
                                <h2 class="text-sm font-semibold text-gray-500">Inspirasi desain</h2>
                                <p class="mt-2 leading-relaxed text-gray-600">{{ $katalog->inspiration_story }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <h2 class="font-bold text-slate-900">Gunakan sebagai referensi awal</h2>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">Setiap ruang memiliki ukuran dan kebutuhan berbeda. Detail pekerjaan dibahas setelah informasi proyek ditinjau.</p>
                    <a href="{{ route('pemesanan.create', ['katalog_id' => $katalog->id]) }}" class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-amber-400 px-5 py-3.5 font-semibold text-slate-950 transition hover:bg-amber-300">
                        Konsultasikan Desain Ini
                    </a>
                </div>
            </article>
        </div>

        @if($relatedKatalogs->isNotEmpty())
            <section class="mt-20" aria-labelledby="related-designs">
                <div class="mb-8 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-amber-700">Inspirasi lainnya</p>
                        <h2 id="related-designs" class="mt-2 text-3xl font-bold text-slate-900">Desain Serupa</h2>
                    </div>
                    <a href="{{ route('katalog') }}" class="font-semibold text-amber-700 hover:text-amber-800">Semua desain</a>
                </div>
                <div class="grid gap-7 md:grid-cols-3">
                    @foreach($relatedKatalogs as $related)
                        <a href="{{ route('katalog.detail', $related->id) }}" class="group overflow-hidden rounded-2xl bg-white ring-1 ring-gray-200 transition hover:-translate-y-1 hover:shadow-xl">
                            @if($related->gambar_utama_url)
                                <img src="{{ $related->gambar_utama_url }}"
                                     alt="{{ $related->nama_desain }}"
                                     class="h-56 w-full object-cover transition duration-500 group-hover:scale-105"
                                     loading="lazy"
                                     decoding="async"
                                     width="640"
                                     height="448">
                            @endif
                            <div class="p-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">{{ $related->category?->name ?? $related->kategori }}</p>
                                <h3 class="mt-2 text-lg font-bold text-slate-900">{{ $related->nama_desain }}</h3>
                                <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-600">{{ $related->deskripsi }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function changeCatalogImage(source, alt) {
    const image = document.getElementById('catalog-main-image');
    if (!image) return;

    image.src = source;
    image.alt = alt;
    image.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>
@endpush
