@extends('layouts.dashboard')

@section('title', 'Kelola FAQ - Daiku Interior')
@section('page-title', 'Kelola FAQ')
@section('page-description', 'Atur pertanyaan dan jawaban yang tampil di Beranda')

@section('content')
<div class="w-full max-w-none">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-950">Pertanyaan Umum</h2>
            <p class="mt-1 text-sm text-slate-500">Kelola konten, status tampil, dan urutan FAQ di Beranda.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('home') }}#faq-title" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-amber-300 hover:bg-amber-50">
                <i class="fas fa-external-link-alt text-xs" aria-hidden="true"></i>Lihat di Beranda
            </a>
            <a href="{{ route('admin.faq.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>Tambah FAQ
            </a>
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach([['Total FAQ', $stats['total'], 'fa-list', 'bg-slate-100 text-slate-600'], ['Tampil di Beranda', $stats['active'], 'fa-eye', 'bg-emerald-50 text-emerald-700'], ['Disembunyikan', $stats['hidden'], 'fa-eye-slash', 'bg-amber-50 text-amber-700']] as [$label, $value, $icon, $tone])
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tone }}"><i class="fas {{ $icon }}" aria-hidden="true"></i></span><div><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</p><p class="mt-1 text-2xl font-bold text-slate-950">{{ $value }}</p></div></div>
            </article>
        @endforeach
    </div>

    <form method="GET" class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row">
        <div class="relative flex-1"><i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Cari pertanyaan atau jawaban..." class="w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-4 text-sm focus:border-amber-500 focus:ring-amber-500"></div>
        <select name="status" class="rounded-xl border-slate-300 px-3 py-2.5 text-sm focus:border-amber-500 focus:ring-amber-500"><option value="">Semua status</option><option value="active" @selected(request('status') === 'active')>Tampil</option><option value="hidden" @selected(request('status') === 'hidden')>Disembunyikan</option></select>
        <button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Terapkan</button>
        @if(request()->hasAny(['search', 'status']))<a href="{{ route('admin.faq.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Reset</a>@endif
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="divide-y divide-slate-100">
            @forelse($faqs as $faq)
                <article class="p-5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Urutan {{ $faq->sort_order }}</span><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $faq->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $faq->is_active ? 'Tampil' : 'Disembunyikan' }}</span></div>
                            <h3 class="mt-3 text-base font-semibold text-slate-950">{{ $faq->question }}</h3>
                            <details class="mt-2"><summary class="cursor-pointer text-sm font-medium text-amber-700">Lihat jawaban</summary><p class="mt-2 max-w-3xl whitespace-pre-line text-sm leading-6 text-slate-600">{{ $faq->answer }}</p></details>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                            <form method="POST" action="{{ route('admin.faq.move', $faq) }}" class="flex gap-1">@csrf <button name="direction" value="up" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:border-amber-300 hover:bg-amber-50" title="Naikkan urutan" aria-label="Naikkan urutan"><i class="fas fa-chevron-up text-xs"></i></button><button name="direction" value="down" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:border-amber-300 hover:bg-amber-50" title="Turunkan urutan" aria-label="Turunkan urutan"><i class="fas fa-chevron-down text-xs"></i></button></form>
                            <form method="POST" action="{{ route('admin.faq.toggle', $faq) }}">@csrf <button class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700 hover:border-amber-300 hover:bg-amber-50">{{ $faq->is_active ? 'Sembunyikan' : 'Tampilkan' }}</button></form>
                            <a href="{{ route('admin.faq.edit', $faq) }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700 hover:border-amber-300 hover:bg-amber-50">Ubah</a>
                            <form method="POST" action="{{ route('admin.faq.destroy', $faq) }}" onsubmit="return confirm('Hapus FAQ ini secara permanen?')">@csrf @method('DELETE')<button class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 px-3 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus</button></form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="px-6 py-16 text-center"><i class="fas fa-circle-question text-4xl text-slate-300" aria-hidden="true"></i><p class="mt-4 font-semibold text-slate-700">Belum ada FAQ yang sesuai</p><p class="mt-1 text-sm text-slate-500">Ubah filter atau tambahkan pertanyaan baru.</p></div>
            @endforelse
        </div>
        @if($faqs->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $faqs->links() }}</div>@endif
    </section>
</div>
@endsection
