@extends('layouts.dashboard')

@section('title', 'Detail Katalog - Admin Dashboard')
@section('page-title', 'Detail Katalog')
@section('page-description', 'Tinjau informasi dan media portofolio desain')

@section('content')
@php
    $statusLabel = match($katalog->status) {
        'published' => 'Dipublikasikan',
        'archived' => 'Diarsipkan',
        default => 'Draft',
    };
    $statusClass = match($katalog->status) {
        'published' => 'bg-blue-50 text-blue-700',
        'archived' => 'bg-stone-100 text-stone-600',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <a href="{{ route('admin.katalog.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-950"><i class="fas fa-arrow-left text-xs"></i>Kembali ke katalog</a>
    <a href="{{ route('admin.katalog.edit', $katalog) }}" class="inline-flex items-center justify-center rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300"><i class="fas fa-pencil-alt mr-2 text-xs"></i>Edit Katalog</a>
</div>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-amber-700">{{ $katalog->category?->name ?? 'Tanpa kategori' }}</p>
                    <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ $katalog->nama_desain }}</h2>
                </div>
                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="mt-5">
                <h3 class="text-sm font-semibold text-slate-700">Deskripsi</h3>
                <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $katalog->deskripsi }}</p>
            </div>

            <dl class="mt-6 grid gap-4 rounded-xl bg-slate-50 p-4 sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Gaya desain</dt><dd class="mt-1 text-sm font-semibold text-slate-700">{{ $katalog->style_tags ?: 'Belum diisi' }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Ukuran ruangan</dt><dd class="mt-1 text-sm font-semibold text-slate-700">{{ $katalog->room_size ? $katalog->room_size.' m²' : 'Belum diisi' }}</dd></div>
            </dl>

            @if($katalog->inspiration_story)
                <div class="mt-6 border-t border-slate-100 pt-5">
                    <h3 class="text-sm font-semibold text-slate-700">Cerita inspirasi</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $katalog->inspiration_story }}</p>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-semibold text-slate-950">Galeri</h2>
            @if(count($katalog->galeri_gambar_urls) > 0)
                <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3">
                    @foreach($katalog->galeri_gambar_urls as $image)
                        <img src="{{ $image }}" alt="Galeri {{ $katalog->nama_desain }}" class="aspect-[4/3] w-full rounded-xl border border-slate-200 object-cover" loading="lazy">
                    @endforeach
                </div>
            @else
                <div class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-400">Belum ada gambar galeri.</div>
            @endif
        </section>
    </div>

    <aside class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:sticky xl:top-24">
        @if($katalog->gambar_utama && $katalog->gambar_utama_url)
            <img src="{{ $katalog->gambar_utama_url }}" alt="{{ $katalog->nama_desain }}" class="aspect-[4/3] w-full object-cover">
        @else
            <div class="flex aspect-[4/3] items-center justify-center bg-slate-50 text-slate-300"><i class="fas fa-image text-3xl"></i></div>
        @endif
        <div class="p-5 text-sm text-slate-500">
            <p><span class="font-semibold text-slate-700">{{ 1 + count($katalog->galeri_gambar ?? []) }}</span> media tersimpan</p>
            <p class="mt-1">Terakhir diubah {{ $katalog->updated_at->translatedFormat('d M Y, H:i') }} WIB</p>
        </div>
    </aside>
</div>
@endsection
