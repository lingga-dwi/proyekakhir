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
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Sebelum desain dimulai</p>
            <h2 id="process-title" class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Tiga langkah menyiapkan brief proyek</h2>
        </div>

        <div class="grid gap-8 md:grid-cols-3">
            <article class="relative rounded-2xl bg-white p-7 shadow-sm ring-1 ring-gray-200">
                <span class="text-sm font-bold text-amber-600">01</span>
                <h3 class="mt-4 text-xl font-bold text-slate-900">Tentukan jenis ruang</h3>
                <p class="mt-3 leading-relaxed text-gray-600">Pilih referensi dari katalog atau tuliskan area yang ingin dikerjakan.</p>
            </article>
            <article class="relative rounded-2xl bg-white p-7 shadow-sm ring-1 ring-gray-200">
                <span class="text-sm font-bold text-amber-600">02</span>
                <h3 class="mt-4 text-xl font-bold text-slate-900">Kirim data awal</h3>
                <p class="mt-3 leading-relaxed text-gray-600">Lengkapi ukuran, foto kondisi ruang, denah, serta gaya atau warna pilihan.</p>
            </article>
            <article class="relative rounded-2xl bg-white p-7 shadow-sm ring-1 ring-gray-200">
                <span class="text-sm font-bold text-amber-600">03</span>
                <h3 class="mt-4 text-xl font-bold text-slate-900">Tinjau bersama tim</h3>
                <p class="mt-3 leading-relaxed text-gray-600">Tim Daiku memeriksa data awal dan membahas cakupan desain serta pengerjaan berikutnya.</p>
            </article>
        </div>
    </div>
</section>

<!-- Featured Portfolio -->
<section class="bg-white py-20" aria-labelledby="portfolio-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-12 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Portofolio</p>
                <h2 id="portfolio-title" class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Pilihan desain rumah, kantor, dan ruang usaha</h2>
                <p class="mt-3 text-lg text-gray-600">Bandingkan susunan ruang dan gaya visualnya sebelum mengirim brief proyek.</p>
            </div>
            <a href="{{ route('katalog') }}" class="inline-flex items-center gap-2 font-semibold text-amber-700 hover:text-amber-800">
                Lihat semua desain <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i>
            </a>
        </div>

        @if($portfolioKatalogs->isNotEmpty())
            <div
                x-data="{
                    active: 0,
                    count: {{ $portfolioKatalogs->count() }},
                    timer: null,
                    start() {
                        if (this.count < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                        this.stop();
                        this.timer = window.setInterval(() => this.next(), 5000);
                    },
                    stop() {
                        if (this.timer) window.clearInterval(this.timer);
                        this.timer = null;
                    },
                    next() {
                        this.active = (this.active + 1) % this.count;
                    },
                    previous() {
                        this.active = (this.active - 1 + this.count) % this.count;
                    },
                    select(index) {
                        this.active = index;
                        this.start();
                    }
                }"
                x-init="start()"
                @mouseenter="stop()"
                @mouseleave="start()"
                @focusin="stop()"
                @focusout="start()"
                class="relative mb-10 overflow-hidden rounded-3xl bg-slate-950 shadow-xl"
                aria-roledescription="carousel"
                aria-label="Sorotan portofolio Daiku"
            >
                <div class="relative min-h-[480px] sm:min-h-[600px] lg:min-h-[620px]">
                    @foreach($portfolioKatalogs as $katalog)
                        @php
                            $slideImages = collect([$katalog->gambar_utama_url])
                                ->merge($katalog->galeri_gambar_urls)
                                ->filter()
                                ->unique()
                                ->take(3)
                                ->values()
                                ->all();
                            $hasGalleryThumbs = count($slideImages) > 1;
                        @endphp
                        <article
                            x-data="{ selectedImage: 0, images: @js($slideImages) }"
                            x-cloak
                            x-show="active === {{ $loop->index }}"
                            x-transition:enter="transition duration-700 ease-out"
                            x-transition:enter-start="opacity-0 scale-[1.02]"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition duration-500 ease-in"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute inset-0"
                            aria-label="{{ $loop->iteration }} dari {{ $portfolioKatalogs->count() }}"
                        >
                            <div @class([
                                'flex h-full flex-col',
                                'lg:grid lg:grid-cols-[minmax(0,1fr)_240px]' => $hasGalleryThumbs,
                            ])>
                                <a href="{{ route('katalog', ['design' => $katalog->id]) }}" class="group relative block min-h-0 flex-1 overflow-hidden">
                                    @if($slideImages)
                                    <img
                                        src="{{ $slideImages[0] }}"
                                        :src="images[selectedImage] || '{{ $slideImages[0] }}'"
                                        alt="{{ $katalog->nama_desain }}"
                                        class="absolute inset-0 h-full w-full object-cover transition duration-[1600ms] group-hover:scale-[1.025]"
                                        @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                        decoding="async"
                                        width="1440"
                                        height="800"
                                    >
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/15 to-transparent"></div>
                                    <div class="absolute inset-x-0 bottom-0 p-7 text-white sm:p-10 lg:p-12">
                                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-300">
                                            {{ $katalog->category?->name ?? 'Portofolio Daiku' }}
                                        </p>
                                        <h3 class="mt-3 max-w-3xl text-3xl font-bold sm:text-4xl lg:text-5xl">{{ $katalog->nama_desain }}</h3>
                                        <p class="mt-4 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base">
                                            {{ Str::limit($katalog->deskripsi, 145) }}
                                        </p>
                                        <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-white">
                                            Lihat desain <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                </a>

                                @if($hasGalleryThumbs)
                                    <div class="flex shrink-0 gap-3 bg-slate-950 p-3 lg:grid lg:grid-rows-2 lg:gap-3">
                                        @foreach(array_slice($slideImages, 1, 2) as $image)
                                            <button
                                                type="button"
                                                @click="selectedImage = {{ $loop->index + 1 }}"
                                                class="relative h-20 min-w-28 flex-1 overflow-hidden rounded-xl border-2 border-transparent transition focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 lg:h-auto lg:min-w-0"
                                                :class="selectedImage === {{ $loop->index + 1 }} ? 'border-amber-300 opacity-100' : 'border-white/15 opacity-70 hover:opacity-100'"
                                                aria-label="Tampilkan foto {{ $loop->index + 2 }} dari {{ $katalog->nama_desain }}"
                                            >
                                                <img src="{{ $image }}" alt="{{ $katalog->nama_desain }} - foto {{ $loop->index + 2 }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($portfolioKatalogs->count() > 1)
                    <div class="absolute right-5 top-5 z-10 flex gap-2 sm:right-7 sm:top-7 lg:right-[260px]">
                        <button type="button" @click="previous(); start()" class="flex h-11 w-11 items-center justify-center rounded-full border border-white/30 bg-slate-950/35 text-white backdrop-blur-md transition hover:bg-white hover:text-slate-950" aria-label="Tampilkan desain sebelumnya">
                            <i class="fas fa-arrow-left text-sm" aria-hidden="true"></i>
                        </button>
                        <button type="button" @click="next(); start()" class="flex h-11 w-11 items-center justify-center rounded-full border border-white/30 bg-slate-950/35 text-white backdrop-blur-md transition hover:bg-white hover:text-slate-950" aria-label="Tampilkan desain berikutnya">
                            <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="absolute bottom-7 right-7 z-10 hidden items-center gap-2 sm:flex lg:bottom-12 lg:right-[270px]" aria-label="Pilih desain">
                        @foreach($portfolioKatalogs as $katalog)
                            <button
                                type="button"
                                @click="select({{ $loop->index }})"
                                class="h-1.5 rounded-full bg-white transition-all duration-300"
                                :class="active === {{ $loop->index }} ? 'w-9 opacity-100' : 'w-4 opacity-45 hover:opacity-80'"
                                aria-label="Tampilkan {{ $katalog->nama_desain }}"
                                :aria-current="active === {{ $loop->index }} ? 'true' : 'false'"
                            ></button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid gap-7 md:grid-cols-3">
                @foreach($portfolioKatalogs->take(3) as $katalog)
                    <x-catalog-card :katalog="$katalog" />
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-gray-300 bg-stone-50 px-6 py-12 text-center">
                <p class="text-gray-600">Portofolio sedang disiapkan.</p>
            </div>
        @endif
    </div>
</section>

@include('home.partials.closing-cta')
@endsection
