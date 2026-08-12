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
                            Buat Pesanan
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-amber-300">
                            Buat Pesanan
                        </a>
                    @endauth
                    <a href="https://wa.me/6285805908809?text=Halo%20Daiku%2C%20saya%20ingin%20bertanya%20tentang%20desain%20interior." target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-lg border border-green-600 px-6 py-3.5 font-semibold text-green-700 transition hover:bg-green-50">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M20.5 3.5A11.9 11.9 0 0 0 12.03 0C5.44 0 .08 5.35.08 11.94c0 2.1.55 4.15 1.59 5.96L0 24l6.24-1.64a11.9 11.9 0 0 0 5.78 1.48h.01c6.59 0 11.94-5.35 11.94-11.94 0-3.19-1.24-6.19-3.47-8.4Zm-8.47 18.3h-.01a9.86 9.86 0 0 1-5.03-1.38l-.36-.21-3.7.97.99-3.61-.23-.37a9.88 9.88 0 1 1 8.34 4.6Zm5.42-7.4c-.3-.15-1.77-.87-2.05-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.24-.46-2.36-1.47-.87-.78-1.46-1.74-1.63-2.04-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.5 1.7.64.72.23 1.37.2 1.89.12.58-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35Z"/>
                        </svg>
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
