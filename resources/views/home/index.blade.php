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
        <div class="mx-auto max-w-5xl text-white">
            <h1 class="text-5xl font-bold leading-[0.98] tracking-tight sm:text-6xl lg:text-7xl">
                Interior yang tertata,
                <span class="mt-2 block font-light italic">dari layout hingga furnitur custom.</span>
            </h1>
            <p class="mx-auto mt-7 max-w-3xl text-base leading-relaxed text-white/90 sm:text-lg">
                Untuk rumah, kantor, dan tempat usaha di Pekanbaru. Siapkan ukuran, denah, atau foto kondisi ruang agar pembahasan desain lebih terarah.
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('konsultasi.index') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-amber-300">
                    Bahas Proyek Anda
                </a>
                <a href="{{ route('katalog') }}" class="inline-flex items-center justify-center rounded-lg border border-white/60 bg-white/5 px-6 py-3.5 font-semibold text-white backdrop-blur-sm transition hover:bg-white hover:text-slate-950">
                    Jelajahi Portofolio
                </a>
            </div>
        </div>
    </div>

    <div class="absolute inset-x-0 bottom-5 z-20 flex justify-center px-4 sm:bottom-6" aria-label="Keunggulan layanan Daiku">
        <div class="grid w-full max-w-[500px] grid-cols-3 items-center gap-3 rounded-xl bg-[#241f1d]/65 px-4 py-3 text-white backdrop-blur-sm sm:gap-5 sm:px-5">
            <div class="flex items-center justify-center gap-2">
                <i class="fas fa-ruler-combined shrink-0 text-xs text-emerald-400" aria-hidden="true"></i>
                <span class="text-[10px] font-medium leading-tight sm:text-xs">Custom sesuai ukuran</span>
            </div>
            <div class="flex items-center justify-center gap-2">
                <i class="fas fa-home shrink-0 text-xs text-amber-400" aria-hidden="true"></i>
                <span class="text-[10px] font-medium leading-tight sm:text-xs">Beragam jenis ruang</span>
            </div>
            <div class="flex items-center justify-center gap-2">
                <i class="fas fa-shield-alt shrink-0 text-xs text-blue-400" aria-hidden="true"></i>
                <span class="text-[10px] font-medium leading-tight sm:text-xs">Progres dapat dipantau</span>
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

@include('home.partials.case-study')

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

        @if($featuredKatalogs->isNotEmpty())
            <div class="grid gap-7 md:grid-cols-3">
                @foreach($featuredKatalogs as $katalog)
                    <a href="{{ route('katalog.detail', $katalog->id) }}" class="group overflow-hidden rounded-2xl bg-white ring-1 ring-gray-200 transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="h-64 overflow-hidden bg-gray-100">
                            @if($katalog->gambar_utama_url)
                                <img src="{{ $katalog->gambar_utama_url }}"
                                     alt="{{ $katalog->nama_desain }}"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                     loading="lazy"
                                     decoding="async"
                                     width="800"
                                     height="640">
                            @endif
                        </div>
                        <div class="p-6">
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                                {{ $katalog->category?->name ?? 'Tanpa kategori' }}
                            </p>
                            <h3 class="mt-2 text-xl font-bold text-slate-900">{{ $katalog->nama_desain }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ Str::limit($katalog->deskripsi, 110) }}</p>
                        </div>
                    </a>
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
