@extends('layouts.main')

@section('title', 'Konsultasi Desain Interior Pekanbaru - Daiku Interior')
@section('meta_description', 'Mulai konsultasi desain interior bersama Daiku di Pekanbaru dengan alur kebutuhan, survei, perencanaan, dan pengerjaan yang terarah.')

@section('content')
@php
    $coverImage = $featuredKatalogs->first()?->gambar_utama_url
        ?? asset('images/hero/daiku-home-hero.jpg');
@endphp

<section class="bg-white py-6 sm:py-8">
    <div class="mx-auto grid min-h-[68vh] max-w-7xl overflow-hidden rounded-2xl bg-white lg:grid-cols-2">
        <div class="flex items-center px-4 py-16 sm:px-8 lg:px-12">
            <div class="max-w-xl">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Konsultasi Daiku</p>
                <h1 class="mt-4 text-4xl font-bold leading-tight text-slate-900 sm:text-5xl">Mulai dari kebutuhan ruang Anda.</h1>
                <p class="mt-6 text-lg leading-relaxed text-gray-600">
                    Mulai konsultasi dengan mengisi kebutuhan ruang, ukuran, preferensi, dan kendala Anda. Informasi ini membantu tim Daiku meninjau proyek sebelum melanjutkan pembahasan melalui WhatsApp.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @auth
                        <a href="{{ route('konsultasi.create') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-amber-300">
                            Buat Permintaan
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-amber-300">
                            Buat Permintaan
                        </a>
                    @endauth
                    <a href="https://wa.me/6285805908809?text=Halo%20Daiku%2C%20saya%20ingin%20bertanya%20tentang%20desain%20interior." target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-lg border border-green-600 px-6 py-3.5 font-semibold text-green-700 transition hover:bg-green-50">
                        <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i>
                        Chat WhatsApp Langsung
                    </a>
                </div>
                <p class="mt-5 text-sm leading-relaxed text-gray-500">
                    Setelah form dikirim, admin akan menghubungi Anda melalui WhatsApp.
                </p>
            </div>
        </div>
        <div class="min-h-[420px] overflow-hidden bg-gray-100 lg:min-h-full">
            <img src="{{ $coverImage }}" alt="Inspirasi desain interior Daiku" class="h-full w-full object-cover" fetchpriority="high" decoding="async">
        </div>
    </div>
</section>

<section class="bg-stone-50 py-20" aria-labelledby="consultation-process">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto mb-14 max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Proses konsultasi</p>
            <h2 id="consultation-process" class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Informasi yang jelas, keputusan yang lebih tepat</h2>
        </div>
        <div class="grid gap-6 md:grid-cols-3">
            <article class="rounded-2xl bg-white p-7 ring-1 ring-gray-200">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <i class="fas fa-message text-xl" aria-hidden="true"></i>
                </div>
                <h3 class="mt-5 text-xl font-bold text-slate-900">1. Sampaikan kebutuhan</h3>
                <p class="mt-3 leading-relaxed text-gray-600">Isi jenis ruang, ukuran, preferensi gaya, waktu, dan referensi yang tersedia.</p>
            </article>
            <article class="rounded-2xl bg-white p-7 ring-1 ring-gray-200">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <i class="fas fa-clipboard-check text-xl" aria-hidden="true"></i>
                </div>
                <h3 class="mt-5 text-xl font-bold text-slate-900">2. Tinjauan awal</h3>
                <p class="mt-3 leading-relaxed text-gray-600">Tim meninjau data untuk memahami konteks dan menyiapkan pembahasan yang relevan.</p>
            </article>
            <article class="rounded-2xl bg-white p-7 ring-1 ring-gray-200">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <i class="fas fa-compass-drafting text-xl" aria-hidden="true"></i>
                </div>
                <h3 class="mt-5 text-xl font-bold text-slate-900">3. Tentukan langkah</h3>
                <p class="mt-3 leading-relaxed text-gray-600">Survei, ruang lingkup desain, jadwal, dan estimasi dibicarakan sesuai kondisi proyek.</p>
            </article>
        </div>
    </div>
</section>

@if($featuredKatalogs->isNotEmpty())
<section class="bg-white py-20" aria-labelledby="consultation-portfolio">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Referensi awal</p>
                <h2 id="consultation-portfolio" class="mt-3 text-3xl font-bold text-slate-900">Pilih arah visual yang Anda sukai</h2>
            </div>
            <a href="{{ route('katalog') }}" class="font-semibold text-amber-700 hover:text-amber-800">Lihat semua desain</a>
        </div>
        <div class="grid gap-7 md:grid-cols-3">
            @foreach($featuredKatalogs as $katalog)
                <a href="{{ route('katalog.detail', $katalog->id) }}" class="group overflow-hidden rounded-2xl ring-1 ring-gray-200 transition hover:-translate-y-1 hover:shadow-xl">
                    <img src="{{ $katalog->gambar_utama_url }}"
                         alt="{{ $katalog->nama_desain }}"
                         class="h-60 w-full object-cover transition duration-500 group-hover:scale-105"
                         loading="lazy"
                         decoding="async"
                         width="800"
                         height="600">
                    <div class="bg-white p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">{{ $katalog->category?->name ?? 'Tanpa kategori' }}</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">{{ $katalog->nama_desain }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
