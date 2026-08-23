@extends('layouts.main')

@section('title', 'Tentang Daiku Interior & Exterior Pekanbaru')
@section('meta_description', 'Kenali Daiku Interior & Exterior, studio desain dan pengerjaan interior-eksterior di Pekanbaru yang melayani klien sejak 2017.')
@section('meta_image', asset('images/founder-daiku.jpg'))

@section('content')
<section class="bg-[#f5f4f0] py-12 sm:py-16 lg:py-14" aria-labelledby="about-title">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.82fr_1.18fr] lg:items-center lg:gap-14 lg:px-8">
        <div class="max-w-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Daiku Interior & Exterior</p>
            <h1 id="about-title" class="mt-5 text-5xl leading-[0.95] text-slate-950 sm:text-6xl">
                Tentang Daiku
            </h1>
            <p class="mt-7 text-base leading-8 text-slate-600">
                Daiku Interior &amp; Exterior menyediakan jasa desain dan pengerjaan interior-eksterior untuk hunian, ruang kerja, dan ruang usaha di Pekanbaru. Daiku menangani penataan ruang pada bangunan baru maupun renovasi.
            </p>
            <p class="mt-4 text-base leading-8 text-slate-600">
                Setiap pekerjaan direncanakan berdasarkan fungsi ruang, ukuran, kondisi bangunan, kebutuhan penyimpanan, preferensi desain, dan anggaran klien. Layanannya mencakup perencanaan desain, pekerjaan sipil, produksi furnitur custom, serta pekerjaan mekanikal dan elektrikal.
            </p>

        </div>

        <figure class="bg-stone-200">
            <img src="{{ asset('images/founder-daiku.jpg') }}"
                 alt="Dhede dan Fendra, founder Daiku Interior"
                 class="h-auto w-full"
                 fetchpriority="high"
                 decoding="async">
        </figure>
    </div>
</section>

<section class="bg-white py-20 sm:py-28" aria-labelledby="story-title">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:gap-20 lg:px-8">
        <figure class="overflow-hidden bg-stone-100">
            <img src="{{ asset('images/katalog/curated/ruang-keluarga/2022-04-04_Cb6kWZCBUST/daikuinterior_Cb6kWZCBUST_0.jpg') }}"
                 alt="Detail furniture hasil pengerjaan Daiku"
                 class="h-[480px] w-full object-cover sm:h-[650px]"
                 loading="lazy"
                 decoding="async">
        </figure>

        <div class="max-w-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Kisah Kami</p>
            <h2 id="story-title" class="mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">
                Perjalanan Daiku sejak awal berdiri.
            </h2>

            <div class="mt-8 space-y-5 leading-8 text-slate-600">
                <p>
                    Daiku Interior &amp; Exterior didirikan oleh Dhede dan Fendra pada 2017 di Pekanbaru. Sejak awal, Daiku bergerak dalam bidang interior dan eksterior untuk hunian, kantor, serta ruang usaha di Pekanbaru dan sejumlah wilayah di Riau.
                </p>
                <p>
                    Pengalaman dari berbagai proyek tersebut membentuk lingkup kerja Daiku yang mencakup perencanaan desain, produksi furnitur custom, pekerjaan sipil, serta mekanikal dan elektrikal.
                </p>
            </div>

        </div>
    </div>
</section>

<section class="bg-[#d9d1c3] py-20 sm:py-24" aria-labelledby="principles-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <h2 id="principles-title" class="text-4xl leading-tight text-slate-950 sm:text-5xl">
                Visi dan Misi Daiku
            </h2>
        </div>

        <div class="mx-auto mt-14 max-w-2xl text-center">
            <div class="flex items-center justify-center gap-4">
                <span class="h-px flex-1 bg-stone-500/30"></span>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Visi</p>
                <span class="h-px flex-1 bg-stone-500/30"></span>
            </div>
            <p class="mt-6 text-xl leading-8 text-slate-800 sm:text-2xl">
                Menjadi perusahaan interior dan konstruksi terpercaya di Riau yang menghadirkan solusi desain inovatif, berkualitas, dan berorientasi pada kepuasan pelanggan.
            </p>
        </div>

        <div class="mx-auto mt-14 max-w-2xl">
            <div class="flex items-center justify-center gap-4">
                <span class="h-px flex-1 bg-stone-500/30"></span>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Misi</p>
                <span class="h-px flex-1 bg-stone-500/30"></span>
            </div>
            <ol class="mt-8 space-y-4">
                <li class="flex items-start gap-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-amber-700/40 text-xs font-semibold text-amber-700">01</span>
                    <span class="mt-1 h-5 w-px shrink-0 bg-stone-500/30"></span>
                    <p class="leading-7 text-slate-700">Memberikan layanan desain, renovasi, dan pembuatan furnitur custom yang profesional dan berkualitas.</p>
                </li>
                <li class="flex items-start gap-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-amber-700/40 text-xs font-semibold text-amber-700">02</span>
                    <span class="mt-1 h-5 w-px shrink-0 bg-stone-500/30"></span>
                    <p class="leading-7 text-slate-700">Mengutamakan kepuasan pelanggan melalui pelayanan yang responsif, transparan, dan tepat waktu.</p>
                </li>
                <li class="flex items-start gap-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-amber-700/40 text-xs font-semibold text-amber-700">03</span>
                    <span class="mt-1 h-5 w-px shrink-0 bg-stone-500/30"></span>
                    <p class="leading-7 text-slate-700">Menghasilkan karya yang mengutamakan fungsi, estetika, serta kualitas material dan pengerjaan.</p>
                </li>
                <li class="flex items-start gap-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-amber-700/40 text-xs font-semibold text-amber-700">04</span>
                    <span class="mt-1 h-5 w-px shrink-0 bg-stone-500/30"></span>
                    <p class="leading-7 text-slate-700">Terus berinovasi mengikuti perkembangan desain, teknologi, dan kebutuhan pasar.</p>
                </li>
                <li class="flex items-start gap-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-amber-700/40 text-xs font-semibold text-amber-700">05</span>
                    <span class="mt-1 h-5 w-px shrink-0 bg-stone-500/30"></span>
                    <p class="leading-7 text-slate-700">Membangun hubungan jangka panjang dengan pelanggan melalui kepercayaan, integritas, dan hasil kerja terbaik.</p>
                </li>
            </ol>
        </div>
    </div>
</section>

<section class="bg-white py-20 sm:py-28" aria-labelledby="work-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Pekerjaan Kami</p>
            <h2 id="work-title" class="mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">Ruang yang telah diwujudkan.</h2>
        </div>

        @php
            $workGalleryImages = collect([6, 7, 8, 9, 10, 11, 12, 13, 14, 16, 17, 18, 20, 22, 23])
                ->map(fn ($n) => asset('images/pekerjaan-kami/1787202465907-069acad7-c8e2-4dd7-ac9c-cd1e5cb94c07_'.$n.'.jpg'))
                ->values();
        @endphp

        <div class="mt-14 grid gap-5 sm:grid-cols-3" x-data="{ open: false, activeIndex: 0, images: @js($workGalleryImages) }" @keydown.escape.window="open = false" @keydown.arrow-right.window="if (open) activeIndex = (activeIndex + 1) % images.length" @keydown.arrow-left.window="if (open) activeIndex = (activeIndex - 1 + images.length) % images.length">
            @foreach($workGalleryImages as $index => $imageUrl)
                <figure class="overflow-hidden bg-stone-100">
                    <button type="button" class="block w-full cursor-zoom-in" @click="open = true; activeIndex = {{ $index }}" aria-label="Perbesar foto pekerjaan {{ $index + 1 }}">
                        <img src="{{ $imageUrl }}" alt="Hasil pengerjaan Daiku Interior & Exterior {{ $index + 1 }}" class="h-auto w-full object-contain transition duration-300 hover:scale-105" loading="lazy" decoding="async">
                    </button>
                </figure>
            @endforeach

            <div
                x-show="open"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                @click.self="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <button type="button" class="absolute right-4 top-4 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" @click="open = false" aria-label="Tutup">
                    <i class="fas fa-times text-lg" aria-hidden="true"></i>
                </button>

                <button type="button" class="absolute left-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:left-4" @click="activeIndex = (activeIndex - 1 + images.length) % images.length" aria-label="Foto sebelumnya">
                    <i class="fas fa-chevron-left text-lg" aria-hidden="true"></i>
                </button>

                <img :src="images[activeIndex]" alt="Foto pekerjaan Daiku diperbesar" class="max-h-[85vh] max-w-full object-contain" @click.stop>

                <button type="button" class="absolute right-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-4" @click="activeIndex = (activeIndex + 1) % images.length" aria-label="Foto berikutnya">
                    <i class="fas fa-chevron-right text-lg" aria-hidden="true"></i>
                </button>

                <p class="absolute bottom-4 left-1/2 -translate-x-1/2 text-sm text-white/70" x-text="`${activeIndex + 1} / ${images.length}`"></p>
            </div>
        </div>
    </div>
</section>

@endsection
