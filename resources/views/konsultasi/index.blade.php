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

@endsection
