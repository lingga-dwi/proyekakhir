@if($caseStudy)
<section class="bg-white py-20" aria-labelledby="case-study-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-10 max-w-2xl">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Proyek unggulan</p>
            <h2 id="case-study-title" class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">
                Dari kondisi awal ke arah desain
            </h2>
            <p class="mt-4 text-lg leading-relaxed text-gray-600">
                Batas ukuran dan fungsi dibaca lebih dulu sebelum penyimpanan, warna, dan material ditentukan.
            </p>
        </div>

        <article class="overflow-hidden rounded-3xl bg-stone-50 ring-1 ring-gray-200 lg:grid lg:grid-cols-[1.15fr_0.85fr]">
            <div class="relative min-h-[340px] overflow-hidden bg-gray-100 sm:min-h-[460px] lg:min-h-[620px]">
                @if($caseStudy->gambar_utama_url)
                    <img src="{{ $caseStudy->gambar_utama_url }}"
                         alt="{{ $caseStudy->nama_desain }}"
                         class="absolute inset-0 h-full w-full object-cover transition duration-700 hover:scale-[1.02]"
                         loading="lazy"
                         decoding="async"
                         width="960"
                         height="960">
                @endif
                <span class="absolute left-5 top-5 rounded-full bg-white/95 px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-800 shadow-sm backdrop-blur-sm">
                    {{ $caseStudy->category?->name ?? 'Tanpa kategori' }}
                </span>
            </div>

            <div class="flex flex-col justify-center p-7 sm:p-10 lg:p-12">
                <p class="text-sm font-semibold text-amber-700">Ringkasan proyek</p>
                <h3 class="mt-2 text-3xl font-bold text-slate-900">{{ $caseStudy->nama_desain }}</h3>

                <div class="mt-8 space-y-7">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-500">Konteks ruang</p>
                        <p class="mt-2 leading-relaxed text-gray-700">{{ $caseStudy->deskripsi }}</p>
                    </div>

                    <div class="border-l-2 border-amber-400 pl-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-500">Arah desain</p>
                        <p class="mt-2 leading-relaxed text-gray-700">{{ $caseStudy->inspiration_story }}</p>
                    </div>
                </div>

                <dl class="mt-9 grid grid-cols-2 gap-x-6 gap-y-5 border-y border-gray-200 py-6">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-gray-500">Jenis ruang</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $caseStudy->category?->name ?? 'Tanpa kategori' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-gray-500">Luas ruang</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ number_format($caseStudy->room_size, 0, ',', '.') }} m&sup2;</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-xs font-bold uppercase tracking-wider text-gray-500">Karakter desain</dt>
                        <dd class="mt-2 flex flex-wrap gap-2">
                            @foreach(collect(explode(',', $caseStudy->style_tags))->map(fn ($tag) => trim($tag))->filter() as $tag)
                                <span class="rounded-full bg-white px-3 py-1.5 text-sm font-medium text-slate-700 ring-1 ring-gray-200">{{ $tag }}</span>
                            @endforeach
                        </dd>
                    </div>
                </dl>

                <a href="{{ route('katalog.detail', $caseStudy->id) }}" class="mt-8 inline-flex items-center gap-2 self-start font-semibold text-amber-700 transition hover:text-amber-800">
                    Lihat detail desain <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i>
                </a>
            </div>
        </article>
    </div>
</section>
@endif
