@extends('layouts.main')

@section('title', 'Daiku Interior Pekanbaru - Desain Ruang yang Personal')
@section('meta_description', 'Jasa desain interior dan furnitur custom untuk rumah, kantor, dan tempat usaha di Pekanbaru. Konsultasikan layout, material, dan kebutuhan pengerjaan Anda.')
@section('meta_image', asset('images/hero/daiku-home-hero.jpg'))

@section('content')
@if(session('success'))
    <div class="border-b border-green-200 bg-green-50 px-4 py-3 text-green-800" role="status">
        <div class="mx-auto max-w-7xl">
            <i class="fas fa-check-circle mr-2" aria-hidden="true"></i>{{ session('success') }}
        </div>
    </div>
@endif

<!-- Hero -->
<section class="relative isolate min-h-[calc(100vh-4rem)] overflow-hidden bg-slate-900">
    <img src="{{ asset('images/hero/daiku-home-hero.jpg') }}"
         alt="Desain interior kamar karya Daiku"
         class="absolute inset-0 h-full w-full object-cover object-center"
         fetchpriority="high"
         decoding="async"
         width="1279"
         height="719">
    <div class="absolute inset-0 bg-slate-950/35"></div>
    <div class="absolute inset-x-0 bottom-0 h-44 bg-gradient-to-t from-slate-950/25 to-transparent"></div>

    <div class="relative mx-auto flex min-h-[calc(100vh-4rem)] max-w-7xl items-center justify-center px-4 pb-32 pt-20 text-center sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl text-white">
            <h1 class="text-5xl font-bold leading-[0.98] tracking-tight sm:text-6xl lg:text-6xl xl:text-7xl">
                Wujudkan interior impian Anda,
                <span class="mt-2 block font-light italic">bersama Daiku Interior Pekanbaru.</span>
            </h1>
            <p class="mx-auto mt-7 max-w-3xl text-base leading-relaxed text-white/90 sm:text-lg">
                Dari ide hingga ruang impian, Daiku membantu mewujudkan desain interior yang sesuai kebutuhan dan gaya Anda.
            </p>
            <div class="mt-8 flex justify-center">
                <a href="{{ route('konsultasi.index') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-amber-300">
                    Buat Pesanan Sekarang
                </a>
            </div>
        </div>
    </div>

    <div class="absolute inset-x-0 bottom-5 z-20 flex justify-center px-4 sm:bottom-6" aria-label="Keunggulan layanan Daiku">
        <div class="flex w-fit max-w-full flex-wrap items-center justify-center gap-x-6 gap-y-2 rounded-xl border border-white/20 bg-white/15 px-5 py-3 text-white shadow-lg shadow-slate-950/15 backdrop-blur-md sm:gap-x-8 sm:px-6">
            <div class="flex items-center justify-center gap-2">
                <i class="fas fa-comments shrink-0 text-xs text-emerald-400" aria-hidden="true"></i>
                <span class="text-[10px] font-medium leading-tight sm:text-xs">Gratis konsultasi</span>
            </div>
            <div class="flex items-center justify-center gap-2">
                <i class="fas fa-bolt shrink-0 text-xs text-amber-400" aria-hidden="true"></i>
                <span class="text-[10px] font-medium leading-tight sm:text-xs">Responsif</span>
            </div>
            <div class="flex items-center justify-center gap-2">
                <i class="fas fa-couch shrink-0 text-xs text-blue-400" aria-hidden="true"></i>
                <span class="text-[10px] font-medium leading-tight sm:text-xs">Furnitur kustom</span>
            </div>
        </div>
    </div>
</section>

@include('home.partials.services')

<!-- Process -->
<section class="bg-stone-50 py-20" aria-labelledby="process-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto mb-14 max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Proses permintaan desain</p>
            <h2 id="process-title" class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Tiga langkah memulai proyek Anda</h2>
        </div>

        <div class="grid gap-8 md:grid-cols-3">
            <article class="relative rounded-2xl bg-white p-7 shadow-sm ring-1 ring-gray-200">
                <span class="text-sm font-bold text-amber-600">01</span>
                <h3 class="mt-4 text-xl font-bold text-slate-900">Kirim permintaan desain</h3>
                <p class="mt-3 leading-relaxed text-gray-600">Isi informasi proyek, jenis bangunan, luas area, anggaran, dan catatan kebutuhan Anda.</p>
            </article>
            <article class="relative rounded-2xl bg-white p-7 shadow-sm ring-1 ring-gray-200">
                <span class="text-sm font-bold text-amber-600">02</span>
                <h3 class="mt-4 text-xl font-bold text-slate-900">Tunggu peninjauan tim</h3>
                <p class="mt-3 leading-relaxed text-gray-600">Tim Daiku meninjau permintaan Anda dan menghubungi melalui WhatsApp untuk membahas kebutuhan awal.</p>
            </article>
            <article class="relative rounded-2xl bg-white p-7 shadow-sm ring-1 ring-gray-200">
                <span class="text-sm font-bold text-amber-600">03</span>
                <h3 class="mt-4 text-xl font-bold text-slate-900">Konsultasi dan tindak lanjut</h3>
                <p class="mt-3 leading-relaxed text-gray-600">Setelah konsultasi, tim menyiapkan ruang lingkup dan melanjutkan permintaan yang disetujui menjadi proyek.</p>
            </article>
        </div>
    </div>
</section>

<!-- Featured Portfolio -->
<section class="bg-white py-20" aria-labelledby="portfolio-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <div class="mx-auto max-w-2xl">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Portofolio Daiku</p>
                <h2 id="portfolio-title" class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Inspirasi interior untuk ruang yang lebih fungsional</h2>
                <p class="mt-3 text-lg text-gray-600">Jelajahi contoh kitchen set, kamar, dan ruang keluarga yang dirancang sesuai kebutuhan hunian.</p>
            </div>
        </div>

        @if($portfolioKatalogs->isNotEmpty())
            <section
                x-data="{
                    active: 0,
                    count: {{ $portfolioKatalogs->count() }},
                    timer: null,
                    start() {
                        if (this.count < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                        this.stop();
                        this.timer = window.setInterval(() => this.next(), 5500);
                    },
                    stop() {
                        if (this.timer) window.clearInterval(this.timer);
                        this.timer = null;
                    },
                    next() { this.active = (this.active + 1) % this.count; },
                    select(index) { this.active = index; this.start(); }
                }"
                x-init="start()"
                @mouseenter="stop()"
                @mouseleave="start()"
                @focusin="stop()"
                @focusout="start()"
                class="relative mb-10 overflow-hidden rounded-3xl bg-slate-900 shadow-xl"
                aria-roledescription="carousel"
                aria-label="Sorotan portofolio Daiku"
            >
                <div class="relative min-h-[440px] sm:min-h-[540px]">
                    @foreach($portfolioKatalogs as $katalog)
                        @php
                            $slideImages = collect([$katalog->gambar_utama_url])
                                ->merge($katalog->galeri_gambar_urls)
                                ->filter()
                                ->unique()
                                ->take(3)
                                ->values()
                                ->all();
                        @endphp
                        <article
                            x-cloak
                            x-show="active === {{ $loop->index }}"
                            class="absolute inset-0"
                            aria-label="{{ $loop->iteration }} dari {{ $portfolioKatalogs->count() }}"
                        >
                            <div class="grid h-full lg:grid-cols-[minmax(0,1fr)_260px]">
                                <div class="relative min-h-[440px] sm:min-h-[540px]">
                                @if($slideImages)
                                    <img
                                        src="{{ $slideImages[0] }}"
                                        alt="{{ $katalog->nama_desain }}"
                                        class="h-full w-full object-cover"
                                        @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                        decoding="async"
                                        width="1440"
                                        height="800"
                                    >
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent"></div>

                                <div class="absolute inset-x-0 bottom-0 p-7 text-white sm:p-10 lg:p-12">
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-300">{{ $katalog->category?->name ?? 'Portofolio Daiku' }}</p>
                                    <h3 class="mt-3 max-w-3xl text-3xl font-bold sm:text-4xl lg:text-5xl">{{ $katalog->nama_desain }}</h3>
                                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base">{{ Str::limit($katalog->deskripsi, 145) }}</p>
                                    <a href="{{ route('katalog', ['design' => $katalog->id]) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-white transition hover:gap-3 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-slate-900">
                                        Lihat desain <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                                    </a>
                                </div>
                                </div>

                                <div class="hidden grid-rows-2 gap-3 bg-slate-950 p-3 lg:grid">
                                    @foreach(array_slice($slideImages, 1, 2) as $galleryImage)
                                        <img src="{{ $galleryImage }}" alt="Detail {{ $katalog->nama_desain }}" class="h-full w-full rounded-xl object-cover" loading="lazy" decoding="async">
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($portfolioKatalogs->count() > 1)
                    <div class="absolute bottom-7 left-1/2 z-10 flex -translate-x-1/2 items-center gap-2 sm:bottom-9" aria-label="Pilih desain">
                        @foreach($portfolioKatalogs as $katalog)
                            <button type="button" @click="select({{ $loop->index }})" class="h-1.5 rounded-full bg-white transition-all duration-300" :class="active === {{ $loop->index }} ? 'w-9 opacity-100' : 'w-4 opacity-45 hover:opacity-80'" aria-label="Tampilkan {{ $katalog->nama_desain }}" :aria-current="active === {{ $loop->index }} ? 'true' : 'false'"></button>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="grid gap-7 md:grid-cols-3">
                @foreach($portfolioKatalogs->take(3) as $katalog)
                    <x-catalog-card :katalog="$katalog" />
                @endforeach
            </div>

            <div class="mt-10 flex justify-center">
                <a href="{{ route('katalog') }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-6 py-3 font-semibold text-slate-950 transition hover:bg-amber-300 focus:outline-none focus:ring-4 focus:ring-amber-100">
                    Lihat semua desain <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i>
                                </a>

                            </div>
        @else
            <div class="rounded-2xl border border-dashed border-gray-300 bg-stone-50 px-6 py-12 text-center">
                <p class="text-gray-600">Portofolio sedang disiapkan.</p>
            </div>
        @endif
    </div>
</section>

<section class="bg-stone-50 py-20" aria-labelledby="faq-title">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Pertanyaan umum</p>
            <h2 id="faq-title" class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Hal yang sering ditanyakan sebelum memulai</h2>
            <p class="mt-4 text-gray-600">Jika masih ada yang ingin dibahas, kirim permintaan desain agar tim Daiku dapat membantu sesuai kebutuhan ruang Anda.</p>
        </div>

        <div class="mt-10 divide-y divide-slate-200 rounded-2xl bg-white px-6 shadow-sm ring-1 ring-slate-200 sm:px-8">
            <details class="group py-5">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-semibold text-slate-900">
                    Bagaimana proses konsultasi dengan Daiku?
                    <i class="fas fa-plus text-sm text-amber-600 transition group-open:rotate-45" aria-hidden="true"></i>
                </summary>
                <p class="max-w-3xl pt-4 leading-relaxed text-slate-600">Mulailah dengan mengirim informasi proyek. Tim Daiku meninjau kebutuhan ruang Anda, lalu menghubungi untuk membahas langkah dan cakupan pekerjaan berikutnya.</p>
            </details>

            <details class="group py-5">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-semibold text-slate-900">
                    Informasi apa yang perlu disiapkan?
                    <i class="fas fa-plus text-sm text-amber-600 transition group-open:rotate-45" aria-hidden="true"></i>
                </summary>
                <p class="max-w-3xl pt-4 leading-relaxed text-slate-600">Siapkan jenis proyek, jenis bangunan, perkiraan luas area, anggaran, serta catatan kebutuhan. Foto atau referensi desain dapat dibahas saat tindak lanjut.</p>
            </details>

            <details class="group py-5">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-semibold text-slate-900">
                    Apakah Daiku menerima furnitur custom?
                    <i class="fas fa-plus text-sm text-amber-600 transition group-open:rotate-45" aria-hidden="true"></i>
                </summary>
                <p class="max-w-3xl pt-4 leading-relaxed text-slate-600">Ya. Kebutuhan furnitur seperti kitchen set, kabinet built-in, meja kerja, dan penyimpanan dapat disesuaikan dengan fungsi serta ukuran ruang.</p>
            </details>

            <details class="group py-5">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-semibold text-slate-900">
                    Apakah melayani rumah, kantor, dan ruang usaha?
                    <i class="fas fa-plus text-sm text-amber-600 transition group-open:rotate-45" aria-hidden="true"></i>
                </summary>
                <p class="max-w-3xl pt-4 leading-relaxed text-slate-600">Daiku melayani kebutuhan interior untuk hunian, ruang kerja, serta ruang usaha. Ceritakan fungsi ruang Anda pada formulir agar peninjauan awal lebih tepat.</p>
            </details>
        </div>
    </div>
</section>

@endsection
