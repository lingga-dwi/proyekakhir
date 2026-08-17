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
                    Daiku didirikan oleh Dhede dan Fendra untuk menangani kebutuhan desain dan pengerjaan ruang. Dalam perjalanannya, Daiku mengerjakan proyek hunian, kantor, dan ruang usaha di Pekanbaru serta sejumlah wilayah di Riau.
                </p>
                <p>
                    Pengalaman dari berbagai proyek tersebut membentuk lingkup kerja Daiku yang mencakup perencanaan desain, produksi furnitur custom, pekerjaan sipil, serta mekanikal dan elektrikal.
                </p>
            </div>

            <dl class="mt-8 grid grid-cols-2 gap-6 border-t border-stone-300 pt-6 sm:grid-cols-3">
                <div>
                    <dt class="text-[11px] uppercase tracking-[0.15em] text-stone-500">Sejak</dt>
                    <dd class="mt-1 font-semibold text-slate-900">2017</dd>
                </div>
                <div>
                    <dt class="text-[11px] uppercase tracking-[0.15em] text-stone-500">Lokasi</dt>
                    <dd class="mt-1 font-semibold text-slate-900">Pekanbaru</dd>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <dt class="text-[11px] uppercase tracking-[0.15em] text-stone-500">Bidang</dt>
                    <dd class="mt-1 font-semibold text-slate-900">Interior & Eksterior</dd>
                </div>
            </dl>

        </div>
    </div>
</section>

<section class="bg-[#d9d1c3] py-20 sm:py-24" aria-labelledby="principles-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-600">Prinsip Kerja Daiku</p>
            <h2 id="principles-title" class="mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">
                Dasar Daiku dalam menjalankan setiap pekerjaan.
            </h2>
        </div>

        <div class="mt-12 grid border-y border-stone-500/30 md:grid-cols-3">
            <article class="py-8 md:pr-8">
                <p class="text-xs font-semibold tracking-[0.18em] text-stone-600">01</p>
                <h3 class="mt-4 text-2xl text-slate-950">Keterlibatan menyeluruh</h3>
                <p class="mt-4 leading-7 text-slate-700">Daiku melibatkan pelanggan agar proses dan keputusan desain dapat dipahami dengan jelas.</p>
            </article>

            <article class="border-t border-stone-500/30 py-8 md:border-l md:border-t-0 md:px-8">
                <p class="text-xs font-semibold tracking-[0.18em] text-stone-600">02</p>
                <h3 class="mt-4 text-2xl text-slate-950">Perhatian terhadap detail</h3>
                <p class="mt-4 leading-7 text-slate-700">Detail pekerjaan diperhatikan sejak penyusunan konsep hingga pemasangan furnitur.</p>
            </article>

            <article class="border-t border-stone-500/30 py-8 md:border-l md:border-t-0 md:pl-8">
                <p class="text-xs font-semibold tracking-[0.18em] text-stone-600">03</p>
                <h3 class="mt-4 text-2xl text-slate-950">Menyesuaikan anggaran</h3>
                <p class="mt-4 leading-7 text-slate-700">Solusi pekerjaan disusun berdasarkan kebutuhan dan anggaran yang telah disepakati bersama pelanggan.</p>
            </article>
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

<section class="bg-[#f5f4f0] py-20 sm:py-28" aria-labelledby="expertise-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-[0.7fr_1.3fr] lg:gap-20">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Keahlian Daiku</p>
                <h2 id="expertise-title" class="mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">
                    Satu proses yang saling terhubung.
                </h2>
                <p class="mt-6 max-w-md leading-8 text-slate-600">
                    Setiap bidang bekerja sebagai bagian dari proses yang sama untuk menjaga kesinambungan antara desain dan pelaksanaan.
                </p>
            </div>

            <div class="border-t border-stone-300">
                <article class="grid gap-3 border-b border-stone-300 py-7 sm:grid-cols-[0.8fr_1.2fr]">
                    <h3 class="text-2xl text-slate-950">Desain & Perencanaan</h3>
                    <p class="leading-7 text-slate-600">Tata ruang, visual, material, dan detail sebagai dasar pelaksanaan proyek.</p>
                </article>
                <article class="grid gap-3 border-b border-stone-300 py-7 sm:grid-cols-[0.8fr_1.2fr]">
                    <h3 class="text-2xl text-slate-950">Teknik Sipil</h3>
                    <p class="leading-7 text-slate-600">Penyesuaian elemen ruang dari lantai, dinding, partisi, hingga plafon.</p>
                </article>
                <article class="grid gap-3 border-b border-stone-300 py-7 sm:grid-cols-[0.8fr_1.2fr]">
                    <h3 class="text-2xl text-slate-950">Furniture Custom</h3>
                    <p class="leading-7 text-slate-600">Furniture berdasarkan ukuran, fungsi penyimpanan, dan karakter desain ruang.</p>
                </article>
                <article class="grid gap-3 border-b border-stone-300 py-7 sm:grid-cols-[0.8fr_1.2fr]">
                    <h3 class="text-2xl text-slate-950">Mekanikal & Elektrikal</h3>
                    <p class="leading-7 text-slate-600">Dukungan desain dan instalasi untuk melengkapi fungsi ruang.</p>
                </article>
            </div>
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
