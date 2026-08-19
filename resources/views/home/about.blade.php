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

<section class="bg-white py-20 sm:py-28" aria-labelledby="approach-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Pendekatan Daiku</p>
            <h2 id="approach-title" class="mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">
                Perhatian yang menyeluruh, dari konsep sampai pemasangan.
            </h2>
        </div>

        <div class="mt-16 grid gap-8 lg:grid-cols-2 lg:gap-10">
            <article>
                <figure class="overflow-hidden bg-stone-100">
                    <img src="{{ asset('images/katalog/curated/dapur/2022-02-15_CZ_Zp2opU9r/daikuinterior_CZ_Zp2opU9r_0.jpg') }}"
                         alt="Furniture custom pada area servis"
                         class="h-[430px] w-full object-cover sm:h-[560px]"
                         loading="lazy"
                         decoding="async">
                </figure>
                <h3 class="mt-7 text-3xl text-slate-950">Memahami sebelum merancang</h3>
                <p class="mt-4 max-w-xl leading-8 text-slate-600">
                    Keterlibatan personal membantu tim memahami prioritas klien dan menjelaskan hubungan antara keputusan desain, proses pengerjaan, serta hasil akhir.
                </p>
            </article>

            <article class="lg:pt-20">
                <figure class="overflow-hidden bg-stone-100">
                    <img src="{{ asset('images/katalog/curated/ruang-keluarga/2022-04-04_Cb6kWZCBUST/daikuinterior_Cb6kWZCBUST_1.jpg') }}"
                         alt="Furniture kamar tidur hasil pengerjaan Daiku"
                         class="h-[430px] w-full object-cover sm:h-[560px]"
                         loading="lazy"
                         decoding="async">
                </figure>
                <h3 class="mt-7 text-3xl text-slate-950">Menjaga detail sampai akhir</h3>
                <p class="mt-4 max-w-xl leading-8 text-slate-600">
                    Perhatian pada ukuran, material, produksi, dan pemasangan dijaga agar pekerjaan yang terbangun tetap selaras dengan rencana dan anggaran.
                </p>
            </article>
        </div>
    </div>
</section>

<section class="bg-white py-20 sm:py-28" aria-labelledby="work-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Pekerjaan Kami</p>
            <h2 id="work-title" class="mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">Ruang yang telah diwujudkan.</h2>
        </div>

        <div class="mt-14 grid gap-5 sm:grid-cols-3">
            <figure class="overflow-hidden bg-stone-100">
                <img src="{{ asset('images/katalog/curated/dapur/2022-02-19_CaJABsOpCeJ/daikuinterior_CaJABsOpCeJ_0.jpg') }}" alt="Area dapur dan ruang makan hasil pengerjaan" class="aspect-[3/4] h-full w-full object-cover" loading="lazy" decoding="async">
                <figcaption class="px-1 pb-2 pt-5 text-center text-sm uppercase tracking-[0.14em] text-slate-500">Dapur & ruang makan</figcaption>
            </figure>
            <figure class="overflow-hidden bg-stone-100 sm:mt-12">
                <img src="{{ asset('images/katalog/curated/ruang-keluarga/2022-04-04_Cb6kWZCBUST/daikuinterior_Cb6kWZCBUST_1.jpg') }}" alt="Panel televisi dan penyimpanan hasil pengerjaan" class="aspect-[3/4] h-full w-full object-cover" loading="lazy" decoding="async">
                <figcaption class="px-1 pb-2 pt-5 text-center text-sm uppercase tracking-[0.14em] text-slate-500">Panel TV & penyimpanan</figcaption>
            </figure>
            <figure class="overflow-hidden bg-stone-100">
                <img src="{{ asset('images/katalog/curated/ruang-keluarga/2022-04-04_Cb6kWZCBUST/daikuinterior_Cb6kWZCBUST_0.jpg') }}" alt="Ruang keluarga hasil pengerjaan Daiku" class="aspect-[3/4] h-full w-full object-cover" loading="lazy" decoding="async">
                <figcaption class="px-1 pb-2 pt-5 text-center text-sm uppercase tracking-[0.14em] text-slate-500">Ruang keluarga</figcaption>
            </figure>
        </div>
    </div>
</section>

@endsection
