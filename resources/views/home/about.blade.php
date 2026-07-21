@extends('layouts.main')

@section('title', 'Tentang Daiku Interior & Exterior Pekanbaru')
@section('meta_description', 'Kenali Daiku Interior & Exterior, studio desain dan pengerjaan interior-eksterior di Pekanbaru yang melayani klien sejak 2017.')
@section('meta_image', asset('images/katalog/rumah/rumah (660).jpg'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Libre+Caslon+Display&display=swap" rel="stylesheet">
<style>
    .about-display {
        font-family: 'Libre Caslon Display', Georgia, serif;
    }
</style>
@endpush

@section('content')
<section class="bg-[#f5f4f0] py-12 sm:py-16 lg:py-14" aria-labelledby="about-title">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.82fr_1.18fr] lg:items-center lg:gap-14 lg:px-8">
        <div class="max-w-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Daiku Interior & Exterior</p>
            <h1 id="about-title" class="about-display mt-5 text-5xl leading-[0.95] text-slate-950 sm:text-6xl">
                Tentang Daiku
            </h1>
            <p class="about-display mt-7 text-2xl leading-snug text-slate-700 sm:text-3xl">
                Studio desain dan pengerjaan ruang yang berbasis di Pekanbaru.
            </p>
            <p class="mt-7 text-base leading-8 text-slate-600">
                Sejak 2017, Daiku membantu pemilik hunian, kantor, dan ruang usaha merencanakan hingga mewujudkan ruang yang mempertemukan fungsi, karakter, dan detail.
            </p>

            <dl class="mt-9 grid grid-cols-2 gap-6 border-t border-stone-300 pt-6 sm:grid-cols-3">
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

        <figure class="overflow-hidden bg-stone-200">
            <img src="{{ asset('images/katalog/rumah/rumah (660).jpg') }}"
                 alt="Interior hunian karya Daiku"
                 class="h-[400px] w-full object-cover object-center sm:h-[500px]"
                 fetchpriority="high"
                 decoding="async">
        </figure>
    </div>
</section>

<section class="bg-white py-20 sm:py-28" aria-labelledby="story-title">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:gap-20 lg:px-8">
        <figure class="overflow-hidden bg-stone-100">
            <img src="{{ asset('images/katalog/rumah/rumah (541).jpg') }}"
                 alt="Detail furniture hasil pengerjaan Daiku"
                 class="h-[480px] w-full object-cover sm:h-[650px]"
                 loading="lazy"
                 decoding="async">
        </figure>

        <div class="max-w-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Kisah Kami</p>
            <h2 id="story-title" class="about-display mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">
                Bertumbuh bersama kebutuhan ruang di Pekanbaru.
            </h2>
            <div class="mt-8 space-y-5 leading-8 text-slate-600">
                <p>
                    Daiku beroperasi sejak 2017 sebagai studio interior dan eksterior di Pekanbaru. Pekerjaan Daiku mencakup desain interior layanan penuh, pembangunan rumah baru, renovasi hunian, serta penataan ruang kerja dan usaha dalam berbagai skala.
                </p>
                <p>
                    Setiap proyek berawal dari percakapan tentang cara ruang akan digunakan. Kebutuhan aktivitas, penyimpanan, karakter visual, kondisi bangunan, dan anggaran menjadi dasar sebelum keputusan desain dibuat.
                </p>
                <p>
                    Perencanaan tersebut kemudian dihubungkan dengan produksi furniture custom, pekerjaan sipil, serta mekanikal dan elektrikal agar gagasan yang disepakati dapat diwujudkan secara terarah.
                </p>
            </div>

            <dl class="mt-10 grid grid-cols-2 gap-7 border-t border-stone-200 pt-8 sm:grid-cols-3">
                <div>
                    <dt class="text-xs uppercase tracking-[0.16em] text-stone-400">Sejak</dt>
                    <dd class="about-display mt-2 text-3xl text-slate-950">2017</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-[0.16em] text-stone-400">Berbasis</dt>
                    <dd class="about-display mt-2 text-3xl text-slate-950">Riau</dd>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <dt class="text-xs uppercase tracking-[0.16em] text-stone-400">Bidang</dt>
                    <dd class="about-display mt-2 text-3xl text-slate-950">Interior</dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<section class="bg-[#d9d1c3] py-20 text-center sm:py-28" aria-labelledby="belief-title">
    <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-600">Yang Kami Percaya</p>
        <h2 id="belief-title" class="about-display mx-auto mt-7 max-w-4xl text-4xl leading-tight text-slate-950 sm:text-6xl">
            Ruang yang baik tidak hanya menarik dilihat, tetapi juga nyaman dijalani setiap hari.
        </h2>
        <p class="mx-auto mt-8 max-w-2xl leading-8 text-slate-700">
            Karena itu, Daiku menjaga hubungan antara arsitektur dan tempat, ruang dan bentuk, warna dan material, serta rencana dan anggaran dalam setiap pekerjaan.
        </p>
    </div>
</section>

<section class="bg-white py-20 sm:py-28" aria-labelledby="approach-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Pendekatan Daiku</p>
            <h2 id="approach-title" class="about-display mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">
                Perhatian yang menyeluruh, dari konsep sampai pemasangan.
            </h2>
        </div>

        <div class="mt-16 grid gap-8 lg:grid-cols-2 lg:gap-10">
            <article>
                <figure class="overflow-hidden bg-stone-100">
                    <img src="{{ asset('images/katalog/rumah/rumah (601).jpg') }}"
                         alt="Furniture custom pada area servis"
                         class="h-[430px] w-full object-cover sm:h-[560px]"
                         loading="lazy"
                         decoding="async">
                </figure>
                <h3 class="about-display mt-7 text-3xl text-slate-950">Memahami sebelum merancang</h3>
                <p class="mt-4 max-w-xl leading-8 text-slate-600">
                    Keterlibatan personal membantu tim memahami prioritas klien dan menjelaskan hubungan antara keputusan desain, proses pengerjaan, serta hasil akhir.
                </p>
            </article>

            <article class="lg:pt-20">
                <figure class="overflow-hidden bg-stone-100">
                    <img src="{{ asset('images/katalog/rumah/rumah (606).jpg') }}"
                         alt="Furniture kamar tidur hasil pengerjaan Daiku"
                         class="h-[430px] w-full object-cover sm:h-[560px]"
                         loading="lazy"
                         decoding="async">
                </figure>
                <h3 class="about-display mt-7 text-3xl text-slate-950">Menjaga detail sampai akhir</h3>
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
                <h2 id="expertise-title" class="about-display mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">
                    Satu proses yang saling terhubung.
                </h2>
                <p class="mt-6 max-w-md leading-8 text-slate-600">
                    Setiap bidang bekerja sebagai bagian dari proses yang sama untuk menjaga kesinambungan antara desain dan pelaksanaan.
                </p>
            </div>

            <div class="border-t border-stone-300">
                <article class="grid gap-3 border-b border-stone-300 py-7 sm:grid-cols-[0.8fr_1.2fr]">
                    <h3 class="about-display text-2xl text-slate-950">Desain & Perencanaan</h3>
                    <p class="leading-7 text-slate-600">Tata ruang, visual, material, dan detail sebagai dasar pelaksanaan proyek.</p>
                </article>
                <article class="grid gap-3 border-b border-stone-300 py-7 sm:grid-cols-[0.8fr_1.2fr]">
                    <h3 class="about-display text-2xl text-slate-950">Teknik Sipil</h3>
                    <p class="leading-7 text-slate-600">Penyesuaian elemen ruang dari lantai, dinding, partisi, hingga plafon.</p>
                </article>
                <article class="grid gap-3 border-b border-stone-300 py-7 sm:grid-cols-[0.8fr_1.2fr]">
                    <h3 class="about-display text-2xl text-slate-950">Furniture Custom</h3>
                    <p class="leading-7 text-slate-600">Furniture berdasarkan ukuran, fungsi penyimpanan, dan karakter desain ruang.</p>
                </article>
                <article class="grid gap-3 border-b border-stone-300 py-7 sm:grid-cols-[0.8fr_1.2fr]">
                    <h3 class="about-display text-2xl text-slate-950">Mekanikal & Elektrikal</h3>
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
            <h2 id="work-title" class="about-display mt-5 text-4xl leading-tight text-slate-950 sm:text-5xl">Ruang yang telah diwujudkan.</h2>
        </div>

        <div class="mt-14 grid gap-5 sm:grid-cols-3">
            <figure class="overflow-hidden bg-stone-100">
                <img src="{{ asset('images/katalog/rumah/rumah (556).jpg') }}" alt="Area dapur dan ruang makan hasil pengerjaan" class="aspect-[3/4] h-full w-full object-cover" loading="lazy" decoding="async">
                <figcaption class="px-1 pb-2 pt-5 text-center text-sm uppercase tracking-[0.14em] text-slate-500">Dapur & ruang makan</figcaption>
            </figure>
            <figure class="overflow-hidden bg-stone-100 sm:mt-12">
                <img src="{{ asset('images/katalog/rumah/rumah (580).jpg') }}" alt="Panel televisi dan penyimpanan hasil pengerjaan" class="aspect-[3/4] h-full w-full object-cover" loading="lazy" decoding="async">
                <figcaption class="px-1 pb-2 pt-5 text-center text-sm uppercase tracking-[0.14em] text-slate-500">Panel TV & penyimpanan</figcaption>
            </figure>
            <figure class="overflow-hidden bg-stone-100">
                <img src="{{ asset('images/katalog/rumah/rumah (550).jpg') }}" alt="Ruang keluarga hasil pengerjaan Daiku" class="aspect-[3/4] h-full w-full object-cover" loading="lazy" decoding="async">
                <figcaption class="px-1 pb-2 pt-5 text-center text-sm uppercase tracking-[0.14em] text-slate-500">Ruang keluarga</figcaption>
            </figure>
        </div>
    </div>
</section>

<section class="bg-slate-950 py-20 text-white sm:py-24" aria-labelledby="today-title">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[1fr_1fr] lg:gap-20 lg:px-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-400">Daiku Hari Ini</p>
            <h2 id="today-title" class="about-display mt-5 max-w-xl text-4xl leading-tight sm:text-5xl">
                Berkarya dari Pekanbaru untuk berbagai kebutuhan ruang.
            </h2>
            <p class="mt-6 max-w-xl leading-8 text-slate-400">
                Daiku terus mengembangkan layanan interior dan eksterior dengan menjaga komunikasi, ketelitian, dan solusi yang menyesuaikan kebutuhan setiap proyek.
            </p>
        </div>

        <address class="grid gap-8 border-t border-white/20 pt-8 not-italic sm:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Studio</p>
                <p class="mt-3 leading-7 text-slate-200">Jl. Yos Sudarso, Pekanbaru 28154</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Telepon</p>
                <a href="tel:+628117597766" class="mt-3 block text-slate-200 transition hover:text-amber-300">0811-7597-766</a>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Email</p>
                <a href="mailto:fendrabudiono@gmail.com" class="mt-3 block break-all text-slate-200 transition hover:text-amber-300">fendrabudiono@gmail.com</a>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Instagram</p>
                <a href="https://www.instagram.com/daikuinterior/" target="_blank" rel="noopener noreferrer" class="mt-3 block text-slate-200 transition hover:text-amber-300">@daikuinterior</a>
            </div>
        </address>
    </div>
</section>
@endsection
